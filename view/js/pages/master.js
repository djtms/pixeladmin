$(MasterStart);

var validate;
var EDITORS = new Array();

function MasterStart()
{
	MESSAGEBOX = new MESSAGEBOX();
	validate = new Validate();
	
	$.ajaxSetup({
		type:"post"
	});
	
	$(".fancybox").fancybox({
		"titleShow":false
	});
	
	postMessage();
	
	$("input[type=date], input[type=datetime], input[type=time]").datepicker();
}

function postMessage(message,error)
{
	var openPostMessage = false;
	
	if((message == undefined) || (message == null) || (message == ""))
	{
		if($.trim($("#postMessage").html()) != "")
			openPostMessage = true;
	}
	else if(message.length > 0)
	{
		openPostMessage = true;
		
		message = '<p ' + (error ? ' style="color:#fc5900;" ' : '') + ' >' + message + '</p>';
		$("#postMessage").html(message);
	}
	
	if(openPostMessage)
	{
		$("#postMessage").stop().css("opacity","1");
		setTimeout(function(){ $("#postMessage").animate({"opacity":"0"},700,function(){
			$(this).html("").css("opacity","1");
		});},5000);
	}
}