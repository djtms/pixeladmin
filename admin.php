<?php 	
require_once dirname(__FILE__) . '/includes.php';

/* Get requested page file */
$pa_menuId = urldecode($_GET["page"]);
$page = "";
global $pa_menu_array;

if(is_array($pa_menu_array)):
	foreach($pa_menu_array as $m)
	{
		if($m["menuId"] == $pa_menuId)
		{
			$page = $m["menuPage"];
		}
		
		if(sizeof($m["subMenus"]) > 0)
		{
			foreach($m["subMenus"] as $sm)
			{
				if($sm["menuId"] == $pa_menuId)
				{
					$page = $sm["menuPage"];
					break;
				}
			}
		}
		
		if(sizeof($m["subPages"]) > 0)
		{
			foreach($m["subPages"] as $sp)
			{
				if($sp["pageId"] == $pa_menuId)
				{
					$page = $sp["page"];
					break;
				}
			}
		}
	}
endif;

if(file_exists($page))
{
	ob_start();
		require_once $page;
		
		$master->content = $modulesContent . ob_get_contents();
	ob_end_clean();	
	
	/* Yapılan çağrının AJAX çağrısı olup olmadığını kontrol ediyoruz, bunun için "ajaxurl" ye atadığımız değişkeni kontrol ediyoruz */
	if($_GET["type_of_post"] == "ajax")
	{
		executeAjaxCall($_POST["action"]);
	}
}	
else
{
	$master->content = "Sayfa Bulunamadı";
}

loadMenus($pa_menuId);
$master->postMessage = get_option("postMessage");
set_option("postMessage","");
$master->render();