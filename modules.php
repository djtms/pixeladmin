<?php	require_once 'includes.php';
$existingModules = array();
$modulesListHtml = "";

if(isset($_GET["admin_action"]))
{
	$moduleFolder = $_GET["moduleFolder"];
	$activeModules = get_option("admin_active_modules");
	
	switch($_GET["admin_action"])
	{
		case("activateModule"):
			if(!preg_match("/" . preg_quote($moduleFolder,"/") . "/",$activeModules))
			{
				$moduleFileUrl = $moduleFolder . $modules_main_file_name;
				
				if(file_exists($moduleFileUrl))
				{
					require_once "$moduleFileUrl";
					global $register_module_function;
					$activation_result = call_user_func($register_module_function);
					if($activation_result !== false)
					{
						$activeModules .= $moduleFolder . ',';
						set_option("admin_active_modules",$activeModules);
						executeActivationCode(urldecode($moduleFolder));
						postMessage("Modül Başarıyla Aktifleştirildi!");
					}
					else if($activation_result === false){
						postMessage("Hata Oluştu!", true);
					}
				}
				else
				{
					postMessage("Modül ana dosyası bulunamadı!",true);
				}
				

				header("Location:admin.php?page=modules");
				exit;
			}
		break;
		
		case("deactivateModules"):
			$activeModules = str_replace("{$moduleFolder},","",$activeModules);
			set_option("admin_active_modules",$activeModules);
			header("Location:admin.php?page=modules");
		break;
	}
}

getModulesInFolder();

function getModulesInFolder()
{
	global $existingModules;
	global $modules_main_file_name;
	global $trCharsForRegExp;
	$modulesDir = "./modules/";
	$modulesFolder = scandir($modulesDir);
	$modulesSubFolders = array();
	$moduleName = "";
	

	foreach($modulesFolder as $mf)
	{
		$folderDir = $modulesDir . $mf . "/";
		if(($mf != ".") && ($mf!="..") && is_dir($folderDir))
		{
			$modulesSubFolders[] = $folderDir;
		}
	}
	
	if(sizeof($modulesSubFolders) > 0)
	{
		foreach($modulesSubFolders as $msf)
		{
			$moduleFileUrl = $msf . $modules_main_file_name;
			
			if(file_exists($moduleFileUrl))
			{
				$fileString = file_get_contents($moduleFileUrl);
				
				if(preg_match(("/Module Name: [a-z0-9\_\-\s" . $trCharsForRegExp . "]+/i"),$fileString,$match))
				{
					$moduleName = preg_replace("/Module Name:/i","",$match[0]);
					$existingModules[] = array("moduleFolder"=>$msf,"moduleName"=>$moduleName);
				}
			}
		}
	}
}

if(sizeof($existingModules) > 0)
{
	$active_modules = get_option("admin_active_modules");
	$active_modules = (substr($active_modules,-1) == ',') ? substr($active_modules,0,-1) : $active_modules;
	
	$modulesArray = explode(',',$active_modules);
	
	foreach($existingModules as $em)
	{
		$moduleFolder = $em["moduleFolder"];
		$isActive = in_array($moduleFolder,$modulesArray);
		$activateTitle = $isActive ? "Pasifleştir" : "Etkinleştir";
		$activeAction = $isActive ? "deactivateModules" : "activateModule";
		$className = $isActive ? "deactivation" : "activation";
		$moduleFolder = urlencode($em["moduleFolder"]);
		
		$modulesListHtml .= sprintf('<li><div class="item"><p class="text">%s</p><div class="rowEditButtonsOuter">
											<a href="admin.php?page=modules&admin_action=%s&moduleFolder=%s" class="%s">%s</a>
										</div></div></li>',$em["moduleName"],$activeAction,$moduleFolder,$className,$activateTitle,$moduleFolder);
	}
}
else
	$modulesListHtml = '<li><div class="item"><p class="text" style="color:#fc5900 !important;">Hiçbir Modül Bulunamadı!</p></li>';
	


$modules->modulesList = $modulesListHtml;
$modules->render();
