(function($){
	$(AddUserStart);

	function AddUserStart(){
		$("#btnAddUser").click(addUser);
	}

	function addUser(){
		var error = false;
		
		if($("#username").val().trim().length < 6){
			var messageText = "Girmiş olduğunuz <b>kullanıcı adı</b> en az 6 karakterden oluşmalıdır!";
			MESSAGEBOX.showMessage('"Kullanıcı Adı" Hatası!', messageText, messageType.WARNING, [{"name":"Tamam","click":MESSAGEBOX.hideMessage}]);	
			error = true;
		}
		else{
			$.ajax({
				data:"admin_action=checkUserStatusByUsername&username=" + $("#username").val().trim(),
				async:false,
				success:function(response){
					
					if(response === "existing_user"){
						var messageText = "Girmiş olduğunuz <b>kullanıcı adı</b> başka bir kullanıcı tarafından kullanılmaktadır! Lütfen farklı bir <b>kullanıcı adı</b> girin.";
						MESSAGEBOX.showMessage('"Kullanıcı Adı" Kullanımda', messageText, messageType.WARNING, [{"name":"Tamam","click":MESSAGEBOX.hideMessage}])
						error = true;
					}
					else if(response === "not_exist"){
						error = false;
					}
					else{
						postMessage("Hata Oluştu!", true);
						error = true;
					}
				},
				error:function(){
					postMessage("Hata Oluştu!", true);
					error = true;
				}
			});
		}
		
		if(!error){
			if(!VALIDATE.validateEmail($("#email").val().trim())){
				var messageText = "Lütfen geçerli bir <b>e-posta adresi</b> giriniz!";
				MESSAGEBOX.showMessage('"E-Posta Adresi" Hatası!', messageText, messageType.WARNING, [{"name":"Tamam","click":MESSAGEBOX.hideMessage}]);	
				error = true;
			}
			else
			{
				$.ajax({
					data:"admin_action=checkUserStatusByEmail&email=" + $("#email").val().trim(),
					async:false,
					success:function(response){
						if(response === "existing_user"){
							var messageText = "Girmiş olduğunuz <b>e-posta adresi</b> başka bir kullanıcı tarafından kullanılmaktadır! Lütfen farklı bir <b>e-posta adresi</b> girin.";
							MESSAGEBOX.showMessage("E-Posta Kullanımda", messageText, messageType.WARNING, [{"name":"Tamam","click":MESSAGEBOX.hideMessage}])
							error = true;
						}
						else if(response === "not_exist"){
							error = false;
						}
						else{
							postMessage("Hata Oluştu!", true);
							error = true;
						}
					},
					error:function(){
						postMessage("Hata Oluştu!", true);
						error = true;
					}
				});
			}
		}
		
		if(!error){
			if($("#password").val().trim().length < 6){
				var messageText = "Girmiş olduğunuz <b>parola</b> en az 6 karakterden oluşmalıdır!";
				MESSAGEBOX.showMessage('"Parola" Hatası!', messageText, messageType.WARNING, [{"name":"Tamam","click":MESSAGEBOX.hideMessage}]);	
				error = true;
			}
			else if($("#password").val().trim() != $("#password_again").val().trim()){
				var messageText = "Girmiş olduğunuz <b>parolalar</b> birbirleri ile eşleşmiyorlar";
				MESSAGEBOX.showMessage('"Parola" Eşleşme Hatası!', messageText, messageType.WARNING, [{"name":"Tamam","click":MESSAGEBOX.hideMessage}]);
				error = true;
			}
			else if($("[name='user_roles[]']:checked").length <= 0){
				var messageText = 'Yeni kullanıcı için en az bir "Rol" seçmeniz gerekiyor!';
				MESSAGEBOX.showMessage('"Kullanıcı Rolleri" Hatası!', messageText, messageType.WARNING, [{"name":"Tamam","click":MESSAGEBOX.hideMessage}]);	
				error = true;
			}
		}
		
		return !error;
	}
})(jQuery);