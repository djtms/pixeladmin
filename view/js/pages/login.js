$(LoginStart);

function LoginStart()
{
	$("[name=username]").focus();
}

function encryptPassword()
{
	var password = SHA1($("#password").val());
	$("[name=password]").val(password);
}