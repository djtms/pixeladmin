$(ReadMessageStart);

function ReadMessageStart()
{
	$("#deleteMessage").click(function(){
		if(confirm("Silmek istediğinizden eminmisiniz?"))
			window.location.href = "admin.php?page=messages&admin_action=deleteMessage&messageId=" + $(this).attr("messageId");
	});
}