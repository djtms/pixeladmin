<?php

$minPhpVersion = "5.0.0";

// date() fonksiyonundaki hata mesajlarini gidermek icin default timezone degerini ata
date_default_timezone_set('Europe/Istanbul');

require_once 'system/includes/init.php';

if (in_admin){
    require_once "language/tr_TR.php";
}

if (version_compare(PHP_VERSION, $minPhpVersion, "<")) {
    echo sprintf($GT->UYARI_MINIMUM_PHP_KULLANIN, $minPhpVersion);
    exit;
}


if (!file_exists(dirname(__FILE__) . "/config.php")) {
    require_once dirname(__FILE__) . '/system/setup/setup.php';
} else {
    require_once dirname(__FILE__) . "/config.php";
}
require_once "system/library/JSON.php";
require_once "system/library/MHA.php";
require_once "system/library/recaptchalib.php";
require_once "system/includes/constant_variables.php";
require_once "system/utils/PixelException.php";
require_once "system/objects/AbstractObject.php";
require_once "system/objects/UserObject.php";
require_once "system/classes/ROLE_PERMISSION.php";
require_once "system/classes/GROUP_PERMISSION.php";
require_once "system/classes/USER_ROLE.php";
require_once "system/classes/USER_GROUP.php";
require_once "system/classes/PERMISSION.php";
require_once "system/classes/ROLE.php";
require_once "system/classes/GROUP.php";
require_once "system/classes/VALIDATE.php";
require_once "system/classes/USER_TRACK.php";
require_once "system/classes/USER_TICKET.php";
require_once "system/classes/USER.php";
require_once "system/classes/AUTHENTICATION.php";
require_once "system/classes/AUTHORIZATION.php";
require_once "system/classes/I18N.php";
require_once "system/classes/LANGUAGE.php";
require_once "system/classes/PHPMailer.php";
require_once "system/classes/MESSAGE.php";
require_once "system/classes/LOG.php";
require_once "system/classes/IMAGE_PROCESSOR.php";
require_once "system/classes/THUMB.php";
require_once "system/classes/FILE.php";
require_once "system/classes/DIRECTORY.php";
require_once "system/classes/UPLOADER.php";
require_once "system/classes/GALLERY_FILE.php";
require_once "system/classes/GALLERY.php";
require_once "system/classes/SITEMAP.php";
require_once "system/classes/FILE_EDITOR.php";
require_once "system/ADMIN.php";
require_once "system/includes/uncategorized.php";
require_once "system/includes/validate.php";
require_once "system/setup/user.php";
require_once "system/includes/menu.php";
require_once "system/includes/i18n.php";
require_once "system/includes/mail.php";
require_once "system/includes/messages.php";
require_once "system/includes/logs.php";
require_once "system/includes/gui.php";
require_once "system/includes/authentication_auhorization.php";
require_once "system/includes/users.php";
require_once "system/includes/role.php";
require_once "system/includes/file.php";
require_once "system/includes/image.php";
require_once "system/includes/gallery.php";
require_once "system/includes/upload.php";
require_once 'system/includes/fileeditor.php';
require_once 'system/includes/sitemap.php';
require_once 'system/includes/custom_html_parser.php';


if (in_admin) {
    require_once 'functions.php';

    setGlobal("GT", $GT);

    // Normalde bu işlemi dil atama esnasında seçilen dile göre yapıyoruz ama panelde kullanılan dil sabit ve Türkçe olduğu için ve de
    // sitede seçilen dil paneli etkilemesin diye burada tekrardan database'e locale ataması yapıyoruz. 
    $DB->execute("SET LC_TIME_NAMES=tr_TR");
} else {
    if (maintanance_mode && !$ADMIN->AUTHENTICATION->authenticated_user) {
        global $allowed_dirs_in_maintanance_mode;
        $allow = false;
        $current_dir = basename(dirname($_SERVER["SCRIPT_FILENAME"]));

        if (is_array($allowed_dirs_in_maintanance_mode) && (sizeof($allowed_dirs_in_maintanance_mode) > 0)) {
            foreach ($allowed_dirs_in_maintanance_mode as $dir) {
                if ($dir == $current_dir)
                    $allow = true;
            }
        }

        if (!$allow) {
            header("Location:custom_pages/uc.html");
            exit;
        }
    }
}

ob_start();
global $modulesContent;
require_once "system/includes/modules.php";
$modulesContent = ob_get_contents();
ob_end_clean();
