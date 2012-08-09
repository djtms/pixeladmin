<?php
// Author: Mehmet Hazar Artuner
// Release Date: 29.06.2011
// WebSite: www.hazarartuner.com

require_once "GlobalValues.php";

class Template extends GlobalValues{
	
	private $directory;
	private $_rendered;
		
	public function __construct($directory) {
		if (file_exists($directory)) 
		{
			parent::__construct($directory);
			$this->directory = $directory;
		}
		else 
			$this->_rendered = sprintf("<div style=\"text-align:center;color:red\">ERROR [N/A]: <b>%s</b></div>", $directory);
	}
	
	/**
	 * 
	 * Üyesi olduğu class ın, html dosyasının değerlerini atayıp string olarak geri döndürür.
	 */
	public function html() {
		$this->_rendered = file_get_contents($this->directory);
		return $this->setValues();
	}
	
	public function render($echo = true) 
	{	
		$this->html(false);
		$this->setupScripts();
		if($echo) 
			echo $this->_rendered;
		else
			return $this->_rendered;
	}
	
	/**
	 * 
	 * Html sayfanın "head" tag'ının sonunda script ekler
	 * @param (string) $scriptUrl eklenmek istenen scriptin url'si
	 */
	public function addScript($scriptUrl, $http = false)
	{
		self::$SCRIPTS .= '<script type="text/javascript" src="' . ($http ? "" : $this->VIEW_URL) . $scriptUrl . '"></script>';
	}
	
	/**
	 * 
	 * Html formatındaki stringin içindeki değişkenlerin değerlerini atar ve stringi geri döndürür
	 * @param $htmlString - Değer atanmasını istediğimiz html formatındaki string
	 */
	public function setValues(&$htmlString = null)
	{
		if($htmlString != null)
			$this->_rendered = $htmlString;
			
		// HTML'imizi aşağıda yazan Regular Expression ifadesine uyan her iki % karakteri arasındaki text bir index olacak şekilde
		// diziye atıyoruz
		preg_match_all("/\{\%[a-zA-Z0-9\.\_]+\%\}/",(string)$this->_rendered,$matches);
		
		// Yukarıdaki ifadede oluşan dizideki her indexi sırayla kontrol ediyoruz
		foreach($matches[0] as $match)
		{
			// ilk önce aşağıdaki işlemleri yapabilmek için dönen string deki % karakterlerini  siliyoruz
			$match = str_replace("{%","",$match);
			$match = str_replace("%}","",$match);
			
			// şimdi ise html de yazan değişkeninkenin bir objeyi mi ifade ettiğini yada tek başına bir değişkenmi olduğunu anlamak için "." karakteri ile parçalıyoruz
			$valuesArray = explode(".",$match);
			
			// okuduğumuz değişken bir objeyi ifade ediyorsa aşağıdaki döngüye sokuyoruz
			if(sizeof($valuesArray) > 1)
			{
				// Burada html den obje olarak tanımladığımız değişkeni php kısmında arayıp bir değişkene atıyoruz
				//$value = $this->{$valuesArray[0]};
				$value = isset($this->{$valuesArray[0]}) ? $this->{$valuesArray[0]} : $GLOBALS[$valuesArray[0]];
				
				if(is_object($value))
				{
					$value = get_class($value) == "Template" ? $value->html() : $value; 	
				}
				
				
				// okuduğumuz değişken kaç basamaklı bir obje yada array ise ona göre her basamağı tek tek okuyup sonucu döndürüyoruz
				for($i = 1; $i<sizeof($valuesArray); $i++)
				{
					// okuduğumuz objenin sonraki basamaklarının array yada object olabilme ihtimaline karşılık bir kontrol yapıp döngümüzü ona göre ilerletiyoruz
					if(is_object($value))
						$value = $value->{$valuesArray[$i]};
					else
						$value = $value[$valuesArray[$i]];
				}
				
				// html den okuduğumuz değişkenimizin değerini hesapladıktan sonra sonucu htmlde okuduğumuz ifadenin yerine yazma işlemini yapıyoruz
				$this->_rendered = preg_replace("/{%$match%}/",$value,$this->_rendered);
				
			}
			else
			{
				// html den okuduğumuz değişkenin obje yada array olmayıp kendi başına bir değişken olduğu durumlarda html deki değişken ismi yerine değişken değerini atama işlemini yapıyoruz
				$value = isset($this->{$match}) ? $this->{$match} : $GLOBALS[$match];
				
				if(is_object($value))
				{
					$value = get_class($value) == "Template" ? $value->html() : "OBJECT"; 	
				}
				
				$this->_rendered = preg_replace("/{%$match%}/",$value,$this->_rendered);
			}
		}
		
		return $this->_rendered;
	}
	
	/*PRIVATE FUNCTIONS*/
	/**
	 * 
	 * Eklenen scriptleri ve global değişkenlerin olduğu scripti head tagının  içine yerleştirir
	 */
	private function setupScripts()
	{
		$this->globalsScript();
		
		if(preg_match("/\<\/head\>/i",$this->_rendered))
		{
			$this->_rendered = preg_replace("/\<\/head\>/i", self::$SCRIPTS . " </head>" ,$this->_rendered);
		}
	}
}

?>