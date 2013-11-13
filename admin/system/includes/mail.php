<?php

/**
 * 
 * Panelde kayıtlı mail adresini kullanarak mail gönderir.
 * @param (string) $gonderenAdi
 * @param (string) $konu
 * @param (string) $mesaj
 * @param (mail) $aliciAdresi
 */
function sendMail($gonderenAdi, $konu, $mesaj, $aliciAdresi = null, $use_theme = true) {
    if (trim(get_option("admin_mail_user")) != "") {
        global $admin_folder_name;

        if ($use_theme) {
            $publicDataUrl = get_option("admin_site_address") . "/$admin_folder_name/publicdata/";

            $mailer = file_get_contents(dirname(__FILE__) . "/../../view/mailer.html");

            $mailer = str_replace('{%publicDataUrl%}', $publicDataUrl, $mailer);
            $mailer = str_replace('{%siteTitle%}', get_option("admin_site_title"), $mailer);
            $mailer = str_replace('{%subject%}', $konu, $mailer);
            $mailer = str_replace('{%message%}', $mesaj, $mailer);
            $mesaj = $mailer;
        }

        if (!is_array($aliciAdresi)) {
            $aliciAdresi = ($aliciAdresi == null) ? get_option("admin_get_mail_address") : $aliciAdresi;

            $aliciAdresi = preg_replace("/\;/", ",", $aliciAdresi);
            $aliciAdresi = preg_replace("/\s/", "", $aliciAdresi);
        } else {
            $aliciAdresiTemp = "";

            foreach ($aliciAdresi as $a) {
                $aliciAdresiTemp .= trim($a) . ",";
            }

            $aliciAdresi = substr($aliciAdresiTemp, 0, -1);
        }

        if (trim(get_option("admin_isSmtpMail")) == "") {
            $kimden = get_option("admin_mail_user");

            $konu = "=?UTF-8?B?" . base64_encode($konu) . "?=\n";
            $from = "From: =?UTF-8?B?" . base64_encode($gonderenAdi) . "?= <" . $kimden . ">\r\n";

            $headers = "MIME-Version: 1.0\n";
            $headers .= "Content-Transfer-encoding: 8bit\n";
            $headers .= "Content-type: text/html; charset=utf-8\n";
            $headers .= $from;

            return mail($aliciAdresiTemp, $konu, $mesaj, $headers);
        } else {
            date_default_timezone_set('Etc/UTC');
            
            $MAIL = new PHPMailer();
            $MAIL->IsSMTP(); // telling the class to use SMTP
            $MAIL->SMTPAuth = true; // enable SMTP authentication
            $MAIL->SMTPDebug  = 0;
            $MAIL->Debugoutput = 'html';
            $MAIL->Host = get_option("admin_mailHost"); // SMTP server
            $MAIL->Username = get_option("admin_mail_user");
            $MAIL->Password = get_option("admin_mailPassword");
            $MAIL->Port = get_option("admin_mail_port");
            $MAIL->SetLanguage("en");
            
            $MAIL->IsHTML(true); // send as HTML
            $MAIL->CharSet = "UTF-8";

            $MAIL->SetFrom(get_option("admin_mail_user"), $gonderenAdi);
            $MAIL->Subject = $konu;
            $MAIL->MsgHTML($mesaj);

            if (!is_array($aliciAdresi)) {
                $aliciAdresi = explode(",", $aliciAdresi);
            }

            foreach ($aliciAdresi as $a) {
                $MAIL->AddAddress($a, $gonderenAdi);
            }

            if($MAIL->Send()){
                return true;
            }
            else{
                echo "Hata: " . $MAIL->ErrorInfo; exit;
                return false;
            }
            
        }
    } else {
        postMessage("Mail adresi belirtilmemiş!", true);
        return false;
    }
}

