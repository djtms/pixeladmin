<?php
class PA_LANGUAGE
{
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
	
	function createLanguage($abbreviation,$name)
	{
		global $DB;
		
		if(!$DB->checkIfColumnExists($this->tableI18n, $abbreviation))
			$DB->execute("ALTER TABLE {$this->tableI18n} ADD $abbreviation TEXT NOT NULL");
		
			
		if(!$this->checkIfLanguageExists($abbreviation))
			$DB->execute("INSERT INTO {$this->tableLang} (abbreviation,name) VALUES (?,?)",array($abbreviation,$name));
		
		return true;
	}
	
	function deleteLanguage($abbreviation)
	{
		global $DB;
		
		if(sizeof($this->listLanguages()) > 1)
		{
			if($DB->execute("DELETE FROM {$this->tableLang} WHERE abbreviation=?",array($abbreviation)) && $DB->execute("ALTER TABLE {$this->tableI18n} DROP $abbreviation"))
			{
				if(($this->getDefaultLanguage() == "") || ($this->getDefaultLanguage() == null))
				{
					$langs = $this->listLanguages();
					return $this->setDefaultLanguage($langs[0]->abbreviation);
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
			echo "En az bir dil bulunmalı!";
			return false;
		}
	}
	
	function setDefaultLanguage($abbreviation)
	{
		global $DB;
		
		return  $DB->execute("UPDATE {$this->tableLang} SET status=1 WHERE status>0") &&
				$DB->execute("UPDATE {$this->tableLang} SET status=10 WHERE abbreviation=?",array($abbreviation));
	}
	
	function getDefaultLanguage()
	{
		global $DB;
		return $DB->get_value("SELECT abbreviation FROM {$this->tableLang} WHERE status=10");
	}
	
	function selectLanguage($abbreviation)
	{
		global $DB;
		return $DB->get_row("SELECT * FROM {$this->tableLang} WHERE abbreviation=?",array($abbreviation));
	}
	
	function updateLanguage($abbreviation,$languageName)
	{
		global $DB;
		return $DB->execute("UPDATE {$this->tableLang} SET name=? WHERE abbreviation=?",array($languageName,$abbreviation));
	}
	
	function listLanguages()
	{
		global $DB;
		return $DB->get_rows("SELECT * FROM {$this->tableLang}",null);
	}
	
	function listActiveLanguages()
	{
		global $DB;
		return $DB->get_rows("SELECT * FROM {$this->tableLang} WHERE status>0",null);
	}
	
	function checkIfLanguageExists($abbreviation)
	{
		global $DB;
		return $DB->get_value("SELECT COUNT(*) FROM {$this->tableLang} WHERE abbreviation=?",array($abbreviation)) > 0 ? true : false;
	}
}