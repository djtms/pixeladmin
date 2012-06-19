<?php

// DEFAULT MENUS
addMenu("Kontrol Paneli","$VIEW_URL/images/icons/dashboard_icon.png","Kontrol Paneli","dashboard","dashboard.php",1,USER_GUEST);
addMenu("Mesajlar","$VIEW_URL/images/icons/messages_icon.png","Mesajlar","messages","messages.php",101,USER_SUPER);
addPage("Mesaj İçeriği","messages" ,"readmessage", "readmessage.php",USER_SUPER);
addMenu("Kullanıcı Hesapları","$VIEW_URL/images/icons/clients_icon.png","Kullanıcı Hesapları","useraaccounts","useraccounts.php",108,USER_SUPER);
addSubMenu("Kullanıcı Ekle", "Kullanıcı Ekle", "useraaccounts", "invite_user", "invite_user.php",2, USER_SUPER);
//addSubMenu("Kullanıcı Yetkileri", "Kullanıcı Yetkileri", "useraaccounts", "user_permissions", "permissions.php");
addPage("Kullanıcı Bilgileri", "useraaccounts", "edit_useraccount", "edit_useraccount.php");
addPage("Yetki Ekle", "useraaccounts", "add_permission", "edit_permission.php");
addPage("Yetki Detayı", "useraaccounts", "edit_permission", "edit_permission.php");
addMenu("Ayarlar","$VIEW_URL/images/icons/options_icon.png","Ayarlar","settings","settings.php",109,USER_SUPER);

if(get_option("admin_SiteMultilanguageMode") == "multilanguage")
{
	addSettingsMenu("Dil Seçenekleri", "Dil Seçenekleri", "languageoptions", "languages.php",10,USER_SUPER);
	addPage("Dil Ekle", "settings", "edit_language", "edit_language.php");
}

addSettingsMenu("Geliştiriciler", "Geliştiriciler", "developers", "developers.php",1000,USER_GUEST);

addMenu("Site Haritası", "", "Site Haritası", "sitemap", "sitemap.php", 100);
addSubMenu("Sayfa Ekle", "Sayfa Ekle", "sitemap", "add_sitemap_page", "edit_sitemap_page.php");
addPage("Sayfa Bilgileri", "sitemap", "edit_sitemap_page", "edit_sitemap_page.php");

addMenu("Profil","$VIEW_URL/images/icons/profile_icon.png","Profil","profile","profile.php",111,USER_GUEST);

global $add_modules_menu;

if($add_modules_menu)
	addMenu("Modüller","$VIEW_URL/images/icons/modules_icon.png","Modüller","modules","modules.php",107,USER_SUPER);
	
global $master;
$master->user = $ADMIN->USER->loggedInUser;
if(get_option("admin_SiteMultilanguageMode") == "multilanguage")
{
	$master->siteTitle = getI18n("admin_siteTitleI18N");
}
else
{
	$master->siteTitle = get_option("admin_siteTitle");
}
$master->siteLink = dirname($_SERVER["SCRIPT_NAME"]) . "/../";