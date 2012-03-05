<?php
$MODEL = new PA_MODEL();

class PA_MODEL
{
	public $MODEL;
	public $USER;
	public $I18N;
	public $LANGUAGE;
	public $MESSAGE;
	public $LOG;
	public $IMAGE_PROCESSOR;
	public $DIRECTOR;
	public $FILE;
	public $THUMB;
	public $UPLOADER;
	public $GALLERY;
	
	function PA_MODEL()
	{
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
	}
}