$(EditRoleStart);

function EditRoleStart()
{
	user_permission_count = user_permissions.length;

	for(var i=0; i<user_permission_count; i++)
	{
		$("[name='permission_checked_" + user_permissions[i].permission_key + "']").attr("checked", true);
	}
	
	$("#permissionsList [type='checkbox']").click(function(){
		if($(this).is(":checked"))
			$(this).parent().parent().find("[type='checkbox']").attr("checked", true);
		else
			$(this).parent().parent().find("[type='checkbox']").attr("checked", false);
	});

    $("#btnSaveRole").on("click", function(){
        var error = false;

        if ($("[name='role_name']").val().length <= 0) {
            error = true;
            MESSAGEBOX.showMessage(GT.UYARI, GT.UYARI_ROL_ADI_GIRIN, messageType.WARNING, [{"name":GT.TAMAM}]);
        }
        else if ($("[name='role_key']").val().length <= 0) {
            error = true;
            MESSAGEBOX.showMessage(GT.UYARI, GT.UYARI_ROL_ANAHTAR_KELIMESI_GIRIN, messageType.WARNING, [{"name":GT.TAMAM}]);
        }

        if(!error){
            $.ajax({
                data:"admin_action=checkIfRoleKeyAvailable&role_key=" + $("[name='role_key']").val().trim() + "&role_id=" + $("[name='role_id']").val(),
                async:false,
                dataType : "text",
                success:function(response){
                    if(response !== "available"){
                        MESSAGEBOX.showMessage(GT.ROLE_ANAHTAR_KELIMESI_KULLANIMDA, GT.UYARI_FARKLI_ROL_ANAHTAR_KELIMESI_GIRIN, messageType.WARNING, [{"name":GT.TAMAM}]);
                        error = true;
                    }
                    else{
                        error = false;
                    }
                },
                error:function(){
                    MESSAGEBOX.showMessage(GT.HATA_OLUSTU, GT.BEKLENMEDIK_HATA, messageType.ERROR, [{"name":GT.TAMAM}]);
                    error = true;
                }
            });
        }

        return !error;
    });
}