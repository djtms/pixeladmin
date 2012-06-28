<?php
if(in_admin):

$folder_image = $ADMIN->DIRECTORY->selectSystemFileByFilename("folder");
$exclamation_image = $ADMIN->DIRECTORY->selectSystemFileByFilename("exclamation");

setGlobal("folder_image", $folder_image);
setGlobal("exclamation_image", $exclamation_image);
setGlobal("predefinedCropResoluions", get_option("admin_predefinedCropResoluions"));

function loadFileTree()
{
	global $ADMIN;
	if(isset($ADMIN->DIRECTORY))
		echo $ADMIN->DIRECTORY->generateFileTreeHtmlByParentId(-1);
}

function listDirectoryTree()
{
	global $ADMIN;
	global $uploadurl;
	
	$directory = $_POST['dir'];
	 
	if($uploadurl != $directory)
	{
		$parentDirectory =  $ADMIN->DIRECTORY->selectDirectoryByDirectory(str_ireplace($uploadurl, "", $directory));
		$parent_id = $parentDirectory->directory_id;
	}
	else
		$parent_id = -1;
		
	$files = $ADMIN->DIRECTORY->listDirectoriesByParentId($parent_id);
	
	echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
	foreach( $files as $file ) {
		if( file_exists($directory . $file->name) && is_dir($directory . $file->name)) {
			echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($directory . $file->name) . "/\">" . $file->name . "</a></li>";
		}
	}
	echo "</ul>";	
}

function listFavouritedDirectories()
{
	global $ADMIN;
	global $uploadurl;
	
	if($favs = $ADMIN->DIRECTORY->listFavouritedDirectories())
	{
		foreach($favs as $f)
		{
			$favsHtmlList .= '<a href="' . $f->directory . '">' . $f->name . '</a>';
		}
	}
	
	echo $favsHtmlList;
}

function setFavouriteStatus()
{
	global $ADMIN;
	global $uploadurl;
	
	$directory = $_POST["dir"];
	$status = $_POST["status"];
	$directory = preg_replace("/" . preg_quote($uploadurl,"/") . "/", "",$directory);

	if($ADMIN->DIRECTORY->setDirectoryFavouriteStatus($directory,$status))
		echo "succeed";
	else
		echo "error";
}

function browseFiles()
{
	global $ADMIN;
	global $uploadurl;
	
	$directory = $_POST["directory"];
	 
	if("" != $directory)
	{
		$parentDirectory =  $ADMIN->DIRECTORY->selectDirectoryByDirectory(str_ireplace($uploadurl, "", $directory));
		$parent_id = $parentDirectory->directory_id;
	}
	else
		$parent_id = -1;
	
	$return->directories = $ADMIN->DIRECTORY->listDirectoriesByParentId($parent_id);
	$return->files = $ADMIN->DIRECTORY->listFilesByDirectory($directory);
	$length = sizeof($return->files);
	
	for($i=0; $i<$length; $i++)
	{
		if(!$return->files[$i]->browser_thumb = $ADMIN->DIRECTORY->getThumbUrl($return->files[$i]->file_id, 123, 87, false, true, "center top", "FFFFFF"))
			$return->files[$i]->browser_thumb = "../upload/system/exclamation.jpg";
	}

	echo json_encode($return);
}

function checkDirectoryExists()
{
	global $ADMIN;
	global $uploadurl;
	
	$directory = str_ireplace($uploadurl, "", $_POST["directory"]);
	
	if($ADMIN->DIRECTORY->selectDirectoryByDirectory($directory))
	{
		echo "exists";
	}
	else
	{
		echo "notexists";
	}
}

function createNewDirectory()
{
	global $ADMIN;
	global $uploadurl;
	
	$directory = str_ireplace($uploadurl, "", $_POST["parent_directory"]);
	
	if("" != $directory)
	{
		$parentDirectory =  $ADMIN->DIRECTORY->selectDirectoryByDirectory(str_ireplace($uploadurl, "", $directory));
		$parent_id = $parentDirectory->directory_id;
	}
	else
		$parent_id = -1;
		
	if($ADMIN->DIRECTORY->createDirectory("$directory{$_POST["dirname"]}/",$parent_id))
		echo "created"; // bu yazıyı değiştirme
	else
		echo "error_happened"; // bu yazıyı değiştirme
}


function selectFileInfo()
{
	global $ADMIN;

	echo json_encode($ADMIN->DIRECTORY->selectFileById($_POST["fileId"]));
}

function getBrowserThumb()
{
	global $ADMIN;
	
	echo $ADMIN->DIRECTORY->getThumbUrl($_POST["fileId"], 123, 87, false, true, "center top", "FFFFFF");
}

