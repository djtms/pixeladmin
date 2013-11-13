<?php
$template_version = "1.0";

require_once "Template.php";
$View = new View();
require_once dirname(__FILE__) . '/Functions.php';

class View
{
	public $deepCount = 1; // Bulunduğumuz dizinin ana dizine göre kaç dizin derinlikte olduğunu verir
	public $deepUrl = "";
	public $lastViewTemplate;
	public $subHtmlFolderName = "subhtml";
	
	
	function loadView($templateFolderName = null)
	{
		$filesList = array();
		
		if($templateFolderName == null)
		{
			$foldersList = $this->getViewFoldersList("view");
			
			foreach($foldersList as $folder)
			{
				$templateDir = $this->deepUrl . "view/" . $folder . "/";
				
				if(is_dir($templateDir))
				{
					$this->getHtmlObjects($templateDir,$filesList);
					
					$GLOBALS[$folder] = (object)$filesList;
				}
			}
		}
		else if($templateFolderName == "nofolder")
		{
			$this->calculateDeepUrl();
			$templateDir = $this->deepUrl . "view/";
			if(is_dir($templateDir))
			{
				$this->getHtmlObjects($templateDir,$filesList);
				
				foreach($filesList as $name=>$object)
				{
					$GLOBALS[$name]=$object;
				}
			}
		}		
		else
		{
			$this->calculateDeepUrl();
			$templateDir = $this->deepUrl . "view/" . $templateFolderName . "/";
			
			if(is_dir($templateDir))
			{
				$this->getHtmlObjects($templateDir,$filesList);
				
				foreach($filesList as $name=>$object)
				{
					$GLOBALS[$name]=$object;
				}
			}
		}
	}
	
	function getHtmlObjects($templateDir,&$filesList)
	{
		$dir = opendir($templateDir);	
		while($file = readdir($dir))
		{
			if(preg_match("/.html/i",$file))
			{
				$fileName = preg_replace("/.html/i","",$file);
				$filesList[$fileName] = new Template($templateDir.$file);
				$this->lastViewTemplate = $fileName;
			}
		}
		closedir($dir);
		
		$this->getSubHtmlObjects($templateDir,$filesList);
	}
	
	function getSubHtmlObjects($templateDir,&$filesList)
	{
		$templateDir .= "{$this->subHtmlFolderName}/";
	
		if(is_dir($templateDir))
		{
			$dir = opendir($templateDir);
					
			while($file = readdir($dir))
			{
				if(preg_match("/.html/i",$file))
				{
					$fileName = preg_replace("/.html/i","",$file);
					$filesList[$fileName] = new Template($templateDir.$file);
				}
			}
		}
	}
	
	// Ana dizine göre ne kadar alt dizinde bulunduğumuzu hesaplar
	private function calculateDeepCount()
	{
		$currentDirectory = str_replace(basename($_SERVER["SCRIPT_NAME"]),"",$_SERVER["SCRIPT_NAME"]);
		$parentFoldersInDir = explode("/",$currentDirectory);
		
		foreach($parentFoldersInDir as $PF)
		{
			if(trim($PF) != "" && $PF != null)
			{
				$this->deepCount += 1;
			}
		}
	}
	
	// View dizini içindeki klasörlerinin isimlerini (alt klasörler hariç) array içinde göndürür
	private function getViewFoldersList($viewFolderName)
	{
		$this->calculateDeepCount();
		for($i=0; $i<$this->deepCount; $i++) 
		{											
			if(is_dir($this->deepUrl . $viewFolderName))
			{
				$dirList = scandir($this->deepUrl . $viewFolderName);
				break;
			}
			else
			{
				$this->deepUrl .= '../';
			}
			
		}
		
		$directory = $this->deepUrl . $viewFolderName . "/";
		
		
		foreach($dirList as $d)
		{
			if(is_dir($directory . $d) && $d != "." && $d != "..")
			{
				$foldersList[] = $d;
			}
		}
		
		return $foldersList;
	}
	
	private function calculateDeepUrl()
	{
		$viewFolderName = "view";
		$this->calculateDeepCount();
		
		for($i=0; $i<$this->deepCount; $i++) 
		{											
			if(!is_dir($this->deepUrl . $viewFolderName))
				$this->deepUrl .= '../';
			else
				break;
		}
	}
}


