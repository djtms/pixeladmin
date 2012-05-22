var messageType = {"INFO":1,"WARNING":2,"ERROR":3};

function messageBox(messageTitle,messageText,messageType,buttons)
{
	var headerIconClass = "";
	var contentIconClass = "";
	switch(messageType)
	{
		case(messageType.INFO):
			headerIconClass = "headerIconInfo";
			contentIconClass = "contentIconInfo";
		break;
		
		case(messageType.WARNING):
			headerIconClass = "headerIconWarning";
			contentIconClass = "contentIconWarning";
		break;
		
		case(messageType.ERROR):
			headerIconClass = "headerIconError";
			contentIconClass = "contentIconError";
		break;
	}
	
	$("#messageBoxHeader #headerIcon").addClass(headerIconClass);
	$("#messageBoxHeader #contentIcon").addClass(contentIconClass);
	
	var buttonCount = buttons.length;
	var buttonsHtml = "";
	
	for(var i=0; i<buttonCount; i++)
	{
		buttonsHtml += '<button>' + buttons[i].name + '</button>';
	}
	$("#messageBox #messageBoxButtonsOuter").html(buttonsHtml);
	$("#messageBox #headerText").html(messageTitle);
	$("#messageBox #messageText").html(messageText);
	
	for(var i=0; i<buttonCount; i++)
	{
		$("#messageBox #messageBoxButtonsOuter button").eq(i).click(buttons[i].click);
	}
	
	$("#messageBoxOuter").css("display","block");
}

function closeMessageBox()
{
	$("#messageBoxOuter").css("display","none");
}