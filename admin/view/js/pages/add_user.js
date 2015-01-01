(function($){
	$(AddUserStart);

	function AddUserStart(){
		$("#btnAddUser").click(addUser);
	}

	function addUser(){
		var error = false;

		if($("#username").val().trim().length < 6){
			MESSAGEBOX.showMessage(GT.KULLANICI_ADI_HATASI, GT.KULLANICI_ADI_MIN_ALTI_KARAKTER, messageType.WARNING, [{"name":GT.TAMAM}]);
			error = true;
		}
		else{
			$.ajax({
				data:"admin_action=checkUserStatusByUsername&username=" + $("#username").val().trim(),
				async:false,
				success:function(response){
					
					if(response === "existing_user"){
						MESSAGEBOX.showMessage(GT.KULLANICI_ADI_KULLANIMDA, GT.HATA_AYNI_KULLANICI_ADI, messageType.WARNING, [{"name":GT.TAMAM}])
						error = true;
					}
					else if(response === "not_exist"){
						error = false;
					}
					else{
                        MESSAGEBOX.showMessage(GT.HATA_OLUSTU, GT.HATA_COKLUDIL_YUKLENMESI, messageType.ERROR, [{"name":GT.TAMAM}]);
						error = true;
					}
				},
				error:function(){
                    MESSAGEBOX.showMessage(GT.HATA_OLUSTU, GT.BEKLENMEDIK_HATA, messageType.ERROR, [{"name":GT.TAMAM}]);
					error = true;
				}
			});
		}
		
		if(!error){
			if(!VALIDATE.validateEmail($("#email").val().trim())){
				MESSAGEBOX.showMessage(GT.EPOSTA_HATASI, GT.HATA_GECERLI_EPOSTA_GIRIN, messageType.WARNING, [{"name":GT.TAMAM}]);
				error = true;
			}
			else
			{
				$.ajax({
					data:"admin_action=checkUserStatusByEmail&email=" + $("#email").val().trim(),
					async:false,
					success:function(response){
						if(response === "existing_user"){
							MESSAGEBOX.showMessage(GT.EPOSTA_KULLANIMDA, GT.HATA_AYNI_EPOSTA_ADRESI, messageType.WARNING, [{"name":GT.TAMAM}])
							error = true;
						}
						else if(response === "not_exist"){
							error = false;
						}
						else{
                            MESSAGEBOX.showMessage(GT.HATA_OLUSTU, GT.BEKLENMEDIK_HATA, messageType.ERROR, [{"name":GT.TAMAM}]);
							error = true;
						}
					},
					error:function(){
                        MESSAGEBOX.showMessage(GT.HATA_OLUSTU, GT.BEKLENMEDIK_HATA, messageType.ERROR, [{"name":GT.TAMAM}]);
						error = true;
					}
				});
			}
		}
		
		if(!error){
			if($("#password").val().trim().length < 6){
				MESSAGEBOX.showMessage(GT.PAROLA_HATASI, GT.PAROLA_MIN_ALTI_KARAKTER, messageType.WARNING, [{"name":GT.TAMAM}]);
				error = true;
			}
			else if($("#password").val().trim() != $("#password_again").val().trim()){
				MESSAGEBOX.showMessage(GT.PAROLA_ESLESME_HATASI, GT.HATA_PAROLALAR_ESLESMIYOR, messageType.WARNING, [{"name":GT.TAMAM}]);
				error = true;
			}
			else if($("[name='user_roles[]']:checked").length <= 0){
				MESSAGEBOX.showMessage(GT.KULLANICI_ROLLERI_HATASI, GT.HATA_MIN_BIR_ROL_SEC, messageType.WARNING, [{"name":GT.TAMAM}]);
				error = true;
			}
		}

        if(!error){
            encryptPassword();
        }
		
		return !error;
	}

    function encryptPassword()
    {
        var password = SHA1($("#password").val());
        $("[name=password]").val(password);
    }
})(jQuery);