<?php

if($_POST["admin_action"] == "checkIfRoleKeyAvailable"){
    $isAvailable = true;

    if($role = $ADMIN->ROLE->selectRoleByRoleKey($_POST["role_key"])){
        if($role->role_id != $_POST["role_id"]){
            $isAvailable = false;
        }
    }

    echo $isAvailable ? "available" : "not_available";
    exit;
}