<?php

// DEFAULT MENUS
addMenu($GT->KONTROL_PANELI,"$VIEW_URL/images/icons/dashboard_icon.png", $GT->KONTROL_PANELI, "dashboard","dashboard.php",1,USER_GUEST);
addMenu($GT->MESAJLAR,"$VIEW_URL/images/icons/messages_icon.png", $GT->MESAJLAR,"messages","messages.php",101,USER_SUPER);
addPage($GT->MESAJ_ICERIGI,"messages" ,"readmessage", "readmessage.php",USER_SUPER);
addMenu($GT->KULLANICI_HESAPLARI,"$VIEW_URL/images/icons/clients_icon.png",$GT->KULLANICI_HESAPLARI,"useraccounts","useraccounts.php",20,USER_SUPER); // 108
//addSubMenu("Kullanıcı Ekle", "Kullanıcı Ekle", "useraccounts", "add_user", "add_user.php", 2, USER_SUPER);
addSubMenu($GT->DAVET_GONDER, $GT->DAVET_GONDER, "useraccounts", "invite_user", "invite_user.php",1, USER_SUPER);


addSubMenu($GT->YETKILER, $GT->YETKILER, "useraccounts", "permissions", "permissions.php", 3);
addSubMenu($GT->ROLLER, $GT->ROLLER, "useraccounts", "roles", dirname(__FILE__) . "/roles.php", 4);
addPage($GT->KULLANICI_BILGILERI, "useraccounts", "edit_useraccount", "edit_useraccount.php");
addPage($GT->YETKI_EKLE, "useraccounts", "add_permission", "edit_permission.php");
addPage($GT->YETKI_DETAYI, "useraccounts", "edit_permission", "edit_permission.php");
addPage($GT->ROL_EKLE, "useraccounts", "add_role", "edit_role.php");
addPage($GT->ROL_DETAYI, "useraccounts", "edit_role", "edit_role.php");
addMenu($GT->AYARLAR,"$VIEW_URL/images/icons/options_icon.png","Ayarlar","settings","settings.php",109,USER_SUPER);

if(get_option("admin_multilanguage_mode") == "multilanguage")
{
	addSettingsMenu($GT->DIL_SECENEKLERI, $GT->DIL_SECENEKLERI, "languageoptions", "languages.php",1,USER_SUPER);
	addSettingsMenu($GT->SABIT_DIL_DEGISKENLERI, $GT->SABIT_DIL_DEGISKENLERI, "global_i18n_variables", "global_i18n_variables.php",2,USER_SUPER);
	addPage($GT->DIL_EKLE, "settings", "add_language", "edit_language.php");
	addPage($GT->DIL_BILGILERI, "settings", "edit_language", "edit_language.php");
}

addSettingsMenu($GT->DELISTIRICILER, $GT->DELISTIRICILER, "developers", "developers.php",2,USER_GUEST);

addMenu($GT->SITE_HARITASI, "", $GT->SITE_HARITASI, "sitemap", "sitemap.php", 100);
addSubMenu($GT->SAYFA_EKLE, $GT->SAYFA_EKLE, "sitemap", "add_sitemap_page", "edit_sitemap_page.php");
addPage($GT->SAYFA_BILGILERI, "sitemap", "edit_sitemap_page", "edit_sitemap_page.php");

addMenu($GT->PROFIL,"$VIEW_URL/images/icons/profile_icon.png", $GT->PROFIL,"profile","profile.php",111,USER_GUEST);

global $add_modules_menu;

if($add_modules_menu)
	addMenu($GT->MODULLER,"$VIEW_URL/images/icons/modules_icon.png", $GT->MODULLER,"modules","modules.php",107,USER_SUPER);
	
global $master;
$master->user = $ADMIN->AUTHENTICATION->authenticated_user;
if(get_option("admin_multilanguage_mode") == "multilanguage")
{
	$master->siteTitle = getI18n("admin_site_titleI18N");
}
else
{
	$master->siteTitle = get_option("admin_site_title");
}
$master->siteLink = dirname($_SERVER["SCRIPT_NAME"]) . "/../";