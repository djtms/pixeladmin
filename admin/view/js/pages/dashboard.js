$(DashboardStart);

function DashboardStart()
{
	$("#btnMaintananceMode").click(function(){
		var mode = null;
		var checked = !$(this).is(":checked");

		
		if(!checked && confirm(GT.BAKIM_MODUNA_ALINSINMI))
		{
			mode = "maintanance";
		}
		else if(checked && confirm(GT.BAKIM_MODUNDAN_CIKSINMI))
		{
			mode = "public";
		}
		else
			return false;
		
		if(mode != null)
		{
			$.ajax({
				data:"admin_action=SetDisplayMode&mode=" + mode,
				success:function(response){
					if((response == "error") && (mode == "maintanance"))
					{
                        MESSAGEBOX.showMessage(GT.HATA_OLUSTU, GT.BAKIM_MODUNA_ALINAMADI, messageType.ERROR, [{"name":GT.TAMAM}]);
						$("#btnMaintananceMode").attr("checked",false);
					}
					else if((response == "error") && (mode == "public"))
					{
                        MESSAGEBOX.showMessage(GT.HATA_OLUSTU, GT.BAKIM_MODUNDAN_CIKARILAMADI, messageType.ERROR, [{"name":GT.TAMAM}]);
						$("#btnMaintananceMode").attr("checked",true);
					}
				}
			});
		}
	});


	$("#btnMultilanguageMode").click(function(){
		var mode = null;
		var checked = !$(this).is(":checked");

		if(!checked && confirm(GT.COKLUDIL_MODUNA_ALINSINMI))
		{
			mode = "multilanguage";
		}
		else if(checked && confirm(GT.COKLUDIL_MODUNDAN_CIKSINMI))
		{
			mode = "simplelanguage";
		}
		else
			return false;

		if(mode != null)
		{
			$.ajax({
				data:"admin_action=SetMultilanguageMode&mode=" + mode,
				success:function(response){
					if((response == "error") && (mode == "simplelanguage"))
					{
                        MESSAGEBOX.showMessage(GT.HATA_OLUSTU, GT.COKLUDIL_MODUNA_ALINAMADI, messageType.ERROR, [{"name":GT.TAMAM}]);
						$("#btnMaintananceMode").attr("checked",false);
					}
					else if((response == "error") && (mode == "multilanguage"))
					{
                        MESSAGEBOX.showMessage(GT.HATA_OLUSTU, GT.COKLUDIL_MODUNDAN_CIKARILAMADI, messageType.ERROR, [{"name":GT.TAMAM}]);
						$("#btnMaintananceMode").attr("checked",true);
					}
					else
					{
						window.location.href = "admin.php?page=dashboard";
					}
				}
			});
		}
	});

	$("#btnDebugMode").click(function(){
		var mode = null;
		var checked = !$(this).is(":checked");

		if(!checked && confirm(GT.DEBUG_MODUNA_ALINSINMI))
		{
			mode = "debugmode";
		}
		else if(checked && confirm(GT.DEBUG_MODUNDAN_CIKSINMI))
		{
			mode = "securitymode";
		}
		else
			return false;

		if(mode != null)
		{
			$.ajax({
				data:"admin_action=SetDebugMode&mode=" + mode,
				success:function(response){
					if((response == "error") && (mode == "debugmode"))
					{
                        MESSAGEBOX.showMessage(GT.HATA_OLUSTU, GT.DEBUG_MODUNA_ALINAMADI, messageType.ERROR, [{"name":GT.TAMAM}]);
						$("#btnDebugMode").attr("checked",false);
					}
					else if((response == "error") && (mode == "securitymode"))
					{
                        MESSAGEBOX.showMessage(GT.HATA_OLUSTU, GT.DEBUG_MODUNDAN_CIKARILAMADI, messageType.ERROR, [{"name":GT.TAMAM}]);
						$("#btnDebugMode").attr("checked",true);
					}
					else
					{
						window.location.href = "admin.php?page=dashboard";
					}
				}
			});
		}
	});
}