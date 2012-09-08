$(ModuleCodesStart);

function ModuleCodesStart()
{
	$("input[type=file]").each(function(){
		if(!$(this).is("[fileid]"))
			return;
		
		var file;
		var fileButtonsOuter;
		var btnEdit;
		var btnLook;
		var btnPlay;
		var btnDelete;
		var fileImage;
		var fileName;
		
		var temp = $(this);
		var readonly = temp.attr("readonly") ? true : false;
		var keyName = temp.attr("name");
		var fileValue = temp.attr("fileid");
		var fileId = parseInt(fileValue) > 0 ? parseInt(fileValue) : -1;
		var fileInput;
		
		temp.wrap('<div class="fileOuter">');
		file = temp.parent();
		
		var html  = '<img class="filethumb" src="" />';
			html += (readonly ? "" : '<span class="button">Değiştir</span>');
			html += '<span class="fileName"></span>';
			html += '<span class="fileButtonsOuter">';
			html += (readonly ? "" : '<span class="editButton fBtn" title="Düzenle" file="' + fileId + '"></span>');
			html += '<a class="lookatButton fancybox fBtn" href="" title="İncele"></a>';
			html += '<a class="playButton fancybox fBtn" href="" title="Oynat"></a>';
			html += (readonly ? "" : '<span class="deleteFile fBtn" title="Kaldır"></span>');
			html += '</span>';
			html += '<input class="fileInput" type="hidden" name="' + keyName + '" value="' + fileId + '" />';
			
		file.html(html);
		
		fileButtonsOuter = file.find(".fileButtonsOuter");
		btnEdit = file.find(".editButton");
		btnLook = file.find(".lookatButton");
		btnPlay = file.find(".playButton");
		btnDelete = file.find(".deleteFile");
		
		fileImage = file.find(".filethumb");
		fileName  = file.find(".fileName");
		fileInput = file.find(".fileInput");
		

		if(fileId <= 0)
		{
			fileImage.attr("src",exclamation_image).css({"border":"none","width":125,"height":89});
			fileName.html("Dosya Bulunamadı!");
			fileButtonsOuter.css("visibility","hidden");
		}
		else
		{
			$.ajax({
				data:"admin_action=getFileInfoById&file=" + fileId,
				dataType:"json",
				success:function(response){
					if((response.url != undefined) && (response.url != ""))
					{
						fileImage.attr("src", response.url);
						fileName.html(response.basename);
						
						if(response.type != "movie")
						{
							btnLook.attr("href",'lookfile.php?type=' + response.type + '&url=' + response.url);
							btnPlay.css("display","none");
						}
						else
						{
							btnPlay.attr("href",'lookfile.php?type=' + response.type + '&url=' + response.url);
							btnLook.css("display","none");
						}
						
						$(".fileOuter .fancybox").fancybox({
							"titleShow":false,
							"scrolling":"no"
						});
						
						fileButtonsOuter.css("visibility","visible");
						
						btnEdit.click(function(){
							var file_id = $(this).attr("file");
							
							$(this).editfile({
								file: file_id,
								onSaved:function(file){
									fileName.html(file.basename);
									
									if(file.type != "movie")
									{
										if(file.thumb != null)
										{
											fileImage.attr("src", file.thumb);
										}
										btnLook.attr("href",'lookfile.php?type=' + file.type + '&url=' + ADMIN.encodeUTF8(file.url));
									}
									else
									{
										btnPlay.attr("href",'lookfile.php?type=' + file.type + '&url=' + ADMIN.encodeUTF8(file.url));
									}
								}
							});
						});
					}
					else
					{
						fileImage.attr("src",exclamation_image);
						fileName.html("Dosya Bulunamadı!");
						fileButtonsOuter.css("visibility","hidden");
					}
				},
				error:function(){
					fileImage.attr("src",exclamation_image);
					fileName.html("Dosya Bulunamadı!");
					fileButtonsOuter.css("visibility","hidden");
				}
			});
		}
		
		$(".filethumb, .button",file).click(function(){
			// Eğer dosya readonly olarak tanımlanmışsa edit eventini kullanma
			if(readonly)
			{
				return false;
			}
			$(document).fileeditor("openFileEditor",{
				multiselection:false,
				onFilesSelect:function(data){
					fileInput.val(data[0].file_id);
					btnEdit.attr("file", data[0].file_id);
					fileImage.attr("src",data[0].thumb);
					file.attr("file",data[0].url);
					fileName.html(data[0].name);
					fileButtonsOuter.css("visibility","visible");
					
					if(data[0].type != "movie")
					{
						btnLook.attr("href",'lookfile.php?type=' + data[0].type + '&url=' + ADMIN.encodeUTF8(data[0].url)).css("display","inline-block");
						btnPlay.css("display","none");
					}
					else
					{
						btnPlay.attr("href",'lookfile.php?type=' + data[0].type + '&url=' + ADMIN.encodeUTF8(data[0].url)).css("display","inline-block");
						btnLook.css("display","none");
					}
				}
			});
		});
		
		
		btnDelete.click(function(){
			fileImage.attr("src",exclamation_image);
			fileName.html("Dosya Bulunamadı!");
			fileButtonsOuter.css("visibility","hidden");
			fileInput.val("-1");
		});
		/****************************************************************/
	});
}