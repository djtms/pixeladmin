$(GalleryStart);

function GalleryStart(){
	$("input[type=gallery]").each(function(){
        $(this).parents("form").attr("method","post"); // Formun metodunu post yapıyoruz

		var gallery = $(this);
		var gallery_id = gallery.val();
		var columnsCount = gallery.is("[cols]") ? (gallery.attr("cols") <= 5 ? gallery.attr("cols") : 5 ) : 5;
		var rowsCount = gallery.is("[rows]") ? gallery.attr("rows") : 1;
        var readonly = $(this).is("[readonly]") ? true : false;

		if((gallery_id == undefined) || (gallery_id==null) || (gallery_id <= 0)){
			$.ajax({
				data:"admin_action=createTemporaryGallery",
				dataType:"json",
				success:function(response){
					gallery_id = response.gallery_id;
					
					if((gallery_id == undefined) || (gallery_id==null) || (gallery_id <= 0))
					{
						postMessage("Galeri yüklenirken hata oluştu!",true);
					}
					else
					{
						gallery.val(gallery_id);
						loadGallery(gallery,columnsCount,rowsCount, readonly);
					}
				}
			});
		}
		else
		{
			loadGallery(gallery,columnsCount,rowsCount, readonly);
		}
	});
	
	$(".galleryList:not(.readonly)").sortable();
}

