$(InviteUserStart);

function InviteUserStart(){
	$("#btnInviteUser").click(checkUserStatusByEmail);
}

function checkUserStatusByEmail(){
	var returnValue = true;
	$.ajax({
		data:"admin_action=checkUserStatusByEmail&email=" + $("[name='email']").val(),
		async:false,
		success:function(response){
			if(response === "already_registered") {
				MESSAGEBOX.showMessage(GT.EPOSTA_KULLANIMDA, GT.HATA_AYNI_EPOSTA_ADRESI, messageType.WARNING, [{"name":GT.TAMAM}])
				returnValue = false;
			}
			else if(response == "not_activated_account") {
				MESSAGEBOX.showMessage(GT.HATA_OLUSTU, GT.HESAP_AKTIF_DEGIL, messageType.WARNING, [{name:GT.AKTIVASYON_MAILI_GONDER,click:reSendInvitationMail},{name:GT.IPTAL}]);
				returnValue = false;
			}
			else if(response === "not_exist") {
				returnValue = true;
			}
			else {
                MESSAGEBOX.showMessage(GT.HATA_OLUSTU, GT.BEKLENMEDIK_HATA, messageType.ERROR, [{"name":GT.TAMAM}]);
				returnValue = false;
			}
		},
		error:function(){
            MESSAGEBOX.showMessage(GT.HATA_OLUSTU, GT.BEKLENMEDIK_HATA, messageType.ERROR, [{"name":GT.TAMAM}]);
			returnValue = false;
		}
	});
	
	return returnValue;
}

function reSendInvitationMail() {
	$.ajax({
		data:"admin_action=resendinvitationmail&email=" + $("[name='email']").val(),
		beforeSend:MESSAGEBOX.openLoader,
		success:function(response){
			MESSAGEBOX.hideMessage();
			if(response !== "succeed") {
                MESSAGEBOX.showMessage(GT.HATA_OLUSTU, GT.BEKLENMEDIK_HATA, messageType.ERROR, [{"name":GT.TAMAM}]);
			}
			else {
				MESSAGEBOX.showMessage(GT.ISLEMINIZ_TAMAMLANDI, GT.UYELIK_DAVETIYESI_GONDERILDI, messageType.INFO, [{"name":GT.TAMAM}]);
			}
		},
		error: function(){
			MESSAGEBOX.hideMessage();
            MESSAGEBOX.showMessage(GT.HATA_OLUSTU, GT.BEKLENMEDIK_HATA, messageType.ERROR, [{"name":GT.TAMAM}]);
		},
		complete:MESSAGEBOX.closeLoader
	});
}