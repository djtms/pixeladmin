<?php

$page_id = $_GET["id"] > 0 ? $_GET["id"] : uniqid();

if($_POST["admin_action"] == "Kaydet")
{
	extract($_POST, EXTR_OVERWRITE);
	
	if($ADMIN->SITEMAP->setSiteMap($page_id, $page_url, $page_title, $page_description, $page_parent))
	{
		postMessage("Başarıyla Kaydedildi!");
		header("Location:admin.php?page=sitemap");
		exit;
	}
	else
	{
		postMessage("Hata Oluştu!", true);
	}
}

$smpage = $ADMIN->SITEMAP->selectSiteMap($page_id);
$smpage->page_id = $page_id; // Eğer yeni bir sitemap sayfası eklenecekse page_id değerini yukarıda aldığımız uniqid değeri olarak kullanıyoruz.


$sitemapsList = $ADMIN->SITEMAP->listSitemaps();
$sitemap_count = sizeof($sitemapsList);
for($i=0; $i<$sitemap_count; $i++)
{
	$selected = $smpage->page_parent == $sitemapsList[$i]->page_id ? " selected='selected' " : "";

	if($smpage->page_id != $sitemapsList[$i]->page_id)
	{
		$otherSMPagesHtml .= "<option value='{$sitemapsList[$i]->page_id}' {$selected} >{$sitemapsList[$i]->page_title}</option>";
	}
}

echo $edit_sitemap_page->html();