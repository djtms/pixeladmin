<?php

class PA_FILE_EDITOR extends DB {

    public $error = array();
    
    private $public_root;
    private $private_root;
    
    function PA_FILE_EDITOR() {
        parent::DB();

        global $public_uploadurl;
        global $private_uploadurl;

        $this->public_root = $public_uploadurl;
        $this->private_root = $private_uploadurl;
    }

    function loadFileEditorItems($directory_id) {
        global $ADMIN;

        $return = new stdClass();
        $return->directories = $ADMIN->DIRECTORY->listDirectoriesByParentId($directory_id);

        if ($return->files = $ADMIN->DIRECTORY->listFilesByDirectory($directory_id)) {
            $length = sizeof($return->files);

            for ($i = 0; $i < $length; $i++) {
                if (!($return->files[$i]->thumb = $ADMIN->DIRECTORY->getThumbUrl($return->files[$i]->file_id, 123, 87, false, true, "center top", "FFFFFF")))
                    $return->files[$i]->thumb = "../upload/files/system/exclamation.jpg";
            }
        }

        echo json_encode($return);
        exit;
    }

    function deleteFileEditorItems($items) {
        global $ADMIN;

        $count = sizeof($items);
        $error = false;

        for ($i = 0; $i < $count; $i++) {
            if (($items[$i]->type == "directory") && !$ADMIN->DIRECTORY->deleteDirectory($items[$i]->id))
                $error = true;
            else if (!$ADMIN->DIRECTORY->deleteFile($items[$i]->id))
                $error = true;
        }

        echo json_encode(array("success" => !$error));
        exit;
    }

    function uploadFile($file) {
        global $ADMIN;
        global $allowedFileFormatsForUpload;

        if ($ADMIN->VALIDATE->validateFileFormat($file["name"], $allowedFileFormatsForUpload)) {
            if ($file_id = $ADMIN->UPLOADER->uploadFile($_POST["directory_id"], $file)) {
                $temp = $ADMIN->DIRECTORY->selectFileById($file_id);
                $temp->error = false;
                if (!$temp->thumb = $ADMIN->DIRECTORY->getThumbUrl($file_id, 123, 87, false, true, "center top", "FFFFFF"))
                    $temp->thumb = "../upload/files/system/exclamation.jpg";

                echo json_encode(array("success" => true, "file" => $temp));
            }
            else {
                echo json_encode(array("success" => false, "msg" => "Hata: " . $ADMIN->UPLOADER->error));
            }
        } else {
            echo json_encode(array("success" => false, "msg" => "Bu dosyayı yüklemek için yeterli izniniz yok!"));
        }

        exit;
    }

    function createDirectory($parent_id, $name) {
        global $ADMIN;

        if ($ADMIN->DIRECTORY->selectDirectoryByNameAndParent($parent_id, $name))
            echo json_encode(array("success" => false, "msg" => "already_exists"));
        else if ($directory_id = $ADMIN->DIRECTORY->createDirectory($name, $parent_id))
            echo json_encode(array("success" => true, "directory_id" => $directory_id));
        else
            echo json_encode(array("success" => false, "msg" => "error_happened"));

        exit;
    }

    function updateDirectory($directory_id, $name) {
        global $ADMIN;
        $directory = $ADMIN->DIRECTORY->selectDirectoryById($directory_id);

        // check if directory exists
        $selected_directory = $ADMIN->DIRECTORY->selectDirectoryByNameAndParent($directory->parent_id, $name);

        if (($selected_directory->directory_id > 0) && ($directory_id != $selected_directory->directory_id))
            echo json_encode(array("success" => false, "msg" => "already_exists"));
        else if ($ADMIN->DIRECTORY->updateDirectory($directory_id, $name))
            echo json_encode(array("success" => true));
        else
            echo json_encode(array("success" => false, "msg" => "error_happened"));

        exit;
    }

    function loadDirectoryTree() {
        global $ADMIN;

        echo json_encode(array("success" => true, "directory_tree" => $ADMIN->DIRECTORY->generateFileTreeHtmlByParentId(-1)));
        exit;
    }

