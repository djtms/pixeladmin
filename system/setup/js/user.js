var validate;

$(UserStart);

function UserStart()
{
	validate = new Validate();
}






function checkForm()
{
	var message = "";
	
	if(!validate.validateUsername($("[name=username]").val()))
	{
		message = "kullanıcı adında boşluk karakteri kullanmayın!";
	}
	else if($("[name=siteTitle]").val().length < 1)
	{
		message = "site başlığı girilmedi!";
	}
	else if($("[name=username]").val().length < 6)
	{
		message = "en az 6 karakterlik bir kullanıcı adı girin!";
	}
	else if($("#password").val().length < 6)
	{
		message = "en az 6 karakterlik bir parola girin!";
	}
	else if($("#password").val() != $("#password_again").val())
	{
		message = "parolalar eşleşmiyor!";
	}
	else if(!validate.validateEmail($("#email").val()))
	{
		message = "uygun bir mail adresi girin!";
	}
	else
		message = "";
	
	$("#errorText").html(message);
	
	if(message == "")
	{
		encryptPassword();
		return true;
	}
	else
		return false;
}

function encryptPassword()
{
	var password = SHA1($("#password").val());
	$("[name=password]").val(password);
}