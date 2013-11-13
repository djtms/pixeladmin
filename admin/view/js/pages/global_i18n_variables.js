$(GlobalI18nVariablesStart);

function GlobalI18nVariablesStart()
{
	var spreadsheetCellChanged = false;
	
	$(".spreadsheetOuter").each(function(){
		var spreadsheetOuter = $(this);
		var spreadsheetContent = spreadsheetOuter.find(".spreadsheetContent");
		var spreadsheetContentDisplay = spreadsheetOuter.find(".spreadsheetContentDisplay");
		var totalWidth = 0;
		var total_column_count = spreadsheetContent.find(".column").length;
		var has_empty_row = false;
		
		// spreadsheetContent ve spreadsheetHeader'ın genişliklerini hesapla 
		spreadsheetOuter.find(".spreadsheetContent:first .column").each(function(){
			totalWidth += $(this).outerWidth(true);
		});
		spreadsheetOuter.find(".spreadsheetContent, .spreadsheetHeader").css("width", totalWidth);
		
		// Scroll eventini bağla
		var contentScroller = spreadsheetOuter.find(".spreadsheetContentScroller");
		var spreadsheetHeader = spreadsheetOuter.find(".spreadsheetHeader");
		contentScroller.scroll(function(e){
			var marginLeft = $(this).scrollLeft();
			spreadsheetHeader.css("margin-left", -marginLeft);
		});
		//------------------------------------------------------------------
		
		$(window).bind("beforeunload", function(){
			// Eğer kaydedilmemiş hücre varsa onu kaydet.
			if(spreadsheetCellChanged)
			{
				$(document.activeElement).trigger("blur");
			}
		});
		
		// Input ların eventini bağla
		$(this).find(".spreadsheetContent input[type='text']").live("blur",function(){
			spreadsheetOuter.find(".cellSelected").removeClass("cellSelected rowSelected");
			
			// Eğer input değişmemişse işlemi tamamlama
			if(!spreadsheetCellChanged)
				return true;
			
			var i18n_code = $(this).attr("i18n_code");
			var add_new_data = i18n_code == "" ? true : false;
			var value = encodeURIComponent($(this).val());
			var column_name = $(this).attr("column_name");
			
			$.ajax({
				type:"post",
				url:"admin.php?page=global_i18n_variables",
				data:"admin_action=updateI18nData&i18n_code=" + i18n_code + "&column=" + column_name + "&value=" + value + "&add_new_data=" + add_new_data,
				dataType:"json",
				async: false,
				success:function(response){
					if(response.success === true)
					{
						// Eğer i18nCode değeri değişti ise
						if((column_name == "i18nCode") && ((i18n_code != value) || (i18n_code == "")))
						{
							$("input[i18n_code='" + i18n_code + "']", spreadsheetOuter).each(function(){
								$(this).attr("i18n_code", value);
								
								// Eğer yeni eklediğimiz satırın tüm kolonlarındaki inputların blur eventini
								// kullanıyorsak burdaki id değeri aşağıdaki şekilde hesaplanmalı.
								var id = value + "_" + ((i18n_code == "") ? $(this).attr("column_name") : column_name);
								$(this).attr("id", id);
							});
						}
						
						// Eğer yeni bir satır eklemişsek
						if((column_name == "i18nCode") && (i18n_code == ""))
						{
							has_empty_row = false;
							
							// Eğer yeni bir satır eklemişsek i18n_code değeri inputlara daha yeni yani burada bağlandı demektir.
							// bu yüzden yeni eklediğimiz satırın inputlarına girdiğimiz değerler eğer i18nCode kolonundaki 
							// input'u daha önce doldurmadıysak kaydedilmeyecektir. İşte tamda burada aktif olan satıradaki tüm 
							// inputların i18n_code değerlerini atadığımız veya güncellediğimiz için daha önce i18n_code değeri 
							// boş olan inputların "blur" eventini kullanarak yeni satırın tüm inputlarındaki değerleri kaydedebilme 
							// şansımız oluyor.
							$("input[i18n_code='" + value + "']", spreadsheetOuter).each(function(){
								$(this).trigger("input").trigger("blur");
							});
						}
					}
					else
					{
						// Eğer i18n kodu değiştirilecek idiyse, hata oluştuğu için eski i18ncode değerini geri yazdırıyoruz.
						if((column_name == "i18nCode") && (i18n_code != value))
						{
							$("#" + i18n_code + "_" + column_name).val(i18n_code);
						}

                        MESSAGEBOX.showMessage("Hata", response.msg);
					}
				},
				complete: function(){
					spreadsheetCellChanged = false;
				}
			});
		})
		.live("focus", function(){
			var connectedElementId = $(this).attr("id");
			var maxlength = $(this).attr("maxlength");
			var text = $(this).val();
			spreadsheetContentDisplay.attr("connectedElementId", connectedElementId);
			spreadsheetContentDisplay.attr("maxlength", maxlength);
			spreadsheetContentDisplay.val(text);
			
			// Stil ataması yap
			spreadsheetOuter.find(".cellSelected").removeClass("cellSelected");
			spreadsheetOuter.find(".rowSelected").removeClass("rowSelected");
			$(this).addClass("cellSelected");
		})
		.live("input",function(){ // Inputlar değiştiğinde trigger olacak event
			spreadsheetContentDisplay.val($(this).val()); // İçerik değiştiğinde alttaki textarea nında içeriğini değiştir
			spreadsheetCellChanged = true;
		})
		.live("keyup",function(e){
			//var row_index = parseInt($(this).attr("row_index"), "10");
			
			if(e.keyCode == 38) // Kullanıcı yukarı ok tuşuna basıyorsa
			{
				$(this).prev().focus().addClass("cellSelected");
			}
			else if(e.keyCode == 40) // Kullanıcı aşağı ok tuşuna basıyorsa
			{
				if($(this).is(":last-child"))
					spreadsheetOuter.trigger("addRow");
				
				$(this).next().focus().addClass("cellSelected");
			}
			else if(e.keyCode == 27)
			{
				// Eğer en az 2 satır varsa
				if(spreadsheetContent.find(".cell.rowCorner").length > 1)
				{
					// boş satırı sil
					spreadsheetOuter.trigger("removeEmptyRow");
				}
			}
		});
		//------------------------------------------------------------------
		
		// Alttaki büyük textarea'nın eventleri
		spreadsheetContentDisplay.blur(function(){ // textarea inaktif duruma geçtiğinde ona bağlı eventinde blur eventini trigger et
			var connectedElementId = $(this).attr("connectedElementId");
			var value = $(this).val();
			$("#" + connectedElementId).blur();
			//  maxlength attribute'unu kaldır
			$(this).removeAttr("maxlength");
		}).bind("input",function(){ // textareanın içeriği değiştiğinde trigger olacak event
			var connectedElementId = $(this).attr("connectedElementId");
			var value = $(this).val();
			$("#" + connectedElementId).val(value);
			spreadsheetCellChanged = true;
		});
		
		
		// Tüm satırı seçme butonuna tıkladığında çalışacak eventi ayarla
		$(".rowCorner").live("click",function(){
			var rowIndex = $(this).attr("row_index");
			$(this).addClass("rowSelected");
			
			spreadsheetOuter.find(".cellSelected").removeClass("cellSelected rowSelected");
			spreadsheetOuter.find("input[row_index='" + rowIndex + "']").addClass("cellSelected rowSelected");
		});
		
		// Yeni satır ekleme ve silme eventleri
		spreadsheetOuter.bind("addRow",{action:"add"},addRemoveEmptyRowEvent);
		spreadsheetOuter.bind("removeEmptyRow", {action:"remove"},addRemoveEmptyRowEvent);
		spreadsheetOuter.bind("removeExistingRow", removeExistingRowEvent);
		
		$(document).keydown(function(e){ 
			// Eğer delete tuşuna basılmışsa ve seçili bir satır varsa
			if((e.keyCode == 46) && (spreadsheetOuter.find(".rowSelected").length > 0))
			{
				if(confirm("Silmek istediğinizden eminmisiniz?"))
				{
					spreadsheetOuter.trigger("removeExistingRow");
				}
			}
		});
		
		// Eğer hiç satır yok ise
		if(spreadsheetContent.find(".column input[type='text']").length <= 0)
		{
			spreadsheetOuter.trigger("addRow");
		}
		
		function removeExistingRowEvent()
		{
			var i18nCodes = new Array();
			$(".rowSelected[column_name='i18nCode']").each(function(){
				i18nCodes.push($(this).val());
			});
			
			$.ajax({
				type:"post",
				url:"admin.php?page=global_i18n_variables",
				data:"admin_action=deleteI18nDatas&i18nCodesList=" + JSON.encode(i18nCodes),
				dataType:"json",
				success:function(response){
					if(response.success === true)
					{
						spreadsheetOuter.trigger("removeEmptyRow");
						spreadsheetContent.find(".rowSelected").remove();
						
						// Eğer hiç satır yok ise boş satır ekle
						if(spreadsheetContent.find(".column input[type='text']").length <= 0)
						{
							spreadsheetOuter.trigger("addRow");
						}
					}
				}
			});
		}
		
		function addRemoveEmptyRowEvent(e)
		{
			var action = e.data.action;
			
			// Daha önce boş yeni satır eklenmişmi onu kontrol et.
			if(!has_empty_row)
			{
				// Daha önce yeni satır eklenmemişse tablodaki son satırın tamamen boş olup olmadığını kontrol et
				var last_row_index = spreadsheetContent.find(".rowCorner:last").attr("row_index");
				var selectedRow = spreadsheetContent.find("[row_index='" + last_row_index + "']");
				
				if(selectedRow.length > 0)
				{
					selectedRow.each(function(){
						if($(this).val() != "")
						{
							has_empty_row = false;
						}
					});
				}
				//-----------------------------------------------
			}
			
			
			spreadsheetContent.find(".column").each(function(){
				var column_index = $(this).index();
				
				var last_cell = $(this).find(".cell:last");
				var row_index = last_cell.attr("row_index");
				row_index = row_index > 0 ? row_index : 0;
				
				// Eğer son satırın  en az bir hücresi dolu ise yeni satır oluştur
				if((action == "add") && !has_empty_row)
				{
					if(column_index == 0)
					{
						$(this).append("<span row_index='" + (row_index + 1) + "' class='cell rowCorner'></span>");
					}
					else
					{
						if(last_cell.length > 0)
						{
							var tabindex = parseInt(last_cell.attr("tabindex")) + total_column_count;
							var column_name = last_cell.attr("column_name");
						}
						else
						{
							var tabindex = (row_index * total_column_count) + column_index;
							var column_name = spreadsheetHeader.find(".header").eq(column_index).attr("column_name");
						}
						var maxlength_attr = column_name == "i18nCode" ? " maxlength='255' " : "";
						
						$(this).append("<input " + maxlength_attr + " tabindex='" + tabindex + "' row_index='" + (row_index + 1) + "' id='_" + column_name + "' column_name='" + column_name + "' i18n_code=''  type='text' class='cell' value='' />");
					}
				}
				else if((action == "remove") && has_empty_row) // son satır boş ise o satırı sil
				{
					last_cell.remove();
				}
			});
			
			if(action == "add")
				has_empty_row = true;
			else if(action == "remove")
				has_empty_row = false;
		}
		
	});
}