<?php

$data = $ADMIN->ROLE->listRoles();

dataGrid($data, "Roller", "rolesList", "{%role_name%}", "admin.php?page=add_role", "admin.php?page=edit_role&id={%role_id%}", "admin.php?page=roles&delete={%role_id%}");