function getBrowserThumbInfo()
{
	global $ADMIN;
	
	echo json_encode($ADMIN->DIRECTORY->getThumbInfo($_POST["fileId"], 123, 87, false, true, "center top", "FFFFFF"));
}

function getFileDetailThumb()
{
	global $ADMIN;
	
	$thumb = $ADMIN->DIRECTORY->getThumbInfo($_POST["fileId"], 420, 350, false, true, "center top", "FFFFFF");
	
	echo json_encode(array("thumb_url"=>$thumb->url, "thumb_file_id"=>$thumb->owner->thumb_file_id));
}

function deleteFile()
{
	global $ADMIN;
	
	if($ADMIN->FILE->deleteFileByUrl($_POST["fileUrl"]))
		echo "deleted";
	else
		echo "error";	
}

function deleteFilesAndDirectories()
{
	global $ADMIN;
	global $uploadurl;
	
	$fileUrls = (object)json_decode($_POST["fileurls"]);
	$error = false;
	
	foreach($fileUrls as $f)
	{
		if($f->id <= 0) // Dosya id'si -1 ise bu bir dizindir. bunu js ile belirliyoruz
		{
			if(!$ADMIN->DIRECTORY->deleteDirectoryByDirectory($f->url))
				$error = true;
		}
		else 
		{
			if(!$ADMIN->DIRECTORY->deleteFileByUrl($f->url))
				$error = true;
			else
				$ADMIN->DIRECTORY->deleteFileThumbs($f->id);
		}
	}
	
	echo $error ? "error" : "deleted"; // bu yazıyı değiştirme
}

function updateFileInfo()
{
	global $ADMIN;
	global $uploadurl;
	
	$fixedurl = preg_replace("/^" . preg_quote($uploadurl,"/") . "/", "", $_POST["url"]);
	$checkedFileId = $ADMIN->DIRECTORY->checkFileExists($fixedurl);
	
	if(($checkedFileId > 0) && ($checkedFileId != $_POST["file_id"]))
	{
		echo json_encode(array("error"=>true,"message"=>"varolan bir dosya adı girdiniz, lütfen başka bir isim girin!"));
	}
	else if(!$ADMIN->DIRECTORY->updateFileInfo($_POST["file_id"], $_POST["basename"], $_POST["filename"],$_POST["thumb_file_id"]))
		echo json_encode(array("error"=>true,"message"=>"bir hata oluştu, lütfen tekrar deneyin!"));
	else
		echo json_encode(array("error"=>false,"message"=>"başarıyla kaydedildi!"));
}

function cropImage()
{
	global $ADMIN;
	
	extract($_POST, EXTR_SKIP);
	
	if($ADMIN->THUMB->cropImage($file_id, $left, $top, $crop_width, $crop_height, $resize_width, $resize_height))
		echo json_encode(array("error"=>false));
	else
		echo json_encode(array("error"=>true));
}

function listCustomCroppedImages()
{
	global $ADMIN;
	
	if($thumbs = $ADMIN->THUMB->listCustomCroppedImages($_POST["file_id"]))
	{
		$list_thumb_url = getThumbImage($_POST["file_id"], 98, 78, false);
		echo json_encode(array("error"=>false, "data"=>$thumbs, "list_thumb_url"=>$list_thumb_url));
	}
	else
		echo json_encode(array("error"=>true, "data"=>"error happened!"));
}

switch($_POST["admin_action"])
{
	case("listDirectoryTree")			:	listDirectoryTree();			exit;
	case("browseFiles")					:	browseFiles();					exit;
	case("checkDirectoryExists")		:	checkDirectoryExists();			exit;
	case("newDirectory")				:	createNewDirectory();			exit;
	case("listFavouritedDirectories")	:	listFavouritedDirectories();	exit;
	case("setFavouriteStatus")			:	setFavouriteStatus();			exit;
	case("loadFileTree")				:	loadFileTree();					exit;
	case("deleteFilesAndDirectories")	:	deleteFilesAndDirectories();	exit;
	case("getBrowserThumb")				:	getBrowserThumb();				exit;
	case("getBrowserThumbInfo")			:	getBrowserThumbInfo();			exit;
	case("getFileDetailThumb")			:	getFileDetailThumb();			exit;
	case("deleteFile")					:	deleteFile();					exit;
	case("selectFileInfo")				:	selectFileInfo();				exit;
	case("updateFileInfo")				:	updateFileInfo();				exit;
	case("cropImage")					:	cropImage();					exit;
	case("listCustomCroppedImages")		:	listCustomCroppedImages();		exit;
}

endif;