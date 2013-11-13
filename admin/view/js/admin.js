/* 
 * Author: Mehmet Hazar Artuner
 * Last Update: 09.08.2012
 */


$(AdminStart);

function AdminStart(){
	ADMIN = new ADMIN();
}

function ADMIN()
{
	// istenen string değişkeni regular expression da sorgu cümlesi olarak kullanılacak şekilde encode eder.
	this.quote = function(str){return str.replace(/([.?*+^$[\]\\(){}-])/g, "\\$1");};
	
	// istenen stringi utf8 formatına çevirir, özellikle linklerde kullanılabilir.
	this.encodeUTF8 = function(str){ return unescape(encodeURIComponent(str)); };
	
	// utf8 formatındaki stringi normal string e çevirir, özellikle linklerde kullanılabilir.
	this.decodeUTF8 = function(str){ return decodeURIComponent(escape(str)); };
	
	// verilen string'i web için uygun formata çevirir, özellikle dosya ismi verirken düzeltme amacıyla kullanılabilir.
	this.fixStringForWeb =  function(str)
								{
									str = str.replace(/\İ/g,"I");
									str = str.replace(/\ı/g,"i");
									str = str.replace(/\Ü/g,"U");
									str = str.replace(/\ü/g,"u");
									str = str.replace(/\Ö/g,"O");
									str = str.replace(/\ö/g,"o");
									str = str.replace(/\Ğ/g,"G");
									str = str.replace(/\ğ/g,"g");
									str = str.replace(/\Ş/g,"S");
									str = str.replace(/\ş/g,"s");
									str = str.replace(/\Ç/g,"C");
									str = str.replace(/\ç/g,"c");
									return str.replace(/\s/g,"_");
								};
								
	this.randomString = function(length, type)
	{
		var alphabeticCharset = 'abcdefghijklmnopqrstuvwxyz';
		var alphaNumericCharset = 'abcdefghijklmnopqrstuvwxyz1234567890';
		var advancedCharset = 'abcdefghijklmnopqrstuvwxyz>#${[]}|@!^+%&()=*?_-1234567890';
		var beingUsedCharset = "";
		
		switch(type)
		{
			case ('alphanumeric'):	beingUsedCharset = alphaNumericCharset;	break;
			case ('advanced'):		beingUsedCharset = advancedCharset;		break;
			default:				beingUsedCharset = alphabeticCharset;	break;
		}
		
		var randomString = '';
		
		for(var i = 0; i<length; $i++)
		{
			var rnd = Math.round(Math.random() * length);
			randomString += beingUsedCharset.substr(rnd,1);
		}
		
		return randomString;
	};
	
	this.cropString = function(text, limit){
		var textArray = text.split(" ");
		var arrayCount = textArray.length;
		var croppedText = "";
		var bufferText = "";
		
		for(var i=0; i<arrayCount; i++)
		{
			bufferText = croppedText + textArray[i];
			if(bufferText.length <= limit)
			{
				croppedText += textArray[i] + " ";
			}
			else
			{
				var charCount = croppedText.length;
				if(croppedText.substr(charCount - 1, charCount) == " ")
				{
					croppedText = croppedText.substr(0, charCount - 1);
				}
				croppedText += "...";
				break;
			}
		}
		
		return croppedText;
	};
	
	this.getFileInfoById = function(file_id){
		$return = false;
		
		$.ajax({
			data:"admin_action=getFileInfoById&file=" + file_id,
			dataType:"json",
			async: false,
			success:function(response){
				$return = response;
			}
		});
		
		return $return;
	}
		
}


