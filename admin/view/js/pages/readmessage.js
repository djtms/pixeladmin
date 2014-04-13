$(ReadMessageStart);

function ReadMessageStart()
{
	$("#deleteMessage").click(function(){
		if(confirm(GT.SILMEK_ISTEDIGINDEN_EMINMISIN))
			window.location.href = "admin.php?page=messages&admin_action=deleteMessage&messageId=" + $(this).attr("messageId");
	});
}