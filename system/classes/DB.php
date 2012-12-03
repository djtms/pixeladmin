<?php
define("FETCH_ASSOC",PDO::FETCH_ASSOC); // databaseden dönen değerlerin tipleri
define("FETCH_NUM",PDO::FETCH_NUM);
define("FETCH_OBJ",PDO::FETCH_OBJ);
define("FETCH_KEY_PAIR",PDO::FETCH_KEY_PAIR);

$DB = new DB();

class DB
{
	private $dbh;
	public $tables;
	public static $dbcon; // Database connection
	private static $inTransaction = false;
	
	/**
	 * 
	 * Database ile bağlantı kurup hızlı işlem yapmaya yarayan bir class
	 * @param string $dbname
	 * @param string $dbhost
	 * @param string $dbuser
	 * @param string $dbpass
	 * @param string $dbcharset
	 * @param string $timezone
	 */
	function DB($dbname = null, $dbhost = null, $dbuser = null, $dbpass=null, $dbcharset="utf8", $timezone="+02:00")
	{
		$arguments = func_get_args();
		
		if(sizeof($arguments) < 4) // eğer database bilgileri atanmamışsa config'deki bilgileri kullanarak database'e bağlan
		{
			// Eğer daha önce kurulu bir bağlantı yok ise yeni bağlantı kur
			if(!isset(self::$dbcon) || self::$dbcon == null)
			{
				global $dbname;
				global $dbhost;
				global $dbuser;
				global $dbpass;
				global $dbcharset;
				global $timezone;
				$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbcharset}, time_zone='{$timezone}'");
					
				try
				{
					self::$dbcon = new PDO("mysql:host={$dbhost};dbname={$dbname}", $dbuser, $dbpass, $options);
					global $dbh;
					$dbh = self::$dbcon;
					$this->dbh = self::$dbcon;
				}
				catch(PDOException $e)
				{
					echo "Veritabanı Bağlantı Hatası: " . $e->getMessage();
					exit;
				}
			}
			else // Eğer daha önce kurulu bir bağlantı var ise onu kullan
			{
				$this->dbh = self::$dbcon;
			}
		}
		else  // argüman olarak tanımlanmış bilgileri kullanarak database'e bağlan
		{
			$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbcharset}, time_zone='{$timezone}'");
			
