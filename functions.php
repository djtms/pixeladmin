<?php

// DEFAULT MENUS
addMenu("Kontrol Paneli","$VIEW_URL/images/icons/dashboard_icon.png","Kontrol Paneli","dashboard","dashboard.php",1,USER_GUEST);
addMenu("Mesajlar","$VIEW_URL/images/icons/messages_icon.png","Mesajlar","messages","messages.php",17,USER_SUPER);
addPage("Mesaj İçeriği","messages" ,"readmessage", "readmessage.php",USER_SUPER);
addMenu("Kullanıcı Hesapları","$VIEW_URL/images/icons/clients_icon.png","Kullanıcı Hesapları","useraaccounts","useraccounts.php",18,USER_SUPER);
addSubMenu("Kullanıcı Ekle", "Kullanıcı Ekle", "useraaccounts", "invite_user", dirname(__FILE__) . "/invite_user.php",2, USER_SUPER);
addMenu("Ayarlar","$VIEW_URL/images/icons/options_icon.png","Ayarlar","settings","settings.php",19,USER_SUPER);

if(get_option("admin_SiteMultilanguageMode") == "multilanguage")
{
	addSettingsMenu("Dil Seçenekleri", "Dil Seçenekleri", "languageoptions", "languages.php",10,USER_SUPER);
	addPage("Dil Ekle", "settings", "edit_language", "edit_language.php");
}

addSettingsMenu("Geliştiriciler", "Geliştiriciler", "developers", "developers.php",1000,USER_GUEST);
addMenu("Profil","$VIEW_URL/images/icons/profile_icon.png","Profil","profile","profile.php",20,USER_GUEST);

global $add_modules_menu;

if($add_modules_menu)
	addMenu("Modüller","$VIEW_URL/images/icons/modules_icon.png","Modüller","modules","modules.php",11,USER_SUPER);
	
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