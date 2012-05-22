<?php 

if($_POST["action"] == "Kaydet")
{
	if(saveGallery() && saveI18n() && set_option("sampleLogoId", $_POST["sampleLogoId"]) && set_option("sampleGalleryId", $_POST["sampleGalleryId"]))
		postMessage("Başarıyla Güncellendi!");
	else
		postMessage("Hata Oluştu!", true);
}

?>
<script type="text/javascript">
$(SampleStart);

function SampleStart()
{
	$("#btnEditFile").click(function(){
		var file_id = $(this).attr("file");

		try
		{
			$(this).editfile({
				file: file_id,
				onInit:function(){
					//browserFilesList.find("li").removeClass("selected");
				},
				onSaved:function(file){
					/*fileName.html(file.basename);
					btnLook.attr("href",'lookfile.php?type=' + file.type + '&url=' + MHA.encodeUTF8(file.url));
					$.ajax({
						data:"admin_action=getBrowserThumb&fileId=" + file.file_id,
						success:function(response){
							thumbObject.attr("src",response);
						}
					});*/
				}
			});
		}
		catch(e)
		{
			alert(e);
		}
		
		return false;
	});	

}
</script>
<form method="post">
	<label>Logo</label>
	<input type="file" name="sampleLogoId" fileid="<?php echo get_option("sampleLogoId"); ?>" />
	<button id="btnEditFile" file="<?php echo get_option("sampleLogoId"); ?>">Edit File</button>
	
	<label>Tarih:</label>
	<input type="date" name="birth" value="00:00:05" />
	
	<label>Galeri</label>
	<input type="gallery" name="sampleGalleryId" value="<?php echo get_option("sampleGalleryId"); ?>" />
	<label>Başlık:</label>
	<input type="text" i18n="sampleHeaderI18n" />
	<label>Yazı:</label>
	<textarea i18n="sampleCommentI18n" type="editor"></textarea>
	
	<input type="submit" name="action" value="Kaydet" />
</form>
<?php
