$(ModuleCodesStart);

function ModuleCodesStart()
{
	$("input[type=file]").each(function(){
		if(!$(this).is("[fileid]"))
			return;
		
		$(this).fileeditor({
			containorId:"file_editor_main_container"
		});
		var file;
		var btnDelete;
		var fileImage;
		var fileName;
		
		var temp = $(this);
		var keyName = temp.attr("name");
		var fileValue = temp.attr("fileid");
		var fileId = parseInt(fileValue) > 0 ? parseInt(fileValue) : -1;
		var fileInput;
		
		temp.wrap('<div class="fileOuter">');
		file = temp.parent();
		
		var html  = '<img class="filethumb" src="" />';
			html += '<span class="fileName"></span>';
			html += '<span class="deleteFile button">Kaldır</span>';
			html += '<input class="fileInput" type="hidden" name="' + keyName + '" value="' + fileId + '" />';
			
		file.html(html);
		
		btnDelete = file.find(".deleteFile");
		fileImage = file.find(".filethumb");
		fileName  = file.find(".fileName");
		fileInput = file.find(".fileInput");
		
		/** EVENTS */
		
		file.mouseenter(function(){
			btnDelete.animate({"opacity":"1"},250);
		}).mouseleave(function(){
			btnDelete.animate({"opacity":"0"},250);
		});
		
		
		if(fileId <= 0)
		{
			fileImage.attr("src",exclamation_image).css({"border":"none","width":125,"height":89});
			fileName.html("Dosya Bulunamadı!");
		}
		else
		{
			btnDelete.css("display","block");
			$.ajax({
				data:"admin_action=getBrowserThumbInfo&fileId=" + fileId,
				dataType:"json",
				success:function(response){
					if((response.url != undefined) && (response.url != ""))
					{
						fileImage.attr("src", response.url);
						fileName.html(response.owner.basename);
					}
					else
					{
						fileImage.attr("src",exclamation_image);
						fileName.html("Dosya Bulunamadı!");
					}
				}
			});
		}
		
		fileImage.click(function(){
			$(this).openFileEditor({
				containorId:"file_editor_main_container",
				multiSelection:false,
				onSelect:function(data){
					fileInput.val(data[0].file_id);
					fileImage.attr("src",data[0].url);
					fileName.html(data[0].name);
					btnDelete.css("display","block");
				}
			});
		});
		
		btnDelete.click(function(){
			fileImage.attr("src",exclamation_image);
			fileName.html("Dosya Bulunamadı!");
			fileInput.val("-1");
			$(this).css("display","none");
		});
		
		/****************************************************************/
	});
}