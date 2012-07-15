<?php
class PA_AUTHORIZATION extends DB
{
	public $authorizationKeyName;
	
	function PA_AUTHORIZATION()
	{
		global $sessionKeysPrefix;
		parent::DB();
		
		$this->authorizationKeyName = $sessionKeysPrefix . "_AUTHORIZATION";
	}
	
	function checkAuthorization()
	{
		global $secureKey;
		
		if(isset($_SESSION[$this->authorizationKeyName]))
		{
			$user_permissions = $_SESSION[$this->authorizationKeyName]["PERMISSIONS"];
			// Olurda birşekilde kişi session'ı illegal şekilde düzenleyip yetkilerini değiştirirse diye burada kontrol yapıyoruz.
			// normalde kişi authorize olduğunda permission id lerini ve config.php de tanımlı olan secureKey değerini hashleyip yine 
			// session a atıyoruz. burada da kontrol yaparken aynı şekilde sessionları secureKey ile hashleyip authorize olduğundaki hash ile
			// karşılaştırıyoruz, eğer doğruysa permissionlara dokunulmamıştır diyip işleme devam ediyoruz.
			if($_SESSION[$this->authorizationKeyName]["VALIDATE_PERMISSIONS_HASH"] == sha1($secureKey . implode($user_permissions, "-")))
			{
				// eğer permission tablosunda kaydı varsa, istenen permission'ın login olan kullanıcıda olup olmadığını kontrol et
				if($permission = $this->getCurrentPagePermissionInfo())
				{
					$permission_count = sizeof($user_permissions);
					$has_permission = false;
					for($i = 0; $i<$permission_count; $i++)
					{
						if($user_permissions[$i] == $permission->permission_id)
						{
							$has_permission = true;
							break;
						}
					}
					
					return $has_permission;
				}
				else
				{
					// eğer bu sayfa permission tablosuna eklenmemişse herkese açık demektir.
					return true;
				}
			}
			else
			{
				echo "sdsf"; exit;
				return false;
			}
		}
		else
		{
			echo "aa"; exit;
			return false;
		}
	}
	
	function authorize()
	{
		global $ADMIN;
		global $secureKey;
		$user = $ADMIN->USER->loggedInUser;
		$user_permissions = array();
		$hash_string_source = "";
		
		// Kullanıcının rollerini listesini al.
		$roles = $ADMIN->USER_ROLE->listUserRolesByUser($user->user_id);
		$role_count = sizeof($roles);
		
		// Kullanıcının gruplarını listele
		$groups = $ADMIN->USER_GROUP->listUserGroupsByUser($user->user_id);
		$group_count = sizeof($groups);
		
		// Kullanıcının rollerine göre permission larını listele
		$query  = "SELECT permission_id FROM {$this->tables->role_permission} WHERE role_id IN (";
		for($i=0; $i<$role_count; $i++)
		{
			$query .= $roles[$i]->role_id . ",";
		}
		$query = substr($query, 0, -1) . ")";
		$user_role_permissions = $ADMIN->DB->get_rows($query, null, FETCH_NUM);
		$user_role_permission_count = sizeof($user_role_permissions);
		
		for($i=0; $i<$user_role_permission_count; $i++)
		{
			$user_permissions[] = $user_role_permissions[$i][0];
		}
		
		
		// Kullanıcının grubuna göre permission larını listele
		$query  = "SELECT permission_id FROM {$this->tables->group_permission} WHERE group_id IN (";
		for($i=0; $i<$group_count; $i++)
		{
			$query .= $groups[$i]->group_id . ",";
		}
		$query = substr($query, 0, -1) . ")";
		$user_group_permissions = $ADMIN->DB->get_rows($query, null, FETCH_NUM);
		$user_group_permission_count = sizeof($user_group_permissions);
		
		for($i=0; $i<$user_group_permission_count; $i++)
		{
			$user_permissions[] = $user_group_permissions[$i][0];
		}
		
		
		$_SESSION[$this->authorizationKeyName]["VALIDATE_PERMISSIONS_HASH"] = sha1($secureKey . implode($user_permissions, "-"));
		$_SESSION[$this->authorizationKeyName]["PERMISSIONS"] = $user_permissions;
		
		return true;
	}
	
	/**
	*
	* Bu fonksiyon, çalıştırıldığı sayfanın varsa database deki permission
	* tablosundan request_uri değerine göre kendine en yakın kaydını döndürür.
	* Örnek: çalıştığı sayfa admin.php?page=test  diyelim,
	* database de deşu şekilde 3 kayıt var diyelim
	* - admin.php?page=test
	* - admin.php?page=test2
	* - admin.php?page=te
	*
	* bu kayıtlar içinde sorgu yaparken, databasedeki kolonlar içinde bizim sayfamızın farklı hesapladığımız şekildeki
	* adresini aramayacak, tam tersi şekilde bizim farklı hesapladığımız sayfa adresi içinde database deki kolonları
	* arıyacak ve eşleşen datalar arasında string değeri en uzun olanı döndürecek.
	* bu duruma göre döndüreceği kayıt "admin.php?page=test" sayfası olacaktır
	* eğer arama işlemini kolonlar içinde sayfa adresini arama şeklinde yapsaydık dönen sonuç
	* "admin.php?page=test2" olacaktı ve yanlış olacaktı. Burada anahtar nokta aramayı kolonlar içinde değil
	* farklı şekilde hesapladığımız sayfa url i içinde databasedeki kayıtları aratarak yapıyoruz
	*/
	function getCurrentPagePermissionInfo()
	{
		$fixed_request_uri = preg_replace("/.*(" . working_folder_name . "\/){1}(?=admin)/i", "", $_SERVER["REQUEST_URI"]);
	
		$query  = "SELECT * FROM {$this->tables->permission} ";
		$query .= "WHERE ? LIKE CONCAT( permission_url, '%') ";
		$query .= "AND permission_url != '' AND permission_url IS NOT NULL ";
		$query .= "ORDER BY LENGTH(permission_url) DESC LIMIT 0,1";
	
		return $this->get_row($query, array($fixed_request_uri));
	}
	
}