<?php

class PA_THUMB extends DB
{
	public $baseDir;
	
	private $table;
	private $table_file;
	private $link_table;
	private $browser_width;
	private $browser_height;
	
	function PA_THUMB()
	{
		global $common_admin_site;
		parent::DB();
		
		$this->baseDir = $common_admin_site;
		$this->table = $this->tables->thumb;
		$this->table_file = $this->tables->file;
		$this->link_table = $this->tables->file_thumb;
	}
	
	
	public function getThumbUrl($file_id, $width, $height, $squeeze = false, $proportion = true, $position = "center center", $bg_color = "FFFFFF")
	{
		$thumb = $this->getThumbInfo($file_id, $width, $height, $squeeze, $proportion, $position, $bg_color);
		return $thumb->url;
	}
	
	public function getThumbInfo($file_id, $width, $height, $squeeze = false, $proportion = true, $position = "center center", $bg_color = "FFFFFF")
	{
		global $thumbsurl;
		global $uploadurl;
		global $systemurl;
		$temp_thumbs_url = $this->baseDir . $thumbsurl;
		$temp_files_url = $this->baseDir . $uploadurl;
		
		// Thumbnail üretmek için kullanacağın dosyayı belirle.
		if(!$file = $this->get_row("SELECT * FROM {$this->table_file} WHERE file_id=?",array($file_id)))
			return false; // Dosya bulunamadığı zaman geri dön.
		$thumb_file = ($file->thumb_file_id <= 0) ? $file : $this->get_row("SELECT * FROM {$this->table_file} WHERE file_id=?",array($file->thumb_file_id));
		
		//---------------------------------------------------------------------------------------------------------------
		// Önce Custom Crop yapılmış dosyayı ara, yok ise auto crop yap.
		//---------------------------------------------------------------------------------------------------------------
		$thumb_filename = $thumb_file->file_id . "-custom_crop-" . $width . "x" . $height;
		$thumb_basename = $thumb_filename . "." . $thumb_file->extension;
		
		if(!$squeeze)
		{
			$thumb_filename = $thumb_file->file_id . "-custom_crop-" . $width . "x" . $height;
			$thumb_basename = $thumb_filename . "." . $thumb_file->extension;
			
			if($thumb = $this->get_row("SELECT *, CONCAT('{$temp_thumbs_url}',url) AS url FROM {$this->table} WHERE basename=? AND crop_type='custom_crop'",array($thumb_basename)))
			{
				return $thumb;
			}
		}
		
		//---------------------------------------------------------------------------------------------------------------
		// Crop edilmemişse Thumnail'i Oluştur
		//---------------------------------------------------------------------------------------------------------------
		
		
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
		if($thumb = $this->get_row("SELECT *,CONCAT('{$temp_thumbs_url}',url) AS url FROM {$this->table} WHERE basename=?",array($thumb_basename)))
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
											"proportion"=>($proportion ? 1 : -1),"crop_position"=>$position,"bg_color"=>$bg_color, "crop_type"=>"auto_crop");
					
				$thumb_id = $this->insert($this->table,$thumbData);
				$thumbData["thumb_id"] = $thumb_id;
				
