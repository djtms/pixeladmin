<?php

/**
 * 
 * mesajı panele kaydeder ve panelde kayıtlı mail adresine uyarı maili gönderir.
 * @param (string) $gonderenAdi
 * @param (string) $konu
 * @param (string) $mesaj
 * @param (mail) $aliciAdresi
 */
function sendMessage($gonderenAdi,$konu,$mesaj,$aliciAdresi = null)
{
	global $ADMIN;
	
	$messageId = $ADMIN->MESSAGE->sendMessage($gonderenAdi,$konu,$mesaj);
	$submitTime = date("d.m.Y / H:i",time());
	global $admin_folder_name;
	
	$publicDataUrl = get_option("admin_site_address") . "/$admin_folder_name/publicdata/";
	$loginLink = get_option("admin_site_address") . "/$admin_folder_name/admin.php?page=readmessage&messageId=$messageId";
	
	$mailMessage = 'Merhaha,<br />
				Sitenize ' . $submitTime . ' tarihinde “' . $gonderenAdi . '” adlı kişi tarafından,
				“' . $konu . '” konulu bir mesaj gönderildi. Mesajı okumak için lütfen giriş yapınız.
				<br />
				<a href="' . $loginLink . '" target="_blank" style="margin-top:22px;  background: #c4eef5; width:113px; 
					height:23px; text-align: center; font:bold 13px Segoe UI; color:#227eac; display:block; 
					border:solid 1px #95c1d7; text-decoration: none; line-height: 23px;">Giriş Yap</a>';
	return sendMail(get_option("admin_site_title"), "Yeni Mesajınız Var!", $mailMessage, $aliciAdresi);
}

if(in_admin)
{
	global $ADMIN;
	$master->unreadMessageCount = $ADMIN->MESSAGE->getMessageCount("unread");
}