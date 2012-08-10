<?php require_once 'includes/config.php';

$sitemaps = $ADMIN->SITEMAP->listSitemapsForSearchEngines();
$sitemap_count = sizeof($sitemaps);
$site_base_path = get_option("admin_site_address");

header("Content-Type: text/xml");
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

for($i=0; $i<$sitemap_count; $i++)
{
	echo '<url><loc>' . $site_base_path .  $sitemaps[$i]->page_url . '</loc>';
	echo '<lastmod>' . $sitemaps[$i]->modified_date . '</lastmod>';
	
	if($sitemaps[$i]->page_parent <= 0)
	{
		echo '<priority>1.0</priority>';
	}
	
	echo '<changefreq>' . $sitemaps[$i]->changefreq . '</changefreq>';
	echo '<priority>' . $sitemaps[$i]->priority . '</priority>';
	echo '</url>';
}

echo '</urlset> ';
