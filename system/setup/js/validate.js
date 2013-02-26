/*
 * Author : Mehmet Hazar Artuner
 * 
 */

function VALIDATE()
{
	this.validateUsername = function(str){
		return !str.match(/[\s]+/i);
	};
	
	this.validateName = function(str){
		return str.match(/^[a-z\s\ç\Ç\ş\Ş\ü\Ü\ı\İ\ğ\Ğ\ö\Ö]+$/i);
	};
	
	this.validatePhone = function(str){
		return str.match(/^\+?[0-9\s]+$/i); 
	};
	
	this.validateEmail = function(str){
		return str.match(/^\w+[a-z0-9_\-\.]*@[a-z0-9_\-]+\.[a-z0-9_\-]+\.*[a-z0-9_\-]*\w+$/i);
	};
	
	this.validateFileFormat = function(fileFullName){
		var formats = new Array("docx","doc","pdf","jpeg","jpg","png","gif");
		var file = fileFullName.split('.');
		var filePartsCount = file.length;
		
		if(filePartsCount<=1)
		{
			return false;
		}
		
		var extension = new String(file[filePartsCount - 1].trim());
		var matched = false;
		
		for(var i=0; i<formats.length; i++)
		{
			var myReg = new RegExp(formats[i]);
			myReg.ignoreCase = true;
			
			if(myReg.exec(extension))
			{
				matched = true;
				break;
			}
			else
				alert(extension  + "  - Unmatched with " + formats[i] );
		}
		
		return matched;
	};
}

