$(EditPermissionStart);

function EditPermissionStart()
{
	// permission_key değerinin kullanılıp kullanılmadığını kontrol et.
	$("#editPermissionForm").submit(function(){
		permission_key = $("#new_permission_key").val();
		can_submit = false;
		
		if(permission_key.length > 0)
		{
			// Eğer permission_key değerini değiştirmişse kontrol yap.
			if($("#old_permission_key").val() != $("#new_permission_key").val())
			{
				$.ajax({
					type:"post",
					data:"admin_action=checkPermissionKey&permission_key=" + permission_key,
					async:false,
					beforeSend: function(){
						$("#checkPermissionKeyLoader").css("display", "block");
						$("#checkPermissionKeyResultText").html("");
					},
					success:function(response){
						if(response == "key_exists")
						{
							$("#checkPermissionKeyResultText").html('* Girdiğiniz "Yetki Anahtarı" kullanımda!');
						}
						else
						{
							can_submit = true;
						}
					},
					complete:function(){
						$("#checkPermissionKeyLoader").css("display", "none");
					}
				});
			}
			else // permission_key değerini değiştirmediği için formu submit edebilir.
			{
				can_submit = true;
			}
			
		}
		else
		{
			$("#checkPermissionKeyResultText").html('* Lütfen geçerli bir "Yetki Anahtarı" kullanın!');
		}
		
		return can_submit;
	});
}