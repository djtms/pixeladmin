<?php
if($_POST["action"] == "Kaydet")
{
	if(saveSitemap(array("work_id"=>"111")))
	{
		postMessage("Başarıyla Kaydedildi!");
	}
}

deleteSitemap("4fdf47cf9e1ca");
?>

<form method="post">
	<input type="sitemap" name="mysitemap" value="" page_url="works.php?work={%work_id%}"  />
	
	<input type="submit" name="action" value="Kaydet" />
</form>

