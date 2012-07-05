$(GlobalI18nVariablesStart);

function GlobalI18nVariablesStart()
{
	var spreadsheetCellChanged = false;
	
	$(".spreadsheetOuter").each(function(){
		var spreadsheetOuter = $(this);
		var spreadsheetContentDisplay = spreadsheetOuter.find(".spreadsheetContentDisplay");
		var totalWidth = 0;
		
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
		
		// Input ların eventini bağla
		$(this).find(".spreadsheetContent input[type='text']").blur(function(){
			// Eğer input değişmemişse işlemi tamamlama
			if(!spreadsheetCellChanged)
				return true;
			
			var i18n_code = $(this).attr("i18n_code");
			var value = $(this).val();
			var column_name = $(this).attr("column_name");
			
			$.ajax({
				type:"post",
				url:"admin.php?page=global_i18n_variables",
				data:"admin_action=updateI18nData&i18n_code=" + i18n_code + "&column=" + column_name + "&value=" + value,
				dataType:"json",
				async: false,
				success:function(response){
					if(response.success === true)
					{
						// Eğer i18nCode değeri değişti ise
						if((column_name == "i18nCode") && (i18n_code != value))
						{
							$("input[i18n_code='" + i18n_code + "']", spreadsheetOuter).each(function(){
								$(this).attr("i18n_code", value);
								$(this).attr("id", value + "_" + column_name);
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
						
						alert(response.msg);
					}
				},
				complete: function(){
					spreadsheetCellChanged = false;
				}
			});
		})
		.focus(function(){
			var connectedElementId = $(this).attr("id");
			var text = $(this).val();
			spreadsheetContentDisplay.attr("connectedElementId", connectedElementId);
			spreadsheetContentDisplay.val(text);
		})
		.bind("input",function(){ // Inputlar değiştiğinde trigger olacak event
			spreadsheetContentDisplay.val($(this).val()); // İçerik değiştiğinde alttaki textarea nında içeriğini değiştir
			spreadsheetCellChanged = true;
		});
		
		//------------------------------------------------------------------
		
		// Alttaki büyük textarea'nın eventleri
		spreadsheetContentDisplay.blur(function(){ // textarea inaktif duruma geçtiğinde ona bağlı eventinde blur eventini trigger et
			var connectedElementId = $(this).attr("connectedElementId");
			var value = $(this).val();
			$("#" + connectedElementId).blur();
		}).bind("input",function(){ // textareanın içeriği değiştiğinde trigger olacak event
			var connectedElementId = $(this).attr("connectedElementId");
			var value = $(this).val();
			$("#" + connectedElementId).val(value);
			spreadsheetCellChanged = true;
		});
		
	});
}