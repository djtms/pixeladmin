$(DataGridStart);

function DataGridStart()
{
	$(".dataGridOuter").on("click", ".crossBtn", function(){
		if(confirm("Silmek istediğinize eminmisiniz?"))
		{
			$(this).css("background","none").find("img").css("display","block");
			window.location.href = $(this).attr("href");
		}
		
		return false;
	}).on("click", ".dataGridAddButton",function(){
		window.location.href = $(this).attr("page");
		return false;
	}).on("dblclick", ".itemsList > ul > li", function(e){
		var href = $(this).find(".editBtn").attr("href");
		
		if(!((href == null) || (href == undefined) || (href == "")))
		{
			window.location.href = href;
		}
	}).on("mousedown", ".itemsList > ul > li",function(e){ e.preventDefault(); });
	
	$(".dataGridOuter").each(function(){
		var firstItem = $(this).find(".item:first");
		var maxWidth = firstItem.width();
		var editButtonsWidth = firstItem.find(".rowEditButtonsOuter").outerWidth(true) + 5;
		var textWidth = maxWidth - editButtonsWidth;
		$(this).find(".text").css("width", textWidth);
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
			var id = $(this).attr("id");
			
			$(this).sortable({
				update:function(){
					var order = $(this).sortable("serialize");
					$.ajax({
						data:"admin_action=sortDataGrid_" + id + "&event=" + event + "&" + order
					});
				}
			});
		}
	});
}