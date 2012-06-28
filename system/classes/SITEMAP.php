<?php
class PA_SITEMAP extends DB
{
	private $table;
	private $table_i18n;
	
	function PA_SITEMAP()
	{
		parent::DB();
		
		$this->table = $this->tables->sitemap;
		$this->table_i18n = $this->tables->i18n;
	}
	
	function setSiteMap($page_id, $page_url, $page_title, $page_description, $page_parent = -1)
	{
		if(!saveI18n())
		{
			return false;	
		}
		
		if($this->selectSiteMap($page_id))
			return $this->update($this->table, array("page_parent"=>$page_parent, "page_url"=>$page_url), array("page_id"=>$page_id));
		else
			return $this->insert($this->table, array("page_id"=>$page_id, "page_parent"=>$page_parent, "page_url"=>$page_url, "page_title"=>$page_title, "page_description"=>$page_description));
	}
	
	function selectSiteMap($page_id)
	{
		return $this->get_row("SELECT * FROM {$this->table} WHERE page_id=?", array($page_id));
	}
	
	function selectSiteMapByUrl($page_url)
	{
		return $this->get_row("SELECT * FROM {$this->table} WHERE page_url=?", array($page_url));
	}
	
	function listSitemaps($return_empty_urls = false)
	{
		global $ADMIN;
		$language = $ADMIN->I18N->language;
		
		$query  = "SELECT sm.*, pt.{$language} AS page_title, pd.{$language} AS page_description FROM {$this->table} AS sm ";
		$query .= "LEFT JOIN {$this->table_i18n} AS pt ON sm.page_title=pt.i18nCode ";
		$query .= "LEFT JOIN {$this->table_i18n} AS pd ON sm.page_description=pd.i18nCode ";
		$query .= $return_empty_urls ? "" : "WHERE sm.page_url != '' AND sm.page_url IS NOT NULL";
		
		return $this->get_rows($query);
	}
	
	function listSitemapsByParentAsHtmlTree($page_parent = null, $list_sub_sitemaps = true, $return_empty_urls = false)
	{
		global $ADMIN;
		$language = $ADMIN->I18N->language;
		
		$query  = "SELECT sm.*, pt.{$language} AS page_title, pd.{$language} AS page_description FROM {$this->table} AS sm ";
		$query .= "LEFT JOIN {$this->table_i18n} AS pt ON sm.page_title=pt.i18nCode ";
		$query .= "LEFT JOIN {$this->table_i18n} AS pd ON sm.page_description=pd.i18nCode ";
		$query .= $return_empty_urls ? "WHERE sm.page_url != '' AND sm.page_url IS NOT NULL AND sm.page_parent=?" : "WHERE sm.page_parent=?";
		
		if($sitemaps = $this->get_rows($query, array($page_parent)))
		{
			$sitemap_count = sizeof($sitemaps);
			$treeHtml  = "<ul>";
			for($i=0; $i<$sitemap_count; $i++)
			{
				$treeHtml .= "<li>";
				$treeHtml .= $sitemaps[$i]->page_title;
				if($list_sub_sitemaps)
				{
					$treeHtml .= $this->listSitemapsByParentAsHtmlTree($sitemaps[$i]->page_id);
				}
				$treeHtml .= "</li>";
			}
			$treeHtml .= "</ul>";
			return $treeHtml;
		}
		else
			return "";
	}
	
	function listSitemapsByParentAsArrayTree($page_parent = null, $list_sub_sitemaps = true, $return_empty_urls = false)
	{
		global $ADMIN;
		$language = $ADMIN->I18N->language;
		
		$query  = "SELECT sm.*, pt.{$language} AS page_title, pd.{$language} AS page_description FROM {$this->table} AS sm ";
		$query .= "LEFT JOIN {$this->table_i18n} AS pt ON sm.page_title=pt.i18nCode ";
		$query .= "LEFT JOIN {$this->table_i18n} AS pd ON sm.page_description=pd.i18nCode ";
		$query .= $return_empty_urls ? "WHERE sm.page_url != '' AND sm.page_url IS NOT NULL AND sm.page_parent=?" : "WHERE sm.page_parent=?";
		
		$sitemap_array = array();
		
		if($sitemaps = $this->get_rows($query, array($page_parent)))
		{
			$sitemap_count = sizeof($sitemaps);
			
			for($i=0; $i<$sitemap_count; $i++)
			{
				if($list_sub_sitemaps)
				{
					$sitemap_array[$i]["sub"] = $this->listSitemapsByParentAsArrayTree($sitemaps[$i]->page_id);
				}
			}
			
			return $sitemap_array;
		}
		else
			return false;
	}
	
	function deleteSitemap($page_id)
	{
		$page = $this->selectSiteMap($page_id);
		
		return deleteI18n($page->page_title) && deleteI18n($page->page_description) && 
			$this->execute("DELETE FROM {$this->table} WHERE page_id=?", array($page_id));		
	}
}