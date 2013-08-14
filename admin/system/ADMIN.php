<?php
$ADMIN = new PA_ADMIN();

class PA_ADMIN
{
	public $DB;
	public $VALIDATE;
	public $USER;
	public $I18N;
	public $LANGUAGE;
	public $MESSAGE;
	public $LOG;
	public $IMAGE_PROCESSOR;
	public $DIRECTORY;
	public $FILE;
	public $THUMB;
	public $UPLOADER;
	public $GALLERY;
	public $ROLE_PERMISSION;
	public $GROUP_PERMISSION;
	public $USER_ROLE;
	public $USER_GROUP;
	public $GROUP;
	public $ROLE;
	public $PERMISSION;
	public $SITEMAP;
	public $AUTHENTICATION;
	public $AUTHORIZATION;
	public $FILE_EDITOR;
	
	
	function PA_ADMIN()
	{
		global $DB;
		
		$this->DB = &$DB; 
		$this->VALIDATE = new PA_VALIDATE();
		$this->USER = new PA_USER();
		$this->I18N = new PA_I18N();
		$this->LANGUAGE = new PA_LANGUAGE();
		$this->MESSAGE = new PA_MESSAGE();
		$this->LOG = new PA_LOG();
		$this->IMAGE_PROCESSOR = new PA_IMAGE_PROCESSOR();
		$this->DIRECTORY = new PA_DIRECTORY();
		$this->FILE = new PA_FILE();
		$this->THUMB = new PA_THUMB();
		$this->UPLOADER = new PA_UPLOADER();
		$this->GALLERY = new PA_GALLERY();
		$this->ROLE_PERMISSION = new PA_ROLE_PERMISSION();
		$this->GROUP_PERMISSION = new PA_GROUP_PERMISSION();
		$this->USER_ROLE = new PA_USER_ROLE();
		$this->USER_GROUP = new PA_USER_GROUP();
		$this->GROUP = new PA_GROUP();
		$this->ROLE = new PA_ROLE();
		$this->PERMISSION = new PA_PERMISSION();
		$this->SITEMAP = new PA_SITEMAP();
		$this->AUTHENTICATION = new PA_AUTHENTICATION();
		$this->AUTHORIZATION = new PA_AUTHORIZATION();
		$this->FILE_EDITOR = new PA_FILE_EDITOR();
	}
}