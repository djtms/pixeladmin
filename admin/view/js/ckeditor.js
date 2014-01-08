$(CkeditorStart);

CKEDITOR.on('instanceReady', function (ev) {
	// Ends self closing tags the HTML4 way, like <br>.
	ev.editor.dataProcessor.htmlFilter.addRules(
	    {
	        elements:
	        {
	            $: function (element) {
	                // Output dimensions of images as width and height
	                if (element.name == 'img') {
	                    var style = element.attributes.style;

	                    if (style) {
	                        // Get the width from the style.
	                        var match = /(?:^|\s)width\s*:\s*(\d+)px/i.exec(style),
	                            width = match && match[1];

	                        // Get the height from the style.
	                        match = /(?:^|\s)height\s*:\s*(\d+)px/i.exec(style);
	                        var height = match && match[1];

	                        if (width) {
	                            element.attributes.style = element.attributes.style.replace(/(?:^|\s)width\s*:\s*(\d+)px;?/i, '');
	                            element.attributes.width = width;
	                        }

	                        if (height) {
	                            element.attributes.style = element.attributes.style.replace(/(?:^|\s)height\s*:\s*(\d+)px;?/i, '');
	                            element.attributes.height = height;
	                        }
	                    }
	                }



	                if (!element.attributes.style)
	                    delete element.attributes.style;

	                return element;
	            }
	        }
	    });
	});

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