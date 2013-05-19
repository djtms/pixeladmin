<?php	require_once 'includes.php';
$existingModules = array();
$modulesListHtml = "";

if(isset($_GET["admin_action"]))
{
	$moduleDir = $_GET["moduleFolder"];
	$activeModules = get_option("admin_active_modules");

	switch($_GET["admin_action"])
	{
		case("activateModule"):
			if(!preg_match("/" . preg_quote($moduleDir,"/") . "/",$activeModules))
			{
				$moduleMainFilePath = $moduleDir . $modules_main_file_name;

				if(file_exists($moduleMainFilePath))
				{
					require_once "$moduleMainFilePath";
					global $register_module_function;
					$activation_result = call_user_func($register_module_function);
					if($activation_result !== false)
					{
						$activeModules .= $moduleDir . ',';
						set_option("admin_active_modules",$activeModules);
						executeActivationCode(urldecode($moduleDir));
						postMessage("Modül Başarıyla Etkinleştirildi!");
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
			$activeModules = str_replace("{$moduleDir},","",$activeModules);
			set_option("admin_active_modules",$activeModules);
			header("Location:admin.php?page=modules");
		break;
	}
}


// Modülleri listele -----------------------------------------------------------------------------------------
$modulesRootPath = "./modules/";
$existingModuleAmount = 0;
$active_modules = get_option("admin_active_modules");
$active_modules = (substr($active_modules,-1) == ',') ? substr($active_modules,0,-1) : $active_modules;
$activeModulesList = explode(',',$active_modules);
$modulesList = scandir($modulesRootPath);

foreach($modulesList as $module){
    $moduleDir = $modulesRootPath . $module . "/";
    if(($module != ".") && ($module!="..") && is_dir($moduleDir)){

        $moduleMainFilePath = $moduleDir . $modules_main_file_name;

        if(file_exists($moduleMainFilePath)){
            $fileContents = file_get_contents($moduleMainFilePath);

            if(preg_match(("/Module Name: [a-z0-9\_\-\s" . $trCharsForRegExp . "]+/i"),$fileContents,$match)){
                $existingModuleAmount++;

                $moduleName = preg_replace("/Module Name:/i","",$match[0]);

                if(in_array($moduleDir,$activeModulesList)){
                    $activateTitle = "Pasifleştir";
                    $activeAction = "deactivateModules";
                    $className = "deactivation";
                }
                else{
                    $activateTitle = "Etkinleştir";
                    $activeAction = "activateModule";
                    $className = "activation";
                }

                $modulesListHtml .= sprintf('<li><div class="item"><p class="text">%s</p><div class="rowEditButtonsOuter">
											<a href="admin.php?page=modules&admin_action=%s&moduleFolder=%s" class="%s">%s</a>
										</div></div></li>',$moduleName,$activeAction,urlencode($moduleDir),$className,$activateTitle,$moduleDir);
            }
        }
    }
}

if($existingModuleAmount <= 0){
	$modulesListHtml = '<li><div class="item"><p class="text" style="color:#fc5900 !important;">Hiçbir Modül Bulunamadı!</p></li>';
}
//----------------------------------------------------------------------------------------------------------------


$modules->modulesList = $modulesListHtml;
$modules->render();
