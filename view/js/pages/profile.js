$(ProfileStart);

var minInputLength = 6; 
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
		message = "Geçerli bir \"E-Posta\" girin!";
	}
	else if((pass1 != "") || (pass2 != ""))
	{
		if(pass1 != pass2)
		{
			error = true;
			message = "\"Parola\" lar uyuşmuyor!";
		}
		else if(pass1.length<minInputLength)
		{
			error = true;
			message = "En az " + minInputLength + " karakter uzunluğunda bir \"Parola\" girin!";
		}
		else
		{
			var password = SHA1($("#password").val());
			$("[name=password]").val(password);
		}
	}
	
	if(error)
	{
		postMessage(message,error);
		window.location.href = "#postMessage";
	}
	return !error;
}

function checkEmail()
{
	var email = $.trim($(this).val());
	if(!VALIDATE.validateEmail(email))
	{
		$("#emailCheckLoader p").html("Uygun mail adresi girin!").addClass("error");
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