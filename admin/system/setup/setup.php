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
	$defaultDirectories[] = dirname(__FILE__) . "/../../../upload/files";
	$defaultDirectories[] = dirname(__FILE__) . "/../../../upload/files/public/";
	$defaultDirectories[] = dirname(__FILE__) . "/../../../upload/files/private/";
	$defaultDirectories[] = dirname(__FILE__) . "/../../../upload/files/system/";
	$defaultDirectories[] = dirname(__FILE__) . "/../../../upload/thumbs/";

	foreach($defaultDirectories as $dir)
	{
		if(!is_dir($dir))
		{
			mkdir($dir);
		}
	}
	
	$default_thumbs_source_dir = dirname(__FILE__) . "/../../view/components/fileeditor/images/default_thumbs/";
	$default_thumbs_target_dir = dirname(__FILE__) . "/../../../upload/files/system/";
	
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
					  `date_format` varchar(25) NOT NULL DEFAULT '%d %M %y',
					  `status` tinyint(2) NOT NULL DEFAULT '-1',
					  PRIMARY KEY (`locale`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					
					INSERT INTO `{$prefix}language` (`locale`, `language_name`, `language_abbr`, `country_name`, `country_abbr`, `date_format`, `status`) VALUES
					('ar_AE', 'Arabic', 'ar', 'United Arab Emirates', 'AE', '%d %M %y', -1),
					('ar_BH', 'Arabic', 'ar', 'Bahrain', 'BH', '%d %M %y', -1),
					('ar_DZ', 'Arabic', 'ar', 'Algeria', 'DZ', '%d %M %y', -1),
					('ar_EG', 'Arabic', 'ar', 'Egypt', 'EG', '%d %M %y', -1),
					('ar_IN', 'Arabic', 'ar', 'India', 'IN', '%d %M %y', -1),
					('ar_IQ', 'Arabic', 'ar', 'Iraq', 'IQ', '%d %M %y', -1),
					('ar_JO', 'Arabic', 'ar', 'Jordan', 'JO', '%d %M %y', -1),
					('ar_KW', 'Arabic', 'ar', 'Kuwait', 'KW', '%d %M %y', -1),
					('ar_LB', 'Arabic', 'ar', 'Lebanon', 'LB', '%d %M %y', -1),
					('ar_LY', 'Arabic', 'ar', 'Libya', 'LY', '%d %M %y', -1),
					('ar_MA', 'Arabic', 'ar', 'Morocco', 'MA', '%d %M %y', -1),
					('ar_OM', 'Arabic', 'ar', 'Oman', 'OM', '%d %M %y', -1),
					('ar_QA', 'Arabic', 'ar', 'Qatar', 'QA', '%d %M %y', -1),
					('ar_SA', 'Arabic', 'ar', 'Saudi Arabia', 'SA', '%d %M %y', -1),
					('ar_SD', 'Arabic', 'ar', 'Sudan', 'SD', '%d %M %y', -1),
					('ar_SY', 'Arabic', 'ar', 'Syria', 'SY', '%d %M %y', -1),
					('ar_TN', 'Arabic', 'ar', 'Tunisia', 'TN', '%d %M %y', -1),
					('ar_YE', 'Arabic', 'ar', 'Yemen', 'YE', '%d %M %y', -1),
					('be_BY', 'Belarusian', 'be', 'Belarus', 'BY', '%d %M %y', -1),
					('bg_BG', 'Bulgarian', 'bg', 'Bulgaria', 'BG', '%d %M %y', -1),
					('ca_ES', 'Catalan', 'ca', 'Spain', 'ES', '%d %M %y', -1),
					('cs_CZ', 'Czech', 'cs', 'Czech Republic', 'CZ', '%d %M %y', -1),
					('da_DK', 'Danish', 'da', 'Denmark', 'DK', '%d %M %y', -1),
					('de_AT', 'German', 'de', 'Austria', 'AT', '%d %M %y', -1),
					('de_BE', 'German', 'de', 'Belgium', 'BE', '%d %M %y', -1),
					('de_CH', 'German', 'de', 'Switzerland', 'CH', '%d %M %y', -1),
					('de_DE', 'German', 'de', 'Germany', 'DE', '%d %M %y', -1),
					('de_LU', 'German', 'de', 'Luxembourg', 'LU', '%d %M %y', -1),
					('en_AU', 'English', 'en', 'Australia', 'AU', '%d %M %y', -1),
					('en_CA', 'English', 'en', 'Canada', 'CA', '%d %M %y', -1),
					('en_GB', 'English', 'en', 'United Kingdom', 'GB', '%d %M %y', -1),
					('en_IN', 'English', 'en', 'India', 'IN', '%d %M %y', -1),
					('en_NZ', 'English', 'en', 'New Zealand', 'NZ', '%d %M %y', -1),
					('en_PH', 'English', 'en', 'Philippines', 'PH', '%d %M %y', -1),
					('en_US', 'English', 'en', 'United States', 'US', '%d %M %y', 1),
					('en_ZA', 'English', 'en', 'South Africa', 'ZA', '%d %M %y', -1),
					('en_ZW', 'English', 'en', 'Zimbabwe', 'ZW', '%d %M %y', -1),
					('es_AR', 'Spanish', 'es', 'Argentina', 'AR', '%d %M %y', -1),
					('es_BO', 'Spanish', 'es', 'Bolivia', 'BO', '%d %M %y', -1),
					('es_CL', 'Spanish', 'es', 'Chile', 'CL', '%d %M %y', -1),
					('es_CO', 'Spanish', 'es', 'Columbia', 'CO', '%d %M %y', -1),
					('es_CR', 'Spanish', 'es', 'Costa Rica', 'CR', '%d %M %y', -1),
					('es_DO', 'Spanish', 'es', 'Dominican Republic', 'DO', '%d %M %y', -1),
					('es_EC', 'Spanish', 'es', 'Ecuador', 'EC', '%d %M %y', -1),
					('es_ES', 'Spanish', 'es', 'Spain', 'ES', '%d %M %y', -1),
					('es_GT', 'Spanish', 'es', 'Guatemala', 'GT', '%d %M %y', -1),
					('es_HN', 'Spanish', 'es', 'Honduras', 'HN', '%d %M %y', -1),
					('es_MX', 'Spanish', 'es', 'Mexico', 'MX', '%d %M %y', -1),
					('es_NI', 'Spanish', 'es', 'Nicaragua', 'NI', '%d %M %y', -1),
					('es_PA', 'Spanish', 'es', 'Panama', 'PA', '%d %M %y', -1),
					('es_PE', 'Spanish', 'es', 'Peru', 'PE', '%d %M %y', -1),
					('es_PR', 'Spanish', 'es', 'Puerto Rico', 'PR', '%d %M %y', -1),
					('es_PY', 'Spanish', 'es', 'Paraguay', 'PY', '%d %M %y', -1),
					('es_SV', 'Spanish', 'es', 'El Salvador', 'SV', '%d %M %y', -1),
					('es_US', 'Spanish', 'es', 'United States', 'US', '%d %M %y', -1),
					('es_UY', 'Spanish', 'es', 'Uruguay', 'UY', '%d %M %y', -1),
					('es_VE', 'Spanish', 'es', 'Venezuela', 'VE', '%d %M %y', -1),
					('et_EE', 'Estonian', 'et', 'Estonia', 'EE', '%d %M %y', -1),
					('eu_ES', 'Basque', 'eu', 'Basque', 'ES', '%d %M %y', -1),
					('fi_FI', 'Finnish', 'fi', 'Finland', 'FI', '%d %M %y', -1),
					('fo_FO', 'Faroese', 'fo', 'Faroe Islands', 'FO', '%d %M %y', -1),
					('fr_BE', 'French', 'fr', 'Belgium', 'BE', '%d %M %y', -1),
					('fr_CA', 'French', 'fr', 'Canada', 'CA', '%d %M %y', -1),
					('fr_CH', 'French', 'fr', 'Switzerland', 'CH', '%d %M %y', -1),
					('fr_FR', 'French', 'fr', 'France', 'FR', '%d %M %y', -1),
					('fr_LU', 'French', 'fr', 'Luxembourg', 'LU', '%d %M %y', -1),
					('gl_ES', 'Galician', 'gl', 'Spain', 'ES', '%d %M %y', -1),
					('gu_IN', 'Gujarati', 'gu', 'India', 'IN', '%d %M %y', -1),
					('he_IL', 'Hebrew', 'he', 'Israel', 'IL', '%d %M %y', -1),
					('hi_IN', 'Hindi', 'hi', 'India', 'IN', '%d %M %y', -1),
					('hr_HR', 'Croatian', 'hr', 'Croatia', 'HR', '%d %M %y', -1),
					('hu_HU', 'Hungarian', 'hu', 'Hungary', 'HU', '%d %M %y', -1),
					('id_ID', 'Indonesian', 'id', 'Indonesia', 'ID', '%d %M %y', -1),
					('is_IS', 'Icelandic', 'is', 'Iceland', 'IS', '%d %M %y', -1),
					('it_CH', 'Italian', 'it', 'Switzerland', 'CH', '%d %M %y', -1),
					('it_IT', 'Italian', 'it', 'Italy', 'IT', '%d %M %y', -1),
					('ja_JP', 'Japanese', 'ja', 'Japan', 'JP', '%d %M %y', -1),
					('ko_KR', 'Korean', 'ko', 'Republic of Korea', 'KR', '%d %M %y', -1),
					('lt_LT', 'Lithuanian', 'lt', 'Lithuania', 'LT', '%d %M %y', -1),
					('lv_LV', 'Latvian', 'lv', 'Latvia', 'LV', '%d %M %y', -1),
					('mk_MK', 'Macedonian', 'mk', 'FYROM', 'MK', '%d %M %y', -1),
					('mn_MN', 'Mongolia', 'mn', 'Mongolian', 'MN', '%d %M %y', -1),
					('ms_MY', 'Malay', 'ms', 'Malaysia', 'MY', '%d %M %y', -1),
					('nb_NO', 'Norwegian(Bokmål)', 'nb', 'Norway', 'NO', '%d %M %y', -1),
					('nl_BE', 'Dutch', 'nl', 'Belgium', 'BE', '%d %M %y', -1),
					('nl_NL', 'Dutch', 'nl', 'The Netherlands', 'NL', '%d %M %y', -1),
					('no_NO', 'Norwegian', 'no', 'Norway', 'NO', '%d %M %y', -1),
					('pl_PL', 'Polish', 'pl', 'Poland', 'PL', '%d %M %y', -1),
					('pt_BR', 'Portugese', 'pt', 'Brazil', 'BR', '%d %M %y', -1),
					('pt_PT', 'Portugese', 'pt', 'Portugal', 'PT', '%d %M %y', -1),
					('ro_RO', 'Romanian', 'ro', 'Romania', 'RO', '%d %M %y', -1),
					('ru_RU', 'Russian', 'ru', 'Russia', 'RU', '%d %M %y', -1),
					('ru_UA', 'Russian', 'ru', 'Ukraine', 'UA', '%d %M %y', -1),
					('sk_SK', 'Slovak', 'sk', 'Slovakia', 'SK', '%d %M %y', -1),
					('sl_SI', 'Slovenian', 'sl', 'Slovenia', 'SI', '%d %M %y', -1),
					('sq_AL', 'Albanian', 'sq', 'Albania', 'AL', '%d %M %y', -1),
					('sr_YU', 'Serbian', 'sr', 'Yugoslavia', 'YU', '%d %M %y', -1),
					('sv_FI', 'Swedish', 'sv', 'Finland', 'FI', '%d %M %y', -1),
					('sv_SE', 'Swedish', 'sv', 'Sweden', 'SE', '%d %M %y', -1),
					('ta_IN', 'Tamil', 'ta', 'India', 'IN', '%d %M %y', -1),
					('te_IN', 'Telugu', 'te', 'India', 'IN', '%d %M %y', -1),
					('th_TH', 'Thai', 'th', 'Thailand', 'TH', '%d %M %y', -1),
					('tr_TR', 'Türkçe', 'tr', 'Turkey', 'TR', '%d %M %y', 10),
					('uk_UA', 'Ukrainian', 'uk', 'Ukraine', 'UA', '%d %M %y', -1),
					('ur_PK', 'Urdu', 'ur', 'Pakistan', 'PK', '%d %M %y', -1),
					('vi_VN', 'Vietnamese', 'vi', 'Viet Nam', 'VN', '%d %M %y', -1),
					('zh_CN', 'Chinese', 'zh', 'China', 'CN', '%d %M %y', -1),
					('zh_HK', 'Chinese', 'zh', 'Hong Kong', 'HK', '%d %M %y', -1);";
	
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
					  `directory_id` int(11) NOT NULL DEFAULT '-1',
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
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=34;
					
					--
					-- Tablo döküm verisi `pa_file`
					--
					
					INSERT INTO `{$prefix}file` (`file_id`, `basename`, `filename`, `directory_id`, `url`, `type`, `extension`, `size`, `creation_time`, `last_update_time`, `width`, `height`, `thumb_file_id`, `copied_file_id`, `access_type`) VALUES
					(1, 'aac.png', 'aac', -1, 'aac.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(2, 'ai.png', 'ai', -1, 'ai.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(3, 'aiff.png', 'aiff', -1, 'aiff.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(4, 'avi.png', 'avi', -1, 'avi.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(5, 'css.png', 'css', -1, 'css.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(6, 'doc.png', 'doc', -1, 'doc.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(7, 'docx.png', 'docx', -1, 'docx.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(8, 'generic.png', 'generic', -1, 'generic.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(9, 'gzip.png', 'gzip', -1, 'gzip.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(10, 'html.png', 'html', -1, 'html.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(11, 'js.png', 'js', -1, 'js.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(12, 'm4a.png', 'm4a', -1, 'm4a.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(13, 'm4v.png', 'm4v', -1, 'm4v.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(14, 'mov.png', 'mov', -1, 'mov.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(15, 'mp3.png', 'mp3', -1, 'mp3.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(16, 'mp4.png', 'mp4', -1, 'mp4.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(17, 'mpeg2.png', 'mpeg2', -1, 'mpeg2.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(18, 'mpg.png', 'mpg', -1, 'mpg.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(19, 'pdf.png', 'pdf', -1, 'pdf.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(20, 'php.png', 'php', -1, 'php.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(21, 'psd.png', 'psd', -1, 'psd.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(22, 'raw.png', 'raw', -1, 'raw.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(23, 'rtf.png', 'rtf', -1, 'rtf.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(24, 'tar.png', 'tar', -1, 'tar.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(25, 'tiff.png', 'tiff', -1, 'tiff.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(26, 'txt.png', 'txt', -1, 'txt.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(27, 'wav.png', 'wav', -1, 'wav.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(28, 'wmv.png', 'wmv', -1, 'wmv.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(29, 'zip.png', 'zip', -1, 'zip.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(30, 'flv.png', 'flv', -1, 'flv.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(31, 'f4v.png', 'f4v', -1, 'f4v.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system'),
					(32, 'folder.png', 'folder', -1, 'folder.png', 'image', 'png', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 58, 51, -1, -1, 'system'),
					(33, 'exclamation.jpg', 'exclamation', -1, 'exclamation.jpg', 'image', 'jpg', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 123, 87, -1, -1, 'system');";
	
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
			  `crop_position` char(20) NOT NULL DEFAULT 'center_top',
			  `crop_left` smallint(5) NOT NULL,
              `crop_top` smallint(5) NOT NULL,
              `crop_width` smallint(5) NOT NULL,
              `crop_height` smallint(5) NOT NULL,
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
				  `permission_key` varchar(50) NOT NULL,
				  PRIMARY KEY (`group_id`,`permission_key`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
	
	$queryPermission = "CREATE TABLE IF NOT EXISTS `{$prefix}permission` (
				  `permission_key` varchar(50) COLLATE utf8_bin NOT NULL,
  				  `permission_parent` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '',
				  `permission_name` varchar(100) COLLATE utf8_bin NOT NULL,
				  `order_num` int(11) NOT NULL,
				  PRIMARY KEY (`permission_key`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;";
	
	$queryRole = "CREATE TABLE IF NOT EXISTS `{$prefix}role` (
				  `role_id` int(11) NOT NULL AUTO_INCREMENT,
				  `role_name` varchar(100) COLLATE utf8_bin DEFAULT NULL,
				  `order_num` int(11) NOT NULL,
				  PRIMARY KEY (`role_id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;
	
				INSERT INTO `{$prefix}role` (`role_id`, `role_name`, `order_num`) VALUES
				(1, 'Yönetici', 0),
				(2, 'Sınırlı Yönetici', 0);";
	
	$queryRolePermission = "CREATE TABLE IF NOT EXISTS `{$prefix}role_permission` (
				  `role_id` int(11) NOT NULL,
				  `permission_key` varchar(50) NOT NULL,
				  PRIMARY KEY (`role_id`,`permission_key`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
	
				INSERT INTO `{$prefix}role_permission` (`role_id`, `permission_key`) VALUES
				(1, 'ADMIN_ADMINPANEL'),
				(1, 'ADMIN_add_permission'),
				(1, 'ADMIN_add_role'),
				(1, 'ADMIN_add_sitemap_page'),
				(1, 'ADMIN_add_user'),
				(1, 'ADMIN_dashboard'),
				(1, 'ADMIN_developers'),
				(1, 'ADMIN_edit_permission'),
				(1, 'ADMIN_edit_role'),
				(1, 'ADMIN_edit_sitemap_page'),
				(1, 'ADMIN_edit_useraccount'),
				(1, 'ADMIN_invite_user'),
				(1, 'ADMIN_messages'),
				(1, 'ADMIN_modules'),
				(1, 'ADMIN_permissions'),
				(1, 'ADMIN_profile'),
				(1, 'ADMIN_readmessage'),
				(1, 'ADMIN_roles'),
				(1, 'ADMIN_settings'),
				(1, 'ADMIN_sitemap'),
				(1, 'ADMIN_useraccounts'),
				(2, 'ADMIN_ADMINPANEL'),
				(2, 'ADMIN_add_sitemap_page'),
				(2, 'ADMIN_dashboard'),
				(2, 'ADMIN_developers'),
				(2, 'ADMIN_edit_sitemap_page'),
				(2, 'ADMIN_messages'),
				(2, 'ADMIN_modules'),
				(2, 'ADMIN_profile'),
				(2, 'ADMIN_readmessage'),
				(2, 'ADMIN_settings'),
				(2, 'ADMIN_sitemap');";
	
	
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
				  `page_image` int(11) DEFAULT '-1',
				  `page_url` varchar(255) COLLATE utf8_bin DEFAULT NULL,
				  `page_title` varchar(25) COLLATE utf8_bin DEFAULT NULL,
				  `page_description` varchar(25) COLLATE utf8_bin DEFAULT NULL,
				  `changefreq` varchar(20) COLLATE utf8_bin DEFAULT 'monthly',
				  `priority` double DEFAULT '0.5',
				  `modified_date` date DEFAULT NULL,
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

