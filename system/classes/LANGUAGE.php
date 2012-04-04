<?php
class PA_LANGUAGE
{
	public $error = "";
	private $tableI18n = "";
	private $tableLang = "";
	
	function PA_LANGUAGE()
	{
		global $DB;
		
		$this->tableI18n = $DB->tables->i18n;
		$this->tableLang = $DB->tables->language;
		
		if(sizeof($this->listLanguages()) <= 0)
		{
			$this->createLanguage("en", "English");
			$this->createLanguage("tr", "Türkçe");
			$this->setDefaultLanguage("tr");
		}
	}
	
	function listLanguages()
	{
		global $DB;
		
		$query = "SELECT language_name, language_abbr FROM {$this->tableLang} GROUP BY language_abbr";
		
		return $DB->get_rows($query);
	}
	
	function listCountries()
	{
		global $DB;
		
		$query = "SELECT country_name, country_abbr FROM {$this->tableLang} GROUP BY country_abbr";
		
		return $DB->get_rows($query);
	}
	
	function listCountriesByLanguageAbbreviation($language_abbr)
	{
		global $DB;
		
		$query = "SELECT country_name, country_abbr FROM {$this->tableLang} WHERE language_abbr=? GROUP BY country_abbr";
		
		return $DB->get_rows($query, array($language_abbr));
	}
	
	
	function addLanguage($locale)
	{
		global $DB;
		
		if(!$DB->checkIfColumnExists($this->tableI18n, $locale) && !$DB->execute("ALTER TABLE {$this->tableI18n} ADD $locale TEXT DEFAULT NULL"))
			return false; // Belirtilen column un tabloda olmadığı durumda eğer o column u oluşturamıyorsak return false olacak.
		
		return $this->setLanguageStatus($locale, 1);
	}
	
	function updateLanguage($old_locale, $new_locale)
	{
		global $DB;
		
		if($this->getLanguageStatus($new_locale) < 0)
		{
			if($DB->execute("ALTER TABLE {$this->tableI18n} CHANGE {$old_locale} {$new_locale} TEXT DEFAULT NULL"))
			{
				$old_language_status = $this->getLanguageStatus($old_locale); // Yeni dile eski dilin status değerini atamak için eski dilin status değerini alıyoruz
				
				return $this->setLanguageStatus($old_locale, -1) &&
						$this->setLanguageStatus($new_locale, $old_language_status);
				 
			}
			else
				return false;
		}
		else
			return false;
	}
	
	
	function deleteLanguage($locale)
	{
		global $DB;
		
		if(sizeof($this->listActiveLanguages()) > 1)
		{
			if($DB->execute("ALTER TABLE {$this->tableI18n} DROP $locale") && $this->setLanguageStatus($locale, -1))
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
		global $DB;
	
		return  $DB->execute("UPDATE {$this->tableLang} SET status=1 WHERE status>0") &&
				$DB->execute("UPDATE {$this->tableLang} SET status=10 WHERE locale=?",array($locale));
	}
	
	function getDefaultLanguage()
	{
		global $DB;
		return $DB->get_value("SELECT locale FROM {$this->tableLang} WHERE status=10 LIMIT 0,1");
	}
	
	function listActiveLanguages()
	{
		global $DB;
		return $DB->get_rows("SELECT * FROM {$this->tableLang} WHERE status>0",null);
	}
	
	function selectLanguage($locale)
	{
		global $DB;
		return $DB->get_row("SELECT * FROM {$this->tableLang} WHERE locale=?",array($locale));
	}
	
	function getLanguageStatus($locale)
	{
		global $DB;
		
		return $DB->get_value("SELECT status FROM {$this->tableLang} WHERE locale=?", array($locale));
	}
	
	private function setLanguageStatus($locale, $status)
	{
		global $DB;
		
		return $DB->execute("UPDATE {$this->tableLang} SET status=? WHERE locale=?", array($status, $locale));
	}
}