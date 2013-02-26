<?php

// DEFAULT MENUS
addMenu("Kontrol Paneli","$VIEW_URL/images/icons/dashboard_icon.png","Kontrol Paneli","dashboard","dashboard.php",1,USER_GUEST);
addMenu("Mesajlar","$VIEW_URL/images/icons/messages_icon.png","Mesajlar","messages","messages.php",101,USER_SUPER);
addPage("Mesaj İçeriği","messages" ,"readmessage", "readmessage.php",USER_SUPER);
addMenu("Kullanıcı Hesapları","$VIEW_URL/images/icons/clients_icon.png","Kullanıcı Hesapları","useraccounts","useraccounts.php",20,USER_SUPER); // 108
//addSubMenu("Kullanıcı Ekle", "Kullanıcı Ekle", "useraccounts", "add_user", "add_user.php", 2, USER_SUPER);
addSubMenu("Davet Gönder", "Davet Gönder", "useraccounts", "invite_user", "invite_user.php",1, USER_SUPER);


addSubMenu("Yetkiler", "Yetkiler", "useraccounts", "permissions", "permissions.php", 3);
addSubMenu("Roller", "Roller", "useraccounts", "roles", dirname(__FILE__) . "/roles.php", 4);
addPage("Kullanıcı Bilgileri", "useraccounts", "edit_useraccount", "edit_useraccount.php");
addPage("Yetki Ekle", "useraccounts", "add_permission", "edit_permission.php");
addPage("Yetki Detayı", "useraccounts", "edit_permission", "edit_permission.php");
addPage("Rol Ekle", "useraccounts", "add_role", "edit_role.php");
addPage("Rol Detayı", "useraccounts", "edit_role", "edit_role.php");
addMenu("Ayarlar","$VIEW_URL/images/icons/options_icon.png","Ayarlar","settings","settings.php",109,USER_SUPER);

if(get_option("admin_multilanguage_mode") == "multilanguage")
{
	addSettingsMenu("Dil Seçenekleri", "Dil Seçenekleri", "languageoptions", "languages.php",1,USER_SUPER);
	addSettingsMenu("Sabit Dil Değişkenleri", "Sabit Dil Değişkenleri", "global_i18n_variables", "global_i18n_variables.php",2,USER_SUPER);
	addPage("Dil Ekle", "settings", "add_language", "edit_language.php");
	addPage("Dil Bilgileri", "settings", "edit_language", "edit_language.php");
}

addSettingsMenu("Geliştiriciler", "Geliştiriciler", "developers", "developers.php",2,USER_GUEST);

addMenu("Site Haritası", "", "Site Haritası", "sitemap", "sitemap.php", 100);
addSubMenu("Sayfa Ekle", "Sayfa Ekle", "sitemap", "add_sitemap_page", "edit_sitemap_page.php");
addPage("Sayfa Bilgileri", "sitemap", "edit_sitemap_page", "edit_sitemap_page.php");

addMenu("Profil","$VIEW_URL/images/icons/profile_icon.png","Profil","profile","profile.php",111,USER_GUEST);

global $add_modules_menu;

if($add_modules_menu)
	addMenu("Modüller","$VIEW_URL/images/icons/modules_icon.png","Modüller","modules","modules.php",107,USER_SUPER);
	
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