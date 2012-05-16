$(MasterStart);

var validate;
var EDITORS = new Array();

function MasterStart()
{
	validate = new Validate();
	
	$.ajaxSetup({
		type:"post"
	});
	
	bindComponents();
	
	if($.trim($("#postMessage").html()) != "")
	{
		setupPostMessageCountdown();
	}
	
	$(window).resize(function(){
		var left = ($(window).width() - 460) / 2;
		var top  = ($(window).height() - 151) / 2;
		
		$("#messageBox").css({"left":left,"top":top});
	});
	
	$(window).trigger("resize");
	$("input[type=date], input[type=datetime], input[type=time]").datepicker();
	
	$(".dataGridOuter .item").live("dblclick", function(e){
		var href = $(this).find(".editBtn").attr("href");
		window.location.href = href;
	}).live("mousedown",function(e){return false;});
}

function bindComponents()
{
	$(".fancybox").fancybox({
		"titleShow":false
	});
}

function postMessage(message,error)
{
	message = '<p ' + (error ? ' style="color:#fc5900;" ' : '') + ' >' + message + '</p>';
	$("#postMessage").html(message);
	setupPostMessageCountdown();
}

function setupPostMessageCountdown()
{
	$("#postMessage").stop().css("opacity","1");
	setTimeout(function(){ $("#postMessage").animate({"opacity":"0"},700,function(){
		$(this).html("").css("opacity","1");
	});},5000);
}

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