function loadGallery(gallery,columnsCount,rowsCount,readonly)
{
	var existingFilesIds = new Array(); // Galerinin yüklenmesi sırasında alınan dosyaların id'lerinin olduğu dizi
	var updatedFilesList = new Array(); // Galeride bulunan dosyaların id ve özelliklerinin bulunduğu dizi
	var galleryAndFilesInfo = {}; /* Galerinin ve galeride olan tüm dosyaların ve silinen dosyaların bilgilerinin bulunduğu dizi, bu değişkenin güncel
											  değerini öğrenmek için "galleryObject"'in "calculateGalleryAndFilesInfo" eventini çalıştırmak gerekiyor*/
	var thisName  = gallery.attr("name");
	var thisId    = gallery.attr("id");
	var thisClass = gallery.attr("class");
	var thisStyle = gallery.attr("style");
	var eachItemWidth = 156;
	
	var btnAddFile;
	var galleryObject;
	var galleryListOuter;
	var gallery_id = gallery.val();

	galleryAndFilesInfo = {"gallery_id":gallery_id,"filesInfo":[]};

	var gHtml = '<input type="hidden" name="' + thisName + '" value="' + gallery_id + '" />';
		gHtml += '<input type="hidden" class="galleryFilesInfo" name="galleries[]" value="" />';
		gHtml += '<div class="galleryListOuter">';
		gHtml += '<ul class="galleryList ' + (readonly ? "readonly" : "") + '">';
		gHtml += '</ul><!-- galleryList -->  ';
		gHtml += '</div><!-- galleryListOuter -->';
		gHtml += readonly ? "" : '<button type="button" class="galleryAddFileButton">Ekle</button>';
		gHtml += '</div>';
		
	gallery.wrap('<div class="galleryOuter">');
	
	var _this = gallery.parent();
	_this.attr("id",thisId);
	_this.attr("class",thisClass);
	_this.attr("style",thisStyle);
	
	_this.html(gHtml);
	
	btnAddFile  = _this.find(".galleryAddFileButton");
	galleryObject = _this.find(".galleryList");
	galleryListOuter = _this.find(".galleryListOuter");
	
	galleryObject.css("width",(columnsCount * eachItemWidth));
	var width = (columnsCount * eachItemWidth) + 25;
	galleryListOuter.css({
		"width"		:	width,
		"height"	:	(rowsCount * 117) + 10,
		"overflow-x":	"hidden",
		"overflow-y":	"scroll"
	});
	_this.css({"width":width + 12});
	
	// Galeri ve Galerideki dosyaların özelliklerinin hesaplandığı event
	galleryObject.bind("calculateGalleryAndFilesInfo",function(){
		calculatedFilesList = new Array();
		var galleryItems = $(this).find(".galleryItem");
		for(var i=0; i<existingFilesIds.length; i++)
		{
			var isFileDeleted = true;
			galleryItems.each(function(){
				var fileId = $(this).find(".fileId").val();
				
				if(fileId == existingFilesIds[i].id)
				{
					isFileDeleted = false;
				}
			});
			
			if(isFileDeleted){
				calculatedFilesList.push({id:existingFilesIds[i].id,"status":"deleted"});
			}
		}
		
		galleryItems.each(function(){
			var fileId = $(this).find(".fileId").val();
			var index = $(this).index();
			
			if($(this).hasClass("newFile")){
				calculatedFilesList.push({id:fileId,"status":"new","order":index});
			}
			else if($(this).hasClass("existingFile")){
				calculatedFilesList.push({id:fileId,"status":"existing","order":index});
			}
		});
		
		galleryAndFilesInfo.filesInfo = calculatedFilesList;
		
		_this.find(".galleryFilesInfo").val(JSON.encode(galleryAndFilesInfo));
	});

    if(!readonly){
        btnAddFile.click(function(){
            updatedFilesList = new Array();
            galleryObject.find(".fileId").each(function(){
                var fileId = $(this).val();
                updatedFilesList.push({id:fileId});
            });

            $(document).fileeditor("openFileEditor",{
                multiselection:true,
                onFilesSelect:function(files){
                    var fileCount = files.length;
                    var filesHtml = '';

                    galleryObject.find(".galleryItem").removeClass("lastAdded");
                    for(i=0; i<fileCount; i++)
                    {
                        // Galerinin ilk yüklendiği andaki dosyaları ile sonradan yüklenen dosyaları ayırabilmek için farklı class'lar ekle
                        var fileStatusClass = "newFile";
                        for(var j=0; j<existingFilesIds.length; j++)
                        {
                            if(files[i].file_id == existingFilesIds[j].id)
                            {
                                fileStatusClass = "existingFile";
                                break;
                            }
                        }

                        //editör'de seçilen dosyaları "updatedFilesList" dizisine ekle
                        updatedFilesList.push({id:files[i].file_id});

                        filesHtml += '<li class="galleryItem ' + fileStatusClass + ' lastAdded">';
                        filesHtml += '<img src="' + files[i].thumb + '" fileId=' + files[i].file_id + ' /><span class="shadow"></span>';
                        if(!readonly){
                            filesHtml += '<span class="delButton button">Sil</span>';
                        }
                        filesHtml += '<input class="fileId" type="hidden" value="' + files[i].file_id + '" />';
                        filesHtml += '</li>';
                    }

                    galleryObject.append(filesHtml);
                    galleryObject.sortable();

                    galleryObject.find(".lastAdded").each(function(){
                        var del = $(this).find(".delButton");
                        if(del.length > 0)
                        {
                            del.css("display","block");
                            $(this).hover(function(){
                                del.animate({"opacity":1},300);
                            },
                            function(){
                                del.animate({"opacity":0},300);
                            });
                        }
                    });
                }
            });

            $(this).blur();
        });
    }

	// Galerinin varolan dosyalarını listele
	$.ajax({
		data:"admin_action=listGalleryFiles&gallery_id=" + gallery_id,
		dataType:"json",
		success:function(response){
			if((response == null) || (response == undefined) || (response.length <= 0)){
				return false;
			}
			
			var filesHtml = "";
			existingFilesIds = new Array();
			
			for(var i=0; i<response.length; i++)
			{
				var fileId = response[i].file_id;
				existingFilesIds.push({id:fileId});
				
				filesHtml += '<li class="galleryItem existingFile">';
				filesHtml += '<img src="' + response[i].thumb + '" fileId="' + fileId + '" /><span class="shadow"></span>';
				filesHtml += readonly ? "" : '<span class="delButton button">Sil</span>';
				filesHtml += '<input class="fileId" type="hidden" value="' + fileId + '" />';
				filesHtml += '</li>';
			}
			
			galleryObject.html(filesHtml);
			
			galleryObject.css("width",(columnsCount * eachItemWidth));
			var width = (columnsCount * eachItemWidth) + 25;
			galleryListOuter.css({
				"width"		:	width,
				"height"	:	(rowsCount * 117) + 10,
				"overflow-x":	"hidden",
				"overflow-y":	"scroll"
			});
			_this.css({"width":width + 12});
			
			
			galleryObject.find(".galleryItem").each(function(){
				var del = $(this).find(".delButton");
				if(del){
					del.css("display","block");
					$(this).hover(function(){
						del.animate({"opacity":1},300);
					},
					function(){
						del.animate({"opacity":0},300);
					});
				}
			});
		}
	});
	
	
	galleryObject.parents("form").bind("submit",function(){
		galleryObject.trigger("calculateGalleryAndFilesInfo");
	});
	
	$(".galleryItem .delButton").die("click").live("click",function(){
		var del = confirm("Eminmisiniz ?");
		
		if(del){
			$(this).parents(".galleryItem").animate({opacity:"0"},300,function(){
				$(this).remove();
			});
		}
	});
}