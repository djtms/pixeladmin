<?php
class PA_DIRECTORY extends PA_FILE
{
	private $table;
	private $table_file;
	
	function PA_DIRECTORY()
	{
		parent::PA_FILE();

		global $DB;
		$this->table = $DB->tables->directory;
		$this->table_file = $DB->tables->file;
	}
	
	function createDirectory($directory,$parent_directory_id=null)
	{
		global $DB;
		global $uploadurl;
		
		$full_directory = $uploadurl . $directory;
		
		if(!file_exists($full_directory))
			mkdir($full_directory,"0777");
		
		if(!$this->selectDirectoryByDirectory($directory))
		{
			$parent_directory_id = $parent_directory_id == null ? -1 : $parent_directory_id;
			
			if(!$DB->insert($this->table,array("parent_id"=>$parent_directory_id,"name"=>basename($directory),"directory"=>$directory)))
				return false;
			else
				return true;
		}
		else
			return true;
	}
	
	function selectDirectoryById($directory_id)
	{
		global $DB;
		
		return $DB->get_row("SELECT * FROM {$this->table} WHERE directory_id=?",array($directory_id));
	}
	
	function listDirectoriesByParentId($parent_id)
	{
		global $DB;
	
		return $DB->get_rows("SELECT * FROM {$this->table} WHERE parent_id=?",array($parent_id));
	}
	
	function selectDirectoryByDirectory($directory)
	{
		global $DB;
		
		return $DB->get_row("SELECT * FROM {$this->table} WHERE directory=?",array($directory));
	}
	
	function listFavouritedDirectories()
	{
		global $DB;
		
		return $DB->get_rows("SELECT * FROM {$this->table} WHERE is_favourite>0",null);
	}
	
	function setDirectoryFavouriteStatus($directory,$status = 1)
	{
		global $DB;
		
		return $DB->execute("UPDATE {$this->table} SET is_favourite=? WHERE directory=?",array($status,$directory));
	}
	
	function generateFileTreeHtmlByParentId($parent_id)
	{
		global $DB;
		global $uploadurl;
		
		$dirs = $DB->get_rows("SELECT * FROM {$this->table} WHERE parent_id=?",array($parent_id));
		
		$dirHtml = '<ul class="fileTree">';
		foreach($dirs as $d)
		{
			$dirHtml .= '<li class="directory collapsed %s">';
			$dirHtml .= '<a href="' . $d->directory . '" ' . ($d->is_favourite > 0 ? 'class="favourite"' : "") . '>' . $d->name . '</a>';
			if($subdirs = $DB->get_rows("SELECT * FROM {$this->table} WHERE parent_id=?",array($d->directory_id)))
			{
				$single = "";
				$dirHtml .= $this->generateFileTreeHtmlByParentId($d->directory_id);
			}
			else
				$single = "single";
			$dirHtml .= '</li>';
			
			$dirHtml = sprintf($dirHtml,$single);
			
		}
		$dirHtml .= '</ul>';
		
		return $dirHtml;
	}
	
	/**
	 * 
	 * dizin silme işlemi esnasında o dizinin içinde bulunan tüm dosya ve klasörlerin ve 
	 * onların alt dosyalarının silinebilmesi için kullanılır.
	 * 
	 * */
	function deleteDirectoryByDirectory($directory)
	{
		global $DB;
		global $uploadurl;
		
		if($this->deleteDirectory($directory))
		{
			if(!$DB->execute("DELETE FROM {$this->table_file} WHERE directory LIKE ? '%'",array($directory)) ||
				!$DB->execute("DELETE FROM {$this->table} WHERE directory LIKE ? '%'",array($directory)))
			{
				echo "Database den silinemedi!";
				return false;
			}
			else
				return true;
		}
		else
		{
			return true;		
		}
	}
	
	private function deleteDirectory($dir)
	{
		global $uploadurl;
		
		$fulldir = $uploadurl . $dir;
		
		if($fulldir == $uploadurl) // Ana upload dizinini silmemesi için önlem alıyoruz
		{
			return false;	
		}

		if(!file_exists($fulldir))
			return true;
		else if($handle = opendir($fulldir))
		{
			$array = array();
		
		    while ($file = readdir($handle)) 
		    {
		        if($file != "." && $file != "..") 
		        {
					if(is_dir($fulldir.$file))
					{
						if(sizeof(scandir($fulldir.$file)) > 2)
							$this->deleteDirectory($dir.$file.'/');
						else
						{
							rmdir($fulldir.$file);
						}
					}
					else
					{
						$this->deleteFileByUrl($dir.$file);
					}
		        }
		    }
		    closedir($handle);
			return rmdir($fulldir);
		}
	}
}