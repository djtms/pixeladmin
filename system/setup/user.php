<?php require_once dirname(__FILE__) . '/../../includes.php';

if($ADMIN->USER->getUserCount() <= 0)
{
	$errorMessage = "";
	
	if(basename($_SERVER["SCRIPT_FILENAME"],".php") != "user")
	{
		header("Location:system/setup/user.php");
		exit;
	}
	
	if($_POST["admin_action"] == "createFirstUser")
	{
		// Setup Default Options ////////////////////////////////////////////////////////////////////////////////////////
		set_option("admin_site_title", $_POST["siteTitle"],"pa_settings");
		setI18n("admin_site_titleI18N", $_POST["siteTitle"]);
		setI18n("admin_descriptionI18N", $_POST["admin_descriptionI18N"]);
		set_option("admin_mail_user", $_POST["email"],"pa_settings");
		set_option("admin_get_mail_address", $_POST["email"],"pa_settings");
		set_option("admin_mail_port", "587","pa_settings");
		set_option("admin_site_address", "http://" . $_SERVER["HTTP_HOST"] . preg_replace("/admin(\/|\\\)system(\/|\\\).*/i", "/", $_SERVER["REQUEST_URI"]),"pa_settings");
		set_option("admin_display_mode","maintanance");
		set_option("admin_debug_mode","debugmode");
		set_option("admin_predefined_crop_resolutions", array(array(1024,768),array(800,600),array(640,480)));
		/**************************************************************************************************************/
		
		
		
		// Setup Default Language ////////////////////////////////////////////////////////////////////////////////////////
		$ADMIN->LANGUAGE->addLanguage("tr_TR");
		$ADMIN->LANGUAGE->addLanguage("en_US");
		$ADMIN->LANGUAGE->setDefaultLanguage("tr_TR");
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		$use_template_engine = ($_POST["use_template_engine"] == "use") ? true : false;
		// İlk kullanıcıyı oluştur
		$user_id = $ADMIN->USER->addUser($_POST["username"], $_POST["username"], $_POST["email"], $_POST["password"]);
		// Kullanıcıya id'si "1" olan rolü ver. (Yönetici Rolüdür ve Panelden Silinemez)
		$ADMIN->USER_ROLE->addUserRole($user_id, 1);
		if(!($user_id > 0))
		{
			$errorMessage = "* kullanıcı oluşturma esnasında hata oluştu!";
		}
		else if(!createStartupFiles($use_template_engine))
		{
			$errorMessage = "* bazı dosyaların kurulumu esnasında hata oluştu!";
		}
		else
		{
			// Kullanıcı başarılı şekilde kayıt olduktan sonra kullanıcıyı giriş yapmış hale getiriyoruz
			$ADMIN->AUTHENTICATION->authenticate($_POST["username"], $_POST["password"]);
			$ADMIN->AUTHORIZATION->authorize();
			add_log("giriş yaptı");
			postMessage("Tebrikler! Kurulum işleminiz başarıyla gerçekleştirildi!");
			header("Location:../../admin.php?page=dashboard");
			exit;
		}
	}

	$html = file_get_contents(dirname(__FILE__) . "/user.html");
	$html = str_ireplace('{%errorText%}', $errorMessage, $html);
	echo $html;
}

function createStartupFiles($use_template_engine = true)
{
	$error = false;
	
	$sourceFilesMainDir = dirname(__FILE__) . ($use_template_engine ? "/tmp_eng_startup_files/" : "/normal_startup_files/");
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
