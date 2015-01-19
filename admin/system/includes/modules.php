<?php

function moduleActivationCode($filedir,$function)
{
	$directory = dirname($filedir);
	$directory = str_replace("\\","/",$directory);
	$lastChar = substr($directory,-1);
	$directory .= $lastChar == "/" ? "" : "/";
	
	global $register_module_function;
	$register_module_function = $function;	
}

function executeActivationCode($directory)
{
	global $register_module_function;
	global $modules_main_file_name;
    global $pa_menu_array;
    global $ADMIN;

	$moduleFileUrl = $directory . $modules_main_file_name;

	if(file_exists($moduleFileUrl)){

        $old_pa_menu_array = $pa_menu_array;
		require_once $moduleFileUrl;
        $new_pa_menu_array = $pa_menu_array;

        // Modül eklenmeden önce ve eklendikten sonraki menu keyleri karşılaştırıp yeni eklenen permission keyleri bul
        $module_menus = array_diff_assoc($new_pa_menu_array, $old_pa_menu_array);

        // Tüm menu, submenu ve subpage keylerini al
        $keys = array();

        foreach($module_menus as $key=>$menu){
            $keys[] = "ADMIN_" . $key;

            if(is_array($menu["subMenus"]) && sizeof($menu["subMenus"]) > 0){
                foreach($menu["subMenus"] as $index=>$sm){
                    $keys[] = "ADMIN_" . key($sm);
                }
            }

            if(is_array($menu["subPages"]) && sizeof($menu["subPages"]) > 0){
                foreach($menu["subPages"] as $spKey=>$sp){
                    $keys[] = "ADMIN_" . $spKey;
                }
            }
        }

        // Yeni eklenen permission keyleri "Yönetici" rolüne ekle
        foreach($keys as $key){
            $ADMIN->ROLE_PERMISSION->addRolePermission(1, $key);
        }

		call_user_func($register_module_function);
	}
}
$active_modules = get_option("admin_active_modules");
$active_modules = (substr($active_modules,-1) == ',') ? substr($active_modules,0,-1) : $active_modules;

$modulesArray = explode(',',$active_modules);
global $modules_main_file_name;

foreach($modulesArray as $m) {
	$moduleFileUrl = dirname(__FILE__) . "/../../" . $m . $modules_main_file_name;

	if(file_exists($moduleFileUrl)) {
		require_once $moduleFileUrl;
	}
}
