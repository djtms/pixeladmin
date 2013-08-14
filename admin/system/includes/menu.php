<?php
function addMenu($menuTitle,$menuIcon,$pageTitle,$menuId,$menuPage,$order=2,$permission=USER_AUTHOR,$addLinkToSubMenu = true)
{
	if(!in_admin)	return; // yönetim panelinde değil isek çalışmayacak
	
	global $ADMIN;
	global $pa_menu_array;
	
	$user = $ADMIN->AUTHENTICATION->authenticated_user;
	
	$pa_menu_array[$menuId] = array("menuTitle"=>$menuTitle,"menuIcon"=>$menuIcon,"pageTitle"=>$pageTitle,"menuPage"=>$menuPage,"menuOrder"=>$order,"permission"=>$permission, "addLinkToSubMenu"=>$addLinkToSubMenu);
}

function addSubMenu($menuTitle,$pageTitle,$parentMenuId,$menuId,$menuPage,$order=-1,$permission=USER_AUTHOR)
{
	if(!in_admin)	return; // yönetim panelinde değil isek çalışmayacak
	
	global $ADMIN;
	global $pa_menu_array;
	
	$pa_menu_array[$parentMenuId]["subMenus"][$order][$menuId] = array("menuTitle"=>$menuTitle,"pageTitle"=>$pageTitle,"parentMenuId"=>$parentMenuId,"menuPage"=>$menuPage,"permission"=>$permission);
}

function addSettingsMenu($menuTitle,$pageTitle,$menuId,$menuPage,$order=-1,$permission=USER_AUTHOR)
{
	addSubMenu($menuTitle, $pageTitle, "settings", $menuId, $menuPage, $order, $permission=USER_AUTHOR);
}

function addPage($pageTitle,$parentMenuId,$pageId,$page,$permission=USER_AUTHOR)
{
	if(!in_admin)	return; // yönetim panelinde değil isek çalışmayacak
	
	global $ADMIN;
	global $pa_menu_array;
	
	$pa_menu_array[$parentMenuId]["subPages"][$pageId] = array("pageTitle"=>$pageTitle,"parentMenuId"=>$parentMenuId,"page"=>$page,"permission"=>$permission);
}

function loadMenus($currentPageId = null)
{
	if(!in_admin)	return; // yönetim panelinde değil isek çalışmayacak
	
	global $pa_page_permission_info_array;
	global $default_menu_icon;
	global $pa_menu_array;
	global $master;
	$MenuHtml = "";
	$BarIconsHtml = "";
	$menuIds = array();
	
	// Menüleri Sırala 
	foreach($pa_menu_array as $key=>$val){
		$menuIds[$val["menuOrder"]] = $key;
	}
    ksort($menuIds);

	// Menu html'ini oluştur 
	foreach($menuIds as $menu_id){
		$menu = $pa_menu_array[$menu_id];
		$menuSelected = "";
		
		// Permission sayfası için kullanılacak array'a menüyü ekle
		$pa_page_permission_info_array[] = (object)array("permission_key"=>"ADMIN_" . $menu_id, "permission_parent"=>"ADMIN_ADMINPANEL", "permission_name"=>$menu["pageTitle"]);
		
		// Sayfanın seçili olup olmadığını belirle
		if($currentPageId == $menu_id){
			$menuSelected = ' selected ';
			$master->pageTitle = $menu["pageTitle"];
		}
		
		
		// Menü ikonunu hazırla
		$menuIcon = file_exists($menu["menuIcon"]) ? $menu["menuIcon"] : $default_menu_icon;
		
		// Menü sayfalarını ekle
		$MenuHtml .= '<li id="' . $menu_id . '" class="menu {%menuSelected%}" ><div class="menuWrapper">';
		
		// Varsa alt sayfaları kontrol et ve talep edilen sayfa bir alt sayfa ise ve o sayfa seçilmişse onun parent menüsünü selected yapmak için aşağıdaki işlemi yap
		if(sizeof($menu["subPages"]) > 0){
			foreach($menu["subPages"] as $pageId=>$sp){
				// Permission sayfası için kullanılacak array'a alt menüyü ekle
				$pa_page_permission_info_array[] = (object)array("permission_key"=>"ADMIN_" .$pageId, "permission_parent"=>"ADMIN_" . $menu_id, "permission_name"=>$sp["pageTitle"]);
				
				
				if($currentPageId == $pageId){
					$menuSelected = ' selected ';
					$master->pageTitle = $sp["pageTitle"];
				}
			}
		}

		/* Menü'nün "Alt Menü" lerini ekle *************************************************************/
		if(sizeof($menu["subMenus"]) > 0){
			ksort($menu["subMenus"]);
			
			// Eğer menü sayfası alt menüyede eklenmek isteniyorsa menü sayfası bilgileri uygun şekilde onun alt menü dizisinin en başına eklenir
			if($menu["addLinkToSubMenu"] === true){
				array_unshift($menu["subMenus"], array("$menu_id"=>array("menuTitle"=>$menu["menuTitle"],"pageTitle"=>$menu["pageTitle"],"parentMenuId"=>$menu["parentMenuId"],"menuPage"=>$menu["menuPage"],"permission"=>$menu["permission"])));						
			}
			
			// Alt menünün bağlı olduğu üst menüyü ekle
			$MenuHtml .= '<span class="pageLink"><span class="menuIcon" style="background-image:url(' . $menuIcon . ');"></span>' . $menu["menuTitle"] . '</span>';
			
			
			foreach($menu["subMenus"] as $subMenuOrder=>$sm){
				$subMenuId = key($sm);
				$sm = $sm[$subMenuId];
				
				// Permission sayfası için kullanılacak array'a alt menüyü ekle
				if($subMenuId != $menu_id) // ana menüyü buradaki alt menüye ekleme
					$pa_page_permission_info_array[] = (object)array("permission_key"=>"ADMIN_" .$subMenuId, "permission_parent"=>"ADMIN_" .$menu_id, "permission_name"=>$sm["pageTitle"]);
				
				if($currentPageId == $subMenuId){
					$menuSelected = ' selected ';
					$subMenuSelected = ' selected ';
					$master->pageTitle = $sm["pageTitle"];
				}
				else
					$subMenuSelected = "";
				
				$MenuHtml .= '<a href="admin.php?page=' . $subMenuId . '" class="subMenuLink ' . $subMenuSelected . '">' . $sm["menuTitle"] . '</a>';
			}
		}
		else{
			$MenuHtml .= '<a href="admin.php?page=' . $menu_id . '" class="pageLink"><span class="menuIcon" style="background-image:url(' . $menuIcon . ');"></span>' . $menu["menuTitle"] . '</a>';
		}
			
		$MenuHtml .= '</div></li>';
		
		$MenuHtml = renderHtml($MenuHtml, array("menuSelected"=>$menuSelected));
		
		/* Bar Menü'yü oluştur */
		if( ($m["menuIcon"] != ".") && ($m["menuIcon"] != "..") && (file_exists($m["menuIcon"])))
			$BarIconsHtml .= '<a href="admin.php?page=' . urlencode($m["menuId"]) . '" style="background-image:url('.$m["menuIcon"] .')"  class="' . $menuSelected . '" title="' . $m["menuTitle"] . '"></a>';
	}
	/*****************************************************************************/

	$master->barIcons = $BarIconsHtml;
	$master->leftMenu = $MenuHtml;
}