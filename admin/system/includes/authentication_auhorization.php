<?php

if (in_admin):
    // Eğer panel birden fazla site için tek bir yerde ortak olarak kullanılıyorsa kişi admin'e giriş yaptığında onu ortak olan admin'e yönlendir.
    global $common_admin_site;

    if ($common_admin_site != "") {
        header("Location:{$common_admin_site}/admin/");
        exit;
    }
    //------------------------------------------------------------------------------------
    // Kişi çıkış yapmak istediğinde
    if ($_REQUEST["admin_action"] == "logout") {
        add_log("çıkış yaptı");
        $ADMIN->AUTHENTICATION->logout();
    }
    //-----------------------------------------------------------------------------------
    // TODO: burası başka yöntemlerle yapılabilirmi bilmiyorum ama bi ara üzerinde düşün
    $page = basename($_SERVER["SCRIPT_FILENAME"], ".php");
    if (in_array($page, array("login", "resetpassword", "newpassword", "complete_registration"))) { // Login olmadığı halde bu sayfalara erişmeye çalışırsa.
        return;
    }
    //---------------------------------------------------------------------

    checkAccessStatus(("ADMIN_" . $_GET["page"]), false, true);
endif;

/**
 * 
 * Verilen permission_key değerine göre kullanıcının authenticate ve authorize durumlarını kontrol eder ve boolean tipinde sonucu döndürür veya yönlendirme işlemi yapar. 
 * @param string $permission_key kontrol edilmek istenen permission anahtarı
 * @param boolean $full_control eğer bu değişken false olursa, sorgulanan anahtar database deki ilgili tablolarda kayıtlı değilse, authenticate olmuş herkese izin verilir, aksi durumda kesinlikle sorgulanan permission kullanıcıda olma şartı aranır.
 * @param boolean $redirect işlem sonucunu boolean döndürmek yerine önceden tanımlı hata sayfalarına yönlendirme işlemi yapması istendiğinde kullanılır.
 */
function checkAccessStatus($permission_key, $full_control = true, $redirect = true) {
    global $ADMIN;
    
    // Kullanıcının giriş yapıp yapmadığını kontrol et.
    if ($ADMIN->AUTHENTICATION->isAuthenticated()) {
        // Kullanıcının yetkilerini kontrol et
        if (!$ADMIN->AUTHORIZATION->isAuthorized($permission_key, $full_control)) {
            if ($redirect) {
                header("Location:" . (in_admin ? "../" : "") . "custom_pages/403.html");
                exit;
            }
            else
                return false;
        }
        else { // Kullanıcının gerekli yetkisi varsa
            return true;
        }
    } else {
        if ($redirect) {
            if (in_admin) {
                $_SESSION["back_to"] = $_SERVER["REQUEST_URI"];
                header("Location:login.php");
            } else {
                header("Location:custom_pages/401.html");
            }

            exit;
        }
        else
            return false;
    }
}

