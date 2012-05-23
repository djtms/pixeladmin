jQuery.fn.fileeditor = function(properties){
	
	var btnClosefileEditor;
	var browserBtnUseFiles;
	var browserFilesList;
	var browserFileItems;
	var fileEditorBackHider;
	var fileEditorOuter;
	var browserBtn_Home;
	var browserBtn_Fav;
	var browserBtn_Prev;
	var browserBtn_NewDir;
	var browserBtn_UploadFile;
	var browser_address;
	var browserFilesListOuter;
	var browserFavoritesList;
	var browserContentLoaderOuter;
	var tooltip;
	var browserDirectoriesOuter;
	var collisionDetector;
	var editor;
	var uploader;
	var queueId;
	var currentDirectory;
	
	return $(this).each(function(){	
		var options = $.extend({
					z_index:1000,
					onSelect:function(){}, // seçim işlemi yapıldıktan sonra
					multiSelection:true, // birden fazla dosya seçimi
					hideFileIds:{}, // editörde gizlenmesini istediğimiz dosyaların id'lerinden oluşan dizi
					listFileTypes:"all",
					containorId:"file_editor_main_container",
					uploaderId:"file_main_uploader",
					queueId:"browserFilesQueue",
					allowedFileExtensions:allowedFileExtensionsString,
					allowedFilesTitle:"Web Files",
					filesEditable:true
				}, properties);
		
		/* COLLISION DETECTOR VALUES */
		var deleteFilesList = new Array();

		var startPosX = 0;
		var startPosY = 0;
		var captureDrag = false;
		var currentPosX;
		var currentPosY;
		var collisionWidth = 0;
		var collisionHeight = 0;
		var filesAndFolders;
		
		if($("#" + options.containorId).length <= 0)
		{
			createEditorHtmlAndEvents();
		}
		else
		{
			editor = $("#" + options.containorId);
		}
		
		$.fn.extend({
			"openFileEditor":openFileEditor
		});
		
		btnClosefileEditor = editor.find(".btnClosefileEditor");
		browserBtnUseFiles = editor.find(".browserBtnUseFiles");
		browserFilesList = editor.find(".browserFilesList");
		fileEditorBackHider = editor.find(".fileEditorBackHider");
		fileEditorOuter = editor.find(".fileEditorOuter");
		browserBtn_Home = editor.find(".browserBtn_Home");
		browserBtn_Fav = editor.find(".browserBtn_Fav");
		browserBtn_Prev = editor.find(".browserBtn_Prev");
		browserBtn_NewDir = editor.find(".browserBtn_NewDir");
		browserBtn_UploadFile = editor.find(".browserBtn_UploadFile");
		browser_address = editor.find(".browser_address");
		browserFilesListOuter = editor.find(".browserFilesListOuter");
		browserFavoritesList = editor.find(".browserFavoritesList");
		browserContentLoaderOuter = editor.find(".browserContentLoaderOuter");
		tooltip = editor.find(".fileTooltip");
		browserDirectoriesOuter = editor.find(".browserDirectoriesOuter");
		collisionDetector = editor.find(".collisionDetector");
		
		bindEvents();
		
		function createEditorHtmlAndEvents()
		{
			$("body").append('<div id="' + options.containorId + '" class="editorOuter"></div>');
			
			editor = $("#" + options.containorId);
			editor.css("z-index",options.z_index);
			
			var editorHtml = '<div class="fileEditorBackHider"></div>';
				editorHtml += '<div class="fileEditorOuter">';
				editorHtml += '<div class="fileEditorOuter_InnerShell">';
				editorHtml += '<div class="fileEditorNavigationBar">';
				editorHtml += '	&nbsp;&nbsp;&nbsp; Dosya Editörü';
				editorHtml += '<span class="btnClosefileEditor">Editörü Kapat</span>';
				editorHtml += '</div><!-- fileEditorNavigationBar -->';
				editorHtml += '<div class="fileEditorContentsOuter">';
				editorHtml += '<div class="browserLeftCorner">';
				editorHtml += '<label class="labelFavorites browserBigTitle">Sık Kullanılanlar</label>';
				editorHtml += '<div class="browserFavoritesList"></div><!-- browserFavoritesList -->';
				editorHtml += '<label class="labelDirectory browserBigTitle">Dizinler</label>';
				editorHtml += '<div class="browserDirectoriesOuter"></div>';
				editorHtml += '</div>';
				editorHtml += '<div class="browserRightCorner">';
				editorHtml += '<div class="addressBarOuter">';
				editorHtml += '<label class="browserBigTitle">Adres:</label>';
				editorHtml += '<input class="browser_address" type="text" name="browser_address" readonly="readonly" />';
				editorHtml += '<a class="browserBtn_Home" href="" title="Ana Dizin"></a>';
				editorHtml += '<a class="browserBtn_Fav" href="" title="Sık Kullanılanlara Ekle"></a>';
				editorHtml += '<a class="browserBtn_Prev" href="" title="Geri Dön"></a>';
				editorHtml += '<a class="browserBtn_NewDir" href="" title="Yeni Klasör"></a>';
				editorHtml += '<a class="browserBtn_UploadFile" onclick="javascript:void(0);" title="Dosya Yükle">';
				editorHtml += '<input id="' + options.uploaderId + '" class="uploadFile" type="file" multiple="multiple" />';
				editorHtml += '</a>';
				
				
				editorHtml += '</div>';
				editorHtml += '<div class="browserSearchOuter">';
				editorHtml += '<label class="browserBigTitle">Arama:</label>';
				editorHtml += '<input type="text" name="browser_search" />';
				editorHtml += '</div>';
				editorHtml += '<div class="tempQueue" style="display:none;"></div>';
				editorHtml += '<div class="browserContentLoaderOuter">';
				editorHtml += '<div class="fileEditorLoader">';
				editorHtml += '<img src="' + VIEW_URL + '/images/fileeditor/browserLoader.gif" />';
				editorHtml += '</div><!-- fileEditorLoader -->';
				editorHtml += '</div><!-- browserContentLoaderOuter -->';
				editorHtml += '<div class="forTooltipVisibility">';
				editorHtml += '<div class="fileTooltip"></div>';
				editorHtml += '<div class="browserFilesListOuter">';
				editorHtml += '<form enctype="multipart/form-data" onsubmit="return false;">';
				editorHtml += '<ul id="' + options.queueId + '"  class="browserFilesList">';
				editorHtml += '<div class="collisionDetector"><div class="collisionDetectorBg"></div></div>';
				editorHtml += '</ul>';
				editorHtml += '</form>';
				editorHtml += '<div class="browserFilesListScroller""></div>';
				editorHtml += '</div><!-- browserFilesListOuter -->';
				editorHtml += '</div><!-- forTooltipVisibility -->';
				editorHtml += '<button class="browserBtnUseFiles disabled" type="button">Kullan</button>';
				editorHtml += '</div>';
				editorHtml += '</div><!-- fileEditorContentsOuter -->';
				editorHtml += '</div><!-- fileEditorOuter_InnerShell -->';
				editorHtml += '</div><!-- fileEditorHider -->';

			editor.append(editorHtml);
			
			$.ajax({
				data:"admin_action=loadFileTree",
				success:function(response){
					browserDirectoriesOuter.html(response);
				}
			});
		}
		
		function checkCollision(ctrlKey)
		{
			var areaLeft = parseInt(collisionDetector.css("left"));
			var areaRight = areaLeft + parseInt(collisionDetector.css("width"));
			var areaTop = parseInt(collisionDetector.css("top"));
			var areaBottom = areaTop + parseInt(collisionDetector.css("height"));
			var lastIndex = filesAndFolders.length - 1;
			
			if(!ctrlKey)
				filesAndFolders.removeClass("selected");
			
			filesAndFolders.each(function(){
				var thisPos = $(this).position();
				var thisLeft = thisPos.left + 20;
				var thisRight = 0;
				var thisTop = 0;
				var thisBottom = 0;
				
				if(!(areaRight > thisLeft))
				{
					return;
				}
				else
					thisRight = thisLeft + 145;
					
				if(!(areaLeft < thisRight))
				{
					return;
				}
				else
					thisTop = thisPos.top + 20;
					
				
				
				if(!(areaBottom > thisTop))
				{
					return;
				}
				else
					thisBottom = thisTop + 130;
				
				if(!(areaTop < thisBottom))
				{
					return;
				}
				else
				{
					if($(this).hasClass("selected"))
					{
						//$(this).removeClass("selected"); // Sorun çıkarıyor sonra bak
					}
					else
						$(this).addClass("selected");
				}
			});
		}
		
		function bindEvents()
		{
			browserFavoritesList.find("a").die("click").live("click",favouritesLinksEvent);
			
			editor.undelegate(".folder","dblclick").delegate(".folder","dblclick",function(){
				var dir = $(this).attr("url");
				editor.find(".directory a[href='" + dir + "']").click();
			});
			
			editor.die("dblclick").live("dblclick",useFilesEvent);
			
			browserBtn_Home.unbind("click").bind("click",homeButtonEvent);
			browserBtn_Fav.unbind("click").bind("click",favouritesButtonEvent);
			browserBtn_Prev.unbind("click").bind("click",previousDirectoryButtonEvent);
			browserBtn_NewDir.unbind("click").bind("click",createNewDirectoryEvent);
			$(document).unbind("keydown").bind("keydown",documentKeyDownEvent);
			editor.undelegate(".browserFilesListOuter","click").delegate(".browserFilesListOuter","click",fileSelectionEvent);
			editor.undelegate(".btnEdit","click").delegate(".btnEdit","click",function(){
				var thisObject = $(this);
				var thisParentObject =  $(this).closest(".file");
				var fileId = thisObject.attr("fileId");
				var thumbObject = thisParentObject.find(".filethumb");
				var fileName = thisObject.parent().find(".fileName");
				var btnLook = thisObject.parent().find(".btnLook");

				$.ajax({
					data:"admin_action=selectFileInfo&fileId=" + fileId,
					dataType:"json",
					success:function(response){
						thisObject.editfile({
							file:response,
							onInit:function(){
								browserFilesList.find("li").removeClass("selected");
							},
							onSaved:function(file){
								var url = MHA.encodeUTF8(file.url);
								fileName.html(file.basename);
								btnLook.attr("href",'lookfile.php?type=' + file.type + '&url=' + url);
								thisParentObject.attr("url", url);
								
								if(file.thumb != null)
								{	
									thumbObject.attr("src",file.thumb);
								}
								else
								{
									$.ajax({
										data:"admin_action=getBrowserThumb&fileId=" + file.file_id,
										success:function(response){
											thumbObject.attr("src",response);
										}
									});
								}
							}
						});
					}
				});
			});
			
			browserFilesListOuter.unbind("mousedown").bind("mousedown",function(e){
				e.preventDefault();
			});
			
			editor.undelegate(".btnDelete","click").delegate(".btnDelete","click",deleteFileEvent);
			editor.undelegate(".fileTree a","click").delegate(".fileTree a","click",listSubDirectories);
			btnClosefileEditor.die("click").live("click",closeFileEditor);
			browserBtnUseFiles.unbind("click").bind("click",useFilesEvent);
			
			/*COLLISION EVENTS START*/
			editor.undelegate(".browserFilesList","mousedown").delegate(".browserFilesList","mousedown",function(e){
				var areaPos = browserFilesList.offset();
				captureDrag = options.multiSelection;
				startPosX = e.pageX - areaPos.left;
				startPosY = e.pageY - areaPos.top;
				collisionDetector = editor.find(".collisionDetector"); // filesList her yenilendiğinde bu element baştan oluşturuluyor o yüzden her defasında aramak gerekiyor. çözüm ise html yapısını değiştirmek onada şu an vaktim yok ilerde bakarım.
				collisionDetector.css("display","block");
				filesAndFolders = browserFilesList.find(".folder,.file");
			});
			
			editor.unbind("mouseup").mouseup(function(){
				captureDrag = false;
				collisionDetector.css({"width":"0","height":"0","left":"-10px","top":"-10px","display":"none"});
			});
			
			editor.undelegate(".browserFilesList","mousemove").delegate(".browserFilesList","mousemove",function(e){
				if(!captureDrag)
					return;
				
				var areaPos = browserFilesList.offset();
				currentPosX = e.pageX - areaPos.left;
				currentPosY = e.pageY - areaPos.top;
				
				// I. BÖLGE
				if((currentPosX > startPosX) && (currentPosY > startPosY))
				{
					collisionWidth = currentPosX - startPosX;
					collisionHeight = currentPosY - startPosY;
					
					collisionDetector.css({
						"left":startPosX,
						"top":startPosY,
						"width":collisionWidth,
						"height":collisionHeight
					});
				}
				// II. BÖLGE
				else if((currentPosX < startPosX) && (currentPosY > startPosY))
				{
					collisionWidth = startPosX - currentPosX;
					collisionHeight = currentPosY - startPosY;
					
					collisionDetector.css({
						"left":currentPosX,
						"top":startPosY,
						"width":collisionWidth,
						"height":collisionHeight
					});
				}
				// III. BÖLGE
				else if((currentPosX > startPosX) && (currentPosY < startPosY))
				{
					collisionWidth = currentPosX - startPosX;
					collisionHeight = startPosY - currentPosY;
					
					collisionDetector.css({
						"left":startPosX,
						"top":currentPosY,
						"width":collisionWidth,
						"height":collisionHeight
					});
				}
				// IV. BÖLGE
				else if((currentPosX < startPosX) && (currentPosY < startPosY))
				{
					collisionWidth = startPosX - currentPosX;
					collisionHeight = startPosY - currentPosY;
					
					collisionDetector.css({
						"left":currentPosX,
						"top":currentPosY,
						"width":collisionWidth,
						"height":collisionHeight
					});
				}
				checkCollision(e.ctrlKey);
				checkIfAnyFileSelected();
			});
			/* COLLISION EVENTS END */
		}
		
		function openFileEditor(taken_opt)
		{
			options = $.extend({
					z_index:1000,
					onSelect:function(){}, // seçim işlemi yapıldıktan sonra
					multiSelection:true, // birden fazla dosya seçimi
					hideFileIds:{}, // editörde gizlenmesini istediğimiz dosyaların id'lerinden oluşan dizi
					listFileTypes:"all",
					containorId:"file_editor_main_container",
					uploaderId:"file_main_uploader",
					queueId:"browserFilesQueue",
					allowedFileExtensions:allowedFileExtensionsString,
					allowedFilesTitle:"Web Files",
					filesEditable:true
				},taken_opt);
			
			editor = $("#" + options.containorId);
			btnClosefileEditor = editor.find(".btnClosefileEditor");
			browserBtnUseFiles = editor.find(".browserBtnUseFiles");
			browserFilesList = editor.find(".browserFilesList");
			fileEditorBackHider = editor.find(".fileEditorBackHider");
			fileEditorOuter = editor.find(".fileEditorOuter");
			browserBtn_Home = editor.find(".browserBtn_Home");
			browserBtn_Fav = editor.find(".browserBtn_Fav");
			browserBtn_Prev = editor.find(".browserBtn_Prev");
			browserBtn_NewDir = editor.find(".browserBtn_NewDir");
			browser_address = editor.find(".browser_address");
			browserFilesListOuter = editor.find(".browserFilesListOuter");
			browserFavoritesList = editor.find(".browserFavoritesList");
			browserContentLoaderOuter = editor.find(".browserContentLoaderOuter");
			tooltip = editor.find(".fileTooltip");
			browserDirectoriesOuter = editor.find(".browserDirectoriesOuter");
			collisionDetector = editor.find(".collisionDetector");
			
			browserBtnUseFiles.addClass("disabled");
			browserContentLoaderOuter.css({"display":"block","opacity":"1"});
			
			editor.css({"display":"block","z-index":options.z_index}).animate({opacity:"1"},500,function(){
				browserFilesListOuter.animate({scrollTop: 0}, 300); // scroller'ı en yukarı al
				fileEditorOuter.css("display","block").animate({opacity:1},500,function(){
					browserBtn_Home.click();
					prepareForUpload();
					listFavouritedDirectories();
				});
			});
		}

		function closeFileEditor()
		{
			fileEditorOuter.animate({opacity:0},500,function(){
				$(this).css("display","none");
				editor.css("display","block").animate({opacity:0},500,function(){
					$(this).css("display","none");
				});
			});
		}
		
		function useFilesEvent(){
			if(browserBtnUseFiles.hasClass("disabled"))
			{
				return false;
			}
			var fileInfos = new Array();
			var selectedFiles = editor.find(".selected.file");
			var fileCount = selectedFiles.length;
			
			for(var i=0; i<fileCount; i++)
			{
				var selectedFile = selectedFiles.eq(i);
				var fileId = selectedFile.attr("fileId");
				var url = selectedFile.attr("url");
				var thumb_url = selectedFile.find(".filethumb").attr("src");
				var name = selectedFile.find(".fileName").html();
				var type = selectedFile.attr("filetype");
				selectedFile.removeClass("selected");
				
				fileInfos.push({"file_id":fileId, "thumb_url":thumb_url, "url":url, "name":name, "type":type});
			}
			
			options.onSelect(fileInfos);
			closeFileEditor();
		}

		function browseFiles(directory)
		{
			currentDirectory = directory;
			var treeObject = editor.find(".fileTree a[href='" + directory + "']");
			browserFavoritesList.find("a").removeClass("selected");
			browserFavoritesList.find("a[href='" + directory + "']").addClass("selected");
			
			/*Sık Kullanılanlar Butonunu Ayarla*/
			if(treeObject.hasClass("favourite"))
				browserBtn_Fav.addClass("active");
			else
				browserBtn_Fav.removeClass("active");
			
			browser_address.val(currentDirectory);
			browserContentLoaderOuter.css("display","block").animate({"opacity":1},300,function(){
				$.ajax({
					data:"admin_action=browseFiles&directory=" + directory,
					dataType:"json",
					success:function(result){
						
						var directories = result.directories;
						var files = result.files;				
						var directoriesHtml = "";
						var filesHtml = "";
						
						for(var i=0; i<directories.length; i++)
						{
							directoriesHtml += '<li class="folder" url="' + directories[i].directory + '">';
							directoriesHtml += '<img src="' + folder_image + '" />';
							directoriesHtml += '<span>' + directories[i].name + '</span></li>';
						}
						
						for(var i=0; i<files.length; i++)
						{
							var addFile = true;
							var fileId = files[i].file_id;
							
							if((options.hideFileIds.length > 0))
							{
								for(var j=0; j<options.hideFileIds.length; j++)
								{
									if(fileId == options.hideFileIds[j].id)
									{
										addFile = false;
										break;
									}
								}
							}
							
							if(!addFile)
								continue;
							
							if(options.listFileTypes == "all" || (options.listFileTypes == files[i].type ) )
							{
								filesHtml += '<li class="file" filetype="' + files[i].type + '" title="Dosya Adı: ' + files[i].basename + '" url="' + files[i].url + '" fileId="' + files[i].file_id + '">';
								filesHtml += '<span class="filethumbOuter" fileId="' + fileId + '">';
								filesHtml += '<img class="filethumb" src="' + files[i].browser_thumb  + '" />';
								filesHtml += '</span>';
								filesHtml += '<span class="fileEditButtonsOuter">';
								
								if(options.filesEditable)
								{
									filesHtml += '<span title="Düzenle" class="btnEdit fBtn" fileId="' + files[i].file_id + '"></span>';
								}
								
								if(files[i].type != "other")
								{
									filesHtml += '<a title="' + (files[i].type == "movie" ? "Oynat" : "İncele") + '" class="' + (files[i].type == "movie" ? "btnPlay" : "btnLook") + ' fancybox fBtn" href="lookfile.php?type=' + files[i].type + '&url=' + MHA.encodeUTF8(files[i].url) + '"></a>';
								}
								
								filesHtml += '<span title="Sil" class="btnDelete fBtn"></span>';
								filesHtml += '</span>';
								filesHtml += '<span class="fileName">' + files[i].basename + '</span>';
								filesHtml += '</li>';
							}
						}
						
						browserFilesList.html( '<div class="collisionDetector"><div class="collisionDetectorBg"></div></div>' + directoriesHtml + filesHtml);
						browserFileItems = browserFilesList.find("li");
						
						$(".file .fancybox").fancybox({
							"titleShow":false,
							"scrolling":"no"
						});
						
						setTimeout(function(){
							browserContentLoaderOuter.animate({"opacity":0},500,function(){
								$(this).css("display","none");
							});
						}, 300);
					}
				});
			});
		}
		
		function loadFileThumbs(fileIndex)
		{
			var fileThumbOuters = browserFilesList.find(".filethumbOuter");
			var maxFileLength = fileThumbOuters.length;
			var outerObject = fileThumbOuters.eq(fileIndex);
			var fileId = outerObject.attr("fileId");
			$.ajax({
				data:"admin_action=getBrowserThumb&fileId=" + fileId,
				success:function(response){
					if(fileIndex <= maxFileLength)
					{
						if((response == null) || (response == undefined) || (response == ""))
						{
							outerObject.append($('<img class="filethumb" />').attr("src","../upload/system/exclamation.jpg"));
							fileIndex++;
							loadFileThumbs(fileIndex);
						}
						else
						{
							var imageObject = $('<img class="filethumb" />').attr("src",response);
							imageObject.load(function(){
								outerObject.append(imageObject);
								fileIndex++;
								loadFileThumbs(fileIndex);
							});
						}
					}
				},
				error:function(){
					outerObject.append($('<img class="filethumb" />').attr("src","../upload/system/exclamation.jpg"));
					fileIndex++;
					loadFileThumbs(fileIndex);
				}
			});
		}

		function listFavouritedDirectories()
		{
			$.ajax({
				data:"admin_action=listFavouritedDirectories",
				success:function(response){
					browserFavoritesList.html(response);
				}
			});
		}

		function prepareForUpload()
		{
			var directory = currentDirectory.replace(uploadurl," ");
			var unique_order = 0;
			var files;
			var loaded_files_count = 0;
			
			uploader = document.getElementById(options.uploaderId);
			uploader.onchange = function(e){
				files = e.target.files;
				filesList = new Array();
				total_files_count = files.length;
				loaded_files_count = 0;
				unique_order++;
				
				for(var i=0, j=files.length; i<j; i++)
				{
					var reader = new FileReader();
					var file = files[i];
					file.queue_item_id = "queue_" + unique_order.toString() + i.toString();
					file.queue_bar_id = "bar_" + unique_order.toString() + i.toString();
					filesList.push({"file":file,"queue_item_id":file.queue_item_id,"queue_bar_id":file.queue_bar_id});
					
					reader.onloadstart = (function(file){
						var queueHtml = '<li id="' + file.queue_item_id + '" class="fileUploadingOuter uploadifyQueueItem">\
											<span class="uploadingText">Yükleniyor...</span>\
											<span class="uploadBarOuter">\
												<span id="' + file.queue_bar_id + '" class="bar">\
													<img class="uploaderImage" src="' + VIEW_URL + 'images/fileeditor/loader.gif" />\
												</span>\
											</span>\
										</li>';
						$("#" + options.queueId).append(queueHtml);				
					})(file);
					
					reader.onloadend = uploadFile;
					reader.readAsDataURL(file);	
				}	
			};
			
			
			function uploadFile(e)
			{
				// Tüm dosyaların belleğe yüklenip yüklenmediğini kontrol et
				loaded_files_count++;
				if(loaded_files_count != total_files_count)
					return;
				
				// Scroll to uploading files queues
				var uploadingFileIndex = editor.find(".fileUploadingOuter:first").index();
				var uploadingFileRowIndex = ((uploadingFileIndex % 4) != 0) ? Math.floor(uploadingFileIndex / 4) : Math.floor(uploadingFileIndex / 4) -1;
				browserFilesListOuter.animate({scrollTop: (uploadingFileRowIndex * 152)}, 400);
				
				// Upload each files
				for(var i=0, j=filesList.length; i<j; i++)
				{
					var form = new FormData();
					var xhr = new XMLHttpRequest();
					form.append("admin_action","uploadFile");
					form.append("directory",directory);
					form.append("uploadFile",filesList[i].file);
					
					xhr.upload.currentIndex = i;
					xhr.upload.onprogress = function(e){
						var data = filesList[this.currentIndex];
						var barObject = $("#" + data.queue_bar_id);
						var left = -111 + ((e.loaded / e.total) * 111);
						barObject.animate({"left":left},500);
					}
					xhr.addEventListener("load", function(e){
						var queueObject = $("#" + filesList[this.upload.currentIndex].queue_item_id);
						var response = e.target.responseText;
						var file = eval("(" + response + ")");
						
						if(file.error == true)
						{
							alert(file.message);
							return false;
						}
						
						var filesHtml = '<li class="file newlyUploaded" title="' + file.basename + '" url="' + MHA.encodeUTF8(file.url) + '" fileId="' + file.file_id + '">';
						
						filesHtml += '<span class="filethumbOuter" fileId="' + file.file_id + '">';
						filesHtml += '<img class="filethumb" src="' + VIEW_URL + 'images/fileeditor/fileloader.gif" />';
						filesHtml += '</span>';
						
						filesHtml += '<span class="fileEditButtonsOuter">';
						
						if(options.filesEditable)
						{
							filesHtml += '<span title="Düzenle" class="btnEdit fBtn" fileId="' + file.file_id + '"></span>';
						}
						
						if(file.type != "other")
						{
							filesHtml += '<a title="İncele" class="' + (file.type == "movie" ? "btnPlay" : "btnLook") + ' fancybox fBtn" href="lookfile.php?type=' + file.type + '&url=' + MHA.encodeUTF8(file.url) + '"></a>';
						}
						
						filesHtml += '<span title="Sil" class="btnDelete fBtn"></span>';
						filesHtml += '</span>';
						filesHtml += '<span class="fileName">' + file.basename + '</span>';
						filesHtml += '</li>';
						
						queueObject.replaceWith(filesHtml);
						
						var fileObject = browserFilesList.find("li.newlyUploaded");
						fileObject.removeClass("newlyUploaded");
						
						$(this).replaceWith(fileObject).animate({"opacity":1},500);
						
						$.ajax({
							data:"admin_action=getBrowserThumb&fileId=" + file.file_id,
							success:function(response){
								fileObject.find(".filethumb").attr("src",response);
							}
						});
						$(".fancybox").fancybox({
							"titleShow":false
						});
					});
					
					xhr.open("POST", "admin.php?page=dashboard", true);
					xhr.send(form);
				}
			}
		}



		function validateFoldername(foldername,onchecked)
		{
			if(validate.validateFilename(foldername,false))
			{
				$.ajax({
					data:"admin_action=checkDirectoryExists&directory=" + currentDirectory + foldername + "/",
					success:function(response){
						if(response == "exists")
							alert("varolan bir dosya adı girdiniz, lütfen başka bir dosya adı girin!");
						else if(response == "notexists")
						{
							onchecked();
						}
						else
							alert("Hata: " + response);
					}
				});
			}
			else
				alert("lütfen uygun klasör ismi girin! \n * dosya ismi uzunluğu en az 1 karakterden oluşmalıdır! \n * dosya ismi nokta (.) karakteri ile başlayamaz! \n * dosya isminde \\,/,:,*,?,<,>,| karakterleri bulunamaz! ");
		}
		
		//EVENTS
		function homeButtonEvent()
		{
			browseFiles("");
			editor.find(".directory").removeClass("expanded").addClass("collapsed");
			editor.find(".directory a").removeClass("active");
			browserFavoritesList.find("a").removeClass("selected");
			return false;
		}

		function favouritesButtonEvent()
		{
			if(currentDirectory == "")
				return false;
			
			var status = -1;
			if($(this).hasClass("active"))
			{
				editor.find(".directory a[href='" + currentDirectory + "']").removeClass("favourite");
				$(this).removeClass("active");
				status = -1;
			}
			else
			{
				editor.find(".directory a[href='" + currentDirectory + "']").addClass("favourite");
				$(this).addClass("active");
				status = 1;
			}
			
			$.ajax({
				data:"admin_action=setFavouriteStatus&status=" + status + "&dir=" + currentDirectory,
				success:function(response){
					
					if(response == "succeed")
					{
						listFavouritedDirectories();
					}
					else
						alert("Hata Oluştu: " + response + "!");
				}
			});
			
			return false;
		}

		function previousDirectoryButtonEvent()
		{
			var prevDirectory = currentDirectory.replace(/[a-z0-9\_\-]+\/$/i,"");
			if((currentDirectory != "") && (currentDirectory != null))
			{
				var prevDirLink = editor.find("li.directory a[href='" + prevDirectory + "']");
				if(prevDirLink.length > 0)
					editor.find("li.directory a[href='" + prevDirectory + "']").click();
				else
					browserBtn_Home.click();
			}
			return false;
		}

		function createNewDirectoryEvent()
		{
			var newFolderIndex;
			var newFolderRowIndex;
			var newFolderName;
			var newDirectory = editor.find("#newDirectory");
			if(newDirectory.length > 0)
			{
				newFolderIndex = newDirectory.index();
				newFolderRowIndex = ((newFolderIndex % 4) != 0) ? Math.floor(newFolderIndex / 4) : Math.floor(newFolderIndex / 4) -1;
				browserFilesListOuter.animate({scrollTop: (newFolderRowIndex * 152)}, 300);
				editor.find("#newFolderName").focus();
				return false;
			}
			
			var newDirectoryHtml = '<li class="folder" id="newDirectory">';
			newDirectoryHtml += '<img src="' + folder_image + '" />';
			newDirectoryHtml += '<input id="newFolderName" value="" /></li>';
			
			if(browserFilesList.find(".folder").length > 0)
				browserFilesList.find(".folder:last").after(newDirectoryHtml);
			else
				browserFilesList.prepend(newDirectoryHtml); 
			
			newDirectory = editor.find("#newDirectory");
			newFolderName = newDirectory.find("#newFolderName");
			
			newFolderIndex = newDirectory.index();
			newFolderRowIndex = ((newFolderIndex % 4) != 0) ? Math.floor(newFolderIndex / 4) : Math.floor(newFolderIndex / 4) -1;
			browserFilesListOuter.animate({scrollTop: (newFolderRowIndex * 152)}, 300);
			
			newFolderName.focus();
			newFolderName.keydown(function(e){
				if(e.keyCode==13)
				{
					newFolderName.val(MHA.fixStringForWeb(newFolderName.val()));
					var filename = $.trim(newFolderName.val());
					
					validateFoldername(filename,function(){
						$.ajax({
							data:"admin_action=newDirectory&parent_directory=" + currentDirectory + "&dirname=" + filename,
							success:function(response){
								if(response == "created")
								{
									//editor.find("#newDirectory").remove();
									var createdDirectory = currentDirectory + filename + "/";
									
									var directoryHtml = '<li class="folder" url="' + createdDirectory + '">\
														<img src="' + folder_image + '" />\
														<span>' + filename + '</span></li>';
									
									var fileTreeHtml = '<li class="directory collapsed single">\
														<a href="' + createdDirectory + '">' + filename + '</a>\
														</li>';
									
									var activeFileTreeObject = editor.find(".directory a[href='" + currentDirectory + "']");
									
									if($.trim(currentDirectory) != "")
									{
										if(activeFileTreeObject.next().hasClass("fileTree"))
										{
											activeFileTreeObject.next().append(fileTreeHtml);
										}
										else
										{
											activeFileTreeObject.after('<ul class="fileTree" style="">' + fileTreeHtml + '</ul>');
										}
										activeFileTreeObject.parent().removeClass("single");
									}
									else
									{
										if(editor.find(".fileTree").length > 0)
											editor.find(".fileTree:first").append(fileTreeHtml);
										else
											browserDirectoriesOuter.html('<ul class="fileTree" style="">' + fileTreeHtml + '</ul>');
									}
									
									newDirectory.replaceWith(directoryHtml);
								}
							}
						});
					});
				}
			});
			
			return false;
		}

		function deleteFileEvent()
		{
			if(confirm("Silmek istediğinizden eminmisiniz?"))
			{
				var element = $(this).closest(".file");
				$.ajax({
					data:"admin_action=deleteFile&fileUrl=" + element.attr("url"),
					success:function(response){
						if(response == "deleted")
						{
							element.animate({"opacity":0},500,function(){
								$(this).remove();
							});
						}
						else
							alert(response);
					}
				});
			}
		}

		function favouritesLinksEvent()
		{
			browserFavoritesList.find("a").removeClass("selected");
			$(this).addClass("selected");

			var url = $(this).attr("href");
			var treeObject = editor.find(".fileTree a[href='" + url + "']");
			
			treeObject.parents(".collapsed").each(function(){
				$(this).removeClass("collapsed").addClass("expanded");
			});
			treeObject.click();
			return false;
		}

		/* DOCUMENT EVENTS */
		function documentKeyDownEvent(e)
		{
			var newDirectory = editor.find("#newDirectory");
			if((e.keyCode == 13) && (newDirectory.length <= 0))
			{
				if(browserFilesList.find(".selected").length > 0)
					browserBtnUseFiles.click();
			}
			if(e.keyCode == 27)
			{
				if(newDirectory.length > 0)
					newDirectory.remove();
			}
			else if((e.keyCode == 46) && (browserFilesList.find(".selected").length > 0))
				deleteSelectedFilesAndDirectoriesEvent();
			else if(e.keyCode == 113)
				renameFolder();
		}

		function checkIfAnyFileSelected()
		{
			if((browserFilesList.find(".selected").length <= 0) || (browserFilesList.find(".folder.selected").length > 0))
				browserBtnUseFiles.addClass("disabled");
			else
				browserBtnUseFiles.removeClass("disabled");
		}

		function fileSelectionEvent(e)
		{
			var _this;
			
			if($(e.target).is(".file,.folder"))
				_this = $(e.target);
			else
				_this = $(e.target).parents("li");
			
			if(!_this.hasClass("file") && !_this.hasClass("folder"))
			{
				browserFilesList.find(".folder, .file").removeClass("selected");
				checkIfAnyFileSelected();
				return true;
			}
			
			if(options.multiSelection && e.ctrlKey)
			{
				if(_this.hasClass("selected"))
				{
					if(browserFilesList.find(".selected").length > 1)
					{
						_this.removeClass("selected");
					}
				}
				else
				{
					_this.addClass("selected");
				}
			}
			else if(options.multiSelection && e.shiftKey)
			{
				if(editor.find(".lastClicked").length > 0)
				{
					var startIndex = editor.find(".lastClicked").index() - 1;
					var endIndex = _this.index() - 1;
					
					if(startIndex < endIndex)
					{
						for(var i=startIndex; i<=endIndex; i++)
						{
							var current = browserFileItems.eq(i);
							if(current.hasClass("file") || current.hasClass("folder"))
							{
								current.addClass("selected");
								if(i==endIndex)
									current.addClass("lastClicked");
							}
						}
					}
					else if(startIndex > endIndex)
					{
						for(var i=endIndex; i<=startIndex; i++)
						{
							var current = browserFileItems.eq(i);
							if(current.hasClass("file") || current.hasClass("folder"))
							{
								current.addClass("selected");
								if(i==endIndex)
									current.addClass("lastClicked");
							}
						}
					}
				}
			}
			else
			{
				browserFilesList.find(".folder,.file").removeClass("selected");
				_this.addClass("selected");
			}
			
			browserFilesList.find(".folder,.file").removeClass("lastClicked");
			_this.addClass("lastClicked");
			checkIfAnyFileSelected();
		}

		function deleteSelectedFilesAndDirectoriesEvent()
		{
			if(confirm("Silmek istediğinize eminmisiniz?"))
			{
				deleteFilesList = new Array();
				browserFilesList.find(".selected").each(function(){
					var url = $(this).attr("url");
					var id = $(this).hasClass("file") ? $(this).attr("fileId") : -1;
					deleteFilesList.push({"url":url,"id":id});
				});
				
				$.ajax({
					data:"admin_action=deleteFilesAndDirectories&fileurls=" + JSON.encode(deleteFilesList),
					success:function(response){
						if(response == "deleted")
						{
							// Önce FileTree den klasörleri sil
							browserFilesList.find(".selected").each(function(){
								var url = $(this).attr("url");
								var parentDir = editor.find(".directory a[href='" + url + "']").parent().parent().parent();
								
								editor.find(".directory a[href='" + url + "']").parent().remove();
								
								if(parentDir.find(".directory").length <= 0)
								{
									parentDir.addClass("single");
								}
							});
							
							browserFilesList.find(".selected").animate({"opacity":0},500,function(){
								$(this).remove();
							});
						}
						else
							alert(response);
					}
				});
			}
		}
		
		function listSubDirectories()
		{
			if(!$(this).parent().hasClass("active"))
			{
				var directory = $(this).attr("href");
				browseFiles(directory);
			}
			
			if($(this).parent().hasClass("collapsed"))
			{
				$(this).parent().removeClass("collapsed").addClass("expanded");
			}
			else if($(this).hasClass("active"))
			{
				$(this).parent().removeClass("expanded").addClass("collapsed");
			}
			
			$(".fileTree li a").removeClass("active");
			$(this).addClass("active");
			
			return false;
		}
	});
};
