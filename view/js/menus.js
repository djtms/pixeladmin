$(MenusStart);

function MenusStart()
{
	$(".menu").each(function(){
		var totalHeight = 0;
		if($(this).find(".subMenuLink").length > 0)
		{
			totalHeight = $(this).find(".menuWrapper").outerHeight();
		
			if($(this).hasClass("selected"))
				$(this).css({"height":totalHeight});
			else
			{
				$(this).css({"height":"45px"});
				$(this).find(".pageLink").click(function(){
					var height = parseInt($(this).parents(".menu").height());
					if(height <= 46){
						height = totalHeight;
						$(this).parents(".menu").addClass("selected");
					}
					else{
						height = 45;
						$(this).parents(".menu").removeClass("selected");
					}
					
					$(this).parents(".menu").css({"height":height});
				});
			}
		}
	});
}