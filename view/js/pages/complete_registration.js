$(CompleteRegistrationStart);

function CompleteRegistrationStart()
{
	$("[name=username]").focus();
}

function checkForm()
{
	var canPost = true;
	
	if($("[name=username]").val().length < 6)
	{
		canPost = false;
		$("#resultText").html('"Kullanıcı Adı" en az 6 karakterden oluşmalı!');
	}
	else if($("#password").val().length < 6)
	{
		canPost = false;
		$("#resultText").html('"Parola" en az 6 karakterden oluşmalı!');
	}
	else if($("#password").val() != $("#password_again").val())
	{
		canPost = false;
		$("#resultText").html("Parolalar uyuşmuyor!");
	}
	else
	{
		$("#resultText").html("");
		$("#usernameCheckLoader").css("display","block");
		$.ajax({
			type:"post",
			url:"complete_registration.php",
			data:"admin_action=checkusername&username=" + $("[name=username]").val(),
			dataType:"json",
			async:false,
			success:function(response)
			{
				if(response.error)
				{
					canPost = false;
					$("#resultText").html(response.message);
				}
				else
				{
					canPost = true;
					encryptPassword();
					$("#resultText").html("");
				}
			},
			complete:function(){
				$("#usernameCheckLoader").css("display","none");
			}
		});
	}
	
	return canPost;
}

function encryptPassword()
{
	var password = SHA1($("#password").val());
	$("[name=password]").val(password);
}