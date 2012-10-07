$(I18nStart);

var i18nCodesArray = new Array();
var i18nDataArray = new Array();
var isI18nSencronised = true;
var activeLanguage = "";
var requestedLanguage = "";

function I18nStart()
{
	activeLanguage = defaultLanguage;
	$("[i18n]").keydown(function(){
		isI18nSencronised = false;
	});
	
	$("[i18n]").each(function(){
		var i18nObject = $(this);
		
		// multilanguage_mode  açık olup olmadığını kontrol edip ona göre farklı işlem yapıyoruz.
		if(multilanguage_mode === false)
		{
			if(!i18nObject.is("[forcei18n]"))
			{
				i18nObject.attr("disabled","disabled");
				i18nObject.attr("title", "Lütfen \"Çoklu Dil\" modunu aktif edin!");
				return true;
			}
		}
		////////////////////////////////////////////////////////////////////////////////////////
		
		var formObject = $(this).parents("form");
		
		if($.trim(i18nObject.attr("i18n")).length <= 0)
		{
			var uniqueI18nCode = uniqid();
			i18nObject.attr("i18n",uniqueI18nCode);
			
			if(i18nObject.is("[editor]"))
			{
				var id = i18nObject.attr("id"); 
				EDITORS[id] = CKEDITOR.replace(id);
				EDITORS[id].on("key", function(){isI18nSencronised = false;});
				CKFinder.setupCKEditor(EDITORS[id], 'view/components/ckfinder/');
				ckIds.push("cke_" + id);
			}
		}
		
		
		var hiddenObject = '<input type="hidden" name="' + i18nObject.attr("name") + '" value="' + i18nObject.attr("i18n") + '" />';
		formObject.append(hiddenObject);
		i18nObject.removeAttr("name");
	});
	
	// Dil tablarını oluştur
	if(multilanguage_mode || ($("[i18n][forcei18n]").length > 0))
	{
		if($("[i18n]").length > 0)
		{
			languageCount = availableLanguages.length;
	
			var languageTabs = '<div id="i18nLanguageOuter" ' + (!multilanguage_mode ? " style='display:none;' " : "") + ' >';
			
			languageTabs += '<div id="i18nButtonsOuter">';
			
			for(var i=0; i<languageCount; i++)
			{
				var isCurrentLanguage = availableLanguages[i].locale == activeLanguage ? true : false;
				var _attrClass = ' class="' +(isCurrentLanguage ? 'activeLanguage' : "") + ' i18nLanguage"';
				var _attrLocale = ' locale="' + availableLanguages[i].locale + '" ';
				var _attrLanguageAbbr = ' language_abbr="' + availableLanguages[i].language_abbr + '" ';
				var _attrTitle = ' title="' + availableLanguages[i].language_name + '" ';
				var _attrCountryAbbr = ' country_abbr="' + availableLanguages[i].country_abbr + '" ';
				var _attrCountryName = ' country_name="' + availableLanguages[i].country_name + '" ';
				var text = (isCurrentLanguage ? availableLanguages[i].language_name : availableLanguages[i].language_abbr);
				languageTabs += "<span " + _attrClass + _attrLocale + _attrLanguageAbbr +  _attrTitle + _attrCountryAbbr + _attrCountryName + " >" + text + "</span>";
			}
			languageTabs += '</div>';
			languageTabs += '<img id="i18nLoader" src="' + VIEW_URL + '/images/loader.gif" />';
			languageTabs += '</div>';
			
			
			$("#postMessage").after(languageTabs);
			var temp = $(".activeLanguage");
			$(".activeLanguage").remove();
			$("#i18nButtonsOuter").prepend(temp);
		}
		
		$(".i18nLanguage").click(function(){
			
			requestedLanguage = $(this).attr("locale");
			activeLanguage = $(".activeLanguage").attr("locale");
			
			if((requestedLanguage != activeLanguage) && !isI18nSencronised)
			{
				MESSAGEBOX.showMessage("Kaydedilmeyen Veriler!", "\"" + $("span[locale=" + activeLanguage + "]").html() + "\" içerik henüz kaydedilmedi, bu sayfadan ayrılmadan önce bilgilerinizin kaybolmaması için Lütfen Kaydedin.",messageType.WARNING,[{"name":"Kaydet","click":ajaxSaveI18n},{"name":"Atla","click":selectI18n}]);
			}
			else
				selectI18n();
		});
	}
	////////////////////////////////////////////////////////////////////////
	
	$("form").each(function(){
		if($(this).find("[i18n]").length > 0)
		{
			var i18nHiddenInputs = "<input type='hidden' name='i18nTextsGroup' value='' />";
			i18nHiddenInputs += "<input type='hidden' name='i18nLanguage' value='' />";
			
			$(this).append(i18nHiddenInputs);
			if(!$(this).attr("method") || $(this).attr("method").toLowerCase() == "get")
			{
				// FIXME: messageBox burada çalışmıyor
				MESSAGEBOX.showMessage("Uyarı!", "Formunuzda \"Çoklu Dil\" özelliği kullandığınız için form elemanınızın veri gönderme şekli \"post\" olarak ayarlandı!", messageType.WARNING, [{name:"Tamam", click:MESSAGEBOX.hideMessage}]);
			}
			$(this).attr("method","post");
			$(this).submit(setI18nValuesToHiddenInput);
		}
	});
	
	$(".activeLanguage").trigger("click");
}

