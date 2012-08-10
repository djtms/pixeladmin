$(InviteUserStart);

function InviteUserStart()
{
	$("#btnInviteUser").click(checkUserStatusByEmail);
}

function checkUserStatusByEmail()
{
	var returnValue = true;
	$.ajax({
		data:"admin_action=checkUserStatusByEmail&email=" + $("[name='email']").val(),
		async:false,
		success:function(response){
			if(response === "already_registered")
			{
				var messageText = "Girmiş olduğunuz <b>e-posta adresi</b> başka bir kullanıcı tarafından kullanılmaktadır! Lütfen farklı bir <b>e-posta adresi</b> girin.";
				MESSAGEBOX.showMessage("E-Posta Kullanımda", messageText, messageType.WARNING, [{"name":"Tamam","click":MESSAGEBOX.hideMessage}])
				returnValue = false;
			}
			else if(response == "not_activated_account")
			{
				var messageText = "Girmiş olduğunuz <b>e-posta adresi</b> henüz aktive edilmemiş bir hesaba ait, bu kullanıcıya tekrar bir aktivasyon maili göndermek istermisiniz?";
				MESSAGEBOX.showMessage("Aktive Edilmemiş Kullanıcı E-posta'sı", messageText, messageType.WARNING, [{name:"Aktivasyon Maili Gönder",click:reSendInvitationMail},{name:"İptal",click:MESSAGEBOX.hideMessage}]);
				returnValue = false;
			}
			else if(response === "not_exist")
			{
				returnValue = true;
			}
			else
			{
				postMessage("Hata Oluştu!", true);
				returnValue = false;
			}
		},
		error:function(){
			postMessage("Hata Oluştu!", true);
			returnValue = false;
		}
	});
	
	return returnValue;
}

function reSendInvitationMail()
{
	$.ajax({
		data:"admin_action=resendinvitationmail&email=" + $("[name='email']").val(),
		beforeSend:MESSAGEBOX.openLoader,
		success:function(response){
			MESSAGEBOX.hideMessage();
			if(response !== "succeed")
			{
				postMessage("Beklenmedik bir hata oluştu, lütfen tekrar deneyin!", true);
			}
			else
			{
				MESSAGEBOX.showMessage("Davetiye Gönderimi", "Üyelik davetiyesi başarıyla gönderildi!", messageType.INFO, [{"name":"Tamam","click":MESSAGEBOX.hideMessage}]);
			}
		},
		error: function(){
			MESSAGEBOX.hideMessage();
			postMessage("Hata Oluştu!", true);
		},
		complete:MESSAGEBOX.closeLoader
	});
}