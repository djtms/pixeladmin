<?php
class PA_LANGUAGE extends DB
{
	public $error = "";
	public $date_format = "'%d %M %y'";
	private $tableI18n = "";
	private $tableLang = "";
	
	function PA_LANGUAGE(){
		parent::DB();
		
		$this->tableI18n = $this->tables->i18n;
		$this->tableLang = $this->tables->language;
		
		if(sizeof($this->listLanguages()) <= 0){
			$this->createLanguage("en", "English");
			$this->createLanguage("tr", "Türkçe");
			$this->setDefaultLanguage("tr");
		}
	}
	
	function listLanguages(){
		return $this->get_rows("SELECT language_name, language_abbr FROM {$this->tableLang} GROUP BY language_abbr");
	}
	
	function listCountries(){
		return $this->get_rows("SELECT country_name, country_abbr FROM {$this->tableLang} GROUP BY country_abbr");
	}
	
	function listCountriesByLanguageAbbreviation($language_abbr){
		return $this->get_rows("SELECT country_name, country_abbr FROM {$this->tableLang} WHERE language_abbr=? GROUP BY country_abbr", array($language_abbr));
	}
	
	
	function addLanguage($locale, $date_format='%d %M %y'){
		if(!$this->checkFieldExists($this->tableI18n, $locale) && !$this->execute("ALTER TABLE {$this->tableI18n} ADD $locale TEXT DEFAULT NULL"))
			return false; // Belirtilen column un tabloda olmadığı durumda eğer o column u oluşturamıyorsak return false olacak.
		
		return $this->setLanguageStatus($locale, 1) && 
				$this->execute("UPDATE {$this->tableLang} SET date_format=? WHERE locale=?", array($date_format, $locale));
	}
	
	function updateLanguage($old_locale, $new_locale, $new_status=null, $date_format='%d %M %y'){
		// Eğer dil değişikliği yapmıyorsa belki sadece "status" değerini değiştiriyorsa 
		// veya şu anda aktif olan bir dili seçmiyorsa
		if(($old_locale == $new_locale) || $this->getLanguageStatus($new_locale) < 0)
		{
			if($this->execute("ALTER TABLE {$this->tableI18n} CHANGE {$old_locale} {$new_locale} TEXT DEFAULT NULL"))
			{
				// Yeni dile eski dilin status değerini atamak için eski dilin status değerini alıyoruz
				$old_language_status = $this->getLanguageStatus($old_locale);
				
				// Eğer aynı dilin status değerini önceden pasif iken aktif yapmışsak
				// $old_languages_status değerini bu dilin önceki değerinden aldığı için burada
				// kontrol yapıp duruma göre $old_language_status değerini minimum aktif dil değeri olan 1 yapıyoruz
				if(($old_language_status == 0) && ($new_status === null))
					$old_language_status = 1;
				else if($new_status !== null)
					$old_language_status = $new_status;
				
				return $this->setLanguageStatus($old_locale, -1) &&
						$this->setLanguageStatus($new_locale, $old_language_status) &&
						$this->execute("UPDATE {$this->tableLang} SET date_format=? WHERE locale=?", array($date_format, $new_locale));
				 
			}
			else
				return false;
		}
		else
			return false;
	}
	
	
	function deleteLanguage($locale){
		if(sizeof($this->listUserSelectedLanguages()) > 1)
		{
			if($this->execute("ALTER TABLE {$this->tableI18n} DROP $locale") && $this->setLanguageStatus($locale, -1))
			{
				// Silinen dil default dil ise veya default dil tanımlı değil ise ilk sıradaki dil'i default yap.
				if(($this->getDefaultLanguage() == "") || ($this->getDefaultLanguage() == null))
				{
					$langs = $this->listLanguages();
					return $this->setDefaultLanguage($langs[0]->locale);
				}
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$this->error = "En az bir dil bulunmalı!";
			return false;
		}
	}
	
	function setDefaultLanguage($locale)
	{
		global $ADMIN;
		
		$ADMIN->I18N->language = $locale;
	
		return  $this->execute("UPDATE {$this->tableLang} SET status=1 WHERE status>0") &&
				$this->execute("UPDATE {$this->tableLang} SET status=10 WHERE locale=?",array($locale));
	}
	
	function getDefaultLanguage(){
        $language = $this->get_value("SELECT locale FROM {$this->tableLang} WHERE status=10 LIMIT 0,1");
        // Sebebini henüz bilmiyorum ama bazen $language değeri object olarak dönüyor, onu
        // kontrol edip hatayı önlemek için bu işlemi yapıyoruz
        return is_object($language) ? $language->locale : $language;
	}
	
	/**
	 * 
	 * Kullanıcının sitesine eklediği ve durumu aktif olan dilleri listeler
	 * @return array
	 */
	function listActiveLanguages()
	{
		return $this->get_rows("SELECT * FROM {$this->tableLang} WHERE status>0",null);
	}
	
	/**
	 * 
	 * Kullanıcının sitesine eklediği dilleri listeler, durumu pasif veya aktif olması farketmez
	 * @return array
	 */
	function listUserSelectedLanguages()
	{
		return $this->get_rows("SELECT * FROM {$this->tableLang} WHERE status>=0",null);
	}
	
	function selectLanguage($locale)
	{
		return $this->get_row("SELECT * FROM {$this->tableLang} WHERE locale=?",array($locale));
	}
	
	function getLanguageStatus($locale)
	{
		return $this->get_value("SELECT status FROM {$this->tableLang} WHERE locale=?", array($locale));
	}
	
	private function setLanguageStatus($locale, $status)
	{
		return $this->execute("UPDATE {$this->tableLang} SET status=? WHERE locale=?", array($status, $locale));
	}
}