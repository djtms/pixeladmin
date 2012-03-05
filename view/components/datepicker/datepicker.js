// Author: Mehmet Hazar Artuner
// Release Date: 20.01.2012 / 11:27
// Version: 1.5.2
// WebPage: www.hazarartuner.com

jQuery.fn.datepicker = function(options){
	
	var defaultOptions = {
		yearCount : 150,
		dateFormat : "yy:mm:dd"
	};

	return this.each(function(){
		
		var _this = $(this);
		var firstValue 	= _this.attr("value");
		var thisNAME 	= _this.attr("name");
		var thisCLASS 	= _this.attr("class");
		var thisSTYLE 	= _this.attr("style");
		var thisID 		= _this.attr("id");
		
		options = $.extend(defaultOptions,options);
		
		var _year;
		var _month;
		var _day;
		var _valueObject;
		
		prepareHtml();
		bindEvents();
		
		function prepareHtml()
		{
			var date = new Date();
			var year = date.getFullYear();
			var month = date.getMonth() + 1;
			var day = date.getDate();
			
			_this.wrap('<div class="datePickerOuter" class="' + thisCLASS + '" style="' + thisSTYLE + '">');
			_this = _this.parent();
			
			var html = '<input type="hidden" class="hiddenDateInput" name="' + thisNAME + '" />';
			html += '<ul class="dateInputsList">';
			html += '<li class="yearLi">';
			html += '<select class="year">';
			html += calculateYears(year,options.yearCount);
			html += '</select>';
			html += '</li>';
			html += '<li class="monthLi">';
			html += '<select class="month">';
			html += '<option value="01">Ocak</option>';
			html += '<option value="02">Şubat</option>';
			html += '<option value="03">Mart</option>';
			html += '<option value="04">Nisan</option>';
			html += '<option value="05">Mayıs</option>';
			html += '<option value="06">Haziran</option>';
			html += '<option value="07">Temmuz</option>';
			html += '<option value="08">Ağustos</option>';
			html += '<option value="09">Eylül</option>';
			html += '<option value="10">Ekim</option>';
			html += '<option value="11">Kasım</option>';
			html += '<option value="12">Aralık</option>';
			html += '</select>';
			html += '</li>';
			html += '<li class="dayLi">';
			html += '<select class="day">';
			html += calculateDays(month,year);
			html += '</select>';
			html += '</li>';
			html += '</ul>';
			
			_this.html(html);
			_year = _this.find(".year");
			_month = _this.find(".month");
			_day = _this.find(".day");
			
			_valueObject = _this.find(".hiddenDateInput");
			
			
			_valueObject.attr("name",thisNAME);
			_this.removeAttr("name");
			setDate(firstValue);
		}

		function calculateDays(month,thisYear)
		{
			var dayCount = 0;
			
			switch(month)
			{
				case "02": 
				case	2:
					dayCount = (thisYear % 4) == 0 ? 29 : 28; 
					break; // February
				
				case "01": // January
				case "03": // March
				case "05": // May
				case "07": // July
				case "08": // August
				case "10": // October
				case "12": // December
				case	1:
				case	3:
				case	5:
				case	7:
				case	8:
				case   10:
				case   12:
					dayCount = 31;
					break;
				
				case "04": // April
				case "06": // June
				case "09": // September
				case "11": // November
					dayCount = 30; 
					break;
			}
			
			var daysHtml = '';
			
			for(var i=1; i<=dayCount; i++)
			{
				var currentVal = i;
				if(i < 10)
				{
					currentVal = "0" + i;
				}
					
				daysHtml += '<option value="' + currentVal + '">' + currentVal + '</option>';
			}
			
			return daysHtml;
		}
		
		function calculateYears(thisYear,yearLength)
		{
			var yearsHtml = '';
			
			for(var i=thisYear; i >=(thisYear - yearLength); i--)
			{
				yearsHtml += '<option value="' + i + '">' + i + '</option>';
			}
			
			return yearsHtml;
		}
		
		function bindEvents()
		{
			_month.change(function(){
				var tempMonth = $(this).val();
				var tempYear = _this.find(".year").val();
				
				_day.html(calculateDays(tempMonth,tempYear));
				setDateToValueObject();
			});
			
			_year.change(function(){
				var tempYear = $(this).val();
				var tempMonth = _month.val();
				_month.val("01");
				_month.trigger("change");
				setDateToValueObject();
			});
			
			_day.change(setDateToValueObject);
		}
		
		function getDate()
		{
			var day = _day.val();
			var month = _month.val();
			var year = _year.val();
			
			var formatSplit = options.dateFormat.split(':');
			var outputHtml = '';
			
			for(var i=0; i<formatSplit.length; i++)
			{
				if(formatSplit[i] == "dd")
					outputHtml += day + "-";
				else if(formatSplit[i] == "mm")
					outputHtml += month + "-";
				else if(formatSplit[i] == "yy")
					outputHtml += year + "-";
			}
			
			outputHtml = outputHtml.substring(0,outputHtml.length - 1);
			
			return outputHtml;
		}
		
		function setDate(date)
		{
			var setCurrentDate = false;
			var dateArray;
			
			if(date == "" || date == null || date == undefined)
				setCurrentDate = true;
			else
				dateArray = date.split('-');
			
			var formatSplit = options.dateFormat.split(':');
			
			var d = new Date();
			var day = d.getDate();
			var month = d.getMonth() + 1;
			var year = d.getFullYear();
			
			month = month.toString().length < 2 ? "0" + month : month;
			day = day.toString().length < 2 ? "0" + day : day;
			
			var valueObjectValueString = "";
			for(var i=0; i<formatSplit.length; i++)
			{
				if(formatSplit[i] == "dd")
				{
					day = setCurrentDate ? day : dateArray[i];
					valueObjectValueString += (day + "-");
				}
				else if(formatSplit[i] == "mm")
				{
					month = setCurrentDate ? month : dateArray[i];
					valueObjectValueString += (month + "-");
				}
				else if(formatSplit[i] == "yy")
				{
					year = setCurrentDate ? year : dateArray[i];
					valueObjectValueString += (year + "-");
				}
			}
			
			day = day.length < 2 ? "0" + day : day;
			month = month.length < 2 ? "0" + month : month;
			
			/////////////////////////
			_day.val(day);
			_month.val(month);
			_year.val(year);
			
			valueObjectValueString = valueObjectValueString.substring(0,10);

			_valueObject.val(valueObjectValueString);
		}
		
		function setDateToValueObject()
		{
			var formatSplit = options.dateFormat.split(':');
			
			var valueObjectValueString = "";
			for(var i=0; i<formatSplit.length; i++)
			{
				if(formatSplit[i] == "dd")
					valueObjectValueString += (_day.val() + "-");
				else if(formatSplit[i] == "mm")
					valueObjectValueString += (_month.val() + "-");
				else if(formatSplit[i] == "yy")
					valueObjectValueString += (_year.val() + "-");
			}
			
			valueObjectValueString = valueObjectValueString.substring(0,10);
			
			_valueObject.val(valueObjectValueString);
		}
	});
}