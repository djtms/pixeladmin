<?php
/*
 * Author: Mehmet Hazar Artuner
 * Version: Beta 0.2
 * Date: 13.01.2012
 * 
 * 
 * Notes: 
 * rotateImage() fonksiyonu eklendi
 */


class PA_IMAGE_PROCESSOR
{
	public function getImageResolution($imageurl)
	{
		if(preg_match("/\.jpeg|\.jpg/i",$imageurl))
			$image = imagecreatefromjpeg($imageurl);
		else if(preg_match("/\.png/i",$imageurl))
			$image = imagecreatefrompng($imageurl);
		else if(preg_match("/\.gif/i",$imageurl))
			$image = imagecreatefromgif($imageurl);
		else
			return false;
			
		return (object) array("width"=>imagesx($image),"height"=>imagesy($image));
	}
	
	public function createThumb($sourceUrl, $targetUrl, $width, $height, $squeeze= false ,$proportion=true, $position="center center", $bg_color="fff")
	{
		$info = pathinfo($sourceUrl);
		
		// Kaynak Resmi Yükle
		if(preg_match("/jpeg|jpg/i",$info["extension"]))
			$source = imagecreatefromjpeg($sourceUrl);
		else if(preg_match("/png/i",$info["extension"]))
			$source = imagecreatefrompng($sourceUrl);
		else if(preg_match("/gif/i",$info["extension"]))
			$source = imagecreatefromgif($sourceUrl);
			
		// Çıktı olarak alacağımız "hedef resmi" yükle
		$target = imagecreatetruecolor($width,$height);
	
		// Arkaplan rengini ayarla
		$color = $this->fixColor($bg_color);
		$bg = imagecolorallocate($target, $color->red, $color->green, $color->blue);
		imagefill($target, 0, 0, $bg);
		
		// Ölçüleri hesapla
		$source_width = imagesx($source);
		$source_height = imagesy($source);
		$source_ratio = $source_width / $source_height;
		
		$target_width = $width;
		$target_height = $height;
		$target_ratio = $target_width / $target_height;
		
		///////////////////////////////////////////////////////////////////////////////////////
		
		$source_opt = (object) array("left"=>0,"top"=>0,"width"=>$source_width,"height"=>$source_height);
		$target_opt = (object) array("left"=>0,"top"=>0,"width"=>$target_width,"height"=>$target_height);

		if($squeeze && $proportion)
		{
			if($source_ratio > $target_ratio) // kaynak resim istenen resme göre geniş ise
			{
				$target_opt->height = $target_opt->width / $source_ratio;
			}
			else if($source_ratio < $target_ratio) // kaynak resim istenen resme göre  dar ise
			{
				$target_opt->width = $target_opt->height * $source_ratio;
			}	
		}
		else if($squeeze && !$proportion)
		{
			// Bu ihtimalde hiçbirşey yapma zaten varsayılan olarak bu olasılığa göre hesaplama yapıyor
		}
		else if(!$squeeze && $proportion)
		{
			if($source_ratio > $target_ratio) // kaynak resim istenen resme göre geniş ise
			{
				$target_opt->width = $target_opt->height * $source_ratio;
				
				if($target_opt->width > $width)
				{
					$pos = $this->calculateImagePosition($position, ($target_opt->width - $width), 0);
					$target_opt->left = $pos->left;
				}
			}
			else if($source_ratio < $target_ratio) // kaynak resim istenen resme göre  dar ise
			{
				$target_opt->height = $target_opt->width / $source_ratio;
				
				if($target_opt->height > $height)
				{
					$pos = $this->calculateImagePosition($position, 0, ($target_opt->height - $height));	
					$target_opt->top = $pos->top;
				}
			}
		}
		else if(!$squeeze && !$proportion)
		{
			if($source_opt->width > $target_opt->width)
				$source_opt->width = $target_opt->width;
			else 
				$target_opt->width = $source_opt->width;
			
			if($source_opt->height > $target_opt->height)
				$source_opt->height = $target_opt->height;
			else 
				$target_opt->height = $source_opt->height;
		}
		
		
		imagecopyresampled($target,$source,$target_opt->left,$target_opt->top,$source_opt->left,$source_opt->top,$target_opt->width,$target_opt->height,$source_opt->width,$source_opt->height);
		
		if(preg_match("/jpeg|jpg/i",$info["extension"]))
			imagejpeg($target, $targetUrl,100);
		else if(preg_match("/png/i",$info["extension"]))
			imagepng($target, $targetUrl);
		else if(preg_match("/gif/i",$info["extension"]))
			imagegif($target, $targetUrl);
			
		imagedestroy($source);
		imagedestroy($target);
		
		return true;
	}
	
	
	function rotateImage($sourceUrl, $targetUrl, $angle, $color = array(255, 255, 255, 127))
	{
		$info = pathinfo($sourceUrl);
		$target_info = pathinfo($targetUrl);

		// Kaynak Resmi Yükle
		if(preg_match("/jpeg|jpg/i",$info["extension"]))
			$source = imagecreatefromjpeg($sourceUrl);
		else if(preg_match("/png/i",$info["extension"]))
			$source = imagecreatefrompng($sourceUrl);
		else if(preg_match("/gif/i",$info["extension"]))
			$source = imagecreatefromgif($sourceUrl);
		
		// Resmin background ve alpha değerini oluştur
		$background_color = imagecolorallocatealpha($source, $color[0], $color[1], $color[2], $color[3]);

		// Resmi Rotate Et
		$source = imagerotate($source,$angle, $background_color);

		// Resmin Alpha değerini ata
		imagesavealpha($source, true);
		
		// Resmi uygun formatta kaydet
		if(preg_match("/jpeg|jpg/i",$target_info["extension"]))
			imagejpeg($source, $targetUrl);
		else if(preg_match("/png/i",$target_info["extension"]))
			imagepng($source, $targetUrl);
		else if(preg_match("/gif/i",$target_info["extension"]))
			imagegif($source, $targetUrl);
		
		imagedestroy($source);
		return true;
	}
	
	
	private function calculateImagePosition($image_position,$overflow_x,$overflow_y)
	{
		switch($image_position)
		{
			case("left center"):
				$calc_position = (object) array("left"=>0,"top"=>-$overflow_y / 2);
			break;
			
			case("left bottom"):
				$calc_position = (object) array("left"=>0,"top"=>-$overflow_y);
			break;
			
			case("right top"):
				$calc_position = (object) array("left"=>-$overflow_x,"top"=>0);
			break;
			
			case("right center"):
				$calc_position = (object) array("left"=>-$overflow_x,"top"=>-$overflow_y/2);
			break;
			
			case("right bottom"):
				$calc_position = (object) array("left"=>-$overflow_x,"top"=>-$overflow_y);
			break;
			
			case("center top"):
				$calc_position = (object) array("left"=>-$overflow_x/2,"top"=>0);
			break;
			
			case("center center"):
				$calc_position = (object) array("left"=>-$overflow_x/2,"top"=>-$overflow_y/2);
			break;
			
			case("center bottom"):
				$calc_position = (object) array("left"=>-$overflow_x/2,"top"=>-$overflow_y);
			break;
			
			default:
				$calc_position = (object) array("left"=>0,"top"=>0);
			break;
		}
		
		return $calc_position;
	}
	
	private function fixColor($color)
	{
		$colorLength = strlen($color);
		
		if($colorLength<3)
		{
			return (object) array("red"=>"0XFF","green"=>"0XFF","blue"=>"0XFF");
		}
		else if(($colorLength == 3) || ($colorLength < 6))
		{
			$red = substr($color, 0,1);
			$green = substr($color, 1,1);
			$blue = substr($color, 2,1);
			
			return (object) array("red"=>"0X".$red.$red,"green"=>"0X".$green.$green,"blue"=>"0X".$blue.$blue);
		}
		else if($colorLength == 6 )
		{
			$red = substr($color, 0,2);
			$green = substr($color, 2,2);
			$blue = substr($color, 4,2);
			
			return (object) array("red"=>"0X".$red,"green"=>"0X".$green,"blue"=>"0X".$blue);
		}
	}

}

