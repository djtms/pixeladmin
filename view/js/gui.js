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
	
	setupSortableLists();
}

function setupSortableLists()
{
	$(".sortableList").each(function(){
		if($(this).hasClass("sort_event_binded"))
		{
			return;
		}
		else
		{
			$(this).addClass("sort_event_binded");
			var event = $(this).attr("sort_event");
			
			$(this).sortable({
				update:function(){
					var order = $(this).sortable("serialize");
					
					$.ajax({
						data:"admin_action=sortDataGrid&event=" + event + "&" + order
					});
				}
			});
		}
	});
}