<?php

if($_POST["action"] == "Kaydet")
{
	if(saveGallery() && saveI18n() && set_option("sampleLogoId", $_POST["sampleLogoId"]) && set_option("sampleGalleryId", $_POST["sampleGalleryId"]))
		postMessage("Başarıyla Güncellendi!");
	else
		postMessage("Hata Oluştu!", true);
}

?>
<br clear="all"/><br />
<form method="post">
	<?php 
	$files = $ADMIN->GALLERY->listGalleryFiles(18);
	
	$appendHtml .= "{%user_name%} - {%age%}";
	$appendHtml .= "<button class='validate' file='{%file_id%}' >Onay Ver</button>";
	$appendHtml .= "<button class='discard' file='{%file_id%}'>Reddet</button>";
	
	echo fileGrid($files, "", "edit,play,view", 1, 1, $appendHtml);
	
	?>

	<label>Logo</label>
	<input type="file" name="sampleLogoId" fileid="<?php echo get_option("sampleLogoId"); ?>" />
	
	<!-- <label>Tarih:</label>
	<input type="date" name="birth" value="00:00:05" /> -->
	
	<label>Galeri</label>
	<input type="gallery" name="sampleGalleryId" value="<?php echo get_option("sampleGalleryId"); ?>" />
	<!-- <label>Başlık:</label>
	<input type="text" i18n="sampleHeaderI18n" />
	<label>Yazı:</label>
	<textarea i18n="sampleCommentI18n" type="editor"></textarea> -->
	
	<input type="submit" name="action" value="Kaydet" />
</form>
<?php
