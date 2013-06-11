/*
 * Author: Mehmet Hazar Artuner
 */

(function($){
	$.fn.editfile = function(properties){
		
		var defaultoptions = {
				file_id:-1,
				onSaved:function(){},
				onInit:function(){}
		};
		

		return $(this).each(function(){
		
			var options = $.extend({}, defaultoptions, properties);
			options.onInit();
			var uniqueId = $(this).index();
			var file = ADMIN.getFileInfoById(options.file_id);
			var resizeWidth;
			var resizeHeight;
			
			if(file === false)
			{
				MESSAGEBOX.showMessage("Hata", "Dosya bulunamadı!");
				return false;
			}
			
			var editHtml = '<div id="' + "editFile_" + uniqueId + '" class="editFileOuter">';
			editHtml += '<div class="editFileBackHider"></div>';
			editHtml += '<div class="editFileContentsOuter">';
			editHtml += '<div class="fileThumbArea">';
			editHtml += '<div class="fileThumbOuter">';
			editHtml += '<img class="fileThumb" src="" />';
			editHtml += '<div class="thumbLoaderOuter"><img class="thumbloader" src="' + VIEW_URL + '/components/fileeditor/images/thumbloader.gif" /></div>';
			editHtml += '<div class="fileThumbButtonsOuter">';
			
			
			if(file.type == "image")
			{
				editHtml += '<a class="fancybox button" href="lookfile.php?type=' + file.type + '&url=' + ADMIN.encodeUTF8(file.url) + '">Görüntüle</a>';
				editHtml += '<button class="btnEditPicture">Resmi Düzenle</button>';
				editHtml += '<ul class="editfilePopupMenu">';
				editHtml += '<li class="btnRotateImage">Resmi Döndür &nbsp; &rsaquo;';
				editHtml += '<ul>';
				editHtml += '<li class="btnRotateRight">Sağa Döndür</li>';
				editHtml += '<li class="btnRotateLeft">Sola Döndür</li>';
				editHtml += '</ul>';
				editHtml += '</li>';
				editHtml += '<li class="btnOpenCrop">Resmi Kırp</li>';
				editHtml += '</ul>';
			}
			else if(file.type == "movie")
			{
				editHtml += '<a class="fancybox button" href="lookfile.php?type=' + file.type + '&url=' + ADMIN.encodeUTF8(file.url) + '">İzle</a>';
				editHtml += '<div class="changeLogoOuter">';
				editHtml += '<button>Logo\'yu Değiştir</button>';
				editHtml += '<input id="thumbfile" type="file" name="thumbfile" />';
				editHtml += '</div>';
			}
			else
			{
				editHtml += '<input type="button" class="btnChangeLogo" value="Değiştir" />';
			}
			editHtml += '</div>';
			editHtml += '</div>';
			editHtml += '</div>';
			editHtml += '<div class="fileInfosOuter">';
			editHtml += '<form class="infoForm" onsubmit="return false;">';
			editHtml += '<input class="fileId" type="hidden" name="file_id" value="' + file.file_id + '" />';
			editHtml += '<input class="extension" type="hidden" name="extension" value="' + file.extension + '" />';
			editHtml += '<input class="directory" type="hidden" name="directory" value="' + file.directory + '" />';
			editHtml += '<input class="basename" type="hidden" name="basename" value="' + file.basename + '" />';
			editHtml += '<input class="thumbFileId" type="hidden" name="thumb_file_id" value="' + file.thumb_file_id + '" />';
			editHtml += '<label style="margin-top:0;">Dosya Adı:</label>';
			editHtml += '<input class="filename" type="text" name="filename" value="' + file.filename + '" />';
			editHtml += '<label>Url:</label>';
			editHtml += '<input class="url" type="text" name="url" readonly="readonly" value="' + file.url + '" />';
			editHtml += '<label>Türü:</label>';
			editHtml += '<input type="text" readonly="readonly" value="' + file.type + '" />';
			editHtml += '<label>Boyutu:</label>';
			editHtml += '<input type="text" readonly="readonly" value="' + file.size + '" />';
			editHtml += '<label>Oluşturulma Tarihi:</label>';
			editHtml += '<input type="text" readonly="readonly" value="' + file.creation_time + '" />';
			editHtml += '<label>Son Güncelleme Tarihi:</label>';
			editHtml += '<input type="text" readonly="readonly" value="' + file.last_update_time + '" />';
			editHtml += '</form>';
			// FILE CROP INFO PANEL -------------------------------------------------------------------------------------------
			editHtml += '<div class="cropInfoPanel">';
			editHtml += '<div class="relative">';
			editHtml += '<form class="cropInfoForm">';
			editHtml += '<div class="predefinedResOuter">';
			editHtml += '<label>Çözünürlükler</label>';
			editHtml += '<select class="predefined_res" name="predefined_res">';
			
			for(var i=0; i<predefinedCropResoluions.length; i++)
			{
				editHtml += '<option value="' + i + '">' + predefinedCropResoluions[i][0] + " x " + predefinedCropResoluions[i][1] + '</option>';
			}
			
			editHtml += '<option value="-1">Diğer</option>';
			editHtml += '</select>';
			editHtml += '</div>';		
			editHtml += '<div class="custom_res_info">';
			editHtml += '<label>Genişlik</label>';
			editHtml += '<label class="space"></label>';
			editHtml += '<label>Yükseklik</label>';
			editHtml += '<br clear="all" />';
			editHtml += '<input type="text" name="width" value="0" />';
			editHtml += '<label class="space">X</label>';
			editHtml += '<input type="text" name="height" value="0" />';
			editHtml += '</div>';
			editHtml += '';
			editHtml += '';
			editHtml += '</form>';
			
			editHtml += '<label>Varolan Çözünürlükler</label>';
			editHtml += '<div class="existingResolutionsOuter">';
			editHtml += '<ul>';
			editHtml += '</ul>';
			editHtml += '<div class="loader"><img src="' + VIEW_URL + 'components/fileeditor/images/thumblistloader.gif" /></div>';
			editHtml += '</div>';
			editHtml += '<div>';
			editHtml += '</div>';
			
			//------------------------------------------------------------------------------------------------------------------
			
			
			
			editHtml += '</div>';
			editHtml += '</div>';
			editHtml += '</div>';
			editHtml += '<div class="saveSubmitButtonsOuter">';
			editHtml += '<div class="relative">';
			
			editHtml += '<div class="infoFormButtons formButtons">';
			editHtml += '<input type="button" class="btnSave" value="Kaydet" />';
			editHtml += '<input type="button" class="btnCancel" value="İptal" />';
			editHtml += '</div>';
			
			editHtml += '<div class="cropFormButtons formButtons">';
			editHtml += '<input type="button" class="btnCloseCrop" value="Bitir" />';
			editHtml += '<input type="button" class="btnCrop" value="Kırp" />';
			editHtml += '</div>';
			
			editHtml += '</div>';
			editHtml += '<img class="editfileloader" src="' + VIEW_URL + 'components/fileeditor/images/editfileloader.gif" />';
			editHtml += '<span class="resultText"></span>';
			editHtml += '</div>';
			
			$("body").append(editHtml);
			
			var JCROP; 
			var bigThumbUrl;
			var smallThumbUrl = null;
			var editFileEditor = $("#editFile_" + uniqueId);
			var editFileBackHider = editFileEditor.find(".editFileBackHider");
			var editFileEditorContents = editFileEditor.find(".editFileContentsOuter");
			var fileThumb = editFileEditor.find(".fileThumb");
			var btnLookAtFile = editFileEditor.find(".lookAtFile");
			var btnChangeLogo = editFileEditor.find(".btnChangeLogo");
			var btnOpenCrop = editFileEditor.find(".btnOpenCrop");
			var btnCloseCrop = editFileEditor.find(".btnCloseCrop");
			var fileId = editFileEditor.find(".fileId");
			var thumbFileId = editFileEditor.find(".thumbFileId");
			var btnSave = editFileEditor.find(".btnSave");
			var btnCancel = editFileEditor.find(".btnCancel");
			var btnCrop = editFileEditor.find(".btnCrop");
			var infoForm = editFileEditor.find(".infoForm");
			var thumbFileId = editFileEditor.find(".thumbFileId");
			var loader = editFileEditor.find(".editfileloader");
			var filename = editFileEditor.find(".filename");
			var extension = editFileEditor.find(".extension");
			var basename = editFileEditor.find(".basename");
			var url = editFileEditor.find(".url");
			var resultText = editFileEditor.find(".resultText");
			var thumbLoaderOuter = editFileEditor.find(".thumbLoaderOuter");
			var fileThumbButtonsOuter = editFileEditor.find(".fileThumbButtonsOuter");
			var cropInfoPanel = editFileEditor.find(".cropInfoPanel");
			var fileThumbOuter = editFileEditor.find(".fileThumbOuter");
			var cropFormButtons = editFileEditor.find(".cropFormButtons");
			var infoFormButtons = editFileEditor.find(".infoFormButtons");
			var thumbListLoader = editFileEditor.find(".existingResolutionsOuter .loader");
			
			var filenameLastValue = filename.val();
			
			editFileEditor.find(".fancybox").fancybox();
			bindEvents();
			openDetailsEditor();
			prepareForChangeLogo();
			
			
			function initializeCrop()
			{
				fileThumbButtonsOuter.css("display","none");
				thumbLoaderOuter.css("display", "block");
				fileThumb.attr("src",file.url).bind("load",function(){
					$(this).Jcrop({
						boxWidth: 420,
				        boxHeight: 390
					},function(){
						JCROP = this;
						$(".fileThumb").each(function(){
							if($(this).css("display") != "none")
							{
								var left = (420 - parseInt($(this).width())) / 2;
								var top = (390 - parseInt($(this).height())) / 2;
								
								$(this).parent().css({"left":left, "top":top});
								thumbLoaderOuter.css("display", "none");
								cropInfoPanel.css("display","block");
								infoFormButtons.css("display", "none");
								cropFormButtons.css("display", "block");
								
								var width = parseInt(file.width) / 2;
								var height = parseInt(file.height) / 2;
								JCROP.setSelect([0,0,width,height]);
								$(".predefined_res").change();
								listCustomThumbs();
							}
						});
					});
				});
			}
			
			function bindEvents()
			{
				/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				var tempTimerId;
				$(".btnEditPicture").click(function(){$(".editfilePopupMenu").css({"display":"block", "opacity":"1"});}); 
				
				$(".editfilePopupMenu, .editfilePopupMenu ul").mouseenter(function(){
					$(".editfilePopupMenu").stop().css("display","block").animate({"opacity":1},200);
				}).mouseleave(function(){
					$(".editfilePopupMenu").stop().animate({"opacity":0},200,function(){ $(".editfilePopupMenu").css("display","none"); });
				});
				
				
				$(".btnRotateRight").click({"file_id":file.file_id, "degree":-90}, rotateImage);
				$(".btnRotateLeft").click({"file_id":file.file_id, "degree":90}, rotateImage);
				
				function rotateImage(e)
				{
					$(".editfilePopupMenu").css("display","none");
					thumbLoaderOuter.css("display","block");
					$.ajax({
						type:"post",
						data:"admin_action=rotateImage&file_id=" + e.data.file_id + "&degree=" + e.data.degree,
						dataType:"json",
						success:function(response){
							if(response.success === true)
							{
								fileThumb.attr("src",response.big_thumb);
								bigThumbUrl = response.big_thumb;
								smallThumbUrl = response.small_thumb;
							}
						},
						error: function(){
							MESSAGEBOX.showMessage("Hata", "Beklenmedik bir hata oluştu, lütfen tekrar deneyin!");
						},
						complete:function(){
							thumbLoaderOuter.css("display","none");
						}
					});
				}
				
				/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				
				btnSave.click(saveFile);
				btnCancel.click(closeDetailsEditor);
				btnOpenCrop.click(initializeCrop);
				filename.keyup(fixUrl);
				btnCloseCrop.click(function(){
					fileThumb.unbind("load").attr("src",bigThumbUrl);
					JCROP.destroy();
					fileThumbButtonsOuter.css("display","block");
					cropInfoPanel.css("display","none");
					infoFormButtons.css("display", "block");
					cropFormButtons.css("display", "none");
				});
				
				$(".predefined_res").change(function(){
					if($(this).val() == -1)
					{
						$(".custom_res_info").css("opacity", 1);
						$(".custom_res_info input").attr("disabled",false);
					}
					else
					{
						$(".custom_res_info").css("opacity", 0.5);
						$(".custom_res_info input").val(0).attr("disabled",true);
						var index = $(this).val();
						var width = predefinedCropResoluions[index][0];
						var height = predefinedCropResoluions[index][1];
						JCROP.setOptions({"aspectRatio": (width / height)});
						resizeWidth = width;
						resizeHeight = height;
					}
				});
				
				$(".custom_res_info input").click(function(){$(this).select();})
				.keyup(function(){
					width = $(".custom_res_info [name='width']").val();
					height = $(".custom_res_info [name='height']").val();
					resizeWidth = width;
					resizeHeight = height;
					JCROP.setOptions({"aspectRatio": (width / height)});
				});
				
				btnCrop.click(function(){
					var values = JCROP.tellSelect();
					thumbLoaderOuter.css("display", "block");
					$.ajax({
						type:"post",
						data:"admin_action=cropImage&file_id=" + file.file_id + "&left=" + values.x + "&top=" + values.y + "&crop_width=" + values.w + "&crop_height=" + values.h + "&resize_width=" + resizeWidth + "&resize_height=" + resizeHeight,
						success: function(response){
							listCustomThumbs();
						},
						complete:function(){
							thumbLoaderOuter.css("display", "none");
						}
					});
					
					JCROP.release();
				});
			}
			
			function listCustomThumbs()
			{
				thumbListLoader.css("display","block");
				$.ajax({
					type:"post",
					data:"admin_action=listCustomCroppedImages&file_id=" + file.file_id,
					dataType:"json",
					success:function(response){
						if(response.error === false)
						{
							var data = response.data;
							var list_thumb_url = response.list_thumb_url;
							var width = data.length * 109;
							var listHtml = "";
							for(var i=0; i<data.length; i++)
							{
								listHtml += '<li>';
								listHtml += '<img src="' + list_thumb_url + '" />';
								listHtml += '<span>' + data[i].width + " x " + data[i].height + '</span>';
								listHtml += '</li>';
							}
							
							$(".existingResolutionsOuter ul").html(listHtml).css("width", width);
						}
						
						thumbListLoader.css("display","none");
					},
					error:function(){
						thumbListLoader.css("display","none");
					}
				});
			}
			
			function fixUrl()
			{
				var urlText = url.val();
				var newName = ADMIN.fixStringForWeb($(this).val());
		
				basename.val(newName + "." + extension.val());
				
				var reg = new RegExp(ADMIN.quote(filenameLastValue) + "(\.[a-zA-Z0-9\_\-]*)$");
				
				url.val(urlText.replace(reg,newName + "$1"));
				filenameLastValue = newName;
			}
			
			function openDetailsEditor(){
				$.ajax({
					data:"admin_action=getFileDetailThumb&fileId=" + fileId.val(),
					dataType:"json",
					success:function(response){
						fileThumb.attr("src",response.thumb_url);
						bigThumbUrl = response.thumb_url;
					}
				});
				
				editFileBackHider.animate({opacity:"0.6"},500,function(){
					editFileEditorContents.animate({opacity:"1"},500);
				});
			}
			
			function prepareForChangeLogo(){
				var uploader = document.getElementById("thumbfile");
				if($("#thumbfile").length > 0)
				{
					uploader.onchange = function(e){
						thumbLoaderOuter.css("display","block");
						var reader = new FileReader();
						reader.onloadend = changeLogo;
						reader.readAsDataURL(uploader.files[0]);
					};
				}
			}
			
			function changeLogo(e){
				var file_id = $("[name='file_id']").val();
				var form = new FormData();
				var thumbfile = document.getElementById("thumbfile").files[0];
				var xhr = new XMLHttpRequest();
				form.append("admin_action","changeThumbnailExceptFileTypeIsImage");
				form.append("file_id", file_id);
				form.append("directory",$("[name='directory']").val());
				form.append("thumbfile", thumbfile);
				
				xhr.addEventListener("load", function(e){
					$.ajax({
						data:"admin_action=getFileDetailThumb&fileId=" + file_id,
						dataType:"json",
						success:function(response){
							bigThumbUrl = response.thumb_url;
							fileThumb.attr("src",bigThumbUrl);
							thumbFileId.val(response.thumb_file_id);
                            $("#browserFilesList [file_id='" + file_id + "']").find(".editorFileThumb").attr("src", response.editor_thumb);
						},
						complete:function(){
							thumbLoaderOuter.css("display","none");
						}
					});
				});
				
				xhr.open("POST", "admin.php?page=dashboard", true);
				xhr.send(form);
			}
			
			function saveFile(){
				filename.val(ADMIN.fixStringForWeb(filename.val()));
				var basenameText = $.trim(filename.val());
				
				if(basenameText.length < 2 )
				{
					resultText.html("dosya ismi en az iki karakterden oluşmalı!");
				}
				else if(!VALIDATE.validateFilename(basenameText,true))
				{
					MESSAGEBOX.showMessage("Uyarı", "Lütfen uygun klasör ismi girin! <br /> * dosya ismi uzunluğu en az 1 karakterden oluşmalıdır! <br /> * dosya ismi nokta (.) karakteri ile başlayamaz! <br /> * dosya isminde \\,/,:,*,?,<,>,| karakterleri bulunamaz! ");
				}
				else
				{
					loader.css("display","block");
					$.ajax({
						data:"admin_action=updateFileInfo&" + infoForm.serialize(),
						dataType:"json",
						success:function(response)
						{
							loader.css("display","none");
							if(response.error)
							{
								resultText.html(response.message);
							}
							else
							{
								resultText.html(response.message);
								file.thumb_file_id = thumbFileId.val();
								file.basename = basename.val();
								file.url = url.val();
								file.thumb = smallThumbUrl;
								options.onSaved(file);
								closeDetailsEditor();
							}
						}
					});
				}
			}
			
			function closeDetailsEditor()
			{
				editFileEditorContents.animate({opacity:"0"},500,function(){
					editFileBackHider.animate({opacity:"0"},500);
					editFileEditor.remove();
				});
			}
		});
	};
})(jQuery);