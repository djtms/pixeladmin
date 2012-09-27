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
	
	function setSiteMap($page_id, $page_image, $page_url, $page_title, $page_description, $changefreq="monthly", $priority="0.5")
	{
		if(!saveI18n()){
			return false;	
		}
		
		if($this->selectSiteMap($page_id))
			return $this->update($this->table, array("page_image"=>$page_image, "page_url"=>$page_url, "changefreq"=>$changefreq, "priority"=>$priority, "modified_date"=>"NOW()"), array("page_id"=>$page_id));
		else
			return $this->insert($this->table, array("page_id"=>$page_id, "page_image"=>$page_image, "page_url"=>$page_url, "page_title"=>$page_title, "page_description"=>$page_description, "changefreq"=>$changefreq, "priority"=>$priority, "modified_date"=>"NOW()"));
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
	
	function listSitemapsForSearchEngines()
	{
		return $this->get_rows("SELECT * FROM {$this->table} WHERE page_url != '' AND page_url IS NOT NULL");
	}
	
	function deleteSitemap($page_id){
		$page = $this->selectSiteMap($page_id);
		
		return deleteI18n($page->page_title) && deleteI18n($page->page_description) && 
			$this->execute("DELETE FROM {$this->table} WHERE page_id=?", array($page_id));		
	}
}