<?php
$sitemapList = $ADMIN->SITEMAP->listSitemaps(true);

dataGrid($sitemapList, "Site Haritası", "sitemapList", "{%page_title%}", "admin.php?page=add_sitemap_page", "admin.php?page=edit_sitemap_page&id={%page_id%}", "admin.php?page=sitemap&delete={%page_id%}");