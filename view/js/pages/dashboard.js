$(DashboardStart);

function DashboardStart()
{
	$("#btnMaintananceMode").click(function(){
		var mode = null;
		var checked = !$(this).is(":checked");

		
		if(!checked && confirm("Web sayfanızı \"Bakım Modu'na\" almak istediğinize eminmisiniz?"))
		{
			mode = "maintanance";
		}
		else if(checked && confirm("Web sayfanızı \"Bakım Modu'ndan\" çıkarmak istediğinize eminmisiniz?"))
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
						postMessage("Site \"Bakım Modu'na\" alınamadı!",true);
						$("#btnMaintananceMode").attr("checked",false);
					}
					else if((response == "error") && (mode == "public"))
					{
						postMessage("Site \"Bakım Modu'ndan\" çıkarılamadı!",true);
						$("#btnMaintananceMode").attr("checked",true);
					}
				}
			});
		}
	});
	
	
	$("#btnMultilanguageMode").click(function(){
		var mode = null;
		var checked = !$(this).is(":checked");
		
		if(!checked && confirm("Web sayfanızı \"Çoklu Dil Modu'na\" almak istediğinize eminmisiniz?"))
		{
			mode = "multilanguage";
		}
		else if(checked && confirm("Web sayfanızı \"Çoklu Dil Modu'ndan\" çıkarmak istediğinize eminmisiniz?"))
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
						postMessage("Siteniz \"Çoklu Dil Modu'na\" alınamadı!",true);
						$("#btnMaintananceMode").attr("checked",false);
					}
					else if((response == "error") && (mode == "multilanguage"))
					{
						postMessage("Siteniz \"Çoklu Dil Modu'ndan\" çıkarılamadı!",true);
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
		
		if(!checked && confirm("Web sayfanızı \"Hata Ayıklama Modu'na\" almak istediğinize eminmisiniz?"))
		{
			mode = "debugmode";
		}
		else if(checked && confirm("Web sayfanızı \"Hata Ayıklama Modu'ndan\" çıkarmak istediğinize eminmisiniz?"))
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
						postMessage("Siteniz \"Hata Ayıklama Modu'na\" alınamadı!",true);
						$("#btnDebugMode").attr("checked",false);
					}
					else if((response == "error") && (mode == "securitymode"))
					{
						postMessage("Siteniz \"Hata Ayıklama Modu'ndan\" çıkarılamadı!",true);
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