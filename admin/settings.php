<?php	require_once 'includes.php';

$IsSiteMultilanguage = (get_option("admin_multilanguage_mode") == "multilanguage") ? true  : false;

if(isset($_POST["admin_action"]) == "save_settings")
{
	$isSmtp = (isset($_POST["smtp"]) && (trim($_POST["smtp"]) != "")) ? ' checked="checked" ' : "";
	
	$success = 	set_option("admin_site_address",$_POST["siteAddress"],"pa_settings") &&
				
				set_option("admin_analystics",$_POST["analystics"],"pa_settings") &&
				set_option("admin_isSmtpMail", $isSmtp,"pa_settings") &&
				set_option("admin_mailHost",$_POST["mailHost"],"pa_settings") &&
				set_option("admin_mail_port",$_POST["mailPort"],"pa_settings") &&
				set_option("admin_mail_user",$_POST["mailUser"],"pa_settings") &&
				set_option("admin_get_mail_address",$_POST["getMailAddress"],"pa_settings") &&
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
		$success = $success && set_option("admin_site_title",$_POST["siteTitle"],"pa_settings") &&
					set_option("admin_description",$_POST["description"],"pa_settings") &&
					set_option("admin_keywords",$_POST["keywords"],"pa_settings");
	}

	$message = $success ? $GT->BASARIYLA_KAYDEDILDI : $GT->HATA_OLUSTU;

	postMessage($message,!$success);
}

$stg = get_optiongroup("pa_settings");

if($IsSiteMultilanguage) {
	$siteTitleValue = ' i18n="admin_site_titleI18N" ';
	$siteDescriptionI18N = ' i18n="admin_descriptionI18N" ';
	$siteKeywordsI18N = ' i18n="admin_keywordsI18N" ';
}
else {
	$siteTitleValue = ' name="siteTitle" value="' . $stg->admin_site_title . '" ';
	$siteDescriptionValue = $stg->admin_description;
	$siteKeywordsValue = $stg->admin_keywords;
}

addScript("js/pages/settings.js");
$settings->render();