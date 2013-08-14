<?php
if(strlen($_GET["delete"]) > 0)
{
	if($ADMIN->SITEMAP->deleteSitemap($_GET["delete"]))
	{
		postMessage("Başarıyla Silindi!");
		header("Location: admin.php?page=sitemap");
		exit;
	}
	else
	{
		postMessage("Hata Oluştu!", true);
	}
}

$sitemapList = $ADMIN->SITEMAP->listSitemaps(true);

echo dataGrid($sitemapList, "Site Haritası", "sitemapList", "{%page_title%}", "admin.php?page=add_sitemap_page", "admin.php?page=edit_sitemap_page&id={%page_id%}", "admin.php?page=sitemap&delete={%page_id%}");