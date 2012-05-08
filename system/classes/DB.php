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
			global $dbname;
			global $dbhost;
			global $dbuser;
			global $dbpass;
			global $dbcharset;
			global $timezone;
			
			// Eğer daha önce kurulu bir bağlantı yok ise yeni bağlantı kur
			if(!isset(self::$dbcon) || self::$dbcon == null)
			{
				$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbcharset}, time_zone='{$timezone}'");
					
				try
				{
					self::$dbcon = new PDO("mysql:host={$dbhost};dbname={$dbname}", $dbuser, $dbpass, $options);
					global $dbh;
					$dbh = self::$dbcon;
					$this->dbh = self::$dbcon;
						
					$this->tables = new DB_TABLES();
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
				
				$this->tables = new DB_TABLES();
			}
			catch(PDOException $e)
			{
				echo "Veritabanı Bağlantı Hatası: " . $e->getMessage();
				exit;
			}
		}
	}
	
	function get_value($query,$values=null)
	{
		$sth = $this->dbh->prepare($query);
		if(is_array($values))
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
	
	function get_row($query,$values=null,$fetchType = FETCH_OBJ)
	{
		$sth = $this->dbh->prepare($query);
		if(is_array($values))
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

	function get_rows($query,$values=null,$fetchType = FETCH_OBJ)
	{
		$sth = $this->dbh->prepare($query);
		if(is_array($values))
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
	
	function insert($tableName,$variables)
	{
		$columns = "";
		$values = "";
		
		if(sizeof($variables)>0)
		{
			foreach($variables as $col=>$val)
			{
				$columns .= "$col,";
				$values  .= is_numeric($col) ? "?," : ":$col,";
			}
			
			$columns = substr($columns,0,-1);
			$values = substr($values,0,-1);
			
			$query = sprintf("INSERT INTO %s (%s) VALUES (%s)",$tableName,$columns,$values);
			$sth = $this->dbh->prepare($query);
			
			if(is_array($variables))
			{
				foreach($variables as $col=>$val)
				{
					$col = is_numeric($col) ? ($col + 1) : ":$col";
					$sth->bindValue($col,$val);	
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
	
	function execute($query,$values = null)
	{
		$sth = $this->dbh->prepare($query);
		if(is_array($values))
		{
			foreach($values as $key=>$val)
			{
				$key = is_numeric($key) ? ($key + 1) : $key;
				$sth->bindValue($key,$val);	
			}
		}
		return $sth->execute();
	}
	
	function checkIfColumnExists($tableName,$columnName)
	{
		$query = "SHOW COLUMNS FROM $tableName";
		
		$sth = $this->dbh->prepare($query);
		$sth->execute();
		$columns = $sth->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($columns as $c)
		{
			if($c["Field"] == $columnName)
				return true;
		}
		
		return false;
	}
	
	function lastInsertId()
	{
		return $this->dbh->lastInsertId();
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
	}
}