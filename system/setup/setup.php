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

if($_POST["action"]=="checkForDB")
{
	if($dbh = connectToDB($_POST["dbname"],$_POST["dbhost"],$_POST["dbuser"],$_POST["dbpass"]))
		generateAdminConfigurationFile($dbh);
	else
		$errorMessage = "* bağlantı kurulamadı!";
}

$html = file_get_contents(dirname(__FILE__) . "/database.html");
$html = str_ireplace('<%errorText%>', $errorMessage, $html);
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
		
	$configfile = str_ireplace('<%dbname%>', $_POST["dbname"], $configfile);
	$configfile = str_ireplace('<%dbuser%>', $_POST["dbuser"], $configfile);
	$configfile = str_ireplace('<%dbpass%>', $_POST["dbpass"], $configfile);
	$configfile = str_ireplace('<%dbhost%>', $_POST["dbhost"], $configfile);
	$configfile = str_ireplace('<%prefix%>', $_POST["prefix"], $configfile);
	$configfile = str_ireplace('<%securekey%>', randomString(32), $configfile);
	$configfile = str_ireplace('<%sessionKeysPrefix%>', uniqid("SES") . "_", $configfile);
	
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
	$defaultDirectories = array();
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
					  `abbreviation` char(2) NOT NULL,
					  `name` char(255) DEFAULT NULL,
					  `status` tinyint(1) DEFAULT '1',
					  PRIMARY KEY (`abbreviation`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	
	$queryOption = "CREATE TABLE IF NOT EXISTS `{$prefix}option` (
					  `optionName` char(255) NOT NULL,
					  `groupName` char(255) NOT NULL,
					  `optionValue` text NOT NULL,
					  PRIMARY KEY (`optionName`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	
	$queryUser = "CREATE TABLE IF NOT EXISTS `{$prefix}user` (
					  `user_id` int(11) NOT NULL AUTO_INCREMENT,
					  `image_id` int(11) NOT NULL,
					  `username` varchar(100) NOT NULL,
					  `displayname` varchar(100) NOT NULL,
					  `birthday` date NOT NULL,
					  `password` varchar(100) NOT NULL,
					  `pass_key` varchar(30) NOT NULL,
					  `email` varchar(100) NOT NULL,
					  `user_type` varchar(20) NOT NULL,
					  `register_date` datetime NOT NULL,
					  `captcha_limit` tinyint(1) NOT NULL DEFAULT '3',
					  `status` varchar(50) NOT NULL DEFAULT 'active',
					  PRIMARY KEY (`user_id`)
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
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;";
	
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
			$dbh->query($queryGalleryFile);
}

