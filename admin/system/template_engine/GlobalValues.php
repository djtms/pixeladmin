<?php

class GlobalValues
{
	protected $VIEW_URL; // Ana dizinden itibaren, View olarak kullanılan html dosyasının bulunduğu klasöre kadar olan, dosya yolunu verir.
	protected $CWD; // CurrentWorkingDirectory'i url tipinde hesaplayıp döndürür.
	protected $HOST; // Hosting ismini döndürür.
	protected static $userDefinedGlobals = '';
	protected static $SCRIPTS;
	
	public function setGlobal($name, $value)
	{
		$value = isset($value) ? $value : "";
		
		if(gettype($value) === "string")
			$value = addslashes($value);
		
		$GLOBALS[$name] = $value;
		$this->{$name} = $value;
		
		switch(gettype($value))
		{
			default:
			case "unknown type":
			case "string":
				$temp = "'" . addslashes(preg_replace("/\n/", "\\n", $value)) . "'";							
			break;
			case "boolean":			$temp = $value === true ? "true" : "false";	break;
			case "double":
			case "integer":			$temp = $value;								break;
			case "object":			
			case "array":			$temp = json_encode($value);			break;
			case "NULL":			$temp = null;									break;
		};

		self::$userDefinedGlobals .= "\n var $name = $temp;";
	}
	
	function __construct($directory)
	{
		$this->VIEW_URL = str_replace(basename($directory),"",$directory);
		$this->HOST = $_SERVER["HTTP_HOST"];
		$this->CWD = "http://" . $this->HOST . "/" . basename(dirname($_SERVER["SCRIPT_FILENAME"])) . "/";
		
		
		$GLOBALS["VIEW_URL"] = $this->VIEW_URL;
		$GLOBALS["HOST"] = $this->HOST;
		$GLOBALS["CWD"] = $this->CWD;
	}
	
	/**
	 * 
	 * Global değişkenlerin tanımlı olduğu scripti oluşturur ve static $SCRIPT değişkenine atar.
	 */
	protected function globalsScript() 
	{
		$this->User_Defined_Values = self::$userDefinedGlobals;
			
		ob_start(); // Çıktıyı hemen göndermeyip tamponlayacağımızı belirtiyoruz
			require_once dirname(__FILE__) . "/_globals.html"; // Html de global değişken olarak kullanacak değişkenlerin bulunduğu html
			$buffer = ob_get_contents(); // tampondaki çıktıyı başka değişkene atıyoruz
		ob_clean(); // Çıktı tamponunu temizliyoruz
		
		$this->setValues($buffer);
		
		self::$SCRIPTS .= $buffer;
	}
	
	
	/**
	 * 
	 * Html formatındaki stringin içindeki değişkenlerin değerlerini atar ve stringi geri döndürür
	 * @param $htmlString - Değer atanmasını isediğimiz html formatındaki string
	 */
	private function setValues(&$htmlString = null)
	{
		if($htmlString != null)
			$htmlString;
			
		// HTML'imizi aşağıda yazan Regular Expression ifadesine uyan her iki % karakteri arasındaki text bir index olacak şekilde
		// diziye atıyoruz
		preg_match_all("/\{\%[\w\.\_]+\%\}/",(string)$htmlString,$matches);
		
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
				$value = $this->{$valuesArray[0]};
				
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
				$htmlString = preg_replace("/{%$match%}/",$value,$htmlString);
			}
			else
			{
				// html den okuduğumuz değişkenin obje yada array olmayıp kendi başına bir değişken olduğu durumlarda html deki değişken ismi yerine değişken değerini atama işlemini yapıyoruz
				$htmlString = preg_replace("/{%$match%}/",$this->{$match},$htmlString);
			}
		}
	}
}