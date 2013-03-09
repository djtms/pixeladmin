messageType = {"INFO":1,"WARNING":2,"ERROR":3};

function MESSAGEBOX()
{
	var self = this;
    var objects = {};
	var headerIconClass = "";
	var contentIconClass = "";
	var buttonCount = 0;
	var buttonsHtml = "";
    var msgType = messageType;


	return {
        init: function(){
            if($("#messageBoxOuter").length <= 0){
                var view_html  = '<div id="messageBoxOuter"><div id="messageBox">';
                    view_html += '<div id="messageBoxHeader"><span id="headerIcon" class="headerIconWarning"></span>';
                    view_html += '<p id="headerText"></p></div><div id="messageBoxContent">';
                    view_html += '<span id="contentIcon" class="contentIconWarning"></span><p id="messageContentText"></p>';
                    view_html += '<div id="messageBoxButtonsOuter"></div><span id="messageBoxLoader"></span></div>';
                    view_html += '</div><div id="adminHider"></div></div>';

                $("#wholeContentsOuter").append(view_html);
            }

            objects.messageBoxOuter = $("#messageBoxOuter");
            objects.messageBox = $("#messageBox");
            objects.headerIcon = $("#headerIcon");
            objects.contentIcon = $("#contentIcon");
            objects.buttonsOuter = $("#messageBoxButtonsOuter");
            objects.headerText = $("#headerText");
            objects.contentText = $("#messageContentText");
            objects.messageBoxLoader = $("#messageBoxLoader");
        },
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

            objects.headerIcon.addClass(headerIconClass);
            objects.contentIcon.addClass(contentIconClass);
			
			buttonCount = buttons.length;
			buttonsHtml = "";
			
			for(var i=0; i<buttonCount; i++){
				buttonsHtml += '<button>' + buttons[i].name + '</button>';
			}

            objects.buttonsOuter.html(buttonsHtml);
            objects.headerText.html(messageTitle);
            objects.contentText.html(messageText);
			
			for(var i=0; i<buttonCount; i++){
                objects.buttonsOuter.find("button").eq(i).click(buttons[i].click || this.hideMessage);
			}
			
			objects.messageBoxOuter.css({"visibility":"visible", "opacity":1});
		},
		
		hideMessage : function(){
			objects.messageBoxOuter.css("opacity","0");
			setTimeout(function(){
				objects.messageBoxOuter.css("visibility","hidden");
			}, 300);
		},
		
		openLoader : function(){
            objects.messageBoxLoader.css("opacity",1);
		},

		closeLoader : function(){
            objects.messageBoxLoader.css("opacity",0);
		}
	};
}