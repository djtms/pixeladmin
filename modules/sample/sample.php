<?php 
if($_POST["action"] == "Kaydet")
{
	if(saveGallery() && saveI18n() && set_option("sampleLogoId", $_POST["sampleLogoId"]) && set_option("sampleGalleryId", $_POST["sampleGalleryId"]))
		postMessage("Başarıyla Güncellendi!");
	else
		postMessage("Hata Oluştu!", true);
}

?>

<form method="post">
	<label>Logo</label>
	<input type="file" name="sampleLogoId" fileid="<?php echo get_option("sampleLogoId"); ?>" />
	<label>Galeri</label>
	<input type="gallery" name="sampleGalleryId" value="<?php echo get_option("sampleGalleryId"); ?>" />
	<label>Başlık:</label>
	<input type="text" i18n="sampleHeaderI18n" />
	<label>Yazı:</label>
	<textarea i18n="sampleCommentI18n" type="editor"></textarea>
	<input type="submit" name="action" value="Kaydet" />
</form>
<?php
