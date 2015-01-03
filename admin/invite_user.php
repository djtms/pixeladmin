<?php

extract($_POST, EXTR_SKIP);

if ($admin_action == "inviteUser") {
    $data = new \com\admin\system\objects\UserObject();
    $data->displayname = $displayname;
    $data->email = $email;

    if ($user = $ADMIN->USER->getUserByEmail($email)) {
        postMessage($GT->MAIL_ADRESI_KULLANIMDA, true);
    } else if ($ADMIN->USER->inviteUser($data, $_POST["user_roles"])) {
        postMessage($GT->DAVETIYENIZ_BASARIYLA_GONDERILDI);
        header("Location:admin.php?page=useraccounts");
        exit;
    } else {
        postMessage($GT->HATA_OLUSTU, true);
    }
} else if ($admin_action == "checkUserStatusByEmail") {
    if ($user = $ADMIN->USER->getUserByEmail($email)) {
        if ($user->status == "active") {
            echo "already_registered";
        } else if ($user->status == "invited") {
            echo "not_activated_account";
        }
    } else {
        echo "not_exist";
    }

    exit;
} else if ($admin_action == "resendinvitationmail") {
    if ($ADMIN->USER->reSendInvitationMail($email))
        echo "succeed";
    else
        echo "error";

    exit;
}

$roles = $ADMIN->ROLE->listRoles();
$invite_user_roles_html = dataGrid($roles, "", "userRolesList", "<input type='checkbox' name='user_roles[]' value='{%role_id%}' /> {%role_name%}", null, null, null);


addScript("js/pages/invite_user.js");
$invite_user->render();