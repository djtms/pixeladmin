/* 
 * Author: Mehmet Hazar Artuner
 * Last Update: 18.12.2011
 */


$(MhaStart);

var MHA;

function MhaStart()
{
	MHA = new MHA();
}

function MHA()
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
									return str.replace(/\s/g,"-");
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
}


