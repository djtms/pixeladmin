<?php

// Authenticate işlemi'ni User sınıfından kaldırıp burada gerçekleştir.
class PA_AUTHENTICATION extends PA_USER {

    public $authenticated_user;

    function PA_AUTHENTICATION() {
        parent::PA_USER();
        $this->authenticated_user = $this->getAuthenticatedUser();
    }

    function authenticate($username, $password, $captcha_used_correctly = false) {
        if ($user = $this->getUserByUsername($username)) {
            if ($user->status == "active") {
                global $secretKey;

                $encryptedPassword = sha1($secretKey . $password . $user->pass_key);

                if ($encryptedPassword == $user->password) {
                    // kullanıcı bizim belirlediğimiz maximum yanlış giriş yapma hakkını (şimdilik 3) kullandıktan sonraki giriş 
                    // denemelerini captcha kullanarak yapması için kontrol yapıp gerekli ayarlamaları yapıyoruz;
                    if (($user->captcha_limit > 0) || $captcha_used_correctly) {
                        $this->resetUserCaptchaLimit($user->user_id);
                        $this->openTrack($user->user_id);
                        $this->authenticated_user = $user;
                        return true;
                    } else {
                        return "login_with_captcha";
                    }
                } else {
                    $this->decreaseUserCaptchaLimit($user->user_id);
                    return false;
                }
            } else {
                return "account_not_activated";
            }
        }
        else
            return false;
    }

    function isAuthenticated() {
        if (is_object($this->authenticated_user) && (sizeof($this->authenticated_user) > 0))
            return true;
        else if (is_array($this->authenticated_user) && (sizeof($this->authenticated_user) > 0))
            return true;
        else
            return false;
    }

    function logout() {
        $tracking_key = $_SESSION[$this->trackKeyName];
        $this->closeTrack($tracking_key);
        unset($_SESSION[$this->trackKeyName]);
        unset($this->authenticated_user);
        header("Location:login.php");
    }

    function getAuthenticatedUser() {
        $tracking_key = $_SESSION[$this->trackKeyName];
        $track = $this->selectTrackByTrackingKey($tracking_key);

        if ($track->status == "active")
            return $this->getUserById($track->user_id);
        else
            return false;
    }

    function resetUserCaptchaLimit($user_id) {
        return $this->execute("UPDATE {$this->table} SET captcha_limit=? WHERE user_id=?", array(3, $user_id));
    }

    // TODO: burdaki işlemi tek sql sorgusu ile yapabilirsin.
    function decreaseUserCaptchaLimit($user_id) {
        $captcha_limit = $this->get_value("SELECT captcha_limit FROM {$this->table} WHERE user_id=?", array($user_id));
        $captcha_limit = intval($captcha_limit);

        if ($captcha_limit > 0) {
            return $this->execute("UPDATE {$this->table} SET captcha_limit=? WHERE user_id=?", array(($captcha_limit - 1), $user_id));
        }

        return true;
    }

}