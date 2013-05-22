<?php

function generateThumbBase64($fileUrl,$width=129,$height=-1){
	$info = pathinfo($fileUrl);
	
	if(preg_match("/jpeg|jpg/i",$info["extension"]))
		$image = imagecreatefromjpeg($fileUrl);
	else if(preg_match("/png/i",$info["extension"]))
		$image = imagecreatefrompng($fileUrl);
	else if(preg_match("/gif/i",$info["extension"]))
		$image = imagecreatefromgif($fileUrl);
		
		
	$ix = imagesx($image);
	$iy = imagesy($image);
	
	
	$calcHeight =  round(($width/$ix) * $iy);
	
	if(($height > 0) && ($calcHeight < $height)){
		$sourceWidth = $ix;
		$sourceHeight = $iy;
		$destinationWidth = $width;
		$destinationHeight = $height;
		
		$ratio = $height / $calcHeight;
		
		$width = $width * $ratio;
		$calcHeight = $height;
	}

	$newImage = imagecreatetruecolor($width,$calcHeight);
	
	$tx = imagesx($newImage);
	$ty = imagesy($newImage);
	
	
	imagecopyresampled($newImage,$image,0,0,0,0,$tx,$ty,$ix,$iy);
	
	ob_start(); // Çıktıyı hemen göndermek yerine tamponlama yapıp tutuyoruz
	
		if(preg_match("/jpeg|jpg/i",$info["extension"]))
			imagejpeg($newImage, null,100);
		else if(preg_match("/png/i",$info["extension"]))
			imagepng($newImage, null);
		else if(preg_match("/gif/i",$info["extension"]))
			imagegif($newImage, null, 100);
			
		$buffered_image = ob_get_contents();
		$attachment = chunk_split(base64_encode($buffered_image));
	
	ob_clean(); // Tuttuğumuz çıktıyı işledikten sonra bırakıyoruz
		
	imagedestroy($image);
	imagedestroy($newImage);
	return 'data:image/' . $info["extension"] . ';base64,' . $attachment;
}

function getThumbImage($file_id, $width, $height, $squeeze = true, $proportion = true, $position = "center center", $bg_color = "FFFFFF"){
	global $ADMIN;
	
	if($thumb = $ADMIN->DIRECTORY->getThumbUrl($file_id, $width, $height, $squeeze, $proportion, $position, $bg_color))
		return $thumb;
	else
		return false;
}

function getThumbInfo($file_id, $width, $height, $squeeze=true, $proportion = true, $position = "center center", $bg_color = "FFFFFF"){
	global $ADMIN;
	
	return $ADMIN->DIRECTORY->getThumbInfo($file_id, $width, $height, $squeeze, $proportion, $position, $bg_color);
}

function getRetinaImage($file_id, $width, $height, $squeeze = true, $proportion = true, $position = "center center", $bg_color = "FFFFFF"){
	global $ADMIN;

    if($retina = $ADMIN->THUMB->getRetinaImageInfo($file_id, $width, $height, $squeeze, $proportion, $position, $bg_color))
        return $retina->url;
    else
        return false;
}

function getMaskedImage($mask_image_path, $file_id, $width, $height, $squeeze = true, $proportion = true, $position = "center center", $bg_color = "FFFFFF"){
	global $ADMIN;
	
	$masked_image = $ADMIN->THUMB->getMaskedImageInfo($mask_image_path, $file_id, $width, $height, $squeeze, $proportion, $position, $bg_color);
	return $masked_image->url;
}

function getMaskedImageInfo($mask_image_path, $file_id, $width, $height, $squeeze = true, $proportion = true, $position = "center center", $bg_color = "FFFFFF"){
	global $ADMIN;

	return $ADMIN->THUMB->getMaskedImageInfo($mask_image_path, $file_id, $width, $height, $squeeze, $proportion, $position, $bg_color);
}