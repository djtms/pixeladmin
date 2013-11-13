$(SitemapStart);

function SitemapStart()
{
	$("[type='sitemap']").each(function(){
		var input_name = $(this).attr("name");
		var page_id = $(this).val();
		var page_title = "";
		var page_description = "";
		var page_url = $(this).attr("page_url");
		var image_id = $(this).attr("image_id") ? $(this).attr("image_id") : -1;
		
		if((page_id != undefined) && (page_id != "") && (page_id != null))
		{
			$.ajax({
				type:"post",
				data:"admin_action=selectSitemap&page_id=" + page_id,
				dataType:"json",
				async: false,
				success:function(response){
					if(response.found == true)
					{
						var page = response.page;
						image_id = image_id == -1 ? page.page_image : image_id;
						page_title = page_title == "" ? page.page_title : page_title;
						page_description = page_description == "" ? page.page_description : page_description;
					}
					else
					{
						page_title = uniqid();
						page_description = uniqid();
					}
				}
			});
		}
		else
		{
			page_id = uniqid();
			page_title = uniqid();
			page_description = uniqid();
		}
			
		var sitemapOuter = $("<div id='sitemapOuter'></div>");
		var sitemapHtml  = "<h2>Site Haritası & Seo Bilgileri</h2>";
			sitemapHtml += "<input type='hidden' name='" + input_name + "' value='" + page_id + "' />";
			sitemapHtml += "<input type='hidden' name='sm_page_id' value='" + page_id + "' />";
			sitemapHtml += "<input type='hidden' name='sm_page_url' value='" + page_url + "' maxlength='255' />";
			sitemapHtml += "<input type='hidden'  value='" + image_id + "' >";
			sitemapHtml += "<input type='hidden' name='sm_page_title' value='" + page_title + "' />";
			sitemapHtml += "<input type='hidden' name='sm_page_description' value='" + page_description + "' />";
			sitemapHtml += "<label>Sayfa Resmi: </label>";
			sitemapHtml += "<input type='file' name='sm_image_id' fileid='" + image_id + "' />";
			sitemapHtml += "<label>Sayfa Başlığı: (title)</label>";
			sitemapHtml += "<input type='text' class='sm_page_title' i18n='" + page_title + "' forcei18n maxlength='250' />";
			sitemapHtml += "<label>Sayfa Açıklaması: (description)</label>";
			sitemapHtml += "<textarea class='sm_page_description' i18n='" + page_description + "' forcei18n style='height:100px;' maxlength='250'></textarea>";
			
		sitemapOuter.append(sitemapHtml);
		$(this).replaceWith(sitemapOuter);
	});
}