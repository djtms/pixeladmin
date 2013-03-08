messageType = {"INFO":1,"WARNING":2,"ERROR":3};

function MESSAGEBOX()
{
	var self = this;
	var messageBoxOuter = $("#messageBoxOuter");
	var messageBox = messageBoxOuter.find("#messageBox");
	var headerIcon = messageBox.find("#headerIcon");
	var contentIcon = messageBox.find("#contentIcon");
	var buttonsOuter = messageBox.find("#messageBoxButtonsOuter");
	var headerText = messageBox.find("#headerText");
	var contentText = messageBox.find("#messageContentText");
	var headerIconClass = "";
	var contentIconClass = "";
	var buttonCount = 0;
	var buttonsHtml = "";
    var msgType = messageType;
	
	return {
		showMessage : function(messageTitle,messageText,messageType,buttons){
            messageType = !messageType ? msgType.WARNING : messageType;
            buttons = !buttons ? [{"name":"Tamam"}] : buttons;

			switch(messageType)
			{
				case(msgType.INFO):
					headerIconClass = "headerIconInfo";
					contentIconClass = "contentIconInfo";
				break;
				
				case(msgType.WARNING):
					headerIconClass = "headerIconWarning";
					contentIconClass = "contentIconWarning";
				break;
				
				case(msgType.ERROR):
					headerIconClass = "headerIconError";
					contentIconClass = "contentIconError";
				break;
			}
			
			headerIcon.addClass(headerIconClass);
			contentIcon.addClass(contentIconClass);
			
			buttonCount = buttons.length;
			buttonsHtml = "";
			
			for(var i=0; i<buttonCount; i++){
				buttonsHtml += '<button>' + buttons[i].name + '</button>';
			}
			
			buttonsOuter.html(buttonsHtml);
			headerText.html(messageTitle);
			contentText.html(messageText);
			
			for(var i=0; i<buttonCount; i++){
				buttonsOuter.find("button").eq(i).click(buttons[i].click || this.hideMessage);
			}
			
			messageBoxOuter.css({"visibility":"visible", "opacity":1});
		},
		
		hideMessage : function(){
			messageBoxOuter.css("opacity","0");
			setTimeout(function(){
				messageBoxOuter.css("visibility","hidden");
			}, 300);
		},
		
		openLoader : function(){
			$("#messageBoxLoader").css("opacity",1);
		},

		closeLoader : function(){
			$("#messageBoxLoader").css("opacity",0);
		}
	};
}