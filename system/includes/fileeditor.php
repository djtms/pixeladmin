<?php
if(in_admin):

$folder_image = $MODEL->DIRECTORY->selectSystemFileByFilename("folder");
$exclamation_image = $MODEL->DIRECTORY->selectSystemFileByFilename("exclamation");

setGlobal("folder_image", $folder_image);
setGlobal("exclamation_image", $exclamation_image);

function loadFileTree()
{
	global $MODEL;
	if(isset($MODEL->DIRECTORY))
		echo $MODEL->DIRECTORY->generateFileTreeHtmlByParentId(-1);
}

function listDirectoryTree()
{
	global $MODEL;
	global $uploadurl;
	
	$directory = $_POST['dir'];
	 
	if($uploadurl != $directory)
	{
		$parentDirectory =  $MODEL->DIRECTORY->selectDirectoryByDirectory(str_ireplace($uploadurl, "", $directory));
		$parent_id = $parentDirectory->directory_id;
	}
	else
		$parent_id = -1;
		
	$files = $MODEL->DIRECTORY->listDirectoriesByParentId($parent_id);
	
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
	global $MODEL;
	global $uploadurl;
	
	if($favs = $MODEL->DIRECTORY->listFavouritedDirectories())
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
	global $MODEL;
	global $uploadurl;
	
	$directory = $_POST["dir"];
	$status = $_POST["status"];
	$directory = preg_replace("/" . preg_quote($uploadurl,"/") . "/", "",$directory);

	if($MODEL->DIRECTORY->setDirectoryFavouriteStatus($directory,$status))
		echo "succeed";
	else
		echo "error";
}

function browseFiles()
{
	global $MODEL;
	global $uploadurl;
	
	$directory = $_POST["directory"];
	 
	if("" != $directory)
	{
		$parentDirectory =  $MODEL->DIRECTORY->selectDirectoryByDirectory(str_ireplace($uploadurl, "", $directory));
		$parent_id = $parentDirectory->directory_id;
	}
	else
		$parent_id = -1;
	
	$return->directories = $MODEL->DIRECTORY->listDirectoriesByParentId($parent_id);
	$return->files = $MODEL->DIRECTORY->listFilesByDirectory($directory);
	
	foreach($return->files as &$f)
	{
		if(!$f->browser_thumb = $MODEL->DIRECTORY->getThumbUrl($f->file_id, 123, 87, false, true, "center top", "FFFFFF"))
			$f->browser_thumb = "../upload/system/exclamation.jpg";
	}
	
	echo json_encode($return);
}

function checkDirectoryExists()
{
	global $MODEL;
	global $uploadurl;
	
	$directory = str_ireplace($uploadurl, "", $_POST["directory"]);
	
	if($MODEL->DIRECTORY->selectDirectoryByDirectory($directory))
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
	global $MODEL;
	global $uploadurl;
	
	$directory = str_ireplace($uploadurl, "", $_POST["parent_directory"]);
	
	if("" != $directory)
	{
		$parentDirectory =  $MODEL->DIRECTORY->selectDirectoryByDirectory(str_ireplace($uploadurl, "", $directory));
		$parent_id = $parentDirectory->directory_id;
	}
	else
		$parent_id = -1;
		
	if($MODEL->DIRECTORY->createDirectory("$directory{$_POST["dirname"]}/",$parent_id))
		echo "created"; // bu yazıyı değiştirme
	else
		echo "error_happened"; // bu yazıyı değiştirme
}


function selectFileInfo()
{
	global $MODEL;

	echo json_encode($MODEL->DIRECTORY->selectFileById($_POST["fileId"]));
}

function getBrowserThumb()
{
	global $MODEL;
	
	echo $MODEL->DIRECTORY->getThumbUrl($_POST["fileId"], 123, 87, false, true, "center top", "FFFFFF");
}

function getBrowserThumbInfo()
{
	global $MODEL;
	
	echo json_encode($MODEL->DIRECTORY->getThumbInfo($_POST["fileId"], 123, 87, false, true, "center top", "FFFFFF"));
}

function getFileDetailThumb()
{
	global $MODEL;
	
	echo $MODEL->DIRECTORY->getThumbUrl($_POST["fileId"], 390, 300, false, true, "center top", "FFFFFF");
}

function deleteFile()
{
	global $MODEL;
	
	if($MODEL->DIRECTORY->deleteFileByUrl($_POST["fileUrl"]))
		echo "deleted";
	else
		echo "error";	
}

function deleteFilesAndDirectories()
{
	global $MODEL;
	global $uploadurl;
	
	$fileUrls = (object)json_decode($_POST["fileurls"]);
	$error = false;
	
	foreach($fileUrls as $f)
	{
		if($f->id <= 0) // Dosya id'si -1 ise bu bir dizindir. bunu js ile belirliyoruz
		{
			if(!$MODEL->DIRECTORY->deleteDirectoryByDirectory($f->url))
				$error = true;
		}
		else 
		{
			if(!$MODEL->DIRECTORY->deleteFileByUrl($f->url))
				$error = true;
			else
				$MODEL->DIRECTORY->deleteFileThumbs($f->id);
		}
	}
	
	echo $error ? "error" : "deleted"; // bu yazıyı değiştirme
}

function updateFileInfo()
{
	global $MODEL;
	global $uploadurl;
	
	$fixedurl = preg_replace("/^" . preg_quote($uploadurl,"/") . "/", "", $_POST["url"]);
	$checkedFileId = $MODEL->DIRECTORY->checkFileExists($fixedurl);
	
	if(($checkedFileId > 0) && ($checkedFileId != $_POST["file_id"]))
	{
		echo json_encode(array("error"=>true,"message"=>"varolan bir dosya adı girdiniz, lütfen başka bir isim girin!"));
	}
	else if(!$MODEL->DIRECTORY->updateFileInfo($_POST["file_id"], $_POST["basename"], $_POST["filename"],$_POST["thumb_file_id"]))
		echo json_encode(array("error"=>true,"message"=>"bir hata oluştu, lütfen tekrar deneyin!"));
	else
		echo json_encode(array("error"=>false,"message"=>"başarıyla kaydedildi!"));
}

switch($_POST["action"])
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
}

endif;