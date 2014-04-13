$(ProfileStart);

var currentEmail;

function ProfileStart()
{
	currentEmail = $.trim($("#email").val());
	$("#email").blur(checkEmail);
}

function checkUserInfo()
{
	var pass1 =	$.trim($("#password").val());
	var pass2 = $.trim($("#password_again").val());
	var error = false;
	var message = "";
	
	if(!VALIDATE.validateEmail($("#email").val()))
	{
		error = true;
		message = GT.GECERLI_EPOSTA_ADRESI_GIRIN;
	}
	else if((pass1 != "") || (pass2 != ""))
	{
		if(pass1 != pass2)
		{
			error = true;
			message = GT.HATA_PAROLALAR_ESLESMIYOR;
		}
		else if(pass1.length < 6)
		{
			error = true;
			message = GT.PAROLA_MIN_ALTI_OLMALI;
		}
		else
		{
			var password = SHA1($("#password").val());
			$("[name=password]").val(password);
		}
	}
	
	if(error)
	{
        MESSAGEBOX.showMessage(GT.HATA_OLUSTU, message, messageType.ERROR, [{"name":GT.TAMAM}]);
	}
	return !error;
}

function checkEmail()
{
	var email = $.trim($(this).val());
	if(!VALIDATE.validateEmail(email))
	{
		$("#emailCheckLoader p").html(GT.GECERLI_EPOSTA_ADRESI_GIRIN).addClass("error");
	}
	else if(email != currentEmail)
	{
		$("#emailCheckLoader p").html("");
		$.ajax({
			data:"admin_action=checkemail&email=" + email,
			success:function(response){
				$("#emailCheckLoader img").css("display","none");
				try
				{
					var result = eval("(" + response + ")");
					
					if(result.error == "false")
					{
						$("#emailCheckLoader p").html(result.message).removeClass("error");
					}
					else
					{
						$("#emailCheckLoader p").html(result.message).addClass("error");
					}
				}
				catch(e)
				{
					$("#emailCheckLoader p").html(response).addClass("error");
				}
			},
			beforeSend:function(){
				$("#emailCheckLoader img").css("display","block");
				$("#emailCheckLoader p").html("");
			}
		});
	}
	else
		$("#emailCheckLoader p").html("");
}