				$this->insert($this->link_table,array("file_id"=>$file_id,"thumb_id"=>$thumb_id));
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
		return $this->get_row("SELECT * FROM {$this->table} WHERE thumb_id=?",array($thumb_id));
	}
	
	public function deleteFileThumbs($file_id)
	{
		global $ADMIN;
		
		global $thumbsurl;
		
		// Delete Thumbnail File  -> Resim haricindeki dosyaların kendine ait gizli thumbnail dosyaları olabiliyor. O dosyaları silme işlemini burada yapıyoruz.
		$file = $ADMIN->FILE->selectFileById($file_id);
		$thumbnailFile = $ADMIN->FILE->selectFileById($file->thumb_file_id);
		
		if($thumbnailFile->access_type == "thumbnail")
		{
			$ADMIN->FILE->deleteFileById($thumbnailFile->file_id);
		}
		/////////////////////////////////////////////////////////////////////////
		
		$query  = "SELECT * FROM {$this->link_table} AS lt ";
		$query .= "LEFT JOIN {$this->table} AS t ON lt.thumb_id=t.thumb_id ";
		$query .= "WHERE lt.file_id=?";
		
		$thumbs = $this->get_rows($query,array($file_id));

		if(sizeof($thumbs) > 0)
		{
			foreach($thumbs as $t)
			{
				if(file_exists($thumbsurl . $t->url))
					unlink($thumbsurl . $t->url);
				
				$this->execute("DELETE FROM {$this->table} WHERE thumb_id=?",array($t->thumb_id));
				$this->execute("DELETE FROM {$this->link_table} WHERE file_id=? AND thumb_id=?",array($file_id, $t->thumb_id));
			}
		}
		
		return true;
	}
	
	function cropImage($file_id, $left, $top, $crop_width, $crop_height, $resize_width, $resize_height)
	{
		global $thumbsurl;
		global $uploadurl;
		global $systemurl;
		
		$temp_thumbs_url = $this->baseDir . $thumbsurl;
		$temp_files_url = $this->baseDir . $uploadurl;
		
		// Thumbnail üretmek için kullanacağın dosyayı belirle.
		if(!$file = $this->get_row("SELECT * FROM {$this->table_file} WHERE file_id=?",array($file_id)))
			return false; // Dosya bulunamadığı zaman geri dön.
		$thumb_file = ($file->thumb_file_id <= 0) ? $file : $this->get_row("SELECT * FROM {$this->table_file} WHERE file_id=?",array($file->thumb_file_id));
		
		// Thumbnail'in özelliklerini belirle;
		$width = intval($resize_width);
		$height = intval($resize_height);
			
		// Thumbnail üretmek için kullanılacak kaynak dosyanın tam adresini belirle
		$source_file_full_url = $uploadurl . $thumb_file->url;
		
		// Thumbnail'in daha önce üretilmiş olma ihtimaline karşılık arama yaparken kullanacağın, 
		// eğer üretilmemişse üretirken kullanacağın dosya adını belirle
		$thumb_filename = $thumb_file->file_id . "-custom_crop-" . $width . "x" . $height;
		$thumb_basename = $thumb_filename . "." . $thumb_file->extension;
		
		// Thumbnail in daha önce üretilmiş olup olmadığını kontrol et
		if($thumb = $this->get_row("SELECT *, CONCAT('{$temp_thumbs_url}',url) AS url FROM {$this->table} WHERE basename=? AND crop_type='custom_crop'",array($thumb_basename)))
		{
			$file->url = $temp_files_url . $file->url; // orjinal dosyanın path'ini düzeltiyoruz. Not: thumbnail'de olduğu gibi database'den çekerken CONCAT ile bağlama hata oluşuyor
			$thumb->owner = $file; 	// Thumbnail'i kullanan dosyanın bilgileri. Bu bilgileri "thumbs" tablosunda tutmadığım için bi önceki
									// satırdaki seçim işleminden sonra diziye eklemem gerekiyor.
			
			if($this->baseDir != "") // Eğer dosyayı başka bir hostta arıyorsak fonksiyonu burada sonlandırıyoruz çünkü başka
				return $thumb;		 // hosttaki bir dosyaya müdahale etme şansımız yok.
			
			if($this->generateCropImage($source_file_full_url, $thumb->url, $left, $top, $crop_width, $crop_height, $resize_width, $resize_height))
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
			if($this->generateCropImage($source_file_full_url, $thumb_full_url, $left, $top, $crop_width, $crop_height, $resize_width, $resize_height))
			{
				$thumbData = array( "basename"=>$thumb_basename,"filename"=>$thumb_filename,"extension"=>$thumb_file->extension,
											"directory"=>"","url"=>$thumb_basename,	"width"=>$width,"height"=>$height,"crop_type"=>"custom_crop");
				
				$thumb_id = $this->insert($this->table,$thumbData);
				$thumbData["thumb_id"] = $thumb_id;
				
				$this->insert($this->link_table,array("file_id"=>$file_id,"thumb_id"=>$thumb_id));
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
	
	function listCustomCroppedImages($file_id)
	{
		global $thumbsurl;
		$temp_thumbs_url = $this->baseDir . $thumbsurl;
		
		$query  = "SELECT t.*,CONCAT('{$temp_thumbs_url}',t.url) AS url FROM {$this->link_table} AS l ";
		$query .= "LEFT JOIN {$this->table} AS t ON l.thumb_id=t.thumb_id ";
		$query .= "WHERE t.crop_type='custom_crop' AND l.file_id=?";
		
		return $this->get_rows($query,array($file_id));
	}
	
	function getRetinaImageInfo($file_id, $width, $height, $squeeze = false, $proportion = true, $position = "center center", $bg_color = "FFFFFF")
	{
		global $thumbsurl;
		global $uploadurl;
		global $systemurl;
		$temp_thumbs_url = $this->baseDir . $thumbsurl;
		$temp_files_url = $this->baseDir . $uploadurl;
		$width = $width * 2;
		$height = $height * 2;
		$retinaImageNameSuffix = "@x2";
		
		// Thumbnail üretmek için kullanacağın dosyayı belirle.
		if(!$file = $this->get_row("SELECT * FROM {$this->table_file} WHERE file_id=?",array($file_id)))
			return false; // Dosya bulunamadığı zaman geri dön.
		$thumb_file = ($file->thumb_file_id <= 0) ? $file : $this->get_row("SELECT * FROM {$this->table_file} WHERE file_id=?",array($file->thumb_file_id));
		
		
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
		$thumb_filename = $thumb_file->file_id . "-" . $width . "-" . $height . "-" . ($squeeze ? "s-" : "") .($proportion ? "p-" : "") . ( $position . "-") . $bg_color . $retinaImageNameSuffix;
		$thumb_basename = $thumb_filename . "." . $thumb_file->extension;
		
		// Thumbnail in daha önce üretilmiş olup olmadığını kontrol et
		if($thumb = $this->get_row("SELECT *,CONCAT('{$temp_thumbs_url}',url) AS url FROM {$this->table} WHERE basename=?",array($thumb_basename)))
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
													"proportion"=>($proportion ? 1 : -1),"crop_position"=>$position,"bg_color"=>$bg_color, "crop_type"=>"auto_crop");
					
				$thumb_id = $this->insert($this->table,$thumbData);
				$thumbData["thumb_id"] = $thumb_id;
		
				$this->insert($this->link_table,array("file_id"=>$file_id,"thumb_id"=>$thumb_id));
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
	
	function getMaskedImageInfo($mask_image_path, $file_id, $width, $height, $squeeze = false, $proportion = true, $position = "center center", $bg_color = "FFFFFF")
	{
		global $thumbsurl;
		global $uploadurl;
		global $systemurl;
		$temp_thumbs_url = $this->baseDir . $thumbsurl;
		$temp_files_url = $this->baseDir . $uploadurl;
		$maskedImageNameSuffix = "_masked";
	
		// Thumbnail üretmek için kullanacağın dosyayı belirle.
		if(!$file = $this->get_row("SELECT * FROM {$this->table_file} WHERE file_id=?",array($file_id)))
			return false; // Dosya bulunamadığı zaman geri dön.
		$thumb_file = ($file->thumb_file_id <= 0) ? $file : $this->get_row("SELECT * FROM {$this->table_file} WHERE file_id=?",array($file->thumb_file_id));
	
	
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
		$thumb_filename = $thumb_file->file_id . "-" . $width . "-" . $height . "-" . ($squeeze ? "s-" : "") .($proportion ? "p-" : "") . ( $position . "-") . $bg_color . $maskedImageNameSuffix;
		$thumb_basename = $thumb_filename . "." . "png"; //$thumb_file->extension;
	
		// Thumbnail in daha önce üretilmiş olup olmadığını kontrol et
		if($thumb = $this->get_row("SELECT *,CONCAT('{$temp_thumbs_url}',url) AS url FROM {$this->table} WHERE basename=?",array($thumb_basename)))
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
			else if($this->generateMaskedImage($mask_image_path, $source_file_full_url, $thumb->url, $width, $height, $squeeze, $proportion, $position, $bg_color))
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
			if($this->generateMaskedImage($mask_image_path, $source_file_full_url, $thumb_full_url, $width, $height, $squeeze, $proportion, $position, $bg_color))
			{
				$thumbData = array( "basename"=>$thumb_basename,"filename"=>$thumb_filename,"extension"=>$thumb_file->extension,
														"directory"=>"","url"=>$thumb_basename,	"width"=>$width,"height"=>$height,"squeeze"=>($squeeze ? 1 : -1),	
														"proportion"=>($proportion ? 1 : -1),"crop_position"=>$position,"bg_color"=>$bg_color, "crop_type"=>"auto_crop");
					
				$thumb_id = $this->insert($this->table,$thumbData);
				$thumbData["thumb_id"] = $thumb_id;
	
				$this->insert($this->link_table,array("file_id"=>$file_id,"thumb_id"=>$thumb_id));
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
	
	//--------------------------------------------------------------------------------------------------------------------------------------------------
	// PRIVATE FUNCTIONS
	//--------------------------------------------------------------------------------------------------------------------------------------------------
	private function generateThumbnailImage($existing_file_url, $target_url, $width, $height, $squeeze = false, $proportion = true, $position = "center top" ,$bg_color = "FFFFFF")
	{
		global $ADMIN;
		
		if(!file_exists($target_url))
		{
			$ADMIN->IMAGE_PROCESSOR->load($existing_file_url);
			if($squeeze)
			{
				$ADMIN->IMAGE_PROCESSOR->resize($width, $height, $proportion, $position, $bg_color);
			}
			else
			{
				$ADMIN->IMAGE_PROCESSOR->autoCrop($width, $height, $position);
			}
			
			return $ADMIN->IMAGE_PROCESSOR->save($target_url);
		}
		else
			return true;
	}
	
	private function generateMaskedImage($mask_image_path, $existing_file_url, $target_url, $width, $height, $squeeze = false, $proportion = true, $position = "center top" ,$bg_color = "FFFFFF")
	{
		global $ADMIN;
		
		if(!file_exists($target_url))
		{
			$ADMIN->IMAGE_PROCESSOR->load($existing_file_url);
			if($squeeze)
			{
				$ADMIN->IMAGE_PROCESSOR->resize($width, $height, $proportion, $position, $bg_color);
			}
			else
			{
				$ADMIN->IMAGE_PROCESSOR->autoCrop($width, $height, $position);
			}
			
			$ADMIN->IMAGE_PROCESSOR->mask($mask_image_path);
				
			return $ADMIN->IMAGE_PROCESSOR->save($target_url);
		}
		else
			return true;
	}
	
	private function generateCropImage($source_url, $target_url, $left, $top, $crop_width, $crop_height, $resize_width, $resize_height)
	{
		global $ADMIN;
		return $ADMIN->IMAGE_PROCESSOR->load($source_url) &&
		$ADMIN->IMAGE_PROCESSOR->crop($crop_width, $crop_height, $left, $top) &&
		$ADMIN->IMAGE_PROCESSOR->resize($resize_width, $resize_height, true) &&
		$ADMIN->IMAGE_PROCESSOR->save($target_url);
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