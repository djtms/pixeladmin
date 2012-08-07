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
	<input type="sitemap" name="mysitemap" value="500567295ec0b1000" page_url="works.php?work2={%work_id%}"  />
	
	<input type="submit" name="action" value="Kaydet" />
</form>

