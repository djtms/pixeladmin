<?php require_once dirname(__FILE__) . '/../../includes.php';

if($MODEL->USER->getUserCount() <= 0)
{
	$errorMessage = "";
	
	if(basename($_SERVER["SCRIPT_FILENAME"],".php") != "user")
		header("Location:system/setup/user.php");
	
	if($_POST["action"] == "createSuperUser")
	{
		// Setup Default Options ////////////////////////////////////////////////////////////////////////////////////////
		set_option("siteTitle", $_POST["siteTitle"],"pa_settings");
		set_option("mailUser", $_POST["email"],"pa_settings");
		set_option("getMailAddress", $_POST["email"],"pa_settings");
		set_option("mailPort", "587","pa_settings");
		set_option("siteAddress", "http://" . $_SERVER["SERVER_NAME"],"pa_settings");
		set_option("SiteDisplayMode","maintanance");
		set_option("SiteDebugMode","debugmode");
		/**************************************************************************************************************/
		
		// Setup Default Language ////////////////////////////////////////////////////////////////////////////////////////
		$MODEL->LANGUAGE->addLanguage("tr_TR");
		$MODEL->LANGUAGE->setDefaultLanguage("tr_TR");
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		$use_mvc = ($_POST["use_mvc"] == "use") ? true : false;
		
		if(!$MODEL->USER->createFirstAdminUser($_POST["username"], $_POST["username"], $_POST["email"], $_POST["password"]))
		{
			$errorMessage = "* kullanıcı oluşturma esnasında hata oluştu!";
		}
		else if(!createStartupFiles($use_mvc))
		{
			$errorMessage = "* bazı dosyaların kurulumu esnasında hata oluştu!";
		}
		else
		{
			header("Location:../../login.php");
			exit;
		}
	}
	
	$html = file_get_contents(dirname(__FILE__) . "/user.html");
	$html = str_ireplace('<%errorText%>', $errorMessage, $html);
	echo $html;
}

function createStartupFiles($use_mvc = true)
{
	$error = false;
	
	$sourceFilesMainDir = dirname(__FILE__) . ($use_mvc ? "/mvc_startup_files/" : "/normal_startup_files/");
	$targetBaseDir = dirname(__FILE__) . "/../../../";
	$sourceFilesList = array();
	$baseDir = $sourceFilesMainDir;
	calculateFilesAndDirs($sourceFilesMainDir,$sourceFilesList,$baseDir);
	
	foreach($sourceFilesList as $d)
	{
		$targetUrl = $targetBaseDir. $d->path;
		
		if(is_dir($d->fullpath))
		{
			if(!is_dir($targetUrl) && !mkdir($targetUrl,0777))
				$error = true;
		}
		else if(!file_exists($targetUrl))
		{
			if(!copy($d->fullpath, $targetUrl))
				$error = true;
		}
	}
	
	return !$error;
}

function calculateFilesAndDirs($dir,&$storage_array,$baseDir)
{
	foreach(scandir($dir) as $d)
	{
		if(($d != ".") && ($d != ".."))
		{
			$path = str_replace($baseDir, "", $dir . $d);
			if(is_dir($dir.$d))
			{
				$storage_array[] = (object) array("type"=>"dir","path"=>$path."/","fullpath"=>$baseDir.$path,"name"=>basename($d));
				calculateFilesAndDirs($dir."$d/",$storage_array,$baseDir);
			}
			else
				$storage_array[] = (object) array("type"=>"file","path"=>$path,"fullpath"=>$baseDir.$path,"name"=>basename($d));
		}
	}
}
