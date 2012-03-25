jQuery.fn.editfile = function(properties){
	
	var defaultoptions = {
			file:{},
			onSaved:function(){},
			onInit:function(){}
	};
	
	return $(this).each(function(){
	
		var options = $.extend(defaultoptions,properties);
		options.onInit();
		var uniqueId = $(this).index();
		var file = options.file;
		
		var editHtml = '<div id="' + "editFile_" + uniqueId + '" class="editFileOuter">';
		editHtml += '<div class="editFileBackHider"></div>';
		editHtml += '<div class="editFileContentsOuter">';
		editHtml += '<div class="fileThumbArea">';
		editHtml += '<div class="fileThumbOuter">';
		editHtml += '<img class="fileThumb" src="" />';
		editHtml += '<a class="lookAtFile fancybox" href="lookfile.php?type=' + file.type + '&url=' + MHA.encodeUTF8(file.url) + '"></a>';
		editHtml += '</div>';
		
		if(file.type != "image")
			editHtml += '<input type="button" class="btnChangeLogo" value="Değiştir" />';
		
		editHtml += '</div>';
		editHtml += '<div class="fileInfosOuter">';
		editHtml += '<form class="infoForm" onsubmit="return false;">';
		editHtml += '<input class="fileId" type="hidden" name="file_id" value="' + file.file_id + '" />';
		editHtml += '<input class="extension" type="hidden" name="extension" value="' + file.extension + '" />';
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
		editHtml += '<input type="button" class="btnSave" value="Kaydet" />';
		editHtml += '<input type="button" class="btnCancel" value="İptal" />';
		editHtml += '<img class="loader" src="' + VIEW_URL + 'images/fileeditor/editfileloader.gif" />';
		editHtml += '<span class="resultText"></span>';
		editHtml += '</form>';
		editHtml += '</div>';
		editHtml += '</div>';
		editHtml += '</div>';
		
		$("body").append(editHtml);
		
		var editFileEditor = $("#editFile_" + uniqueId);
		var editFileBackHider = editFileEditor.find(".editFileBackHider");
		var editFileEditorContents = editFileEditor.find(".editFileContentsOuter");
		var fileThumb = editFileEditor.find(".fileThumb");
		var btnLookAtFile = editFileEditor.find(".lookAtFile");
		var btnChangeLogo = editFileEditor.find(".btnChangeLogo");
		var fileId = editFileEditor.find(".fileId");
		var thumbFileId = editFileEditor.find(".thumbFileId");
		var btnSave = editFileEditor.find(".btnSave");
		var btnCancel = editFileEditor.find(".btnCancel");
		var infoForm = editFileEditor.find(".infoForm");
		var thumbFileId = editFileEditor.find(".thumbFileId");
		var loader = editFileEditor.find(".loader");
		var filename = editFileEditor.find(".filename");
		var extension = editFileEditor.find(".extension");
		var basename = editFileEditor.find(".basename");
		var url = editFileEditor.find(".url");
		var resultText = editFileEditor.find(".resultText");
		
		var filenameLastValue = filename.val();
		
		btnLookAtFile.fancybox();
		btnSave.click(saveFile);
		btnCancel.click(closeDetailsEditor);
		btnChangeLogo.click(changeLogo);
		filename.keyup(fixUrl);
		
		openDetailsEditor();
		
		function fixUrl()
		{
			var urlText = url.val();
			var newName = MHA.fixStringForWeb($(this).val());
	
			basename.val(newName + "." + extension.val());
			
			var reg = new RegExp(MHA.quote(filenameLastValue) + "(\.[a-zA-Z0-9\_\-]*)$");
			
			url.val(urlText.replace(reg,newName + "$1"));
			filenameLastValue = newName;
		}
		
		function openDetailsEditor()
		{
			$.ajax({
				data:"admin_action=getFileDetailThumb&fileId=" + fileId.val(),
				success:function(response){
					fileThumb.attr("src",response);
				}
			});
			
			editFileBackHider.animate({opacity:"0.6"},500,function(){
				editFileEditorContents.animate({opacity:"1"},500);
			});
		}
		
		function changeLogo()
		{
			$(this).fileeditor({
				z_index:10000,
				multiSelection:false,
				listFileTypes:"image",
				containorId:"file_editor_editfile_container",
				uploaderId:"file_editfile_uploader",
				queueId:"browserEditFilesQueue"
			});
			
			$(this).openFileEditor({
				containorId:"file_editor_editfile_container",
				z_index:10000,
				filesEditable:false,
				multiSelection:false,
				uploaderId:"file_editfile_uploader",
				queueId:"browserEditFilesQueue",
				onSelect:function(files){
					var fileId = files[0].file_id;
					thumbFileId.val(fileId);
					
					$.ajax({
						data:"admin_action=getFileDetailThumb&fileId=" + fileId,
						success:function(response){
							fileThumb.attr("src",response);
						}
					});
				}});
		}
		
		function saveFile()
		{
			filename.val(MHA.fixStringForWeb(filename.val()));
			var basenameText = $.trim(filename.val());
			
			if(basenameText.length < 2 )
			{
				resultText.html("dosya ismi en az iki karakterden oluşmalı!");
			}
			else if(!validate.validateFilename(basenameText,true))
			{
				alert("lütfen uygun klasör ismi girin! \n * dosya ismi uzunluğu en az 1 karakterden oluşmalıdır! \n * dosya ismi nokta (.) karakteri ile başlayamaz! \n * dosya isminde \\,/,:,*,?,<,>,| karakterleri bulunamaz! ");
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