    function addToFavourites($directory_id) {
        global $ADMIN;

        if ($ADMIN->DIRECTORY->setDirectoryFavouriteStatus($directory_id, 1))
            echo json_encode(array("success" => true));
        else
            echo json_encode(array("success" => false));
        exit;
    }

    function removeFromFavourites($directory_id) {
        global $ADMIN;

        if ($ADMIN->DIRECTORY->setDirectoryFavouriteStatus($directory_id, -1))
            echo json_encode(array("success" => true));
        else
            echo json_encode(array("success" => false));
        exit;
    }

    function loadFavouritedDirectories() {
        global $ADMIN;

        $favourited_directories = "";

        if ($dirs = $ADMIN->DIRECTORY->listFavouritedDirectories()) {
            $count = sizeof($dirs);

            for ($i = 0; $i < $count; $i++) {
                $favourited_directories .= '<span directory_id="' . $dirs[$i]->directory_id . '">' . $dirs[$i]->name . '</span>';
            }
        }

        echo json_encode(array("success" => true, "favourited_directories" => $favourited_directories));
        exit;
    }

    function synchronizeFilesAndDirectories() {
        global $ADMIN;

        if ($ADMIN->DIRECTORY->synchronizeDirectories() && $ADMIN->FILE->syncronizeFiles()) {
            echo "succeed";
        }
        exit;
    }

    function duplicateFile($file_id, $directory_id) {
        global $ADMIN;

        if (!$file = $ADMIN->FILE->selectFileById($file_id)) {
            $this->error[] = "Dosya bilgisi database'den alinamadi. Dosya: " . __FILE__ . " Satır: " . __LINE__;
            return false;
        }
        
        $upload_root = $this->{$file->access_type . "_root"};
        
        if (!$properties = $ADMIN->FILE->calculateFileProperties($directory_id, $file->basename)) {
            $this->error[] = "Dosya bilgisi hesaplanamadi. Dosya: " . __FILE__ . " Satır: " . __LINE__;
            return false;
        } else if (!copy($file->url, $upload_root . $properties->url)) {
            $this->error[] = "Dosya kopyalanamadi. Dosya: " . __FILE__ . " Satır: " . __LINE__;
            return false;
        } else if (!$ADMIN->FILE->saveFileInfoToDbByPath($upload_root . $properties->url, $file->access_type)) {
            $this->error[] = "Dosya bilgileri database'e kaydedilemedi. Dosya: " . __FILE__ . " Satır: " . __LINE__;
            return false;
        } else {
            return true;
        }
    }

    function duplicateFilesAndDirectories($current_directory_id, $data, $overwrite_status = 'duplicate') {
        global $ADMIN;

        $success = true;

        if (sizeof($data->files) > 0) {
            foreach ($data->files as $f) {
                if ($file = getFileInfo($f->id)) {
                    // Eğer varolan dosyanın üzerine yazılmayacaksa normal kopyalama işlemi yapıyoruz
                    if ($overwrite_status != 'overwrite') {
                        if (!$this->duplicateFile($f->id, $current_directory_id)) {
                            $this->error = array_merge($ADMIN->UPLOADER->error, $this->error);
                            $this->error[] = 'Hata: Dosya yüklenemedi! Dosya: ' . __FILE__ . ' Satır: ' . __LINE__;
                            $success = false;
                        }
                    } else {
                        // Eğer aynı dizinde aynı isimde başka bir dosya varsa
                        if ($existing_file = $ADMIN->FILE->selectFileByDirectoryAndName($current_directory_id, $f->name)) {
                            if (!deleteFile($existing_file->file_id)) {
                                $this->error[] = 'Hata: Varolan dosya silinemedi! Dosya: ' . __FILE__ . ' Satır: ' . __LINE__;
                                $success = false;
                                continue;
                            }
                        }

                        // Eğer benzer dosya yoksa veya varolan benzer dosya silindiyse yeni dosyayı kopyala
                        if (!$ADMIN->UPLOADER->uploadFile($current_directory_id, $file->url)) {
                            $this->error[] = 'Hata: Dosya yüklenemedi! Dosya: ' . __FILE__ . ' Satır: ' . __LINE__;
                            $success = false;
                        }
                    }
                }
            }
        }

        echo json_encode(array('success' => $success, 'msg' => json_encode($this->error)));
        exit;
    }

}