function selectI18n()
{
	updateStyles();
	$.ajax({
		data:"admin_action=selectI18n&i18nLanguage=" + requestedLanguage + "&codes=" + JSON.encode(i18nCodesArray),
		dataType:"json",
		success:function(response){
			activeLanguage = requestedLanguage;
			
			var count = response.length;
			for(var i=0; i<count; i++)
			{
				var selector = "[i18n=" + response[i].i18nCode + "]";
				if(multilanguage_mode || ($(selector + "[forcei18n]").length > 0))
				{
					if($(selector).is("[editor]"))
					{
						var editorName = $(selector).attr("id");
						EDITORS[editorName].setData(response[i].text,function(){
							this.resetDirty();
						});
						$("#" + editorName).css("opacity","1");
					}
					else
					{
						$(selector).val(response[i].text).attr("disabled",false).css("opacity","1");
					}
				}
			}
			
			$("#i18nLoader").css("display","none");
		},
		error: function(){
			postMessage("\"Çoklu Dil\" içeriği yüklenirken hata oluştu!", true);
		}
	});
	
	isI18nSencronised = true;
	MESSAGEBOX.hideMessage();
}

function setI18nValuesToHiddenInput()
{	
	var i18nDataArray = new Array();
	
	if($(this).find("[i18n]").length > 0)
	{
		$(this).find("[i18n]").each(function(){
			var i18nCode = $(this).attr("i18n");
			if($(this).is("[editor]"))
			{	
				var id = $(this).attr("id");
				var text = EDITORS[id].getData();
			}
			else
				var text = $(this).val();
			
			i18nDataArray.push({"i18nCode":i18nCode,"text":text});
		});
		
		$(this).find("[name=i18nTextsGroup]").val(JSON.encode(i18nDataArray));
		$(this).find("[name=i18nLanguage]").val(activeLanguage);
	}
}

function updateStyles()
{
	i18nCodesArray = new Array();
	
	var t1 = "span[locale=" + activeLanguage + "]";
	var t2 = "span[locale=" + requestedLanguage + "]";
	
	$(t1).html($(t1).attr("language_abbr"));
	$(t1).removeClass("activeLanguage");
	$(t2).html($(t2).attr("title"));
	$(t2).addClass("activeLanguage");
	
	$("#i18nLoader").css("display","block");
	$("[i18n]").each(function(){
		var i18nCode = $(this).attr("i18n");
		i18nCodesArray.push({"i18nCode":i18nCode});
		
		if($(this).is("[editor]"))
		{
			var id = $(this).attr("id");
			$("#" + id).css("opacity","0.5");
		}
		else
			$(this).attr("disabled",true).css("opacity","0.5");	
	});
}

function ajaxSaveI18n()
{
	updateStyles();
	var i18nForm = new FormData();
	var i18nText = "";
	
	i18nForm.append("admin_action","ajaxSaveI18n");
	i18nForm.append("i18nLanguage",activeLanguage);
	
	$("[i18n]").each(function(){
		var i18nCode = $(this).attr("i18n");
		
		if($(this).is("[editor]"))
		{	
			var editorId = $(this).attr("id");
			i18nText = EDITORS[editorId].getData();
		}
		else
		{
			i18nText = $(this).val();
		}
		
		i18nForm.append("i18nCode[]",i18nCode);
		i18nForm.append("i18nText[]",i18nText);
	});
	
	var xhr = new XMLHttpRequest();
	xhr.addEventListener("load",function(){
		selectI18n();
		postMessage("Başarıyla Güncellendi!");
	});
	xhr.open("POST", "admin.php?page=dashboard");
	xhr.send(i18nForm);
	MESSAGEBOX.hideMessage();
}