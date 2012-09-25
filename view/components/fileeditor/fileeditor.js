(function($){
	var editor_html = '';
	var contents_html = '';
	var objects = {};
	var script_file = window.location.href; // scriptin çalıştığı adresi temsil etmek için undefined atıyoruz.
	var default_properties = {
		multiselection: true,
		files_editable: true,
		onFilesSelect : function(files){}
	};
	
	// statik değişkenleri tanımla
	FILE_EDITOR.uploaded_file_count = 0;
	
	function FILE_EDITOR(){
		// SELECTING_OBJECTS_2D class'ını inherit et
		SELECTING_OBJECTS_2D.call(this);
		
		var self = this;
		var editor_form_list = [];
		var file_reader_list = [];
		var xhr_list = [];
		var options = {}; // bu değer initialize() fonksiyonu içinde dolduruluyor.
		var current_directory_id = -1; // ana dizin değeri "-1"
		
		var loadElements = function(){
			objects.fileEditorMainContainer = $("#fileEditorMainContainer");
			objects.fileEditorBackHider = objects.fileEditorMainContainer.find("#fileEditorBackHider");
			objects.fileEditorOuter = objects.fileEditorMainContainer.find("#fileEditorOuter");
			objects.btnCloseFileEditor = objects.fileEditorMainContainer.find("#btnCloseFileEditor");
			objects.fileEditorContentsLoader = objects.fileEditorMainContainer.find("#fileEditorContentsLoader");
			objects.editorDragFilesArea = objects.fileEditorMainContainer.find("#editorDragFilesArea");
			objects.browser_address = objects.fileEditorMainContainer.find("#browser_address");
			objects.search_input = objects.fileEditorMainContainer.find("#search_input");
			objects.browserBtn_Home = objects.fileEditorMainContainer.find("#browserBtn_Home");
			objects.browserBtn_Refresh = objects.fileEditorMainContainer.find("#browserBtn_Refresh");
			objects.browserBtn_Fav = objects.fileEditorMainContainer.find("#browserBtn_Fav");
			objects.browserBtn_NewDir = objects.fileEditorMainContainer.find("#browserBtn_NewDir");
			objects.browserBtn_Prev = objects.fileEditorMainContainer.find("#browserBtn_Prev");
			objects.fileEditorUploader = objects.fileEditorMainContainer.find("#fileEditorUploader");
			objects.browserFavouritesList = objects.fileEditorMainContainer.find("#browserFavouritesList");
			objects.browserDirectoriesOuter = objects.fileEditorMainContainer.find("#browserDirectoriesOuter");
			objects.browserFilesListOuter = objects.fileEditorMainContainer.find("#browserFilesListOuter");
			objects.browserFilesListOuter.focused = false; // bu değişkene bakarak browserFilesListOuter alanındaki klavye kısayollarını kullanıp kullanmaman gerektiğini kontrol edebilirsin
			objects.browserFilesList = objects.browserFilesListOuter.find("#browserFilesList");
			objects.browserBtnUseFiles = objects.fileEditorMainContainer.find("#browserBtnUseFiles");
		};
		
		var bindEvents = function(){
			objects.search_input.keydown(events.onSearchFiles);
			objects.browserBtn_Home.click(events.onLoadFiles);
			objects.browserBtn_Refresh.click(events.onRefreshCurrentDirectory);
			objects.browserBtn_Fav.click(events.onSetFavouriteStatus);
			objects.browserBtn_NewDir.click(events.onCreateNewDirectory);
			objects.browserBtn_Prev.click(events.onMoveUpperDirectory);
			objects.btnCloseFileEditor.click(public_methods.closeFileEditor);
			objects.browserDirectoriesOuter.delegate("li", "mousedown", events.onClickDirectoryItem);
			objects.browserFavouritesList.delegate("span", "mousedown", events.onClickFavouritedDirectoryItem);
			objects.browserFilesListOuter.on("mousedown", events.onContentsAreaMouseDown);
			objects.browserFilesList.delegate(".btnDelete","click", events.onEditorItemDeleteButtonClick);
			objects.browserBtnUseFiles.on("click", events.onUseSelectedItems);
			objects.browserFilesList.delegate(".file","dblclick", events.onUseSelectedItems);
			objects.browserFilesList.delegate(".directory","dblclick", events.onLoadFiles);
			objects.fileEditorUploader.on("change", events.onFilesSelectedForUpload);
			$(document).on("mouseup", events.onDocumentMouseUp);
			$(document).on("keydown", events.onDocumentKeyDown);
			document.addEventListener("dragover", events.onDragOver);
			document.addEventListener("dragend", events.onDragEnd);
			objects.editorDragFilesArea[0].addEventListener("drop", events.onFilesSelectedForUpload);
			self.onCapturing = events.onContentStatusChange;
		};
		
		var fixStringForWeb =  function(str, remove_forbidden_chars){
			str = str.replace(/\İ/g,"I");
			str = str.replace(/\ı/g,"i");
			str = str.replace(/\Ü/g,"U");
			str = str.replace(/\ü/g,"u");
			str = str.replace(/\Ö/g,"O");
			str = str.replace(/\ö/g,"o");
			str = str.replace(/\Ğ/g,"G");
			str = str.replace(/\ğ/g,"g");
			str = str.replace(/\Ş/g,"S");
			str = str.replace(/\ş/g,"s");
			str = str.replace(/\Ç/g,"C");
			str = str.replace(/\ç/g,"c");
			str = remove_forbidden_chars === true ? str.replace(/\\|\/|:|*|?|<|>|\|/g, "") : str;
			return str.replace(/\s/g,"_");
		};
		
		var generateFileHtml = function(file){
			temp_html = '<li class="file editor_content notload" file_id="' + file.file_id + '" url="' + file.url + '"  title="' + file.basename + '" thumb="' + file.thumb + '" file_type="' + file.type + '" >';
			temp_html += '<span class="editorFileThumbOuter">';
			temp_html += '<span class="thumbShadow"></span>';
			temp_html += '<img class="editorFileThumb" src="' + file.thumb + '" />';
			temp_html += '</span>';
			temp_html += '<span class="fileEditButtonsOuter">';
			
			if(options.files_editable)
			{
				temp_html += '<span title="Düzenle" class="btnEdit fBtn" file_id="' + file.file_id + '"></span>';
			}
			
			if(file.type != "other")
			{
				temp_html += '<a title="' + (file.type == "movie" ? "Oynat" : "İncele") + '" class="' + (file.type == "movie" ? "btnPlay" : "btnLook") + ' fancybox fBtn" href="lookfile.php?type=' + file.type + '&url=' + encodeURIComponent(file.url) + '" target="_blank"></a>';
			}
			else
			{
				temp_html += '<a title="İndir"  class="btnDownload fBtn" href="'  + encodeURIComponent(file.url) + '"></a>';
			}
			
			temp_html += '<span title="Sil" class="btnDelete fBtn"></span>';
			temp_html += '</span>';
			temp_html += '<span class="fileName">' + file.basename + '</span>';
			temp_html += '</li>';
			
			return temp_html;
		};
		
		// editor_contents: silinecek elemanların jquery object tipinde listesi
		var deleteFileEditorItems = function(editor_contents, onDeleted){
			if((editor_contents.length > 0) && confirm("Silmek istediğinize eminmisiniz?"))
			{
				items_info = [];
				editor_contents.each(function(){
					if($(this).is(".file"))
						items_info.push({"id":$(this).attr("file_id"), "type":"file"});
					else
						items_info.push({"id":$(this).attr("directory_id"), "type":"directory"});
				});
				
				$.ajax({
					type:"post",
					url:script_file,
					data:"admin_action=deleteFileEditorItems&items=" + JSON.stringify(items_info),
					dataType:"json",
					success:function(){
						// Eğer tanımlanmışsa onDeleted callback ini çalıştır
						if((typeof(onDeleted) == "object") || (typeof(onDeleted) == "function"))
						{
							onDeleted();
						}
						
						for(i=0, j=items_info.length; i<j; i++)
						{
							if(items_info[i].type == "directory"){
								objects.browserDirectoriesOuter.find("[directory_id=" + items_info[i].id + "]").remove();
								objects.browserFavouritesList.find("[directory_id=" + items_info[i].id + "]").remove();
							}
						}
						
						delete i,j, items_info;
						
						editor_contents.css("opacity", 0);
						setTimeout(function(){editor_contents.remove();}, 500); // burdaki 500 değeri fade animasyonunun süresini temsil ediyor 
					},
					error:function(){
						console.log("Hata: dosyalar silinemedi!");
					}
				});
			}
		};
		
		// Yeni klasör oluşturur
		var createDirectory = function(){
			if(($directory_item = $("#newDirectory")) && ($directory_item.length > 0))
			{
				$directory_name = fixStringForWeb($("#newFolderName").val());
				$parent_id = current_directory_id;
				
				if(($directory_name.length <= 0) || ($directory_name.match(/^\./i)) || ($directory_name.match(/[\s\/\\:\*\?\>\<\|"]+$/i)))
				{
					alert("Lütfen uygun klasör ismi girin! \n * klasör ismi uzunluğu en az 1 karakterden oluşmalıdır! \n * klasör ismi nokta (.) karakteri ile başlayamaz! \n * klasör isminde \\,/,:,*,?,<,>,| karakterleri bulunamaz! ");
				}
				else
				{
					$.ajax({
						type:"post",
						url:script_file,
						data:"admin_action=createDirectory&name=" + $directory_name + "&parent_id=" + $parent_id,
						dataType:"json",
						success:function(response){
							if(response.success === false)
							{
								if(response.msg == "already_exists")
								{
									alert("Girdiğiniz klasör zaten mevcut, lütfen farklı bir klasör ismi girin! ");
								}
								else if(response.msg == "error_happened")
								{
									alert("Beklenmedik bir hata oluştu, lütfen tekrar deneyin!");
								}
							}
							else
							{
								$directory_item.removeAttr("id");
								$directory_item.find("#newFolderName").replaceWith("<span>" + $directory_name + "</span>");
								$directory_item.attr("directory_id" , response.directory_id);
								
								// Yeni eklenen dizini directory tree'ye ekle
								temp_html  = '<li directory_id="' + response.directory_id + '" class="empty">';
								temp_html += '<icon></icon><span class="name">' + $directory_name + '</span></li>';
								
								parent_tag = $parent_id == -1 ? objects.browserDirectoriesOuter : objects.browserDirectoriesOuter.find("[directory_id='" + $parent_id + "']");
								
								
								// en az bir tane treelist varsa, o treelist e ekle.
								if(parent_tag.find(">ul").length > 0)
								{
									parent_tag.find(">ul").append(temp_html);
								}
								else // hiç treelist yoksa yani bu oluşturulan ik klasör ise yeni bir treelist oluştur
								{
									parent_tag.addClass("expanded").removeClass("empty");
									parent_tag.append("<ul class='fileTree'>" + temp_html + "</ul>");
								}
								//-------------------------------------------------------------------------------
							}
						},
						error:function(){
							alert("Beklenmedik bir hata oluştu, lütfen tekrar deneyin!");
						},
						complete: function(){
							delete temp_html, $parent_id, parent_tag;
						}
					});
				}
				
				return true;
			}
		};
		
		// klasör ismini günceller
		var updateDirectory = function(){
			if($("#updateFolderName").length > 0)
			{
				$directory_name = fixStringForWeb($("#updateFolderName").val());
				$directory_item = $("#updateFolderName").closest(".directory");
				
				// Eğer dosya ismi değişmemişse server tarafında güncelleme işlemi yapmaya gerek yok
				if($directory_name == $("#updateFolderName").attr("old_value"))
				{
					$directory_item.find("#updateFolderName").replaceWith("<span>" + $directory_name + "</span>");
					return true;
				}
				
				if(($directory_name.length <= 0) || ($directory_name.match(/^\./i)) || ($directory_name.match(/[\s\/\\:\*\?\>\<\|"]+$/i)))
				{
					alert("Lütfen uygun klasör ismi girin! \n * klasör ismi uzunluğu en az 1 karakterden oluşmalıdır! \n * klasör ismi nokta (.) karakteri ile başlayamaz! \n * klasör isminde \\,/,:,*,?,<,>,| karakterleri bulunamaz! ");
					return false;
				}
				else
				{
					directory_id = $directory_item.attr("directory_id");
					$.ajax({
						type:"post",
						url:script_file,
						data:"admin_action=updateDirectory&name=" + $directory_name + "&directory_id=" + directory_id,
						dataType:"json",
						success:function(response){
							if(response.success === false)
							{
								if(response.msg == "already_exists")
								{
									alert("Girdiğiniz klasör zaten mevcut, lütfen farklı bir klasör ismi girin! ");
								}
								else if(response.msg == "error_happened")
								{
									alert("Beklenmedik bir hata oluştu, lütfen tekrar deneyin!");
								}
							}
							else
							{
								// klasör ismini gerekli yerlerde de değiştir
								$directory_item.find("#updateFolderName").replaceWith("<span>" + $directory_name + "</span>");
								objects.browserDirectoriesOuter.find("[directory_id='" + directory_id + "'] > .name").html($directory_name);
								objects.browserFavouritesList.find("[directory_id='" + directory_id + "']").html($directory_name);
							}
						},
						error:function(){
							alert("Beklenmedik bir hata oluştu, lütfen tekrar deneyin!");
						}
					});
					
				}
				
				return true;
			}
		};
		
		// klasör ismini güncelleme işlemini iptal edip klasörün kayıtlı olan ismini geri yükler.
		var cancelFolderUpdating = function(){
			if(($directory = $("#updateFolderName").closest(".directory")) && ($directory.length > 0))
			{
				$("#updateFolderName").replaceWith("<span>" + $("#updateFolderName").attr("old_value") + "</span>");
			}
		};
		
		var events = {
			onLoadFiles: function(e, settings){
				settings = $.extend({}, {"filter":{"order_by":"default", "type":"all"}, "directory_id":-1}, settings);
				directory_id = $(this).is("[directory_id]") ? $(this).attr("directory_id") : settings.directory_id;
				current_directory_id = directory_id; // current_directory_id değerini burada atıyoruz.
				
				// Eğer bu event büyük alandaki bir dizin tarafından trigger edilmişse, bu eventi iptal edip onun yerine
				// aynı özellikteki dizini browserDirectoriesOuter içindeki elemanlardan bulup çalıştırıyoruz.
				// böyle yapmamızın sebebi, bir dizine girildiğinde dizinlerden oluşan treeList'i düzenleyebilmek.
				if($(this).is(".editor_content")){
					objects.browserDirectoriesOuter.find("li[directory_id='" + directory_id + "']").trigger("mousedown");
					return false;
				}
				
				// Eğer directory_id değeri 0'dan küçük ise ana dizin listeleniyor demektir. bu durumda directory tree den ve favourites listesinden
				// seçili olan elemanların seçili olma özelliğini kaldırıp, directory tree deki tüm dizinleri kapalı şekle getiriyoruz.
				// Ayrıca adres input'unun değerini boş string yapıyoruz
				if(directory_id <= 0){
					objects.browserDirectoriesOuter.find("li").removeClass("selected").removeClass("expanded");
					objects.browserFavouritesList.find("span").removeClass("selected");
					objects.browser_address.val("");
				}
				
				$.ajax({
					type:"post",
					url:script_file,
					data:"admin_action=loadFileEditorItems&directory_id=" + directory_id + "&order_by=" + settings.filter.order_by,
					dataType:"json",
					beforeSend: function(){
						// Loader'ı göster
						objects.fileEditorContentsLoader.css({"visibility":"visible", "opacity":"1"});
					},
					success:function(response){
						contents_html = "";
						
						for(i=0, j=response.directories.length; i<j; i++)
						{
							contents_html += '<li class="directory editor_content" directory_id="' + response.directories[i].directory_id + '" parent_id="' + response.directories[i].parent_id + '">';
							contents_html += '<icon></icon>';
							contents_html += '<span>' + response.directories[i].name + '</span></li>';
						}
						
						for(i=0, j=response.files.length; i<j; i++)
						{
							contents_html += generateFileHtml(response.files[i]);
						}
						
						objects.browserFilesList.html(contents_html);
						
						// yeni yüklenen dosyaların thumbnailarına load eventini burdan bağla, delegate veya live ile bağlanınca çalışmıyorlar
						objects.browserFilesList.find(".notload .editorFileThumb").load(events.onFileThumbnailLoad).error(events.onFileThumbnailError);
						
						
						// eğer yüklü ise fancybox'ı bağla
						if($.fn.fancybox)
						{
							$(".file .fancybox").fancybox({
								"titleShow":false,
								"scrolling":"no"
							});
						}
						
						//----------------------------------------------------------------------------------------------------------------
						// success sırasındaki işlemler uzun sürebileceği için loader'ı gizleme işlemini bu işlemlerden sonra yapıyoruz
						//----------------------------------------------------------------------------------------------------------------
						objects.fileEditorContentsLoader.css({"opacity":"0"});
						setTimeout(function(){
							objects.fileEditorContentsLoader.css({"visibility":"hidden"});
						}, 150);
						//-----------------------------------------------------------------
					},
					error:function(){
						alert("Beklenmedik bir hata oluştu, lütfen tekrar deneyin!");
					}
				});
				
				return false; // event olarak kullandığımızda sayfa yönlendirmesini engellemek için "return false;" yapıyoruz
			},
			onSearchFiles: function(){
				
			},
			onRefreshCurrentDirectory: function(e){
				events.onLoadFiles(e, {"directory_id": current_directory_id});
				return false;
			},
			onFileThumbnailLoad: function(e){
				// sebebini bilmiyorum ama direk css'i düzenleyince css de tanımladığım transition çalışmıyor yani opacity fade animasyonlu değilde
				// direk 1 oluyor. Bu şekilde setTimout fonksiyonu ile çok minik hatta 0 gecikme verdiğimde transition çalışıyor.
				var temp_uploaded_item = $(this);
				setTimeout(function(){
					temp_uploaded_item.parent().parent().removeClass("notload");
					temp_uploaded_item.css({"visibility":"visible", "opacity":"1"});
				}, 0);
			},
			onFileThumbnailError:function(e){
				$(this).parent().addClass("loaderror").parent().removeClass("notload");
			},
			onSetFavouriteStatus: function(e){
				e.preventDefault();
				
				if(objects.browserDirectoriesOuter.find("[directory_id='" + current_directory_id + "']").length > 0) // Eğer ana dizinde değilse ve belirtilen dizin mevcutsa
				{
					temp_directory_id = current_directory_id; // ajax işlemi sırasında işlem sonlanmadan önce current_directory_id değeri değişebilir, o yüzden bu değeri dışarıdan değişemeyecek başka bir değişkene atıyoruz
					favourite_action = "addToFavourites";
					if(objects.browserFavouritesList.find("[directory_id='" + temp_directory_id + "']").length > 0)
					{
						favourite_action = "removeFromFavourites";
					}
					else
					{
						// favourites'e eklenen dizinin bilgilerini alıyoruz. Bunu burada yapma sebebimiz çok çok düşük bir ihtimalde olsa
						// olurda kullanıcı favourites e eklediği dizini ajax işlemi bitmeden silmeye karar verdi diyelim. Ve bu silme işleminin ajax sonucu 
						// favourites işleminin ajax sonucundan daha önce döndü ve dizin silme işlemi gerçekleşti. Bu durumda favourites için
						// yapılan ajax işlemi sonucunda ilgili dizin silinmiş olacağı için onun bilgilerini alamayacağız ve hata oluşacak.
						// buna engel olmak için işlemi burada yapıyoruz
						temp_directory = objects.browserDirectoriesOuter.find("li[directory_id='" + temp_directory_id + "'] .name"); 
					}
					
					$.ajax({
						type:"post",
						url:script_file,
						data:"admin_action=" + favourite_action + "&directory_id=" + temp_directory_id,
						dataType:"json",
						success:function(response){
							if(response.success === true)
							{
								if(favourite_action === "addToFavourites")
								{
									objects.browserFavouritesList.append("<span class='selected' directory_id='" + temp_directory_id + "'>" + temp_directory.html() + "</span>");
									
									// temp_directory bazı durumlarda tanımlanmayacağı için, böyle bi durumda delete işleminde hata verebiliyor. 
									// o yüzden bu değişkenin delete işlemini son kullanıldığı yerde yani burda yapıyoruz.
									delete temp_directory;
								}
								else
								{
									objects.browserFavouritesList.find("[directory_id='" + temp_directory_id + "']").remove();
								}
							}
							else
							{
								alert("Beklenmedik bir hata oluştu, lütfen tekrar deneyin!");
							}
						},
						error:function(){
							alert("Beklenmedik bir hata oluştu, lütfen tekrar deneyin!");
						},
						complete:function(){
							delete favourite_action, temp_directory_id;
						}
					});
				}
			},
			onClickDirectoryItem: function(e){
				e.stopPropagation();
				e.preventDefault();
				
				if(!$(this).is(".empty"))
				{
					if($(this).is(".expanded"))
						$(this).removeClass("expanded");
					else
						$(this).addClass("expanded");
				}
				
				// filetree nin durumunu ayarla---------------------------------------------
				objects.browserDirectoriesOuter.find("li").removeClass("selected");
				$(this).addClass("selected");
				//--------------------------------------------------------------------------
				
				// favourites listesinin durumunu ayarla----------------------------------------------------------------------------------
				directory_id = $(this).attr("directory_id");
				objects.browserFavouritesList.find("span").removeClass("selected");
				objects.browserFavouritesList.find("[directory_id='" + directory_id + "']").addClass("selected");
				
				adress_path = "";
				$(this).parents("li[directory_id]").each(function(){
					// filetree'de seçilen elemanın parent elemanlarından .expanded 
					// class'ına sahip olmayanlara expanded class'ı ekle
					if($(this).is("li:not(.expanded)"))
					{
						$(this).addClass("expanded");
					}
					
					// şu anki çalışma adresini hesapla ve adres input'unda güncelle
					adress_path = $(this).find(">.name").text() + "/" + adress_path;
				});
				adress_path += 	$(this).find(">.name").text() + "/";
				objects.browser_address.val(adress_path);
				
				delete adress_path, directory_id;
				//------------------------------------------------------------------------------------------------------------------------
				
				// dosyaları yükle
				events.onLoadFiles.call(this);
			},
			onClickFavouritedDirectoryItem: function(e){
				e.stopPropagation();
				e.preventDefault();
				
				// favourite elemanının durumunu ayarla
				objects.browserFavouritesList.find("span").removeClass("selected");
				$(this).addClass("selected");
				
				// filetree deki aynı elemanı trigger et. 
				directory_id = $(this).attr("directory_id");
				objects.browserDirectoriesOuter.find("[directory_id='" + directory_id + "']").trigger("mousedown");
				
				delete directory_id;
			},
			onContentsAreaMouseDown: function(e){
				// bu değişkene bakarak browserFilesListOuter alanındaki klavye kısayollarını kullanıp kullanmaman gerektiğini kontrol edebilirsin
				objects.browserFilesListOuter.focused = true;
				
				// eğer bir klasör içindeki input'a tıklamışsak muhtemelen klasör ismi giriyoruzdur o yüzden bu
				// eventin iptal edilmemesi gerekiyor bu yüzden eventin gerisini çalıştırmayıp return true yaparak eventi
				// normal şekilde çalışmasını sağlıyoruz
				if((e.target.tagName.toLowerCase() == "input") && ($(e.target).closest(".directory").length > 0))
				{
					return true;
				}
				else
				{
					createDirectory(); // Eğer yeni klasör oluşturma işlemi başlatılmışsa onu tamamla
					updateDirectory(); // Eğer varolan bir klasörü güncelleme işlemi başlatılmışsa onu tamamla
				}
				
				// eğer "alt" veya "shift" tuşlarını kullanmıyorsa ve çoklu seçim özelliğini kullanmamız isteniyorsa, sürükle yöntemiyle eleman seçme özelliğini aç
				if(!e.ctrlKey && !e.shiftKey && options.multiselection)
				{
					self.selector.selectableObjects = objects.browserFilesList.find(".editor_content");
					self.selector.selectableArea = objects.browserFilesList;
					self.startCapturing(e);
				}
				
				// Üzerine tıklanan eleman bir dosya yada klasör değil ise fonksiyonu bitir
				if(!($editor_content = $(e.target).closest(".editor_content")))
				{
					return false;
				}
				
				// Eğer ctrl key kullanılmamışsa veya multiselection özelliği kapalı ise önceden seçili olan elemanların hepsini seçilmemiş duruma getiriyoruz
				if(!e.ctrlKey || (options.multiselection === false))
				{
					objects.browserFilesList.find("li").removeClass("selected");
				}
				
				if(e.ctrlKey && (options.multiselection === true)) // eğer ctrl tuşu kullanılmış ve çoklu seçip ipal edilmişse
				{
					if($editor_content.is(".selected"))
						$editor_content.removeClass("selected");
					else
						$editor_content.addClass("selected");
				}
				else if(e.shiftKey && (options.multiselection === true)) // eğer shift butonu kullanılmış ve çoklu seçim aktif edildiyse
				{
					// shift ile seçim yaparken kullanacağımız hmtlobject dizisinin başlangıç ve bitiş indexlerini hesapla
					$start = $first_index = $editor_content.index();
					$end = $second_index = objects.browserFilesList.find(".last_selected").index();
					
					// eğer ters yönde seçme işlemi yapılıyorsa
					if($first_index > $second_index)
					{
						$start = $second_index;
						$end = $first_index;
					}
					
					objects.browserFilesList.find("li").slice($start, ++$end).addClass("selected");
				}
				else
				{
					// Üzerine tıklanan elemanı seç
					$editor_content.addClass("selected");
				}
				
				// En son seçilen elemanı belirle
				objects.browserFilesList.find(".last_selected").removeClass("last_selected"); // önceden eklenmiş olan last_selected class'larını kaldır.
				$editor_content.addClass("last_selected"); // şimdi tıklanan objeye last_selected class'ı ekle.
				
				// "Kullan" butonunun durumunu belirlemek için bu eventi çalıştırıyoruz
				events.onContentStatusChange();
				e.preventDefault();
			},
			onDocumentMouseUp: function(e){
				if($(e.target).closest(objects.browserFilesListOuter).length <= 0)
				{
					// eğer browserFilesListOuter alanına tıklamamışsa bu alanın focused değerini false yap. 
					// bu şekilde buraya bağladığın klavye kısayollarını kullanıp kullanmaman gerektiğini bu 
					// değişkene bakarak kontrol edebilirsin.
					objects.browserFilesListOuter.focused = false; 
				}
				
				self.stopCapturing(e);
				events.onDragEnd();
			},
			onDocumentKeyDown:function(e){
				if(e.keyCode == 46) // Eğer delete tuşuna basmışsa
				{
					deleteFileEditorItems(objects.browserFilesList.find("li.selected"));
				}
				else if(e.keyCode == 13) // Eğer enter tuşuna basmışsa
				{
					// Eğer yeni klasör oluşturma işlemi veya varolan bir klasörü güncelleme işlemi başlatılmışsa onu tamamla
					if(createDirectory() || updateDirectory()) 
					{
						return true;
					}
					
					if(objects.browserFilesListOuter.focused === true){
						// Aksi bir durum yoksa seçili elemanları kullanma eventini çalıştır.
						events.onUseSelectedItems();
					}
				}
				else if((e.keyCode == 65) && e.ctrlKey) // eğer CTRL + A  tuşuna basıldıysa
				{
					if(objects.browserFilesListOuter.focused === true)
					{
						objects.browserFilesList.find("li").addClass("selected");
						return false;
					}
				}
				else if(e.keyCode == 27) // Eğer esc tuşuna basarsa
				{
					self.stopCapturing(e); // sürükleme ile çoklu dosya seçimi işlemini bitir.
					events.onDragEnd(); // Sürükleyerek dosya ekleme işlemi başlatılmışsa onu iptal et
					$("#newDirectory").remove(); // Eğer yeni klasör oluşturma işlemi başlatılmışsa onu iptal et
					cancelFolderUpdating(); // Eğer varoan klasör ismini güncelleme işlemi yapılıyorsa onu iptal et
				}
				else if(e.keyCode == 113) // Eğer f2 tuşuna basıyorsa
				{
					if((objects.browserFilesListOuter.focused === true) && ($selected_folder = objects.browserFilesList.find(".selected.directory:first")) && ($selected_folder.length > 0))
					{
						temp = $selected_folder.find("span");
						temp.replaceWith("<input type='text' id='updateFolderName' value='" + temp.html() + "' old_value='" + temp.html() + "' />");
						$("#updateFolderName").focus();
						delete temp;
					}
				}
				else if(e.keyCode == 116) // Eğer f5 tuşuna basmışsa
				{
					if((objects.browserFilesListOuter.focused === true) && !e.ctrlKey)
					{
						events.onRefreshCurrentDirectory(e);
						return false;
					}
				}
				else if(e.keyCode == 8) // eğer backspace tuşuna basmışsa
				{
					if((objects.browserFilesListOuter.focused === true) && ($("#updateFolderName, #newFolderName").length <= 0)){
						events.onMoveUpperDirectory(e);
					}
				}
				
				return true;
			},
			onEditorItemDeleteButtonClick: function(){
				deleteFileEditorItems($(this).closest(".editor_content"));
				return false;
			},
			onUseSelectedItems:function(){
				if(($selected_files = objects.browserFilesList.find(".file.selected")) && ($selected_files.length > 0))
				{
					files = [];
					$selected_files.each(function(){
						file = $(this);
						files.push({"file_id":file.attr("file_id"), "basename":file.attr("title"), "url":file.attr("url"), "thumb":file.attr("thumb"), "type":file.attr("file_type")});
					});
					
					options.onFilesSelect(files);
					public_methods.closeFileEditor();
				}
			},
			onContentStatusChange:function(){
				if((objects.browserFilesList.find(".file.selected").length > 0) && (objects.browserFilesList.find(".directory.selected").length <= 0))
					objects.browserBtnUseFiles.attr("disabled", false);
				else
					objects.browserBtnUseFiles.attr("disabled", true);
			},
			onFilesSelectedForUpload:function(e){
				e.stopPropagation();
				e.preventDefault();
				
				// upload edilecek dosyaların kullanıcı tarafından eklenme şekline göre (normal yükleme veya Drag & Drop şeklinde) dosyaları seç
				files = e.type == "change" ? e.target.files : e.dataTransfer.files;
				
				for(i=0, j=files.length; i<j; i++)
				{
					// statik değeri arttır
					FILE_EDITOR.uploaded_file_count++;
					
					// kullanılacak classları örnekle
					xhr_list[i] = new XMLHttpRequest();
					editor_form_list[i] = new FormData(); 
					file_reader_list[i] = new FileReader();
					
					// form bilgilerini ekle
					editor_form_list[i].append("file", files[i]);
					editor_form_list[i].append("directory_id", current_directory_id);
					editor_form_list[i].append("admin_action", "uploadFile");
					
					// filereader eventini bağla
					file_reader_list[i].addEventListener("load", (function(i){
						var index = i;
						return function(e){
							xhr_list[index].open("post",script_file);
							xhr_list[index].send(editor_form_list[index]);
						};
					}(i)));
					
					// xhr eventlerini bağla-------------------------------------------
					xhr_list[i].addEventListener("loadstart", (function(){ 
						var index = FILE_EDITOR.uploaded_file_count;
						
						return function(e){
						queueItem  = '<li id="queue_' + index + '" class="fileUploadingOuter uploadifyQueueItem">';
						queueItem += '<span class="uploadingText">Yükleniyor...</span>';
						queueItem += '<span class="uploadBarOuter">';
						queueItem += '<span id="bar_' + index + '" class="bar">';
						queueItem += '<span class="uploaderImage"></span>';
						queueItem += '</span></span></li>';
						
						objects.browserFilesList.append(queueItem);
						queueItem = "";
					}; }()));
					
					xhr_list[i].addEventListener("progress", (function(){ 
						var index = FILE_EDITOR.uploaded_file_count;
						
						return function(e){
							$("#bar_" + index).css("left", ((e.loaded / e.total) * 111) - 111);
					}; }()));
					
					xhr_list[i].addEventListener("load", (function(){ 
						var index = FILE_EDITOR.uploaded_file_count;
						
						return function(e){
							$response = $.parseJSON(e.target.response);
							
							if($response.success === true){
								$temp = $(generateFileHtml($response.file));
								$temp.attr("id", "uploaded_" + index);
								$("#queue_" + index).replaceWith($temp);
								// yeni yüklenen dosyanın thumbnailinin load eventini burdan bağla, delegate veya live ile bağlanınca çalışmıyorlar
								objects.browserFilesList.find("#uploaded_" + index).removeAttr("id").find(".editorFileThumb").load(events.onFileThumbnailLoad).error(events.onFileThumbnailError);
								//-------------------------------------------------------------------------------------------------------------------
								delete $temp;
							}
							else{
								console.log($response.msg);
							}
							
							delete $response;
					}; }()));
					//---------------------------------------------------------------
					
					file_reader_list[i].readAsDataURL(files[i]);
				}
				
				objects.editorDragFilesArea.css({"visibility":"hidden"});
				delete files;
			},
			onDragOver:function(e){				
				objects.editorDragFilesArea.css({"visibility":"visible"});
			},
			onDragEnd:function(){
				objects.editorDragFilesArea.css({"visibility":"hidden"});
			},
			onMoveUpperDirectory: function(e){
				e.preventDefault();
				 
				if((temp = objects.browserDirectoriesOuter.find(".selected")) && (temp.length > 0))
				{
					// Var ise üst dizine çık
					if((temp2 = temp.parent().parent()) && (temp2.is("[directory_id]"))) 
					{
						temp2.trigger("mousedown");
					}
					else // bir üst dizin bulunamadıysa ana dizine git.
						objects.browserBtn_Home.trigger("click");
					
					delete temp2;
				}
				
				delete temp;
			},
			onCreateNewDirectory: function(e){
				// daha önce başlatılan klasör ismini değiştirme işlemi varsa o işlemi tamamla
				updateDirectory();
				
				// editördeki seçili elemanların seçilme durumlarını iptal et
				objects.browserFilesList.find(".selected").removeClass("selected");
				
				// Eğer daha önce yeni klasör oluşturma işlemi başlatılmış ama tamamlanmamışsa
				if($("#newDirectory").length <= 0)
				{
					newDirectoryHtml = '<li class="directory editor_content" id="newDirectory" directory_id="" parent_id="-1">';
					newDirectoryHtml += '<icon></icon>';
					newDirectoryHtml += '<input type="text" id="newFolderName" value="" /></li>';
					
					objects.browserFilesList.append(newDirectoryHtml);
				}
				
				$("#newFolderName").focus();
				return false;
			},
			onLoadDirectoryTree: function(){
				$.ajax({
					type:"post",
					url:script_file,
					data:"admin_action=loadDirectoryTree",
					dataType:"json",
					success:function(response){
						if(response.success === true)
						{
							objects.browserDirectoriesOuter.html(response.directory_tree);
						}
						else
						{
							alert("Beklenmedik bir hata oluştu, lütfen tekrar deneyin!");
						}
					},
					error:function(){
						alert("Beklenmedik bir hata oluştu, lütfen tekrar deneyin!");
					}
				});
			},
			onListFavouritedDirectories: function(){
				$.ajax({
					type:"post",
					url:script_file,
					data:"admin_action=loadFavouritedDirectories",
					dataType:"json",
					success:function(response){
						if(response.success === true)
						{
							objects.browserFavouritesList.html(response.favourited_directories);
						}
						else
						{
							alert("Beklenmedik bir hata oluştu, lütfen tekrar deneyin!");
						}
					},
					error:function(){
						alert("Beklenmedik bir hata oluştu, lütfen tekrar deneyin!");
					}
				});
			}
		};
		
		var public_methods = {
			openFileEditor:function(properties){
				// İlk ayarları belirle
				options = $.extend({}, default_properties, properties);
				//------------------------------------------------------------------
				
				//İçerikleri yüklemeye başla
				events.onListFavouritedDirectories();
				events.onLoadDirectoryTree();
				events.onLoadFiles();
				//---------------------------------------------
				
				// "Kullan" butonunu disable yap, çünkü henüz hiçbir dosya seçilmedi
				objects.browserBtnUseFiles.attr("disabled", true);
				
				// ilk açılışta içerik alanına focus olmuş kabul ediyoruz
				objects.browserFilesListOuter.focused = true;
				
				// Editörü göster
				objects.fileEditorMainContainer.css({"visibility":"visible", "opacity":"1"});
				setTimeout(function(){
					objects.fileEditorOuter.css({"opacity":"1"});
				}, 250);
			},
			
			closeFileEditor:function(){
				objects.fileEditorMainContainer.css({"opacity":"0"});
				setTimeout(function(){
					objects.fileEditorOuter.css({"opacity":"0"});
					objects.fileEditorMainContainer.css({"visibility":"hidden"});
					objects.browserFilesListOuter.focused = false;
				}, 250);
			},
			
			initialize: function(properties){
				if($("#fileEditorMainContainer").length <= 0)
				{
					editor_html += '<div id="fileEditorMainContainer">';
					editor_html += '<div id="fileEditorBackHider"></div>';
					editor_html += '<div id="fileEditorOuter">';
					editor_html += '<div id="fileEditorOuter_InnerShell">';
					editor_html += '<div id="fileEditorNavigationBar">';
					editor_html += '<span id="fileEditorTitle">Dosya Editörü</span>';
					editor_html += '<span id="btnCloseFileEditor">Editörü Kapat</span>';
					editor_html += '</div>';
					editor_html += '<div id="fileEditorContentsOuter">';
					editor_html += '<div id="browserLeftCorner">';
					editor_html += '<label class="labelFavourites browserBigTitle">Sık Kullanılanlar</label>';
					editor_html += '<div id="browserFavouritesList"></div>';
					editor_html += '<label class="labelDirectory browserBigTitle">Dizinler</label>';
					editor_html += '<div id="browserDirectoriesOuter"></div>';
					editor_html += '</div>';
					editor_html += '<div id="browserRightCorner">';
					editor_html += '<div id="addressBarOuter">';
					editor_html += '<label class="browserBigTitle">Adres:</label>';
					editor_html += '<input id="browser_address" type="text" name="browser_address" readonly="readonly">';
					editor_html += '<a id="browserBtn_Home" href="" title="Ana Dizin"></a>';
					editor_html += '<a id="browserBtn_Refresh" href="" title="Yenile"></a>';
					editor_html += '<a id="browserBtn_Fav" href="" title="Sık Kullanılanlara Ekle"></a>';
					editor_html += '<a id="browserBtn_Prev" href="" title="Geri Dön"></a>';
					editor_html += '<a id="browserBtn_NewDir" href="" title="Yeni Klasör"></a>';
					editor_html += '<a id="browserBtn_UploadFile" onclick="javascript:void(0);" title="Dosya Yükle">';
					editor_html += '<input id="fileEditorUploader" type="file" multiple="multiple">';
					editor_html += '</a>';
					editor_html += '</div>';
					editor_html += '<div id="browserSearchOuter">';
					editor_html += '<label class="browserBigTitle">Arama:</label>';
					editor_html += '<input id="search_input" type="text" disabled="disabled" title="Yapım Aşamasında!" >';
					editor_html += '<p id="searchAlertBox" style="display:none;">Kayıt bulunamadı!</p>';
					editor_html += '</div>';
					editor_html += '<div id="tempQueue" style="display:none;"></div>';
					editor_html += '<div id="browserCotentsOuter">';
					editor_html += '<div id="fileEditorContentsLoader"><span></span></div>';
					editor_html += '<div id="browserFilesListOuter">';
					editor_html += '<div id="editorDragFilesArea"><span></span></div>';
					editor_html += '<ul id="browserFilesList">';
					editor_html += '</ul>';
					editor_html += '<div id="browserFilesListScroller"></div>';
					editor_html += '</div>';
					editor_html += '</div>';
					editor_html += '<button id="browserBtnUseFiles" type="button">Kullan</button>';
					editor_html += '</div>';
					editor_html += '</div>';
					editor_html += '</div>';
					editor_html += '</div>';
					editor_html += '</div>';
					
					$("body").append(editor_html);
					
					// Özellikleri belirle
					loadElements();
					bindEvents();
				}
			}
		};
		
		return public_methods;
	};
	
	function SELECTING_OBJECTS_2D(){
		var self = this;
		var deleteFilesList = new Array();
		var startPosX = 0;
		var startPosY = 0;
		var currentPos = {};
		var selectableAreaPos = {};
		var placeholderWidth = 0;
		var placeholderHeight = 0;
		var placeholderLeft = 0;
		var placeholderTop = 0;
		var placeholderRight = 0;
		var placeholderBottom = 0;
		var selectabeObjectPos = 0;
		var selectabeObjectLeft = 0;
		var selectabeObjectRight = 0;
		var selectabeObjectTop = 0;
		var selectabeObjectBottom = 0;
		
		// public_events
		this.onCapturing = function(){};
		
		// public options
		this.drag_select_enabled = false;
		this.selector = {};
		this.selector.selectableObjects = null;
		this.selector.selectorPlaceHolder = null;
		this.selector.selectableArea = null
		
		this.startCapturing = function(e){
			if((self.selector.selectableObject === null) || (self.selector.selectableArea === null))
			{
				console.log("Hata: 'selectableObject' ve 'selectableArea' değerleri atanmamış!");
				self.drag_select_enabled = false;
				return false;
			}
			
			$placeholder = $("#selectorPlaceHolder");
			
			if($placeholder.length <= 0)
			{
				$placeholder = $("<div id='selectorPlaceHolder'></div>");
				self.selector.selectableArea.append($placeholder);
				self.selector.selectorPlaceHolder = $placeholder;
				placeholderCreated = true;
			}
			
			self.drag_select_enabled = true;
			selectableAreaPos = self.selector.selectableArea.offset();
			startPosX = e.pageX - selectableAreaPos.left;
			startPosY = e.pageY - selectableAreaPos.top;
			self.selector.selectorPlaceHolder.css("display","block");
			self.selector.selectableArea.on("mousemove", captureObjectsEvent);
		};
		
		this.stopCapturing = function(){
			if(self.drag_select_enabled)
			{
				self.drag_select_enabled = false;
				self.selector.selectorPlaceHolder.css({"width":"0","height":"0","left":"-10px","top":"-10px","display":"none"});
				self.selector.selectableArea.off("mousemove");
			}
		};
		
		// PRIVATE FUNCTIONS
		var captureObjectsEvent = function(e){
			if(!self.drag_select_enabled)
				return;
			
			selectableAreaPos = self.selector.selectableArea.offset();
			currentPos.left = e.pageX - selectableAreaPos.left;
			currentPos.top = e.pageY - selectableAreaPos.top;
			
			placeholderLeft = -10;
			placeholderTop = -10;
			placeholderWidth = 0;
			placeholderHeight = 0;
			
			
			// Seçim işlemi yaparken oluşturulan görsel alanın özelliklerini ayarla
			if((currentPos.left > startPosX) && (currentPos.top > startPosY)){ // I. BÖLGE
				placeholderLeft = startPosX;
				placeholderTop = startPosY;
				placeholderWidth = currentPos.left - placeholderLeft;
				placeholderHeight = currentPos.top - placeholderTop;
			}
			else if((currentPos.left < startPosX) && (currentPos.top > startPosY)){ // II. BÖLGE
				placeholderLeft = currentPos.left;
				placeholderTop = startPosY;
				placeholderWidth = startPosX - placeholderLeft;
				placeholderHeight = currentPos.top - placeholderTop;
			}
			else if((currentPos.left > startPosX) && (currentPos.top < startPosY)){ // III. BÖLGE
				placeholderLeft = startPosX;
				placeholderTop = currentPos.top;
				placeholderWidth = currentPos.left - placeholderLeft;
				placeholderHeight = startPosY - placeholderTop;
			}
			else if((currentPos.left < startPosX) && (currentPos.top < startPosY)){ // IV. BÖLGE
				placeholderLeft = currentPos.left;
				placeholderTop = currentPos.top;
				placeholderWidth = startPosX - placeholderLeft;
				placeholderHeight = startPosY - placeholderTop;
			}
			
			self.selector.selectorPlaceHolder.css({
				"left":placeholderLeft,
				"top":placeholderTop,
				"width":placeholderWidth,
				"height":placeholderHeight
			});

			
			placeholderRight = placeholderLeft + placeholderWidth;
			placeholderBottom = placeholderTop + placeholderHeight;
			
			// İlk olarak seçilen tüm elemanların seçilme durumunu sıfırla
			if((placeholderWidth > 2) || (placeholderHeight > 2))
				self.selector.selectableObjects.removeClass("selected");
			
			// İkinci olarak elemanların her birini tek tek kontrol edip seçilme durumlarını belirle
			self.selector.selectableObjects.each(function(){
				selectabeObjectPos = $(this).position();
				selectabeObjectLeft = selectabeObjectPos.left + 20;
				selectabeObjectRight = 0;
				selectabeObjectTop = 0;
				selectabeObjectBottom = 0;
				
				if(!(placeholderRight > selectabeObjectLeft))
					return true;
				else
					selectabeObjectRight = selectabeObjectLeft + 145;
					
				if(!(placeholderLeft < selectabeObjectRight))
					return true;
				else
					selectabeObjectTop = selectabeObjectPos.top + 20;
					
				if(!(placeholderBottom > selectabeObjectTop))
					return true;
				else
					selectabeObjectBottom = selectabeObjectTop + 130;
				
				if(!(placeholderTop < selectabeObjectBottom))
					return true;
				else
					$(this).addClass("selected");
			});
			
			// onCapturing eventini çalıştır.
			self.onCapturing();
		};
	}
	
	var EDITOR = new FILE_EDITOR();
	
	$.fn.fileeditor = function(action, value){
		if((typeof(action) == "object") || !action)
		{
			return this.each(function(){
				EDITOR.initialize(action);
			});
		}
		else
		{
			if(EDITOR[action])
			{
				return this.each(function(){
					EDITOR[action](value);
				});				
			}
			else
			{
				console.log(action + " fonksiyonu bulunamadı!");
				return this;
			}
		}
	};
}(jQuery));