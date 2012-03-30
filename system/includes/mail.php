<?php 
/**
 * 
 * Panelde kayıtlı mail adresini kullanarak mail gönderir.
 * @param (string) $gonderenAdi
 * @param (string) $konu
 * @param (string) $mesaj
 * @param (mail) $aliciAdresi
 */
function sendMail($gonderenAdi,$konu,$mesaj,$aliciAdresi = null, $use_theme = true)
{
	if(trim(get_option("admin_mailUser")) != "")
	{
		global $admin_folder_name;
		
		if($use_theme)
		{
			$publicDataUrl = get_option("admin_siteAddress") . "/$admin_folder_name/publicdata/";
	
			$mailer = file_get_contents(dirname(__FILE__) . "/../../view/mailer.html");
			
			$mailer = str_replace('<%publicDataUrl%>', $publicDataUrl, $mailer);
			$mailer = str_replace('<%siteTitle%>', get_option("admin_siteTitle"), $mailer);
			$mailer = str_replace('<%subject%>', $konu, $mailer);
			$mailer = str_replace('<%message%>', $mesaj, $mailer);
			$mesaj = $mailer;
		}
		
		$konu = get_option("admin_siteTitle") . " - " . $konu;
		
		if(!is_array($aliciAdresi))
			$aliciAdresi = ($aliciAdresi == null) ? get_option("admin_getMailAddress") : $aliciAdresi;
		
		if(trim(get_option("admin_isSmtpMail")) == "")
		{
			$kimden = get_option("admin_mailUser");
			
			$konu="=?UTF-8?B?".base64_encode($konu)."?=\n";
			$from ="From: =?UTF-8?B?".base64_encode($gonderenAdi)."?= <". $kimden . ">\r\n";
			
			$headers = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=utf-8\r\n";
			$headers .= "Content-Transfer-Encoding: utf-8\r\n";
			$headers .= $from;
			
			if(is_array($aliciAdresi))
			{
				foreach($aliciAdresi as $a)
				{
					$aliciAdresiTemp .= $a . ",";	
				}
				
				$aliciAdresiTemp = substr($aliciAdresiTemp, 0, -1);
			}
			else
				$aliciAdresiTemp = $aliciAdresi;
			
			return mail($aliciAdresiTemp, $konu, $mesaj, $headers);
		}
		else
		{
			$MAIL = new PHPMailer();
			$MAIL->IsSMTP(); // telling the class to use SMTP
			$MAIL->SMTPAuth = true; // enable SMTP authentication
			$MAIL->Host = get_option("admin_mailHost"); // SMTP server
			$MAIL->Username = get_option("admin_mailUser");
			$MAIL->Password = get_option("admin_mailPassword");
			$MAIL->Port = get_option("admin_mailPort");
			
			$MAIL->IsHTML(true); // send as HTML
			$MAIL->CharSet = "UTF-8";
			
			$MAIL->From = get_option("admin_mailUser");
			$MAIL->FromName = $gonderenAdi;
			$MAIL->Subject = $konu;
			$MAIL->MsgHTML($mesaj);
			
			if(is_array($aliciAdresi))
			{
				foreach($aliciAdresi as $a)
				{
					$MAIL->AddAddress($a, $gonderenAdi);
				}
			}
			else
			{
				$MAIL->AddAddress($aliciAdresi, $gonderenAdi);
			}
			return $MAIL->Send();
		}
	}
	else
	{
		postMessage("Mail adresi belirtilmemiş!",true);
		return false;
	}
}

	
	 
	