			try
			{
				$this->dbh = new PDO("mysql:host={$arguments[1]};dbname={$arguments[0]}", $arguments[2], $arguments[3], $options);
				global $dbh;
				$dbh = $this->dbh;
			}
			catch(PDOException $e)
			{
				echo "Veritabanı Bağlantı Hatası: " . $e->getMessage();
				exit;
			}
		}
		
		$this->tables = new DB_TABLES();
	}
	
	/**
	 * 
	 * Select query'sinde ilk field'ın değerini döndürür
	 * @param string $query
	 * @param array $values query içinde veri bağlanacaksa, o verileri bu array içine ekliyoruz
	 * @return strings
	 */ 
	function get_value($query, $values=array())
	{
		$sth = $this->dbh->prepare($query);
		if(is_array($values) && (sizeof($values) > 0))
		{
			foreach($values as $key=>$val)
			{
				$key = is_numeric($key) ? ($key + 1) : $key;
				$sth->bindValue($key,$val);	
			}
		}
		
		if($sth->execute())
		{
			$result = $sth->fetch(PDO::FETCH_NUM);
			return $result[0];
		}
	}
	
	/**
	 * Select query'sinde ilk satırı döndürür.
	 * @param string $query
	 * @param array $values query içinde veri bağlanacaksa, o verileri bu array içine ekliyoruz
	 * @param defined_values $fetchType
	 */
	function get_row($query, $values=array(), $fetchType = FETCH_OBJ)
	{
		$sth = $this->dbh->prepare($query);
		if(is_array($values) && (sizeof($values) > 0))
		{
			foreach($values as $key=>$val)
			{
				$key = is_numeric($key) ? ($key + 1) : $key;
				$sth->bindValue($key,$val);	
			}
		}
		
		if($sth->execute())
			return $sth->fetch($fetchType);
	}

	/**
	* Select query'sindeki tüm satırları döndürür.
	* @param string $query
	* @param array $values query içinde veri bağlanacaksa, o verileri bu array içine ekliyoruz
	* @param defined_values $fetchType
	*/
	function get_rows($query, $values=array(), $fetchType = FETCH_OBJ)
	{
		$sth = $this->dbh->prepare($query);
		if(is_array($values) && (sizeof($values) > 0))
		{
			foreach($values as $key=>$val)
			{
				$key = is_numeric($key) ? ($key + 1) : $key;
				$sth->bindValue($key,$val);	
			}
		}
		if($sth->execute())
			return $sth->fetchAll($fetchType);
	}
	
	/**
	 * database de istenen tablodaki istenen fieldlara veri ekler.
	 * @param string $table
	 * @param array $variables array("key"=>$value) şeklinde bir array olmalı
	 * @return string|boolean
	 */
	function insert($table, $variables)
	{
		$columns = "";
		$values = "";
		
		if(sizeof($variables)>0)
		{
			foreach($variables as $col=>$val)
			{
				if(trim($val) != "NOW()")
				{
					$columns .= "$col,";
					$values  .= is_numeric($col) ? "?," : ":$col,";
				}
				else
				{
					$columns .= "$col,";
					$values  .= "NOW(),";
				}
			}
			
			$columns = substr($columns,0,-1);
			$values = substr($values,0,-1);
			
			$query = sprintf("INSERT INTO %s (%s) VALUES (%s)",$table,$columns,$values);
			$sth = $this->dbh->prepare($query);
			
			if(is_array($variables))
			{
				foreach($variables as $col=>$val)
				{
					if(trim($val) != "NOW()")
					{
						$col = is_numeric($col) ? ($col + 1) : ":$col";
						$sth->bindValue($col,$val);	
					}
				}
			}
			
			if($sth->execute())
			{
				if($id = $this->dbh->lastInsertId())
					return $id;
				else
					return true;
			}
			else
				return false;
		}
		else
			return false;
	}
	
	/**
	* database de istenen tablodaki istenen fieldları günceller.
	* @param string $table
	* @param array $variables array("key"=>$value) şeklinde bir array olmalı
	* @return string|boolean
	*/
	function update($table, $variables, $where)
	{
		$query = "UPDATE {$table} SET";
		foreach($variables as $col=>$val)
		{
			if(trim($val) != "NOW()")
			{
				$query .= " $col=";
				$query  .= is_numeric($col) ? "?," : ":$col,";
				
			}
			else
			{
				$query .= " {$col}=NOW(),";
			}
		}
		
		$query  = substr($query, 0, -1);
		
		
		if(is_array($where) && (sizeof($where) > 0))
		{
			$variables = array_merge($variables, $where);
			
			$query .= " WHERE ";
			
			foreach($where as $col=>$val)
			{
				$query .= "$col=";
				$query  .= is_numeric($col) ? "? " : ":$col ";
				$query .= "AND ";
			}
			
			$query = substr($query, 0, -4);
		}
		else if(is_string($where))
		{
			if(!preg_match("/^\b+WHERE\b/i", $where))
			{
				$query .= " WHERE ";
			}
			
			$query .= $where;
		}
		
		$sth = $this->dbh->prepare($query);
		
		foreach($variables as $col=>$val)
		{
			if(trim($val) != "NOW()")
			{
				$col = is_numeric($col) ? ($col + 1) : ":$col";
				$sth->bindValue($col,$val);
			}
		}
		
		return $sth->execute();
	}
	
	/**
	 * İstenen query'i çalıştırır.
	 * @param string $query
	 * @param array $values query içinde veri bağlanacaksa, o verileri bu array içine ekliyoruz
	 */
	function execute($query, $values = array())
	{
		$sth = $this->dbh->prepare($query);
		if(is_array($values) && (sizeof($values) > 0))
		{
			foreach($values as $key=>$val)
			{
				
				$key = is_numeric($key) ? ($key + 1) : $key;
				$sth->bindValue($key,$val);	
			}
		}
		return $sth->execute();
	}
	
	/**
	 * 
	 * Database deki bir field'ın varolup olmadığını kontrol eder
	 * @param string $table
	 * @param string $column
	 * @return boolean
	 */
	function checkFieldExists($table, $column)
	{
		$query = "SHOW COLUMNS FROM $table";
		
		$sth = $this->dbh->prepare($query);
		$sth->execute();
		$columns = $sth->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($columns as $c)
		{
			if($c["Field"] == $column)
				return true;
		}
		
		return false;
	}
	
	/**
	 * 
	 * Son eklenen primary_key değerini döndürür
	 * @return string
	 */
	function lastInsertId()
	{
		return $this->dbh->lastInsertId();
	}
	
	/**
	 * 
	 * Transaction işlemi başlatır
	 * @return boolean
	 */
	function beginTransaction()
	{
		if(self::$inTransaction == false)
		{
			if($this->dbh->beginTransaction())
			{
				self::$inTransaction = true;
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return true;
		}
	}
	
	/**
	 * 
	 * Transaction işlemine daha önce başlanıp başlanmadığını bildirir
	 * @return boolean
	 */
	function inTransaction()
	{
		return self::$inTransaction;
	}
	
	/**
	 * 
	 * Transaction işlemini olumlu şekilde tamamlar ve transaction süresi boyunca database için yapılan tüm işlemleri database de gerçekleştirir
	 * @return boolean
	 */
	function commit()
	{
		if($this->dbh->commit())
		{
			self::$inTransaction = false;
			return true;
		}
		else
			return false;
	}
	
	/**
	 * Transaction işlemini olumsuz şekilde bitirir. Transaction süresi boyunca yapılan işlemleri geri alır.
	 */
	function rollBack()
	{
		
		if($this->dbh->rollBack())
		{
			self::$inTransaction = false;
			return true;
		}
		else
		{
			return false;
		}
	}
}

