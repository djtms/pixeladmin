<?php

require_once dirname(__FILE__) . '/includes.php';

// Talep edilen sayfanın dosyasını bul 
$pa_menuId = urldecode($_GET["page"]);

foreach ($pa_menu_array as $key => $menu) {
    if ($key == $pa_menuId) {
        $page = $menu["menuPage"];
    }

    if (sizeof($menu["subMenus"]) > 0) {
        foreach ($menu["subMenus"] as $order => $sm) {
            $key = key($sm);
            $sm = $sm[$key];

            if ($key == $pa_menuId) {
                $page = $sm["menuPage"];
                break;
            }
        }
    }

    if (sizeof($menu["subPages"]) > 0) {
        foreach ($menu["subPages"] as $pageId => $sp) {
            if ($pageId == $pa_menuId) {
                $page = $sp["page"];
                break;
            }
        }
    }
}

// TODO: loadMenus fonksiyonunu burada tanımlamamızın bir dezavantajı var. aslında kullanılacağını sanmadığım ve bence kullanılmaması gereken bir
//  yöntem ama olurda kişi yazdığı modülde açtığı sayfa içinde bir menü yada page tanımlaması yaparsa panelde onu göremeyecektir. bunun üzerind 
// vaktin olduğunda bir ara düşün belki başka alternatif yollar bulabilirsin.
loadMenus($pa_menuId);

if (file_exists($page)) {
    ob_start();
    require_once $page;
    require_once "system/includes/late_call.php"; // ancak tüm sayfalar yükledikten sonra çalıştırılması gereken kodları bu sayfada bulunduruyoruz.

    $master->content = $modulesContent . ob_get_contents();
    ob_end_clean();
} else {
    $master->content = "Sayfa Bulunamadı";
}

$master->postMessage = get_option("admin_postMessage");
set_option("admin_postMessage", "");
echo parseCustomHtml($master->render(false));