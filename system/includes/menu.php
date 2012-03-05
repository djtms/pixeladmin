<?php
function addMenu($menuTitle,$menuIcon,$pageTitle,$menuId,$menuPage,$order=2,$permission=USER_AUTHOR,$addLinkToSubMenu = true)
{
	if(!in_admin)	return; // yönetim panelinde değil isek çalışmayacak
	
	global $MODEL;
	global $pa_menu_array;
	
	$user = $MODEL->USER->loggedInUser;
	
	if($user->user_type >= $permission)
		$pa_menu_array[] = array("subMenus"=>array(),"menuTitle"=>$menuTitle,"menuIcon"=>$menuIcon,"pageTitle"=>$pageTitle,"menuId"=>$menuId,"menuPage"=>$menuPage,"menuOrder"=>$order,"permission"=>$permission,"addLinkToSubMenu"=>$addLinkToSubMenu);
}

function addSubMenu($menuTitle,$pageTitle,$parentMenuId,$menuId,$menuPage,$order=-1,$permission=USER_AUTHOR)
{
	if(!in_admin)	return; // yönetim panelinde değil isek çalışmayacak
	
	global $MODEL;
	global $pa_menu_array;
	
	$user = $MODEL->USER->loggedInUser;
	
	if($user->user_type >= $permission)
	{
		foreach($pa_menu_array as &$menu)
		{
			if($menu["menuId"] == $parentMenuId)
			{
				$menu["subMenus"][] = array("menuTitle"=>$menuTitle,"pageTitle"=>$pageTitle,"parentMenuId"=>$parentMenuId,"menuId"=>$menuId,"menuPage"=>$menuPage,"menuOrder"=>$order,"permission"=>$permission);
			}
		}
	}
}

function addSettingsMenu($menuTitle,$pageTitle,$menuId,$menuPage,$order=-1,$permission=USER_AUTHOR)
{
	addSubMenu($menuTitle,$pageTitle,"settings",$menuId,$menuPage,$order=-1,$permission=USER_AUTHOR);
}

function addPage($pageTitle,$parentMenuId,$pageId,$page,$permission=USER_AUTHOR)
{
	if(!in_admin)	return; // yönetim panelinde değil isek çalışmayacak
	
	global $MODEL;
	global $pa_menu_array;
	
	$user = $MODEL->USER->loggedInUser;
	
	if($user->user_type >= $permission)
	{
		foreach($pa_menu_array as &$menu)
		{
			if($menu["menuId"] == $parentMenuId)
			{
				$menu["subPages"][] = array("pageTitle"=>$pageTitle,"parentMenuId"=>$parentMenuId,"pageId"=>$pageId,"page"=>$page,"permission"=>$permission);
			}
		}
	}
}

function loadMenus($currentPageId = null)
{
	if(!in_admin)	return; // yönetim panelinde değil isek çalışmayacak
	
	global $pa_menu_array;
	global $master;
	$MenuHtml = "";
	$BarIconsHtml = "";
	$ids = array();
	
	/* Menüleri Sırala   *********************************************************/
		foreach($pa_menu_array as $m)
		{
			$ids = array_merge($ids,array($m["menuId"]=>$m["menuOrder"]));
		}
		
		asort($ids);
		$sortedMenu = array();
		
		foreach($ids as $id=>$order)
		{
			foreach($pa_menu_array as $m)
			{
				if($m["menuId"] == $id)
				{
					$sortedMenu[] = $m;
					break;
				}
			}
		}
		
		$pa_menu_array = &$sortedMenu;
	/*****************************************************************************/
	
	/* Generate MenuHtml *********************************************************/
		foreach($pa_menu_array as $m)
		{
			$selected = "";
			$hasSubMenus = false;
			
			if(($currentPageId != null) && ($currentPageId == $m["menuId"]))
			{
				$selected = ' selected ';
				$master->pageTitle = $m["pageTitle"];
			}
			
			if(sizeof($m["subMenus"]) > 0)
			{
				$hasSubMenus = true;
				if($m["addLinkToSubMenu"])
					array_unshift($m["subMenus"],array("menuTitle"=>$m["menuTitle"],"pageTitle"=>$m["pageTitle"],"parentMenuId"=>$m["menuId"],"menuId"=>$m["menuId"],"menuPage"=>$m["menuPage"],"menuOrder"=>$m["menuOrder"],"permission"=>$m["permission"]));
				
				foreach($m["subMenus"] as $sm)
				{
					if(($currentPageId != null) && ($currentPageId == $sm["menuId"]))
					{
						$selected = ' selected ';
						$master->pageTitle = $sm["pageTitle"];
						$selectedSubMenuId = $sm["menuId"];
					}
				}
			}
			
			
			if(sizeof($m["subPages"]) > 0)
			{
				foreach($m["subPages"] as $sp)
				{
					if(($currentPageId != null) && ($currentPageId == $sp["pageId"]))
					{
						$selected = ' selected ';
						$master->pageTitle = $sp["pageTitle"];
					}
				}
			}
			
			
			if( ($m["menuIcon"] != ".") && ($m["menuIcon"] != "..") && (file_exists($m["menuIcon"])))
			{
				$menuIcon = $m["menuIcon"];
			}
			else
			{
				global $default_menu_icon;
				$menuIcon = $default_menu_icon;
			}
			
			$MenuHtml .= '<li id="' . $m["menuId"] . '" class="menu ' . $selected . '" ><div class="menuWrapper">';
			
			if($hasSubMenus)
				$MenuHtml .= '<span class="pageLink"><span class="menuIcon" style="background-image:url(' . $menuIcon . ');"></span>' . $m["menuTitle"] . '</span>';
			else
				$MenuHtml .= '<a href="admin.php?page=' . $m["menuId"] . '" class="pageLink"><span class="menuIcon" style="background-image:url(' . $menuIcon . ');"></span>' . $m["menuTitle"] . '</a>';
	
			/* Menü'nün "Alt Menü" lerini ekle *************************************************************/
			if($hasSubMenus)
			{
				foreach($m["subMenus"] as $sm)
				{
					$MenuHtml .= '<a href="admin.php?page=' . $sm["menuId"] . '" class="subMenuLink ' . ($selectedSubMenuId == $sm["menuId"] ? 'selected' : '') . '">' . $sm["menuTitle"] . '</a>';
				}
			}
				
			$MenuHtml .= '</div></li>';
			
			/* Bar Menü'yü oluştur */
			if( ($m["menuIcon"] != ".") && ($m["menuIcon"] != "..") && (file_exists($m["menuIcon"])))
				$BarIconsHtml .= '<a href="admin.php?page=' . urlencode($m["menuId"]) . '" style="background-image:url('.$m["menuIcon"] .')"  class="' . $selected . '" title="' . $m["menuTitle"] . '"></a>';
		}
	/*****************************************************************************/
	
	
	$master->barIcons = $BarIconsHtml;
	$master->leftMenu = $MenuHtml;
}