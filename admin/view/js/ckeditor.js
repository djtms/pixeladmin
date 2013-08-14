$(CkeditorStart);

var ckIds; 

function CkeditorStart()
{
	var i=0;
	var id;
	ckIds = new Array();
	
	$("#content textarea[editor]").each(function(){
		i++;
		try
		{
			var id; 
			id = $(this).attr("id");

			if(!$(this).has("[id]") || ($.trim(id).length <= 0))
			{
				id = "paEditor_p" + i;
				$(this).attr("id",id);
			}
			
			if(!$(this).is("[i18n]") || ($.trim($(this).attr("i18n")).length > 0)) // Eğer editör i18n türünde ise ve i18n değeri atanmamışsa bu editörü i18n.js dosyasında unique bir i18n kodu üretip editöre atadıktan sonra yüklüyoruz. 
			{
				EDITORS[id] = CKEDITOR.replace(id,{"customConfig":"config/my_ckeditor.js"});
				if($(this).is("[i18n]")) // Eğer editör i18n değil ise bu eventi bağlamamak gerekiyor.
					EDITORS[id].on("key",function(){isI18nSencronised = false;});
				CKFinder.setupCKEditor(EDITORS[id], 'view/components/ckfinder/');
				ckIds.push("cke_" + id);
			}
		}
		catch(e)
		{
			alert(e);
		}
	});
	
	for(var i=0; i<ckIds.length; i++)
	{
		$("#" + ckIds[i]).css("float","left");
	}	
}