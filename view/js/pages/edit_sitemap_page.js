$(EditSitemapStart);

function EditSitemapStart()
{
	var changefreq = $("[name='changefreq']").attr("checked_option");
	$("[name='changefreq']").val(changefreq);
}