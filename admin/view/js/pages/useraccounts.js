$(UserAccounts);

minInputLength = 6;

function UserAccounts(){
	$("#username").blur(checkUsername);
	$("#email").blur(checkEmail);
	$("#username").blur(function(){
		if($.trim($("#displayname").val()) == "" )
			$("#displayname").val($(this).val());
	});
	
	$("#userAccountsList .crossBtn").click(deleteUser);
}

function deleteUser(){
	var del = confirm("Silmek istediğinize eminmisiniz?");
	var user_id = $(this).attr("user_id");
	var button = $(this);
	var row = $(this).parents("li");
	var fadeSpeed = 700;
	
	if(del)
	{
		$.ajax({
			data:"admin_action=deleteuser&user_id=" + user_id,
			success:function(response){
				button.html("");
				try
				{
					var result = eval("(" + response + ")");
					if(result.error == "false")
					{	
						postMessage("Başarıyla Silindi!");
						button.css("opacity","0");
						row.animate({"opacity":"0"},fadeSpeed,function(){row.remove();});
					}
					else
					{
						postMessage("Kullanıcı Silinemedi!", true);
					}
				}
				catch(e)
				{
                    MESSAGEBOX.showMessage("Hata", response);
				}
			},
			beforeSend:function(){
				button.html("<img src='" + VIEW_URL + "images/loader.gif' />");
			}
		});
	}
}

function openAddUserForm(){
	$("#addUserFormOuter").css("display","block");
}

function checkUserInfo(){
	var username = $.trim($("#username").val());
	var pass1 =	$.trim($("#password").val());
	var pass2 = $.trim($("#password_again").val());
	var error = false;
	var message = "";
	
	if(username.length<minInputLength)
	{
		error = true;
		message = "En az " + minInputLength + " karakter uzunluğunda bir \"Kullanıcı Adı\" girin!";
	}
	else if(pass1.length<minInputLength)
	{
		error = true;
		message = "En az " + minInputLength + " karakter uzunluğunda bir \"Parola\" girin!";
	}
	else if(pass1 != pass2)
	{
		error = true;
		message = "\"Parola\" lar uyuşmuyor!";
	}
	
	if(error)
	{
		postMessage(message,error);
		window.location.href = "#postMessage";
	}
	else
	{
		$("[name=password]").val( SHA1(pass1));
	}
	
	return !error;
}

function checkUsername(){
	var username = $.trim($(this).val());
	
	if(username.length >= minInputLength)
	{
		$("#usernameLoader p").html("");
		$.ajax({
			data:"admin_action=checkusername&username=" + username,
			success:function(response){
				
				$("#usernameLoader img").css("display","none");
				try
				{
					var result = eval("(" + response + ")");
					
					if(result.error == "false")
					{
						$("#usernameLoader p").html(result.message).removeClass("error");
					}
					else
					{
						$("#usernameLoader p").html(result.message).addClass("error");
					}
				}
				catch(e)
				{
					$("#usernameLoader p").html(response).addClass("error");
				}
			},
			beforeSend:function(){
				$("#usernameLoader img").css("display","block");
				$("#usernameLoader p").html("");
			}
			
		});
	}
}

function checkEmail()
{
	var email = $.trim($(this).val());
	if(VALIDATE.validateEmail(email)) // daha sonra buraya mail validation koy
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
	{
		$("#emailCheckLoader p").html("Uygun mail adresi girin!").addClass("error");
	}
}