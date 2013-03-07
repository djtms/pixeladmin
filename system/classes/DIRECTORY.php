<?php
class PA_DIRECTORY extends PA_FILE
{
	private $table;
	private $table_file;
	
	function PA_DIRECTORY()
	{
		parent::PA_FILE();
		
		$this->table = $this->tables->directory;
		$this->table_file = $this->tables->file;
	}
	
	function createDirectory($name, $parent_id=-1){
		global $uploadurl;
		
		if(!$this->selectDirectoryByNameAndParent($parent_id, $name))
		{
			// database e kaydedilecek şekilde dizin bilgisini hesapla
			if($parent_directory = $this->selectDirectoryById($parent_id)) // Üst dizin varsa 
				$directory = preg_replace("/^" . preg_quote($uploadurl, "/") . "/", "", $parent_directory->directory) . $name . "/";
			else
				$directory = $name . "/";
			
			// dosyayı oluşturmada kullanmak için tam dizin bilgisini hespla
			$full_path = $uploadurl . $directory;
			
			// dizinleri oluştur ve yetkilerini ata
			if(!file_exists($full_path))
			{
				if(!mkdir($full_path, 0755)) // dosyayı oluştururken mode değeri desteklenmediği için aşağıda tekrardan chmod işlemi yapıyoruz
					return false;
				
				// Bazen chmod yanlış atanıyor, onu sağlama almak tekrar chmod değişikliği yaptırıyoruz.
				if(!chmod($full_path, 0755))
					return false;
			}
			
			// database'e kaydet ve sonucu döndür
			return $this->insert($this->table,array("parent_id"=>$parent_id,"name"=>$name,"directory"=>$directory));
		}
		else
			return false;
	}	
	
	function updateDirectory($directory_id, $new_name){
		global $uploadurl;

        $directory = $this->selectDirectoryById($directory_id);
        $selected_directory = $this->selectDirectoryByNameAndParent($directory->parent_id, $new_name);

		// Eğer böyle bir dizin yoksa işlemi hatalı olarak sonlandır
		if(!$directory){
			return false;
		}
		else if(($directory->name == $new_name)){// Eğer ismi değişmemişse işlemi sonlandır
			return true;
		}
        else if(($selected_directory->directory_id > 0) && ($selected_directory->directory_id != $directory_id)){ // eğer bulunan dizin başka bir dizine aitse farklı isim kullanması gerekir
            return false;
        }
		else { // Eğer yeni isim, içinde bulunduğu dizinde yok ise güncelleme işemini gerçekleştir
			$error = false;
			
			// Önce database de olmayan ve olmaması gereken ama bizim kolaylık olsun diye database den seçim esnasında CONCAT() ile eklediğimiz
			// uploadurl'i temizle. Çünkü database de uploadurl bu şekilde ekli olarak kayıtlı değil ve aramayı doğru yapabilmek için uploadurl'i
			// temizlememiz gerekiyor.
			$search_directory = preg_replace(("/^" . preg_quote($uploadurl, "/") . "/"), "", $directory->directory);
			
			// Şimdi uploadurl değeri temizlenmiş url içinden directory adını yenisiyle değiştir.
			$replace_directory = preg_replace(("/" . preg_quote($directory->name, "/") . "\/?$/"), $new_name . "/", $search_directory);

			// varsa güncellenecek dizinin altında bulunan dosyaların url'lerini güncelle
			if($files = $this->get_rows("SELECT file_id, url FROM {$this->table_file} WHERE directory_id > 0 AND url LIKE ? '%'",array($search_directory)))
			{
				$file_count = sizeof($files);
					
				for($i=0; $i<$file_count; $i++){
					$url = preg_replace(("/^" . preg_quote($search_directory , "/") . "/"), $replace_directory, $files[$i]->url);
					if(!$this->execute("UPDATE {$this->table_file} SET url=? WHERE file_id=?", array($url, $files[$i]->file_id)))
						$error = true;
				}	
			}
			
			// varsa güncellenecek dizinin altındaki tüm dizinlerin directory değerini güncelle
			if($directories = $this->get_rows("SELECT directory_id, directory FROM {$this->table} WHERE parent_id > 0 AND directory LIKE ? '%'", array($search_directory)))
			{
				$directory_count = sizeof($directories);
				
				for($i=0; $i<$directory_count; $i++){
					$new_directory = preg_replace(("/^" . preg_quote($search_directory, "/") . "/"), $replace_directory, $directories[$i]->directory);
					if(!$this->execute("UPDATE {$this->table} SET directory=? WHERE directory_id=?", array($new_directory, $directories[$i]->directory_id)))
						$error = true;
				}
			}
			
			// Son olarak hata yok ise istenen dizini güncelle
			if(!$error){
				if(rename($directory->directory, $uploadurl . $replace_directory)){
					return $this->execute("UPDATE {$this->table} SET name=?, directory=? WHERE directory_id=?", array($new_name, $replace_directory, $directory_id));
				}
				else{
					return false;
				}
			}
			else
				return false;
		}
	}
	
