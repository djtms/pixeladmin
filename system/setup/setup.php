<?php  error_reporting(E_ALL ^ E_NOTICE);

if(!file_exists(dirname(__FILE__) . "/../../config.php"))
{
	if(basename($_SERVER["SCRIPT_FILENAME"],".php") != "setup")
	{
		if(in_admin)
			header("Location:system/setup/setup.php");
		else
			header("Location:$admin_folder_name/system/setup/setup.php");
	}
}
else
	exit;

$errorMessage = "";

if($_POST["admin_action"]=="checkForDB")
{
	if($dbh = connectToDB($_POST["dbname"],$_POST["dbhost"],$_POST["dbuser"],$_POST["dbpass"]))
		generateAdminConfigurationFile($dbh);
	else
		$errorMessage = "* bağlantı kurulamadı!";
}

$html = file_get_contents(dirname(__FILE__) . "/database.html");
$html = str_ireplace('{%errorText%}', $errorMessage, $html);
echo $html;

exit;

/*********************************************************************************************************************************/
/* FUNCTIONS *********************************************************************************************************************/
/*********************************************************************************************************************************/

function connectToDB($dbname,$dbhost,$dbuser,$dbpass)
{
	if((trim($dbname) == "" ))
		return false;
	
	define("DB_DSN","mysql:host={$dbhost};dbname={$dbname}");
	define("DB_USER",$dbuser);
	define("DB_PASSWORD",$dbpass);
	
	$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8, time_zone='+02:00'");
	
	try
	{
		if($dbh = new PDO(DB_DSN, DB_USER, DB_PASSWORD, $options))
		{
			return $dbh;
		}
		else
			return false;
	}
	catch(PDOException $e)
	{
		return false;	
	}
}

function generateAdminConfigurationFile($dbh)
{
	global $errorMessage;
	$configfile = file_get_contents(dirname(__FILE__) . "/config-sample.php");
		
	$configfile = str_ireplace('{%dbname%}', $_POST["dbname"], $configfile);
	$configfile = str_ireplace('{%dbuser%}', $_POST["dbuser"], $configfile);
	$configfile = str_ireplace('{%dbpass%}', $_POST["dbpass"], $configfile);
	$configfile = str_ireplace('{%dbhost%}', $_POST["dbhost"], $configfile);
	$configfile = str_ireplace('{%prefix%}', $_POST["prefix"], $configfile);
	$configfile = str_ireplace('{%securekey%}', randomString(32), $configfile);
	$configfile = str_ireplace('{%sessionKeysPrefix%}', uniqid("SES") . "_", $configfile);
	
	if(!file_put_contents(dirname(__FILE__) . "/../../config.php", $configfile))
		echo "config dosyası oluşturulamadı!";
	if(!createDbTables($dbh, $_POST["prefix"]))
		echo "Database tabloları oluşturulamadı!";
	if(!createDirectoriesAndFiles())
		echo "Dosya ve dizinler oluşturulamadı!";
	else
		header("Location:user.php");
}

function randomString($length = 6)
{
	$charset = 'abcdefghijklmnopqrstuvwxyz>#${[]}|@!^+%&()=*?_-1234567890';
	$randomString = '';
	
	for($i = 0; $i<$length; $i++)
	{
		$rnd = rand(0,56);
		$randomString .= substr($charset,$rnd,1);
	}
	
	return $randomString;
}

function createDirectoriesAndFiles()
{
	$defaultDirectories   = array();
	$defaultDirectories[] = dirname(__FILE__) . "/../../../upload/";	
	$defaultDirectories[] = dirname(__FILE__) . "/../../../upload/system/";
	$defaultDirectories[] = dirname(__FILE__) . "/../../../upload/files/";
	$defaultDirectories[] = dirname(__FILE__) . "/../../../upload/thumbs/";

	foreach($defaultDirectories as $dir)
	{
		if(!is_dir($dir))
		{
			mkdir($dir);
		}
	}
	
	$default_thumbs_source_dir = dirname(__FILE__) . "/../../view/images/fileeditor/default_thumbs/";
	$default_thumbs_target_dir = dirname(__FILE__) . "/../../../upload/system/";
	
	if(($files = scandir($default_thumbs_source_dir)) && (sizeof($files) > 2))
	{
		$files_copied = true;
		foreach($files as $f)
		{
			if(($f == ".") || ($f == ".."))
				continue;
				
			$source 	 = 	$default_thumbs_source_dir . $f;
			$destination = 	$default_thumbs_target_dir . $f;
			
			if(!copy($source, $destination))
				$files_copied = false;	
		}
		
		return $files_copied;
	}
	else
	{
		return false;
	}
}

