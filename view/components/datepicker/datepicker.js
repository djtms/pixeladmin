// Author: Mehmet Hazar Artuner
// Release Date: 28.07.2012 / 23:06
// Version: 1.1
// WebPage: www.hazarartuner.com

(function($){
	$.fn.datepicker = function(settings){
		if(this.length == 0)
		{
			return;
		}
		
		if(this.length > 1)
		{
			this.each(function(){
				$(this).datepicker(settings);
			});
			
			return this;
		}
		
		// Setup Variables
		var defaultSettings = {
				type:"date"
			};
			
		var options = $.extend({},defaultSettings, settings);
		var _this = this;
		var currentValue = this.val();
		var year;
		var month;
		var day;
		var hour;
		var minute;
		var second;
		var monthNames = [null, "Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"];
		var yearRange = {"minus":100,"plus":10};
		var type = _this.attr("type");
		type = (type in {"date":1,"time":2,"datetime":3}) ? type : options.type;
		////////////////////////////////////////////////////////////////////
		
		
		var start = function(){
			loadGivenDateAndTimeVariables();
			generateUI();
			generateDayCount();
			setInitValues();
			bindEvents();
			return _this;
		};
		
		
		// input içinde tanımlanmış tarih/saat değerini bu object içindeki tarih-saat değişkenlerine yükler
		var loadGivenDateAndTimeVariables = function(){
			//currentValue değerini kontrol edip düzenle
			matchedDate = currentValue.match(/[0-9]{4}-[0-9]{2}-[0-9]{2}/);
			matchedTime = currentValue.match(/[0-9]{2}:[0-9]{2}:[0-9]{2}/);
			var current = getCurrentDateAndTimeVariables();
			
			currentValue = "";
			
			if((matchedDate == null) || (matchedDate == "0000-00-00"))
			{
				matchedDate  = current.year;
				matchedDate += "-" + (current.month < 10 ? "0" + current.month : current.month);
				matchedDate += "-" + (current.day < 10 ? "0" + current.day : current.day);
			}
			
			if((matchedTime == null) || (matchedTime == "00:00:00"))
			{
				matchedTime = (current.hour < 10 ? "0" + current.hour : current.hour);
				matchedTime += ":" + (current.minute < 10 ? "0" + current.minute : current.minute);
				matchedTime += ":" + (current.second < 10 ? "0" + current.second : current.second);
			}
			
			if(type == "datetime")
				currentValue = matchedDate + " " + matchedTime;
			else if(type == "date")
				currentValue = matchedDate;
			else if(type == "time")
				currentValue = matchedTime;
			////////////////////////////////////////////////////////////////
			
			if(type == "datetime")
			{
				var splittedDateAndTime = currentValue.split(" ");
				var date = splittedDateAndTime[0];
				var time = splittedDateAndTime[1];
			}
			else
			{				
				var date = currentValue.toString();
				var time = currentValue.toString();
			}
			
			switch(type)
			{
				case "date":
					var splittedDate = date.split("-");

					year  	= 	parseInt(splittedDate[0], 10);
					month 	= 	parseInt(splittedDate[1], 10);
					day 	= 	parseInt(splittedDate[2], 10);
				break;
					
				case "time":
					var splittedTime = time.split(":");
					
					hour  	= 	splittedTime[0];
					minute 	= 	splittedTime[1];
					second 	= 	splittedTime[2];
				break;
					
				case "datetime":
					var splittedDate = date.split("-");
					var splittedTime = time.split(":");
					
					year  	= 	parseInt(splittedDate[0], 10);
					month 	= 	parseInt(splittedDate[1], 10);
					day 	= 	parseInt(splittedDate[2], 10);
					
					hour  	= 	splittedTime[0];
					minute 	= 	splittedTime[1];
					second 	= 	splittedTime[2];
				break;
			};
		};
		
		// şu anın tarih ve saat değerini array olarak döndürür. array değişkenleri index sırasına göre; yıl, ay, gün, saat, dakika, saniye değerlerini verir
		var getCurrentDateAndTimeVariables = function(){
			var date = new Date();
			var datetime = {};
			
			datetime.year 	= 	date.getFullYear();
			datetime.month 	= 	date.getMonth() + 1;
			datetime.day   	=	date.getDate();
			
			datetime.hour 	=	date.getHours();
			datetime.minute =	date.getMinutes();
			datetime.second =	date.getSeconds();
			
			return datetime;
		};
		
		// arayüzü oluşturur
		var generateUI = function(){
			var begin_year	= (year - parseInt(yearRange.minus, 10));
			var end_year	= (year + parseInt(yearRange.plus, 10));
			var _thisName = _this.attr("name");
			
			_this.wrap('<div class="dateTimePickerOuter ' + type + '">');
			_this = _this.parent();
			
			
			var	UI  = '<div class="dateOuter">';
				UI += '<input type="hidden" name="' + _thisName + '" value="" />';
				UI += '<select class="dp_year">';
				
				for(var i=begin_year; i<end_year; i++)
				{
					UI += '<option value="' + i + '">' + i + '</option>';
				}
				
				UI += '</select>';
				UI += '<select class="dp_month">';
				
				for(var i=1; i<=12; i++)
				{
					UI += '<option value="' + i + '">' + monthNames[i] + '</option>';
				}
				
				UI += '</select>';
				UI += '<select class="dp_day"></select>';
				UI += '</div>';
				UI += '<div class="timeOuter">';
				UI += '<input class="dp_hour" type="text" value="0" maxlength="2" /><span>:</span>';
				UI += '<input class="dp_minute" type="text" value="0" maxlength="2" /><span>:</span>';
				UI += '<input class="dp_second" type="text" value="0" maxlength="2" />';
				UI += '</div>';
			_this.html(UI);
		};
		
		// arayüzde ilk değer atamasını yapar
		var setInitValues = function(){
			_this.find(".dp_year").val(year);
			_this.find(".dp_month").val(month);
			_this.find(".dp_day").val(day);
			_this.find(".dp_hour").val(hour);
			_this.find(".dp_minute").val(minute);
			_this.find(".dp_second").val(second);
			_this.find("input[type='hidden']").val(getCurrentValue());
		};
		
		// arayüzde olması gereken gün sayısını tarihe göre hesaplayıp arayüzde gösterir
		var generateDayCount = function(e){
			
			if(e != undefined)
			{
				year = parseInt(_this.find(".dp_year").val(), 10);
				month = parseInt(_this.find(".dp_month").val(), 10);
			}
			
			var extendFebruary = (year % 4) == 0 ? 1 : 0;
			
			switch(month)
			{
				case 2:		dayCount = 28 + extendFebruary; 	break;
					
				case 1:
				case 3:
				case 5:
				case 7:
				case 8:
				case 10:
				case 12: 	dayCount = 31;	break;
				default: 	dayCount = 30;	break;
			}
			
			UI = "";
			for(var i=1; i<=dayCount; i++)
			{
				UI += '<option value="' + i + '">' + i + '</option>';
			}
			
			_this.find(".dp_day").html(UI);
		};
		
		var getCurrentValue = function(){
			var value = "";
			
			switch(type)
			{
				case"date":
					year = parseInt(_this.find(".dp_year").val(), 10);
					month = parseInt(_this.find(".dp_month").val(), 10);
					day = parseInt(_this.find(".dp_day").val(), 10);
					
					value = year + "-" + (month<10 ? "0" + month : month) + "-" + (day<10 ? "0" + day : day);
					break;
					
				case"time":
					hour = parseInt(_this.find(".dp_hour").val(), 10);
					minute = parseInt(_this.find(".dp_minute").val(), 10);
					second = parseInt(_this.find(".dp_second").val(), 10);
					
					value = (hour < 10 ? "0" + hour : hour) + ":" + (minute < 10 ? "0" + minute : minute) + ":" + (second < 10 ? "0" + second : second);
					break;
					
				case"datetime":
					year = parseInt(_this.find(".dp_year").val(), 10);
					month = parseInt(_this.find(".dp_month").val(), 10);
					day = parseInt(_this.find(".dp_day").val(), 10);
					
					hour = parseInt(_this.find(".dp_hour").val(), 10);
					minute = parseInt(_this.find(".dp_minute").val(), 10);
					second = parseInt(_this.find(".dp_second").val(), 10);
					
					value = year + "-" + (month<10 ? "0" + month : month) + "-" + (day<10 ? "0" + day : day);
					value += " " + (hour < 10 ? "0" + hour : hour) + ":" + (minute < 10 ? "0" + minute : minute) + ":" + (second < 10 ? "0" + second : second);
					break;
			};
			
			return value;
		};
		
		var bindEvents = function(){
			_this.find(".dp_year, .dp_month").change(generateDayCount);
			_this.find("input[type=text]").click(function(){$(this).select();});
			_this.find("select, input[type=text]").change(function(){
				_this.find("input[type='hidden']").val(getCurrentValue());
			});
		};
		
		return start();
	};
})(jQuery);