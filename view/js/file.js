(function($){
	var FILE_CLASS = function(action, properties){
		var file = $(action);
		var file_id, readonly, form_name;
		var objects = {};
		
		var events = {
			onChangeFile: function(){
				// Eğer dosya readonly olarak tanımlanmışsa edit eventini kullanma
				if(readonly){
					return false;
				}
				
				$(document).fileeditor("openFileEditor",{
					multiselection:false,
					onFilesSelect:function(files){
						file.attr("file",files[0].url);
						objects.fileinput.val(files[0].file_id);
						objects.btn_edit.attr("file", files[0].file_id);
						
						private_methods.setFileInfo(files[0]);						
					}
				});
			},
			onRemoveFile: function(){
				events.onFileNotFound();
				objects.fileinput.val("-1");
			},
			onFileNotFound: function(){
				objects.filethumb.attr("src",exclamation_image).css({"border":"none","width":125,"height":89});
				objects.filename.html("Dosya Bulunamadı!");
				objects.buttons_outer.css("visibility","hidden");
			}
		};
		
		var private_methods = {
			loadFileInfos: function(){
				if(file_id <= 0){
					events.onFileNotFound();
				}
				else{
					$.ajax({
						data:"admin_action=getFileInfoById&file=" + file_id,
						dataType:"json",
						success:function(response){
							if((response.url != undefined) && (response.url != ""))
							{
								private_methods.setFileInfo(response);
								
								objects.btn_edit.click(function(){
									var file_id = $(this).attr("file");
									
									$(this).editfile({
										file_id: file_id,
										onSaved:function(file){
											objects.filename.html(file.basename);
											
											if(file.type != "movie")
											{
												if(file.thumb != null)
												{
													objects.filethumb.attr("src", file.thumb);
												}
												objects.btn_look.attr("href",'lookfile.php?type=' + file.type + '&url=' + encodeURIComponent(file.url));
											}
											else
											{
												objects.btn_play.attr("href",'lookfile.php?type=' + file.type + '&url=' + encodeURIComponent(file.url));
											}
										}
									});
								});
							}
							else{
								events.onFileNotFound();
							}
						},
						error: events.onFileNotFound
					});
				}
			},
			setFileInfo: function(file){
				objects.filethumb.attr("src", file.thumb);
				objects.filename.html(file.basename);
				objects.buttons_outer.css("visibility","visible");
				
				if(file.type != "movie")
				{
					objects.btn_look.attr("href",'lookfile.php?type=' + file.type + '&url=' + file.url);
					objects.btn_look.fancybox({
						"titleShow":false,
						"scrolling":"no"
					});
					
					objects.btn_play.css("display","none");
				}
				else
				{
					objects.btn_play.attr("href",'lookfile.php?type=' + file.type + '&url=' + file.url);
					objects.btn_look.css("display","none");
				}
			}
		};
		
		var public_methods = {
			initialize: function(){
				// ilk değerleri al
				file_id = file.attr("fileid");
				readonly = file.is("[readonly]") ? true : false;
				form_name = file.attr("name");
				
				// wrap işlemi uygula
				file.wrap('<div class="fileOuter">');
				file = file.parent();
				
				// view'i oluştur
				var view  = '<img class="filethumb" src="" />';
					view += (readonly ? "" : '<span class="button">Değiştir</span>');
					view += '<span class="fileName"></span>';
					view += '<span class="fileButtonsOuter">';
					view += (readonly ? "" : '<span class="editButton fBtn" title="Düzenle" file="' + file_id + '"></span>');
					view += '<a class="lookatButton fancybox fBtn" href="" title="İncele"></a>';
					view += '<a class="playButton fancybox fBtn" href="" title="Oynat"></a>';
					view += (readonly ? "" : '<span class="deleteFile fBtn" title="Kaldır"></span>');
					view += '</span>';
					view += '<input class="fileInput" type="hidden" name="' + form_name + '" value="' + file_id + '" />';
					
				file.html(view);
				//-----------------------------------------------------------------------------
				
				// gerekli html taglerini seç
				objects.buttons_outer = file.find(".fileButtonsOuter");
				objects.btn_edit = file.find(".editButton");
				objects.btn_look = file.find(".lookatButton");
				objects.btn_play = file.find(".playButton");
				objects.btn_delete = file.find(".deleteFile");
				objects.btn_change = file.find(".button");
				objects.filethumb = file.find(".filethumb");
				objects.filename  = file.find(".fileName");
				objects.fileinput = file.find(".fileInput");
				//--------------------------------------------------
				
				private_methods.loadFileInfos();
				objects.filethumb.click(events.onChangeFile);
				objects.btn_change.click(events.onChangeFile);
				objects.btn_delete.click(events.onRemoveFile);
			} 
		};
		
		return public_methods;
	};
	
	$.fn.file = function(action, value){
		return this.each(function(){
			if((typeof(action) === "object") || typeof(action) === "function" || !action){
				var FILE = new FILE_CLASS($(this));
				FILE.initialize();
				
				$(this).data("filedata", FILE);
			}
			else if(FILE_CLASS[action]){
				var FILE = $(this).data("filedata");
				FILE[action].call(this, properties);
			}
			else{
				MESSAGEBOX.show("Hata", action + " method'u bulunamadı!");
			}
		});
	};
}(jQuery));

$(FileStart);



function FileStart()
{
	$("input[type=file][fileid]").file();
}
