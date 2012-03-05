$(ReadMessageStart);

function ReadMessageStart()
{
	$("#deleteMessage").click(function(){
		if(confirm("Silmek istediğinizden eminmisiniz?"))
			window.location.href = "admin.php?page=messages&action=deleteMessage&messageId=" + $(this).attr("messageId");
	});
}