	function selectDirectoryById($directory_id)
	{
		global $uploadurl;
		return $this->get_row("SELECT *, CONCAT('$uploadurl', directory) AS directory FROM {$this->table} WHERE directory_id=?",array($directory_id));
	}
	
	function selectDirectoryByNameAndParent($parent_id, $name)
	{
		global $uploadurl;
		return $this->get_row("SELECT *, CONCAT('$uploadurl', directory) AS directory FROM {$this->table} WHERE parent_id=? AND name=?",array($parent_id, $name));
	}
	
	function listDirectoriesByParentId($parent_id)
	{
		global $uploadurl;
		return $this->get_rows("SELECT *, CONCAT('$uploadurl', directory) AS directory FROM {$this->table} WHERE parent_id=?",array($parent_id));
	}
	
	function listFavouritedDirectories()
	{
		global $uploadurl;
		return $this->get_rows("SELECT *, CONCAT('$uploadurl', directory) AS directory FROM {$this->table} WHERE is_favourite>0",null);
	}
	
	function setDirectoryFavouriteStatus($directory_id, $status = 1)
	{
		return $this->execute("UPDATE {$this->table} SET is_favourite=? WHERE directory_id=?", array($status, $directory_id));
	}
	
	function generateFileTreeHtmlByParentId($parent_id)
	{
		$dirs = $this->get_rows("SELECT * FROM {$this->table} WHERE parent_id=?", array($parent_id));
		
		$dirHtml = '<ul class="fileTree">';
		foreach($dirs as $d)
		{
			$dirHtml .= '<li class="%s" directory_id="' . $d->directory_id . '">';
			$dirHtml .= '<icon></icon><span class="name">' . $d->name . '</span>';
			if($subdirs = $this->get_rows("SELECT * FROM {$this->table} WHERE parent_id=?",array($d->directory_id)))
			{
				$empty = "";
				$dirHtml .= $this->generateFileTreeHtmlByParentId($d->directory_id);
			}
			else
				$empty = "empty";
			$dirHtml .= '</li>';
			
			$dirHtml = sprintf($dirHtml,$empty);
			
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
	function deleteDirectory($directory_id)
	{
		global $uploadurl;
		
		$d = $this->selectDirectoryById($directory_id);
		
		if($this->deleteDirectoryCompletely($d->directory)) // dizin içindeki tüm dosya ve klasörleri sil
		{
			$d->directory = preg_replace(("/^" . preg_quote($uploadurl, "/") . "/"), "", $d->directory);
			
			// Dosyaların thumbnaillerini sil
			if($files = $this->get_rows("SELECT * FROM {$this->table_file} WHERE url LIKE ? '%'",array($d->directory))){
				$count = sizeof($files);
				
				for($i=0; $i<$count; $i++){
					$this->deleteFileThumbs($files[$i]->file_id);
				}
			}
			
			// Dosya ve dizin bilgilerini database den sil
			if(!$this->execute("DELETE FROM {$this->table_file} WHERE access_type != 'system' AND url LIKE ? '%'",array($d->directory)) ||
				!$this->execute("DELETE FROM {$this->table} WHERE directory LIKE ? '%'",array($d->directory)))
			{
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * 
	 * girilen dizini içerikleri ile birlikte siler, herhangi bir database işlemi yapmar, işlemi sadece dosya bazında gerçekleştirir
	 * @param string $directory
	 */
	private function deleteDirectoryCompletely($directory)
	{
		if(!file_exists($directory))
		{
			return true;
		}
		else if($contents = scandir($directory))
		{
			$count = sizeof($contents);
			for($i=0; $i<$count; $i++){
				$content_name = $contents[$i];
				$content_path = $directory . $content_name;
				
				if(($content_name != ".") && ($content_name != "..")){
					if(is_dir($content_path)){
						$this->deleteDirectoryCompletely($content_path.'/');
					}
					else if(file_exists($content_path)){
						unlink($content_path);
					}
				}
			}
			
			return rmdir($directory);
		}
	}
}