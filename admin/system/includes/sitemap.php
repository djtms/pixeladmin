<?php
if($_POST["admin_action"] == "selectSitemap")
{
	$page_id = $_POST["page_id"];
	if($page = $ADMIN->SITEMAP->selectSiteMap($page_id)){
		echo json_encode(array("found"=>true, "page"=>$page));
	}
	else{
		echo json_encode(array("found"=>false));
	}
	
	exit;
}

// TODO: sitemap değerlerini kaydetme işlemini saveI18n() den ayır.
function saveSitemap($url_params = array())
{
	global $ADMIN;
	$page_url = renderHtml($_REQUEST["sm_page_url"], $url_params);

	if($ADMIN->SITEMAP->setSiteMap($_POST["sm_page_id"], $_POST["sm_image_id"], $page_url, $_POST["sm_page_title"], $_POST["sm_page_description"]))
	{
		return saveI18n();
	}
}

function deleteSitemap($page_id)
{
	global $ADMIN;

	return $ADMIN->SITEMAP->deleteSitemap($page_id);
}