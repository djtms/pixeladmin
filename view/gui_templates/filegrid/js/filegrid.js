$(FileGridStart);

function FileGridStart()
{
	$(".fileGridOuter .btnEdit").live("click",function(){
		var btnEdit = $(this);
		var parent  = btnEdit.parent();
		var file_id = btnEdit.attr("file");
		var btnView = parent.find(".btnView");
		var btnPlay = parent.find(".btnPlay");
		var thumbImage = btnEdit.closest(".gridFile").find(".thumbImage");
		
		$(this).editfile({
			file_id: file_id,
			onSaved:function(file){
				if(file.type != "movie")
				{
					if(file.thumb != null)
					{
						thumbImage.attr("src", file.thumb);	
					}
					
					btnView.attr("href",'lookfile.php?type=' + file.type + '&url=' + ADMIN.encodeUTF8(file.url));
				}
				else
				{
					btnPlay.attr("href",'lookfile.php?type=' + file.type + '&url=' + ADMIN.encodeUTF8(file.url));
				}
			}
		});
	});
	
	$(".fileGridOuter .btnDelete").live("click", function(){
		if(confirm("Dosyayı bu listeden kaldırmak istediğinizden emin misiniz?"))
		{
			$(this).closest(".gridFile").animate({"opacity":0},300, function(){
				$(this).remove();
			});
		}
	});
	
	fixFileGridStyles();
}

function fixFileGridStyles()
{
	$(".fileGridOuter").each(function(){
		var rowCount = $(this).is("[rows]") ? parseInt($(this).attr("rows")) : 1;
		var oneItemHeight = parseInt($(this).find(".gridFile").eq(0).outerHeight(true));
		
		var totalHeight = (rowCount * oneItemHeight) + 9;
		$(this).height(totalHeight);
		$(this).find(".overflowFixer").height(totalHeight);
	});
	
	$(".fileGridOuter .fancybox").fancybox({
		"titleShow":false,
		"scrolling":"no"
	});
}

function openFileGridLoader(gridId)
{
	$("#" + gridId + " .fileGridLoader").css("display", "block");
}

function closeFileGridLoader(gridId)
{
	$("#" + gridId + " .fileGridLoader").css("display", "none");
}
