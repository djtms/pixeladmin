<?php

class PA_THUMB
{
	public $baseDir;
	
	private $table;
	private $table_file;
	private $link_table;
	private $browser_width;
	private $browser_height;
	
	function PA_THUMB()
	{
		global $DB;
		global $common_admin_site;
		
		$this->baseDir = $common_admin_site;
		$this->table = $DB->tables->thumb;
		$this->table_file = $DB->tables->file;
		$this->link_table = $DB->tables->file_thumb;
	}
	
	
	public function getThumbUrl($file_id, $width, $height, $squeeze = false, $proportion = true, $position = "center center", $bg_color = "FFFFFF")
	{
		$thumb = $this->getThumbInfo($file_id, $width, $height, $squeeze, $proportion, $position, $bg_color);
		return $thumb->url;
	}
	
	public function getThumbInfo($file_id, $width, $height, $squeeze = false, $proportion = true, $position = "center center", $bg_color = "FFFFFF")
	{
		global $DB;
		global $thumbsurl;
		global $uploadurl;
		global $systemurl;
		
		$temp_thumbs_url = $this->baseDir . $thumbsurl;
		$temp_files_url = $this->baseDir . $uploadurl;
		
		// Thumbnail üretmek için kullanacağın dosyayı belirle.
		if(!$file = $DB->get_row("SELECT * FROM {$this->table_file} WHERE file_id=?",array($file_id)))
			return false; // Dosya bulunamadığı zaman geri dön.
		$thumb_file = ($file->thumb_file_id <= 0) ? $file : $DB->get_row("SELECT * FROM {$this->table_file} WHERE file_id=?",array($file->thumb_file_id));
		
		// Thumbnail'in özelliklerini belirle;
		$width = intval($width);
		$width = round(($width > 0) ? $width : ($height * ($thumb_file->width / $thumb_file->height)));
		$height = intval($height);
		$height = round(($height > 0) ? $height : ($width / ($thumb_file->width / $thumb_file->height)));
			
		$position = $this->fixCropPosition($position);
		
		// Thumbnail üretmek için kullanılacak kaynak dosyanın tam adresini belirle
		if($thumb_file->access_type == "system") // Eğer thumbnail dosyası sistem dosyası ise
			$source_file_full_url = $systemurl . $thumb_file->url;
		else
			$source_file_full_url = $uploadurl . $thumb_file->url;
		
		// Thumbnail'in daha önce üretilmiş olma ihtimaline karşılık arama yaparken kullanacağın, 
		// eğer üretilmemişse üretirken kullanacağın dosya adını belirle
		$thumb_filename = $thumb_file->file_id . "-" . $width . "-" . $height . "-" . ($squeeze ? "s-" : "") .($proportion ? "p-" : "") . ( $position . "-") . $bg_color ;
		$thumb_basename = $thumb_filename . "." . $thumb_file->extension;
		
		// Thumbnail in daha önce üretilmiş olup olmadığını kontrol et
		if($thumb = $DB->get_row("SELECT *,CONCAT('{$temp_thumbs_url}',url) AS url FROM {$this->table} WHERE basename=?",array($thumb_basename)))
		{
			$file->url = $temp_files_url . $file->url; // orjinal dosyanın path'ini düzeltiyoruz. Not: thumbnail'de olduğu gibi database'den çekerken CONCAT ile bağlama hata oluşuyor
			$thumb->owner = $file; 	// Thumbnail'i kullanan dosyanın bilgileri. Bu bilgileri "thumbs" tablosunda tutmadığım için bi önceki
									// satırdaki seçim işleminden sonra diziye eklemem gerekiyor.
			
			if($this->baseDir != "") // Eğer dosyayı başka bir hostta arıyorsak fonksiyonu burada sonlandırıyoruz çünkü başka
				return $thumb;		 // hosttaki bir dosyaya müdahale etme şansımız yok.
			
			if(file_exists($thumb->url))
			{
				return $thumb;
			}
			else if($this->generateThumbnailImage($source_file_full_url, $thumb->url, $width, $height, $squeeze, $proportion, $position, $bg_color))
			{
				return $thumb;
			}
			else
			{
				return false;
			}
		}
		else
		{
			if($this->baseDir != "") // Eğer dosyayı başka bir hostta arıyorsak fonksiyonu burada sonlandırıyoruz çünkü başka
				return false;		 // hosttaki bir dosyaya müdahale etme şansımız yok.
			
			$file->url = $temp_files_url . $file->url; // orjinal dosyanın path'ini düzeltiyoruz. Not: thumbnail'de olduğu gibi database'den çekerken CONCAT ile bağlama hata oluşuyor
			$thumb_full_url = $thumbsurl . $thumb_basename;
			if($this->generateThumbnailImage($source_file_full_url, $thumb_full_url, $width, $height, $squeeze, $proportion, $position, $bg_color))
			{
				$thumbData = array( "basename"=>$thumb_basename,"filename"=>$thumb_filename,"extension"=>$thumb_file->extension,
											"directory"=>"","url"=>$thumb_basename,	"width"=>$width,"height"=>$height,"squeeze"=>($squeeze ? 1 : -1),	
											"proportion"=>($proportion ? 1 : -1),"crop_position"=>$position,"bg_color"=>$bg_color);
					
				$DB->insert($this->table,$thumbData);
				$thumb_id = $DB->lastInsertId();
				$thumbData["thumb_id"] = $thumb_id;
				
				$DB->insert($this->link_table,array("file_id"=>$file_id,"thumb_id"=>$thumb_id));
				$thumbData["url"] = $thumbsurl . $thumbData["url"]; // buradaki değişkeni database den çekmeyip kendim ürettiğim için '$thumbsurl' değişkenini başa eklemem gerekiyor
				$thumbData["owner"] = $file; // Thumbnail'i kullanan dosyanın bilgileri. Bu bilgileri "thumbs" tablosunda tutmadığım için bi önceki
											 // satırdaki insert işleminden sonra diziye eklemem gerekiyor.
				
				return (object)$thumbData;
			}
			else
			{
				return false;
			}
		}
	}
	
