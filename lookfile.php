<?php require_once dirname(__FILE__) . '/includes.php'; 

$url = $_GET["url"];
$type = $_GET["type"];

if($type == "movie")
{
?>
<script type="text/javascript" src="view/components/mpw_player/swfobject.js"></script>
<script type="text/javascript">
	var so = new SWFObject("view/components/mpw_player/mpw_player.swf", "main", "720", "576", "9", "#000000");
	so.addVariable("flv", '<?php echo "../../../$url"; ?>');
	so.addVariable("autoplay","true");
	so.addParam("allowFullScreen","true");
	so.addVariable("backcolor","222222");  
    so.addVariable("frontcolor","ffffff");
	so.write("videoPlayer");
</script>
<div id="videoPlayer" style="width:720px; height:576px; float:left;"></div>
<?php
}
else if($type == "sound")
{
?>
<script type="text/javascript" src="view/components/mpw_player/swfobject.js"></script>
<script type="text/javascript">
	var so = new SWFObject("view/components/mpw_player/mpw_player.swf", "main", "720", "576", "9", "#000000");
	so.addVariable("mp3", '<?php echo "../../../$url"; ?>');  
    so.addVariable("autoplay","true");
    so.addParam("allowFullScreen","true");
    so.addVariable("backcolor","222222");  
    so.addVariable("frontcolor","ffffff");
    so.write("videoPlayer");
</script>
<div id="videoPlayer" style="width:720px; height:576px; float:left;"></div>
<?php
}
else if($type == "image")
{
	header('Content-Type: image/jpeg');
	readfile($url);
}