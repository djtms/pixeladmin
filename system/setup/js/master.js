$(MasterStart);

function MasterStart()
{
	
}

function checkDatabase()
{
	$.ajax({
		type:"post",
		url:"setup.php",
		data:"admin_action=checkForDB&" + $("#dbInfoForm").serialize(),
		success:function(response){
			alert(response);
			try
			{
				var result = eval("(" + response + ")");
				
				if(result.error == "false")
				{
					window.location.href = "user.php";
				}
				else
					$("#errorText").html(result.response);
			}
			catch(e)
			{
				alert(response);
			}
			
			
		}
	});
}