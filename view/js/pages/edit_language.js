$(EditLanguage);

function EditLanguage()
{
	var languageObject = $("[name='language']");
	languageObject.change(listCountries);
	
	if(selected_language_abbr != "no_language")
		languageObject.val(selected_language_abbr).change();
	
	$("#country").change(function(){
		$.ajax({
			type:"post",
			url:"admin.php?page=edit_language",
			data:"admin_action=get_date_format&locale=" + $("[name='language']").val() + "_" + $("#country").val(),
			dataType:"json",
			success:function(response){
				if(response.success === true)
				{
					$("#date_format").val(response.format);
				}
				else
				{
					postMessage("Beklenmedik hata oluştu!", true);
				}
			},
			error: function(){
				postMessage("Beklenmedik hata oluştu!", true);
			}
		});
	});
}

function listCountries()
{
	$.ajax({
		type:"post",
		data:"admin_action=l_c_b_l&language=" + $("[name='language']").val(),
		dataType:"json",
		success:function(response){
			var length = response.length;
			var cHtml = "";
			var l = null;
			
			for(var i=0; i<length; i++)
			{
				l = response[i];
				cHtml += '<option value="' + l.country_abbr + '" ';
				if(selected_country_abbr != "no_country")
					cHtml += selected_country_abbr == l.country_abbr ? ' selected="true" ' : "";
				cHtml += '>' + l.country_name + '</option>';
			}
			
			$("[name='country']").html(cHtml).trigger("change");
		},
		error:function(){
			postMessage("Hata: edit_language.js, Satır:26, Adres: admin/view/js/pages/edit_language.js", true);
		},
		complete: function(){
			
		}
		
	});
}