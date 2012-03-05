$(GuiStart);

function GuiStart()
{
	$(".crossBtn").live("click",function(){
		if(confirm("Silmek istediğinize eminmisiniz?"))
		{
			window.location.href = $(this).attr("href");
		}
		
		return false;
	});
	
	$(".dataGridAddButton").live("click",function(){
		window.location.href = $(this).attr("page");
		return false;
	});
	
	$(".sortableList").each(function(){
		var action = $(this).attr("sort_action");
		
		$(this).sortable({
			update:function(){
				var order = $(this).sortable("serialize");
				
				$.ajax({
					data:"action=" + action + "&" + order
				});
			}
		});
	});
}