function createDbTables($dbh,$prefix)
{
	$queryI18n = "CREATE TABLE IF NOT EXISTS `{$prefix}i18n` (
					  `i18nCode` char(255) NOT NULL,
					  `scope` char(50) NOT NULL,
					  PRIMARY KEY (`i18nCode`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	
	$queryLanguage = "CREATE TABLE IF NOT EXISTS `{$prefix}language` (
					  `locale` varchar(8) NOT NULL,
					  `language_name` varchar(100) NOT NULL,
					  `language_abbr` varchar(4) NOT NULL,
					  `country_name` varchar(100) NOT NULL,
					  `country_abbr` varchar(4) NOT NULL,
					  `status` tinyint(2) NOT NULL DEFAULT '-1',
					  PRIMARY KEY (`locale`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					
					INSERT INTO `{$prefix}language` (`locale`, `language_name`, `language_abbr`, `country_name`, `country_abbr`, `status`) VALUES
					('ar_AE', 'Arabic', 'ar', 'United Arab Emirates', 'AE', -1),
					('ar_BH', 'Arabic', 'ar', 'Bahrain', 'BH', -1),
					('ar_DZ', 'Arabic', 'ar', 'Algeria', 'DZ', -1),
					('ar_EG', 'Arabic', 'ar', 'Egypt', 'EG', -1),
					('ar_IN', 'Arabic', 'ar', 'India', 'IN', -1),
					('ar_IQ', 'Arabic', 'ar', 'Iraq', 'IQ', -1),
					('ar_JO', 'Arabic', 'ar', 'Jordan', 'JO', -1),
					('ar_KW', 'Arabic', 'ar', 'Kuwait', 'KW', -1),
					('ar_LB', 'Arabic', 'ar', 'Lebanon', 'LB', -1),
					('ar_LY', 'Arabic', 'ar', 'Libya', 'LY', -1),
					('ar_MA', 'Arabic', 'ar', 'Morocco', 'MA', -1),
					('ar_OM', 'Arabic', 'ar', 'Oman', 'OM', -1),
					('ar_QA', 'Arabic', 'ar', 'Qatar', 'QA', -1),
					('ar_SA', 'Arabic', 'ar', 'Saudi Arabia', 'SA', -1),
					('ar_SD', 'Arabic', 'ar', 'Sudan', 'SD', -1),
					('ar_SY', 'Arabic', 'ar', 'Syria', 'SY', -1),
					('ar_TN', 'Arabic', 'ar', 'Tunisia', 'TN', -1),
					('ar_YE', 'Arabic', 'ar', 'Yemen', 'YE', -1),
					('be_BY', 'Belarusian', 'be', 'Belarus', 'BY', -1),
					('bg_BG', 'Bulgarian', 'bg', 'Bulgaria', 'BG', -1),
					('ca_ES', 'Catalan', 'ca', 'Spain', 'ES', -1),
					('cs_CZ', 'Czech', 'cs', 'Czech Republic', 'CZ', -1),
					('da_DK', 'Danish', 'da', 'Denmark', 'DK', -1),
					('de_AT', 'German', 'de', 'Austria', 'AT', -1),
					('de_BE', 'German', 'de', 'Belgium', 'BE', -1),
					('de_CH', 'German', 'de', 'Switzerland', 'CH', -1),
					('de_DE', 'German', 'de', 'Germany', 'DE', -1),
					('de_LU', 'German', 'de', 'Luxembourg', 'LU', -1),
					('en_AU', 'English', 'en', 'Australia', 'AU', -1),
					('en_CA', 'English', 'en', 'Canada', 'CA', -1),
					('en_GB', 'English', 'en', 'United Kingdom', 'GB', -1),
					('en_IN', 'English', 'en', 'India', 'IN', -1),
					('en_NZ', 'English', 'en', 'New Zealand', 'NZ', -1),
					('en_PH', 'English', 'en', 'Philippines', 'PH', -1),
					('en_US', 'English', 'en', 'United States', 'US', -1),
					('en_ZA', 'English', 'en', 'South Africa', 'ZA', -1),
					('en_ZW', 'English', 'en', 'Zimbabwe', 'ZW', -1),
					('es_AR', 'Spanish', 'es', 'Argentina', 'AR', -1),
					('es_BO', 'Spanish', 'es', 'Bolivia', 'BO', -1),
					('es_CL', 'Spanish', 'es', 'Chile', 'CL', -1),
					('es_CO', 'Spanish', 'es', 'Columbia', 'CO', -1),
					('es_CR', 'Spanish', 'es', 'Costa Rica', 'CR', -1),
					('es_DO', 'Spanish', 'es', 'Dominican Republic', 'DO', -1),
					('es_EC', 'Spanish', 'es', 'Ecuador', 'EC', -1),
					('es_ES', 'Spanish', 'es', 'Spain', 'ES', -1),
					('es_GT', 'Spanish', 'es', 'Guatemala', 'GT', -1),
					('es_HN', 'Spanish', 'es', 'Honduras', 'HN', -1),
					('es_MX', 'Spanish', 'es', 'Mexico', 'MX', -1),
					('es_NI', 'Spanish', 'es', 'Nicaragua', 'NI', -1),
					('es_PA', 'Spanish', 'es', 'Panama', 'PA', -1),
					('es_PE', 'Spanish', 'es', 'Peru', 'PE', -1),
					('es_PR', 'Spanish', 'es', 'Puerto Rico', 'PR', -1),
					('es_PY', 'Spanish', 'es', 'Paraguay', 'PY', -1),
					('es_SV', 'Spanish', 'es', 'El Salvador', 'SV', -1),
					('es_US', 'Spanish', 'es', 'United States', 'US', -1),
					('es_UY', 'Spanish', 'es', 'Uruguay', 'UY', -1),
					('es_VE', 'Spanish', 'es', 'Venezuela', 'VE', -1),
					('et_EE', 'Estonian', 'et', 'Estonia', 'EE', -1),
					('eu_ES', 'Basque', 'eu', 'Basque', 'ES', -1),
					('fi_FI', 'Finnish', 'fi', 'Finland', 'FI', -1),
					('fo_FO', 'Faroese', 'fo', 'Faroe Islands', 'FO', -1),
					('fr_BE', 'French', 'fr', 'Belgium', 'BE', -1),
					('fr_CA', 'French', 'fr', 'Canada', 'CA', -1),
					('fr_CH', 'French', 'fr', 'Switzerland', 'CH', -1),
					('fr_FR', 'French', 'fr', 'France', 'FR', -1),
					('fr_LU', 'French', 'fr', 'Luxembourg', 'LU', -1),
					('gl_ES', 'Galician', 'gl', 'Spain', 'ES', -1),
					('gu_IN', 'Gujarati', 'gu', 'India', 'IN', -1),
					('he_IL', 'Hebrew', 'he', 'Israel', 'IL', -1),
					('hi_IN', 'Hindi', 'hi', 'India', 'IN', -1),
					('hr_HR', 'Croatian', 'hr', 'Croatia', 'HR', -1),
					('hu_HU', 'Hungarian', 'hu', 'Hungary', 'HU', -1),
					('id_ID', 'Indonesian', 'id', 'Indonesia', 'ID', -1),
					('is_IS', 'Icelandic', 'is', 'Iceland', 'IS', -1),
					('it_CH', 'Italian', 'it', 'Switzerland', 'CH', -1),
					('it_IT', 'Italian', 'it', 'Italy', 'IT', -1),
					('ja_JP', 'Japanese', 'ja', 'Japan', 'JP', -1),
					('ko_KR', 'Korean', 'ko', 'Republic of Korea', 'KR', -1),
					('lt_LT', 'Lithuanian', 'lt', 'Lithuania', 'LT', -1),
					('lv_LV', 'Latvian', 'lv', 'Latvia', 'LV', -1),
					('mk_MK', 'Macedonian', 'mk', 'FYROM', 'MK', -1),
					('mn_MN', 'Mongolia', 'mn', 'Mongolian', 'MN', -1),
					('ms_MY', 'Malay', 'ms', 'Malaysia', 'MY', -1),
					('nb_NO', 'Norwegian(Bokmål)', 'nb', 'Norway', 'NO', -1),
					('nl_BE', 'Dutch', 'nl', 'Belgium', 'BE', -1),
					('nl_NL', 'Dutch', 'nl', 'The Netherlands', 'NL', -1),
					('no_NO', 'Norwegian', 'no', 'Norway', 'NO', -1),
					('pl_PL', 'Polish', 'pl', 'Poland', 'PL', -1),
					('pt_BR', 'Portugese', 'pt', 'Brazil', 'BR', -1),
					('pt_PT', 'Portugese', 'pt', 'Portugal', 'PT', -1),
					('ro_RO', 'Romanian', 'ro', 'Romania', 'RO', -1),
					('ru_RU', 'Russian', 'ru', 'Russia', 'RU', -1),
					('ru_UA', 'Russian', 'ru', 'Ukraine', 'UA', -1),
					('sk_SK', 'Slovak', 'sk', 'Slovakia', 'SK', -1),
					('sl_SI', 'Slovenian', 'sl', 'Slovenia', 'SI', -1),
					('sq_AL', 'Albanian', 'sq', 'Albania', 'AL', -1),
					('sr_YU', 'Serbian', 'sr', 'Yugoslavia', 'YU', -1),
					('sv_FI', 'Swedish', 'sv', 'Finland', 'FI', -1),
					('sv_SE', 'Swedish', 'sv', 'Sweden', 'SE', -1),
					('ta_IN', 'Tamil', 'ta', 'India', 'IN', -1),
					('te_IN', 'Telugu', 'te', 'India', 'IN', -1),
					('th_TH', 'Thai', 'th', 'Thailand', 'TH', -1),
					('tr_TR', 'Türkçe', 'tr', 'Turkey', 'TR', -1),
					('uk_UA', 'Ukrainian', 'uk', 'Ukraine', 'UA', -1),
					('ur_PK', 'Urdu', 'ur', 'Pakistan', 'PK', -1),
					('vi_VN', 'Vietnamese', 'vi', 'Viet Nam', 'VN', -1),
					('zh_CN', 'Chinese', 'zh', 'China', 'CN', -1),
					('zh_HK', 'Chinese', 'zh', 'Hong Kong', 'HK', -1);";
	
	$queryOption = "CREATE TABLE IF NOT EXISTS `{$prefix}option` (
					  `option_name` char(255) NOT NULL,
					  `option_value` text NOT NULL,
					  `group_name` char(255) NOT NULL,
					  `data_type` char(20) NOT NULL,
					  PRIMARY KEY (`option_name`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	
	$queryUser = "CREATE TABLE IF NOT EXISTS `{$prefix}user` (
					  `user_id` int(11) NOT NULL AUTO_INCREMENT,
					  `image_id` int(11) NOT NULL,
					  `username` varchar(100) NOT NULL,
					  `displayname` varchar(100) NOT NULL,
					  `birthday` date NOT NULL,
					  `first_name` varchar(100) DEFAULT NULL,
					  `last_name` varchar(100) DEFAULT NULL,
					  `email` varchar(100) NOT NULL,
					  `phone` varchar(30) DEFAULT NULL,
					  `password` varchar(100) NOT NULL,
					  `pass_key` varchar(30) NOT NULL,
					  `register_time` datetime NOT NULL,
					  `user_type` varchar(20) NOT NULL,
					  `captcha_limit` tinyint(1) NOT NULL DEFAULT '3',
					  `status` varchar(50) NOT NULL DEFAULT 'active',
					  PRIMARY KEY (`user_id`),
					  UNIQUE KEY `username, email` (`username`,`email`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	
	$queryUserTrack = "CREATE TABLE IF NOT EXISTS `{$prefix}user_track` (
					  `track_id` int(11) NOT NULL AUTO_INCREMENT,
					  `tracking_key` varchar(30) NOT NULL,
					  `user_id` int(11) NOT NULL,
					  `user_session` varchar(200) NOT NULL,
					  `user_ip` varchar(30) NOT NULL,
					  `start_time` datetime NOT NULL,
					  `end_time` datetime NOT NULL,
					  `status` varchar(20) NOT NULL DEFAULT 'active',
					  PRIMARY KEY (`track_id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	
	$queryUserTicket = "CREATE TABLE IF NOT EXISTS `{$prefix}user_ticket` (
					  `ticket_id` int(11) NOT NULL AUTO_INCREMENT,
					  `user_id` int(11) NOT NULL DEFAULT '-1',
					  `ticket_type` varchar(20) NOT NULL DEFAULT 'invitation',
					  `ticket_key` varchar(100) NOT NULL,
					  `end_time` datetime NOT NULL,
					  `status` varchar(20) NOT NULL DEFAULT 'active',
					  PRIMARY KEY (`ticket_id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
	
	$queryMessage = "CREATE TABLE IF NOT EXISTS `{$prefix}message` (
					  `messageId` int(11) NOT NULL AUTO_INCREMENT,
					  `fromName` char(100) NOT NULL,
					  `subject` char(255) NOT NULL,
					  `message` text NOT NULL,
					  `submitTime` datetime NOT NULL,
					  `readStatus` char(20) NOT NULL DEFAULT 'unread',
					  PRIMARY KEY (`messageId`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	
	$queryLog = "CREATE TABLE IF NOT EXISTS `{$prefix}log` (
					  `log_id` int(11) NOT NULL AUTO_INCREMENT,
					  `user_id` int(11) NOT NULL,
					  `date` datetime NOT NULL,
					  `log` text NOT NULL,
					  `type` char(20) NOT NULL DEFAULT 'log',
					  PRIMARY KEY (`log_id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
	
	$queryFile = "CREATE TABLE IF NOT EXISTS `{$prefix}file` (
				  `file_id` int(11) NOT NULL AUTO_INCREMENT,
				  `basename` char(255) NOT NULL,
				  `filename` char(255) NOT NULL,
				  `directory` char(255) NOT NULL,
				  `url` char(255) NOT NULL,
				  `type` char(20) NOT NULL,
				  `extension` char(20) NOT NULL,
				  `size` char(255) NOT NULL,
				  `creation_time` datetime NOT NULL,
				  `last_update_time` datetime NOT NULL,
				  `width` int(11) NOT NULL DEFAULT '-1',
				  `height` int(11) NOT NULL DEFAULT '-1',
				  `thumb_file_id` int(11) NOT NULL DEFAULT '-1',
				  `copied_file_id` int(11) NOT NULL DEFAULT '-1',
				  `access_type` char(20) NOT NULL DEFAULT 'public',
				  PRIMARY KEY (`file_id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
				
				INSERT INTO `{$prefix}file` (`file_id`, `basename`, `filename`, `directory`, `url`, `type`, `extension`, `size`, `creation_time`, `last_update_time`, `width`, `height`, `thumb_file_id`, `copied_file_id`, `access_type`) VALUES
				(1, 'aac.png', 'aac', '', 'aac.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(2, 'ai.png', 'ai', '', 'ai.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(3, 'aiff.png', 'aiff', '', 'aiff.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(4, 'avi.png', 'avi', '', 'avi.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(5, 'css.png', 'css', '', 'css.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(6, 'doc.png', 'doc', '', 'doc.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(7, 'docx.png', 'docx', '', 'docx.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(8, 'generic.png', 'generic', '', 'generic.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(9, 'gzip.png', 'gzip', '', 'gzip.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(10, 'html.png', 'html', '', 'html.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(11, 'js.png', 'js', '', 'js.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(12, 'm4a.png', 'm4a', '', 'm4a.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(13, 'm4v.png', 'm4v', '', 'm4v.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(14, 'mov.png', 'mov', '', 'mov.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(15, 'mp3.png', 'mp3', '', 'mp3.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(16, 'mp4.png', 'mp4', '', 'mp4.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(17, 'mpeg2.png', 'mpeg2', '', 'mpeg2.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(18, 'mpg.png', 'mpg', '', 'mpg.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(19, 'pdf.png', 'pdf', '', 'pdf.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(20, 'php.png', 'php', '', 'php.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(21, 'psd.png', 'psd', '', 'psd.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(22, 'raw.png', 'raw', '', 'raw.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(23, 'rtf.png', 'rtf', '', 'rtf.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(24, 'tar.png', 'tar', '', 'tar.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(25, 'tiff.png', 'tiff', '', 'tiff.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(26, 'txt.png', 'txt', '', 'txt.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(27, 'wav.png', 'wav', '', 'wav.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(28, 'wmv.png', 'wmv', '', 'wmv.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(29, 'zip.png', 'zip', '', 'zip.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(30, 'flv.png', 'flv', '', 'flv.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(31, 'f4v.png', 'f4v', '', 'f4v.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
				(32, 'folder.png', 'folder', '', 'folder.png', 'image', 'png', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 58, 51, -1, -1, 'system'),
				(33, 'exclamation.jpg', 'exclamation', '', 'exclamation.jpg', 'image', 'jpg', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system');";
	
	$queryDirectory = "CREATE TABLE IF NOT EXISTS `{$prefix}directory` (
				  `directory_id` int(11) NOT NULL AUTO_INCREMENT,
				  `parent_id` int(11) NOT NULL,
				  `name` char(100) NOT NULL,
				  `directory` char(255) NOT NULL,
				  `is_favourite` tinyint(1) NOT NULL DEFAULT '-1',
				  `access_type` char(20) NOT NULL DEFAULT 'public',
				  PRIMARY KEY (`directory_id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
	
	$queryThumb = "CREATE TABLE IF NOT EXISTS `{$prefix}thumb` (
			  `thumb_id` int(11) NOT NULL AUTO_INCREMENT,
			  `basename` char(255) NOT NULL,
			  `filename` char(255) NOT NULL,
			  `extension` char(20) NOT NULL,
			  `directory` char(255) NOT NULL,
			  `url` char(255) NOT NULL,
			  `width` int(6) NOT NULL,
			  `height` int(6) NOT NULL,
			  `squeeze` tinyint(1) NOT NULL DEFAULT '-1',
			  `proportion` tinyint(1) NOT NULL DEFAULT '1',
			  `crop_position` char(20) NOT NULL DEFAULT 'center top',
			  `bg_color` char(6) NOT NULL DEFAULT 'FFFFFF',
			  `crop_type` char(25) NOT NULL DEFAULT 'auto_crop',
			  PRIMARY KEY (`thumb_id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	
	$queryFileThumb = "CREATE TABLE IF NOT EXISTS `{$prefix}file_thumb` (
				  `file_id` int(11) NOT NULL,
				  `thumb_id` int(11) NOT NULL,
				  PRIMARY KEY (`file_id`,`thumb_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	
	$queryGallery = "CREATE TABLE IF NOT EXISTS `{$prefix}gallery` (
				  `gallery_id` int(11) NOT NULL AUTO_INCREMENT,
				  `status` char(20) NOT NULL DEFAULT 'temporary',
				  PRIMARY KEY (`gallery_id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
		
	$queryGalleryFile = "CREATE TABLE IF NOT EXISTS `{$prefix}gallery_file` (
				  `gallery_id` int(11) NOT NULL,
				  `file_id` int(11) NOT NULL,
				  `order_num` int(11) NOT NULL,
				  PRIMARY KEY (`gallery_id`,`file_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	
	$queryGroup = "CREATE TABLE IF NOT EXISTS `{$prefix}group` (
				  `group_id` int(11) NOT NULL AUTO_INCREMENT,
				  `group_name` varchar(100) COLLATE utf8_bin NOT NULL,
				  `order_num` int(11) NOT NULL,
				  PRIMARY KEY (`group_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;";
	
	$queryGroupPermission = "CREATE TABLE IF NOT EXISTS `{$prefix}group_permission` (
				  `group_id` int(11) NOT NULL,
				  `permission_id` int(11) NOT NULL,
				  PRIMARY KEY (`group_id`,`permission_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
	
	$queryPermission = "CREATE TABLE IF NOT EXISTS `{$prefix}permission` (
				  `permission_id` int(11) NOT NULL AUTO_INCREMENT,
				  `permission_parent` int(11) NOT NULL,
				  `permission_name` varchar(100) COLLATE utf8_bin NOT NULL,
				  `permission_url` varchar(255) COLLATE utf8_bin NOT NULL,
				  `order_num` int(11) NOT NULL,
				  PRIMARY KEY (`permission_id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;";
	
	$queryRole = "CREATE TABLE IF NOT EXISTS `{$prefix}role` (
				  `role_id` int(11) NOT NULL AUTO_INCREMENT,
				  `role_name` varchar(100) COLLATE utf8_bin DEFAULT NULL,
				  `order_num` int(11) NOT NULL,
				  PRIMARY KEY (`role_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;";
	
	$queryRolePermission = "CREATE TABLE IF NOT EXISTS `{$prefix}role_permission` (
				  `role_id` int(11) NOT NULL,
				  `permission_id` int(11) NOT NULL,
				  PRIMARY KEY (`role_id`,`permission_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
	
	
	$queryUserGroup = "CREATE TABLE IF NOT EXISTS `{$prefix}user_group` (
				  `user_id` int(11) NOT NULL,
				  `group_id` int(11) NOT NULL,
				  PRIMARY KEY (`user_id`,`group_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
	
	$queryUserRole = "CREATE TABLE IF NOT EXISTS `{$prefix}user_role` (
				  `user_id` int(11) NOT NULL,
				  `role_id` int(11) NOT NULL,
				  PRIMARY KEY (`user_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
	
	$querySitemap = "CREATE TABLE IF NOT EXISTS `{$prefix}sitemap` (
				  `page_id` varchar(25) COLLATE utf8_bin NOT NULL,
				  `page_parent` varchar(25) COLLATE utf8_bin NOT NULL DEFAULT '-1',
				  `page_url` varchar(255) COLLATE utf8_bin DEFAULT NULL,
				  `page_title` varchar(25) COLLATE utf8_bin DEFAULT NULL,
				  `page_description` varchar(25) COLLATE utf8_bin DEFAULT NULL,
				  PRIMARY KEY (`page_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
	
	
	return  $dbh->query($queryI18n) &&
			$dbh->query($queryLanguage) &&
			$dbh->query($queryOption) &&
			$dbh->query($queryUser) &&
			$dbh->query($queryUserTrack) &&
			$dbh->query($queryUserTicket) &&
			$dbh->query($queryMessage) &&
			$dbh->query($queryLog) &&
			$dbh->query($queryFile) &&
			$dbh->query($queryDirectory) &&
			$dbh->query($queryFileThumb) &&
			$dbh->query($queryThumb) && 
			$dbh->query($queryGallery) && 
			$dbh->query($queryGalleryFile) &&
			$dbh->query($queryGroup) &&
			$dbh->query($queryGroupPermission) &&
			$dbh->query($queryPermission) &&
			$dbh->query($queryRole) &&
			$dbh->query($queryRolePermission) &&
			$dbh->query($queryUserGroup) && 
			$dbh->query($queryUserRole) &&
			$dbh->query($querySitemap);
}

