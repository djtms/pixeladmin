<?php
class PA_USER extends PA_USER_TICKET
{
	private $table;
	public $trackKeyName;
	public $loggedInUser;
	
	function PA_USER()
	{
		parent::PA_USER_TICKET();
		$this->table = $this->tables->user;
		$this->loggedInUser = $this->getLoggedInUser();
	}
	
	function completeRegistration($user_id, $username, $password)
	{
		global $secureKey;
		
		$pass_key = randomString(20);
		$encryptedPassword = sha1($secureKey . $password . $pass_key);
		
		return $this->execute("UPDATE {$this->table} SET username=?, password=?, pass_key=?, status='active' WHERE user_id=?", array($username, $encryptedPassword, $pass_key, $user_id));
	}
	
	function inviteAdminUser($displayname, $email, $user_type, $end_date = "0000-00-00 00:00:00")
	{
		if($user_id = $this->insert($this->table, array("displayname"=>$displayname, "email"=>$email, "user_type"=>$user_type, "status"=>"invited")))
		{
			return $this->sendInvitationMail($user_id);
		}
		else
			return false;
	}
	
	function sendInvitationMail($user_id, $end_date = "0000-00-00 00:00:00")
	{
		$user = $this->getUserById($user_id);
		$this->closeTicketsByTicketType($user_id, "invitation");
		$ticket_id = $this->openTicket($user_id, "invitation", $end_date);
		$ticket = $this->selectTicket($ticket_id);
		$site_title = get_option("admin_siteTitle");
		$register_link = get_option("admin_siteAddress") . "/admin/complete_registration.php?type=invitation&user={$user_id}&key={$ticket->ticket_key}";
		$invitation_sender = $this->loggedInUser; // Davetiyeyi gönderen kullanıcı
			
		$mesaj  = "Sayın  <b>{$user->displayname}</b>, <br /> ";
		$mesaj .= "<b>{$invitation_sender->displayname}</b> kullanıcısı ";
		$mesaj .= "<b>{$site_title}</b> sitesine üye olmanız için size bir davetiye gönderdi.";
		$mesaj .= "Daveti kabul edip üyelik işleminizi gerçekleştirmek için aşağıdaki linki kullanın.";
		$mesaj .= '<a href="' . $register_link . '" target="_blank" style="margin-top:22px;  background: #c4eef5; width:113px; ';
		$mesaj .= 'height:23px; text-align: center; font:bold 13px Segoe UI; color:#227eac; display:block; ';
		$mesaj .= 'border:solid 1px #95c1d7; text-decoration: none; line-height: 23px;">Üye Ol</a>';
		
		return sendMail($site_title, "Üyelik Davetiyesi", $mesaj, $user->email);
	}
	
	function reSendInvitationMail($email)
	{
		$user = $this->getUserByEmail($email);
		return $this->sendInvitationMail($user->user_id);
	}
	
	function login($username, $password, $captcha_used_correctly = false)
	{
		if($user = $this->getUserByUsername($username))
		{
			global $secureKey;
			
			$encryptedPassword = sha1($secureKey . $password . $user->pass_key);
			
			if($encryptedPassword == $user->password)
			{
				if(($user->captcha_limit > 0) || $captcha_used_correctly)
				{
					$this->resetUserCaptchaLimit($user->user_id);
					$this->openTrack($user->user_id);
					$this->loggedInUser = $user;
					return true;
				}
				else
				{
					return "login_with_captcha";
				}
			}
			else
			{
				$this->decreaseUserCaptchaLimit($user->user_id);
				return false;
			}
		}
		else
			return false;
	}
	
	function logout()
	{
		$tracking_key = $_SESSION[$this->trackKeyName];
		$this->closeTrack($tracking_key);
		unset($_SESSION[$this->trackKeyName]);
		unset($this->loggedInUser);
		header("Location:login.php");
	}
	
	function changePassword($user_id, $password)
	{
		global $secureKey;
		
		$pass_key = randomString(20);
		$encryptedPassword = sha1($secureKey . $password . $pass_key);
		
		return $this->execute("UPDATE {$this->table} SET password=?, pass_key=? WHERE user_id=?", array($encryptedPassword, $pass_key, $user_id));
	}
	
	function openResetPasswordTicket($email_or_username, $reset_password_page = "/admin/newpassword.php")
	{
		if($user = $this->getUserByEmail_OR_Username($email_or_username))
		{
			$ticket_type = "resetpassword";
			// Daha önce açık olan ticket ları kapat
			$this->closeTicketsByTicketType($user->user_id, $ticket_type);
			
			// Yeni bir ticket aç ve mail gönder
			$ticket_id = $this->openTicket($user->user_id, $ticket_type);
			if($ticket = $this->selectTicket($ticket_id))
			{
				$site_title = get_option("admin_siteTitle");
				$reset_password_link = get_option("admin_siteAddress") . $reset_password_page . "?type=resetpassword&user={$user->user_id}&key={$ticket->ticket_key}";
					
				$mesaj  = "Sayın  <b>{$user->displayname},</b><br />";
				$mesaj .= "Talebiniz üzerine parola değiştirme işleminizi gerçekleştirmek için aşağıda bulunan \"Parolamı Değiştir\" ";
				$mesaj .= "butonunu kullanarak, ilgili sayfaya yönlendirildikten sonra parolanızı değiştirebilirsiniz. <br />";
				$mesaj .= '<a href="' . $reset_password_link . '" target="_blank" style="margin-top:22px;  background: #c4eef5; width:145px; ';
				$mesaj .= 'height:23px; text-align: center; font:bold 13px Segoe UI; color:#227eac; display:block; ';
				$mesaj .= 'border:solid 1px #95c1d7; text-decoration: none; line-height: 23px;">Parolamı Değiştir</a>';
					
				if(sendMail($site_title, "Parola Değiştirme", $mesaj, $user->email))
					return true;
				else
				{
					$this->error = "Parola sıfırlama maili gönderilemedi!";
					return false;
				}
			}
			else
			{
				$this->error = "Parola sıfırlama işlemi için izin alınamadı, lütfen tekrar deneyin!";
				return false;
			}
		}
		else
		{
			$this->error = "Kullanıcı adı veya mail adresiniz doğru değil!";
			return false;
		}
	}
	
