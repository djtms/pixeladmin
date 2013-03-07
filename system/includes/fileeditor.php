<?php
if(in_admin)
{
	if($_POST["admin_action"] == "loadFileEditorItems"){
		$ADMIN->FILE_EDITOR->loadFileEditorItems($_POST["directory_id"]);
	}
	else if($_POST["admin_action"] == "deleteFileEditorItems"){
		$ADMIN->FILE_EDITOR->deleteFileEditorItems(json_decode($_POST["items"]));
	}
	else if($_POST["admin_action"] == "uploadFile"){
		$ADMIN->FILE_EDITOR->uploadFile($_FILES["file"]);
	}
	else if($_POST["admin_action"] == "createDirectory"){	
		$ADMIN->FILE_EDITOR->createDirectory($_POST["parent_id"], $_POST["name"]);
	}
	else if($_POST["admin_action"] == "updateDirectory"){
		$ADMIN->FILE_EDITOR->updateDirectory($_POST["directory_id"], $_POST["name"]);
	}
	else if($_POST["admin_action"] == "loadDirectoryTree"){
		$ADMIN->FILE_EDITOR->loadDirectoryTree();
	}
	else if($_POST["admin_action"] == "addToFavourites"){
		$ADMIN->FILE_EDITOR->addToFavourites($_POST["directory_id"]);
	}
	else if($_POST["admin_action"] == "removeFromFavourites"){
		$ADMIN->FILE_EDITOR->removeFromFavourites($_POST["directory_id"]);
	}
	else if($_POST["admin_action"] == "loadFavouritedDirectories"){
		$ADMIN->FILE_EDITOR->loadFavouritedDirectories();
	}
    else if($_POST["admin_action"] == "syncFilesAndDirs"){
        $ADMIN->FILE_EDITOR->synchronizeFilesAndDirectories();
    }
}


$exclamation_image = $ADMIN->DIRECTORY->selectSystemFileByFilename("exclamation");

setGlobal("exclamation_image", $exclamation_image);
setGlobal("predefinedCropResoluions", get_option("admin_predefined_crop_resolutions"));