class DB_TABLES
{
	public $i18n = "i18n";
	public $option = "option";
	public $user = "user";
	public $user_ticket = "user_ticket";
	public $user_track = "user_track";
	public $language = "language";
	public $message = "message";
	public $log = "log";
	public $file = "file";
	public $directory = "directory";
	public $thumb = "thumb";
	public $file_thumb = "file_thumb";
	public $gallery = "gallery";
	public $gallery_file = "gallery_file";
	public $role = "role";
	public $permission = "permission";
	public $group = "group";
	public $user_role = "user_role";
	public $user_group = "user_group";
	public $group_permission = "group_permission";
	public $role_permission = "role_permission";
	public $sitemap = "sitemap";
	
	function DB_TABLES()
	{
		$this->GenerateTableNames();
	}
	
	function GenerateTableNames()
	{
		global $prefix;
		
		$this->i18n = $prefix . $this->i18n;
		$this->option = $prefix . $this->option;
		$this->user = $prefix . $this->user;
		$this->user_ticket = $prefix . $this->user_ticket;
		$this->user_track = $prefix . $this->user_track;
		$this->language = $prefix . $this->language;
		$this->message = $prefix . $this->message;
		$this->log = $prefix . $this->log;
		$this->file = $prefix . $this->file;
		$this->directory = $prefix . $this->directory;
		$this->thumb = $prefix . $this->thumb;
		$this->file_thumb = $prefix . $this->file_thumb;
		$this->gallery = $prefix . $this->gallery;
		$this->gallery_file = $prefix . $this->gallery_file;
		$this->role = $prefix . $this->role;
		$this->permission = $prefix . $this->permission;
		$this->group = $prefix . $this->group;
		$this->user_role = $prefix . $this->user_role;
		$this->user_group = $prefix . $this->user_group;
		$this->group_permission = $prefix . $this->group_permission;
		$this->role_permission = $prefix . $this->role_permission;
		$this->sitemap = $prefix . $this->sitemap;
	}
}