	function createFirstAdminUser($username, $displayname, $email, $password)
	{
		if($this->getUserCount() <= 0)
		{
			
			global $secureKey;
			
			$pass_key = randomString(20);
			$encryptedPassword = sha1($secureKey . $password . $pass_key);
			
			return $this->insert($this->table, array("username"=>$username, "displayname"=>$displayname, "password"=>$encryptedPassword, "pass_key"=>$pass_key, "email"=>$email, "user_type"=>100, "register_time"=>"NOW()"));
		}
		else
		{
			$this->error = "Zaten en az bir kullanıcı mevcut, bu şekilde yeni bir kullanıcı oluşturamazsınız!";
			return false;
		}
	}
	
	// TODO: username değerini değiştirme özelliği ekle
	function updateUser($user_id, $image_id, $displayname, $birthday, $first_name, $last_name, $email, $phone, $password)
	{	
		$variables = array($image_id, $displayname, $birthday, $first_name, $last_name, $email, $phone);
		$query = "UPDATE {$this->table} SET image_id=?, displayname=?, birthday=?, first_name=?, last_name=?, email=?, phone=?";
		if(($password != null) && ($password != false) && (strlen($password) >= 6))
		{
			$query .= ", password=? ";
			
			global $secureKey;
				
			$user = $this->getUserById($user_id);
			$encryptedPassword = sha1($secureKey . $password . $user->pass_key);
			$variables[] = $encryptedPassword;
		}
		$query .= " WHERE user_id=?";
		
		$variables[] = $user_id;
		
		if($this->execute($query, $variables))
		{
			$this->loggedInUser = $this->getLoggedInUser();
			return true;
		}
		else
			return false;
	}
	
	function deleteUser($user_id, $delete_tracks = true)
	{
		if($delete_tracks && !$this->deleteTracksByUserId($user_id))
			return false;
		
		return $this->deleteUsersAllTickets($user_id) &&
				$this->execute("DELETE FROM {$this->table} WHERE user_id=?", array($user_id));
	}
	
	function deleteUserItself($user_id, $delete_tracks = true)
	{
		if($this->deleteUser($user_id, $delete_tracks))
		{
			$this->logout();
		}
		else
			return false;
	}
	
	// TODO: burdaki işlemi tek sql sorgusu ile yapabilirsin.
	function decreaseUserCaptchaLimit($user_id)
	{
		$captcha_limit = $this->get_value("SELECT captcha_limit FROM {$this->table} WHERE user_id=?", array($user_id));
		$captcha_limit = intval($captcha_limit);

		if($captcha_limit > 0)
		{
			return $this->execute("UPDATE {$this->table} SET captcha_limit=? WHERE user_id=?", array(($captcha_limit - 1), $user_id));
		}
		
		return true;
	}
	
	function resetUserCaptchaLimit($user_id)
	{
		return $this->execute("UPDATE {$this->table} SET captcha_limit=? WHERE user_id=?", array(3, $user_id));
	}
	
	function getUserCount($status = "all")
	{
		$variables = array();
		$query = "SELECT COUNT(*) FROM {$this->table} ";
		if($status != "all")
		{
			$query .= "WHERE status=?";
			$variables[] = $status;
		}
		
		return $this->get_value($query, $variables);
	}
	
	function getUserById($user_id)
	{
		return $this->get_row("SELECT * FROM {$this->table} WHERE user_id=?", array($user_id));
	}
	
	function getUserByUsername($username)
	{
		return $this->get_row("SELECT * FROM {$this->table} WHERE username=?", array($username));
	}
	
	function getUserByEmail($email)
	{
		return $this->get_row("SELECT * FROM {$this->table} WHERE email=?", array($email));
	}
	
	function getUserByEmail_OR_Username($email_or_username)
	{
		return $this->get_row("SELECT * FROM {$this->table} WHERE email=? OR username=?", array($email_or_username, $email_or_username));
	}
	
	function listUsers($status = "all")
	{
		$variables = array();
		$query = "SELECT * FROM {$this->table} ";
		if($status != "all")
		{
			$query .= "WHERE status=?";
			$variables[] = $status;
		}
		
		return $this->get_rows($query, $variables);
	}
	
	private function getLoggedInUser()
	{
		$tracking_key = $_SESSION[$this->trackKeyName];
		$track = $this->selectTrackByTrackingKey($tracking_key);
		if($track->status == "active")
			return $this->getUserById($track->user_id);
		else
			return false;
	}
}