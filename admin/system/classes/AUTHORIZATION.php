<?php

class PA_AUTHORIZATION extends DB {

    public $authorizationKeyName;

    function PA_AUTHORIZATION() {
        global $sessionKeysPrefix;
        parent::DB();

        $this->authorizationKeyName = $sessionKeysPrefix . "_AUTHORIZATION";
    }

    /**
     *
     * Kullanıcının bulunduğu sayfaya erişim yetkisinin olup olmadığını kontrol eder
     * @return boolean
     */
    function isAuthorized($permission_key, $full_control = true) {
        global $secretKey;
        global $ADMIN;

        if (isset($_SESSION[$this->authorizationKeyName])) {
            $user_roles = $_SESSION[$this->authorizationKeyName]["ROLES"];
            $user_roles_count = sizeof($user_roles);

            $user_groups = $_SESSION[$this->authorizationKeyName]["GROUPS"];
            $user_groups_count = sizeof($user_groups);

            // İstenen permission'a bağlı rolleri listele
            $role_permissions = $ADMIN->ROLE_PERMISSION->listRolePermissionsByPermissionKey($permission_key);
            $role_permission_count = sizeof($role_permissions);

            // İstenen permission'a bağlı gurupları listele
            $group_permissions = $ADMIN->GROUP_PERMISSION->listGroupPermissionsByPermissionKey($permission_key);
            $group_permission_count = sizeof($group_permissions);

            // Olurda birşekilde kişi session'ı illegal şekilde düzenleyip yetkilerini değiştirirse diye burada kontrol yapıyoruz.
            // normalde kişi authorize olduğunda bağlı olduğu role_id ve group_id değerlerini ve config.php de tanımlı olan secretKey değerini 
            // hashleyip session a atıyoruz. burada da kontrol yaparken aynı şekilde session daki permissionları secretKey ile hashleyip 
            // authorize olduğundaki hash ile karşılaştırıyoruz, eğer doğruysa permissionlara dokunulmamıştır diyip işleme devam ediyoruz.
            if ($_SESSION[$this->authorizationKeyName]["VALIDATE_ROLES_GROUPS_HASH"] == sha1($secretKey . implode($user_roles, "-") . "-" . implode($user_groups, "-"))) {
                // Eğer istenen permission'ın role_permission ve/veya group_permission tablosunda kayıtları varsa kullanıcıya izin vermek için
                // kullanıcıda bu tablolardan dönen kayırları ara. eğer yoksa izin verme, en az biri varsa izin ver.
                if (($role_permission_count > 0) || ($group_permission_count > 0)) {
                    // İstenen permission'a bağlı rollerin kullanıcıda olup olmadığını ara. 
                    // En az bir rol authenticate olmuş kullanıcıda varsa giriş izni ver ve bu fonksiyondan çık 
                    for ($i = 0; $i < $role_permission_count; $i++) {
                        // Aranan role_id
                        $role_id = $role_permissions[$i]->role_id;

                        // Kullanıcıda yukardaki role_id'yi ara
                        for ($j = 0; $j < $user_roles_count; $j++) {
                            // Kullanıcı rollerden birine sahipse izin ver
                            if ($role_id == $user_roles[$j]) {
                                return true;
                            }
                        }
                    }

                    // İstenen permission'a bağlı gurupların kullanıcıda olup olmadığını ara.
                    // En az bir gurup authenticate olmuş kullanıcıda varsa giriş izni ver ve bu fonksiyondan çık
                    for ($i = 0; $i < $group_permission_count; $i++) {
                        // Aranan group_id
                        $group_id = $group_permissions[$i]->group_id;

                        // Kullanıcıda yukardaki group_id'yi ara
                        for ($j = 0; $j < $user_groups_count; $j++) {
                            // Kullanıcı guruplardan birine sahipse izin ver
                            if ($group_id == $user_groups[$j]) {
                                return true;
                            }
                        }
                    }

                    // Buraya kadar gelmişse kullanıcıda ilgili tablolardan dönen kayıtları bulamadı demektir o yüzden giriş izni vermiyoruz.
                    return false;
                } else { // Aranan permission database de ilgili tablolarda bulunamadıysa
                    // Eğer tam denetim yapıyorsak kullanıcıya giriş izni vermiyoruz, ama tam denetim yapmıyorsak authenticate olmuş herkese izin veriyoruz.
                    return !$full_control;
                }
            } else { // Session daki hash ile eşleşmediği için illegal erişim olduğunu düşünüp izin vermiyoruz.
                return false;
            }
        } else {
            return false;
        }
    }

    function authorize() {
        global $ADMIN;
        global $secretKey;
        $user = $ADMIN->AUTHENTICATION->authenticated_user;
        $user_permissions = array();
        $hash_string_source = "";

        // Kullanıcının rollerini listele
        $query = "SELECT role_id FROM {$this->tables->user_role} WHERE user_id=?";
        $user_roles = $ADMIN->DB->get_rows($query, array($user->user_id), FETCH_NUM);
        $user_roles_count = sizeof($user_roles);

        // Kullanıcının gruplarını listele
        $query = "SELECT group_id FROM {$this->tables->user_group} WHERE user_id=?";
        $user_groups = $ADMIN->DB->get_rows($query, array($user->user_id), FETCH_NUM);
        $user_groups_count = sizeof($user_groups);

        $roles = array();
        for ($i = 0; $i < $user_roles_count; $i++) {
            $roles[] = $user_roles[$i][0];
        }

        $groups = array();
        for ($i = 0; $i < $user_groups_count; $i++) {
            $groups[] = $user_groups[$i][0];
        }

        $_SESSION[$this->authorizationKeyName]["VALIDATE_ROLES_GROUPS_HASH"] = sha1($secretKey . implode($roles, "-") . "-" . implode($groups, "-"));
        $_SESSION[$this->authorizationKeyName]["ROLES"] = $roles;
        $_SESSION[$this->authorizationKeyName]["GROUPS"] = $groups;

        return true;
    }

    /**
     * 
     * Yetki bilgilerini session'dan temizler.
     * @return boolean
     */
    function drop() {
        unset($_SESSION[$this->authorizationKeyName]);
        return true;
    }

    function getRolesAndGroupsByPermission($permission_key) {
        $query = "SELECT * FROM  ";
        $query .= "";

        return $this->get_row($query, array($fixed_request_uri));
    }

}