	public function selectThumbById($thumb_id)
	{
		global $DB;
		
		return $DB->get_row("SELECT * FROM {$this->table} WHERE thumb_id=?",array($thumb_id));
	}
	
	public function deleteFileThumbs($file_id)
	{
		global $DB;
		global $thumbsurl;
		
		$query  = "SELECT * FROM {$this->link_table} AS lt ";
		$query .= "LEFT JOIN {$this->table} AS t ON lt.thumb_id=t.thumb_id ";
		$query .= "WHERE lt.file_id=?";
		
		$thumbs = $DB->get_rows($query,array($file_id));

		if(sizeof($thumbs) > 0)
		{
			foreach($thumbs as $t)
			{
				if(file_exists($thumbsurl . $t->url))
					unlink($thumbsurl . $t->url);
				
				$DB->execute("DELETE FROM {$this->table} WHERE thumb_id=?",array($t->thumb_id));
				$DB->execute("DELETE FROM {$this->link_table} WHERE file_id=? AND thumb_id=?",array($file_id, $t->thumb_id));
			}
		}
		
		return true;
	}
	
	private function generateThumbnailImage($existing_file_url,$target_url,$width, $height, $squeeze = false, $proportion = true, $position = "center top" ,$bg_color = "FFFFFF")
	{
		global $MODEL;
		
		return $MODEL->IMAGE_PROCESSOR->createThumb($existing_file_url, $target_url, $width, $height, $squeeze, $proportion, $position, $bg_color);
	}
	
	private function fixCropPosition($position)
	{
		$X = "left";
		$Y = "top";
		
		if(($position == "") || ($position == null) || !(preg_match("/[\s]+/", trim($position))))
		{
			return $X . "_" . $Y;
		}
		
		$position = strtolower($position);
		$position = trim($position);
		$position = preg_replace("/[\s]+/", " ", $position);
		$position_array = explode(" ", $position);
		
		
		if($position_array[0] == "right")
			$X = "right";
		else if($position_array[0] == "center")
			$X = "center";
			
		if($position_array[1] == "bottom")
			$Y = "bottom";
		else if($position_array[1] == "center")
			$Y = "center";
			
		return $X . "_" . $Y;
	}
}