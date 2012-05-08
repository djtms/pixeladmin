<?php	require_once 'includes.php';

$IsSiteMultilanguage = (get_option("admin_SiteMultilanguageMode") == "multilanguage") ? true  : false;

if(isset($_POST["admin_action"]) == "Kaydet")
{
	$isSmtp = (isset($_POST["smtp"]) && (trim($_POST["smtp"]) != "")) ? ' checked="checked" ' : "";
	
	$success = 	set_option("admin_siteAddress",$_POST["siteAddress"],"pa_settings") &&
				
				set_option("admin_analystics",$_POST["analystics"],"pa_settings") &&
				set_option("admin_isSmtpMail", $isSmtp,"pa_settings") &&
				set_option("admin_mailHost",$_POST["mailHost"],"pa_settings") &&
				set_option("admin_mailPort",$_POST["mailPort"],"pa_settings") &&
				set_option("admin_mailUser",$_POST["mailUser"],"pa_settings") &&
				set_option("admin_getMailAddress",$_POST["getMailAddress"],"pa_settings") &&
				set_option("admin_mailPassword",$_POST["mailPassword"],"pa_settings") &&
				set_option("admin_facebook",$_POST["facebook"],"pa_settings") &&
				set_option("admin_twitter",$_POST["twitter"],"pa_settings");
	
	// Site'nin multilanguage olma ihtimali yüzünden, siteTitle ve siteDescription değerlerini özel olarak kaydediyoruz.
	if($IsSiteMultilanguage === true)
	{
		$success = $success && saveI18n();
	}
	else
	{
		$success = $success && set_option("admin_siteTitle",$_POST["siteTitle"],"pa_settings") &&
					set_option("admin_description",$_POST["description"],"pa_settings") &&
					set_option("admin_keywords",$_POST["keywords"],"pa_settings");
	}

	$message = $success ? "Ayarlarınız Başarıyla Kaydedildi!" : "Hata Oluştu!";

	postMessage($message,!$success);
}

$stg = get_optiongroup("pa_settings");

if($IsSiteMultilanguage)
{
	$siteTitleValue = ' i18n="admin_siteTitleI18N" ';
	$siteDescriptionI18N = ' i18n="admin_siteDescriptionI18N" ';
	$siteKeywordsI18N = ' i18n="admin_keywordsI18N" ';
}
else
{
	$siteTitleValue = ' name="siteTitle" value="' . $stg->admin_siteTitle . '" ';
	$siteDescriptionValue = $stg->admin_description;
	$siteKeywordsValue = $stg->admin_keywords;
}

addScript("js/pages/settings.js");
$settings->render();