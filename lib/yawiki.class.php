<?php

// =====================================================================
// 
// This program is part of Yet Another Wiki (Yawiki).  For more
// information, please visit http://yawiki.com/ at your convenience.
// 
// Copyright (C) 2004 Paul M. Jones. <pmjones@ciaweb.net>
// 
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
// your option) any later version.
// 
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// General Public License for more details.
//
// http://www.gnu.org/copyleft/gpl.html
//
// ---------------------------------------------------------------------
//
// $Id: yawiki.class.php,v 1.18 2006/02/22 07:13:46 delatbabel Exp $
//
// =====================================================================

/**
* 
* This class keeps track of the YaWiki environment: which wiki you're in,
* which page you're on, the wiki map, etc.  We do this to encapsulate
* the variables and objects away from the local scripts.
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/

require_once 'Text/Wiki.php';
require_once 'yawiki_acl.class.php';
require_once 'yawiki_areas.class.php';
require_once 'yawiki_hits.class.php';
require_once 'yawiki_links.class.php';
require_once 'yawiki_locks.class.php';
require_once 'yawiki_search.class.php';
require_once 'yawiki_store.class.php';

if (! defined('ABSOLUTE_URL')) {
	define('ABSOLUTE_URL', 'http://' . HTTP_HOST . HREF_BASE);
}

class yawiki {
	
	
	var $conf = array(
		'absolute_url'    => ABSOLUTE_URL,
		'default_area'     => 'Main',
		'default_mod_rewrite_area' => 'Main',
		'default_page'     => 'HomePage',
		'only_area'        => false
	);
	
	
	/**
	* 
	* A yawiki_acl object instance.
	* 
	* @access public
	*
	* @var object
	* 
	*/
	
	var $acl;
	
	
	/**
	* 
	* The API version (release number) of Yawiki.
	* 
	* @access public
	*
	* @var string
	* 
	*/
	
	var $apiVersion;
	
	
	/**
	* 
	* The name of the area being viewed as determined by the GET var
	* and the yawiki config file defaults.
	* 
	* @access public
	*
	* @var string
	* 
	*/
	
	var $area;
	
	
	/**
	* 
	* The list of info about areas in the wiki.
	* 
	* @access public
	*
	* @var array
	* 
	*/
	
	var $areaInfo = null;
	
	
	/**
	* 
	* A yawiki_areas object instance.
	* 
	* @access public
	*
	* @var object
	* 
	*/
	
	var $areas;
	
	
	/**
	* 
	* The date-time version of the page being viewed; null means the
	* most-current version.
	* 
	* @access public
	*
	* @var string
	* 
	*/
	
	var $dt;
	
	
	/**
	* 
	* A yawiki_hits object instance.
	* 
	* @access public
	*
	* @var object
	* 
	*/
	
	var $hits;
	
	
	/**
	* 
	* A yawiki_links object instance.
	* 
	* @access public
	*
	* @var object
	* 
	*/
	
	var $links;
	
	
	/**
	* 
	* A yawiki_locks object instance.
	* 
	* @access public
	*
	* @var object
	* 
	*/
	
	var $locks;
	
	
	/**
	* 
	* Arrray of navigational elements for the current page; nav[0] is
	* the top-level, nav[1] is the subsection, and nav[3] is the
	* sub-subsection.  nav['map'] is a link to the AreaMap page.
	* 
	* @access public
	*
	* @var array
	* 
	*/
	
	var $nav;
	
	
	
	/**
	* 
	* An array of area maps (the page hierarchies, titles, etc).
	* 
	* @access public
	*
	* @var array
	* 
	*/
	
	var $map;
	
	
	/**
	* 
	* The name of the page being viewed as determined by the GET var
	* and the yawiki config file defaults.
	* 
	* @access public
	*
	* @var string
	* 
	*/
	
	var $page;
	
	
	/**
	* 
	* A Text_Wiki object instance.
	* 
	* @access public
	*
	* @var object
	* 
	*/
	
	var $parse;
	
	
	/**
	* 
	* The "path" of pages leading to the current page (as determined by
	* AreaMap).
	* 
	* @access public
	*
	* @var array
	* 
	*/
	
	var $path;
	
	
	/**
	* 
	* The username currently signed in; if not signed in, the user's IP
	* address.
	* 
	* @access public
	*
	* @var string
	* 
	*/
	
	var $username;
	
	
	/**
	* 
	* A yawiki_search object instance.
	* 
	* @access public
	*
	* @var object
	* 
	*/
	
	var $search;
	
	
	/**
	* 
	* A yawiki_store object instance.
	* 
	* @access public
	*
	* @var object
	* 
	*/
	
	var $store;
	
	
	/**
	* 
	* Is the current page from the wiki? (As opposed to an admin script.)
	* 
	* @access public
	*
	* @var array
	* 
	*/
	
	var $isContentPage = false;
	
	
	
	/**
	* 
	* Constructor.
	* 
	* @access public
	*
	* @return object A yawiki object.
	* 
	*/
	
	function yawiki()
	{
		// get the API version if we can
		$this->apiVersion = Yawp::getFile(
			dirname(__FILE__) . '/../docs/VERSION'
		);
		
		// Yawp.conf.php overrides the default $conf
		$this->conf = array_merge(
			$this->conf,
			Yawp::getConfGroup('yawiki')
		);
		
		// the current username, or IP address if not signed in
		$this->username = Yawp::authUsername();
		if (! $this->username) {
			$tmp_user = htmlspecialchars(strip_tags($_SERVER['REMOTE_ADDR']));
			$this->username = $tmp_user;
		}
		
		// the requested area; if only_area is set, force to that area.
		if ($this->conf['only_area']) {
			$this->area = $this->conf['only_area'];
		} else {
			$this->area = Yawp::getGet('area', $this->conf['default_area']);
		}
		
		// the requested page name
		$this->page = Yawp::getGet('page', $this->conf['default_page']);
		
		// the requested page version date-time
		$this->dt = Yawp::getGet('dt', null);
		
		// access control object
		$this->acl =& new yawiki_acl();
		if ($this->acl->error) {
			echo "yawiki_acl error";
			Yawp::dump($this->acl->error);
			die();
		}
		
		// subwiki area object
		$this->areas =& new yawiki_areas();
		if ($this->areas->error) {
			echo "yawiki_area error";
			Yawp::dump($this->areas->error);
			die();
		}
		
		// page storage object
		$this->store =& new yawiki_store();
		if ($this->store->error) {
			echo "yawiki_store error";
			Yawp::dump($this->store->error);
			die();
		}
		
		// link tracking object
		$this->links =& new yawiki_links();
		if ($this->links->error) {
			echo "yawiki_links error";
			Yawp::dump($this->links->error);
			die();
		}
		
		// page-lock object
		$this->locks =& new yawiki_locks();
		if ($this->locks->error) {
			echo "yawiki_locks error";
			Yawp::dump($this->locks->error);
			die();
		}
		
		// view-hits object
		$this->hits =& new yawiki_hits();
		if ($this->hits->error) {
			echo "yawiki_hits error";
			Yawp::dump($this->hits->error);
			die();
		}
		
		// search object
		$this->search =& new yawiki_search();
		if ($this->search->error) {
			echo "yawiki_search error";
			Yawp::dump($this->search->error);
			die();
		}
		
		// build the default area information
		$this->loadAreaInfo();
		
		// build the Text_Wiki parser
		$this->_parseSetup();
		
		// is this a content page (as opposed to an admin page or a
		// special view)?
		$tmp = basename(htmlspecialchars(strip_tags($_SERVER['SCRIPT_NAME'])));
		if ( $tmp == 'edit.php') {
			$this->isContentPage = true;
		} elseif ($tmp == 'index.php' && ! Yawp::getGet('view', false)) {
			$this->isContentPage = true;
		} else {
			$this->isContentPage = false;
		}
		
		// build the map for this area
		$this->_buildMap();
		
		// build the navigational elements
		$this->_navSetup();
		
	}
	
	
	/**
	* 
	* Gets the HTML <title>...</title> string for the current page.
	* 
	* @access public
	*
	* @return void
	* 
	*/
	
	function getHtmlTitle()
	{
		// set up the page title
		if ($this->isContentPage) {
			
			if (isset($this->map[$this->area]['show'][$this->page]) &&
				$this->map[$this->area]['show'][$this->page] != $this->page) {
				// use the 'show' text
				$htmlTitle = $this->map[$this->area]['show'][$this->page];
			} else {
				// if the nav title and the page name are the same,
				// show a hierarchy instead.
				// for content pages, the name is based on the
				// area and page-path
				$htmlTitle = $this->area;
				
				if (count($this->path)) {
					$htmlTitle .= ' -- ' .
					implode(' : ', $this->path);
				} else {
					$htmlTitle .= ' : ' . $this->page;
				}
			}
		} else {
			
			// for non-content (administrative) pages, the
			// name is based on the script name.
			$htmlTitle = basename(strip_tags($_SERVER['SCRIPT_NAME']));
			$htmlTitle = str_replace('.php', '', $htmlTitle);
			$htmlTitle = str_replace('_', ' ', $htmlTitle);
			$htmlTitle = ucwords($htmlTitle);
			
		}
		
		return $htmlTitle;
	}
	
	
	/**
	* 
	* Builds the $this->parse object.
	* 
	* @access public
	*
	* @return void
	* 
	*/
	
	function _parseSetup()
	{
		// =============================================================
		//
		// Build and configure the basic Text_Wiki object
		//
		
		// grab a rule set, if one is specified
		$rules = Yawp::getConfElem('Text_Wiki', 'rule');
		if ($rules) {
			settype($rules, 'array');
			foreach ($rules as $key => $val) {
				// trim and force an inital upper-case character
				$rules[$key] = trim(ucwords(strtolower($val)));
			}
		}
		
		// base Text_Wiki object
		$this->parse =& new Text_Wiki($rules);
		
		// parse paths; reverse them because we want the first listed to be
		// the first searched.
		$tmp = Yawp::getConfElem('Text_Wiki_Parse', 'path', array());
		settype($tmp, 'array');
		$tmp = array_reverse($tmp);
		foreach ($tmp as $dir) {
			$this->parse->addPath('parse', $dir);
		}
		
		// render paths; reverse them because we want the first listed to be
		// the first searched.
		$tmp = Yawp::getConfElem('Text_Wiki_Render', 'path', array());
		settype($tmp, 'array');
		$tmp = array_reverse($tmp);
		foreach ($tmp as $dir) {
			$this->parse->addPath('render', $dir);
		}
		
		// enable rules
		$tmp = Yawp::getConfElem('Text_Wiki', 'enable');
		settype($tmp, 'array');
		foreach ($tmp as $rule) {
			$rule = trim(ucwords(strtolower($rule)));
			$this->parse->enableRule($rule);
		}
		
		// disable rules
		$tmp = Yawp::getConfElem('Text_Wiki', 'disable');
		settype($tmp, 'array');
		foreach ($tmp as $rule) {
			$rule = trim(ucwords(strtolower($rule)));
			$this->parse->disableRule($rule);
		}
		
		// get the XHTML format configs
		foreach (Yawp::getConfGroup('Text_Wiki_Render_Xhtml') as $key => $val) {
			$this->parse->setFormatConf('Xhtml', $key, $val);
		}
		
		// loop through all rules in the set,
		// grab the parse and render configs for each rule,
		// and apply them.
		foreach ($this->parse->rules as $rule) {
			
			// parse config
			$tmp = Yawp::getConfGroup("Text_Wiki_Parse_$rule");
			foreach ($tmp as $key => $val) {
				$this->parse->setParseConf($rule, $key, $val);
			}
			
			// render config
			$tmp = Yawp::getConfGroup("Text_Wiki_Render_Xhtml_$rule");
			foreach ($tmp as $key => $val) {
				$this->parse->setRenderConf('Xhtml', $rule, $key, $val);
			}
		}
		
		// =============================================================
		//
		// Override the basic config with YaWiki specifics
		//
		
		// -------------------------------------------------------------
		//
		// pages in the wiki for intra-links
		//
		
		$pageList = $this->store->getPageList($this->area);
		$this->parse->setRenderConf('Xhtml', 'Wikilink', 'pages', $pageList);
		$this->parse->setRenderConf('Xhtml', 'Freelink', 'pages', $pageList);
		
		// -------------------------------------------------------------
		// 
		// are we running single- or multi-area?  build the view_url and
		// new_url links respectively.
		//
		
		if ($this->conf['only_area']) {
			// single-area links
			$view_url = 'index.php?page=%s';
			$new_url = 'edit.php?page=%s';
		} else {
			// multi-area
			if ($this->conf['default_mod_rewrite_area'] == $this->area) {
				$view_url = $this->conf['absolute_url']."%s";
			} else {
				$view_url = $this->conf['absolute_url']."{$this->area}/%s";
			}
			$new_url = $this->conf['absolute_url']."edit.php?area={$this->area}&page=%s";
		}
		
		$this->parse->setRenderConf('Xhtml', 'Wikilink', 'view_url', $view_url);
		$this->parse->setRenderConf('Xhtml', 'Wikilink', 'new_url', $new_url);
		
		$this->parse->setRenderConf('Xhtml', 'Freelink', 'view_url', $view_url);
		$this->parse->setRenderConf('Xhtml', 'Freelink', 'new_url', $new_url);
		
		
		// -------------------------------------------------------------
		//
		// build InterWiki sites list
		//
		
		// what's in the parser right now
		$sites = $this->parse->getRenderConf('Xhtml', 'Interwiki', 'sites');
		
		// add [yawiki] interwiki_site configs
		if (isset($this->conf['interwiki_site'])) {
			settype($this->conf['interwiki_site'], 'array');
			foreach ($this->conf['interwiki_site'] as $val) {
				$tmp = explode(',', $val);
				$sites[trim($tmp[0])] = trim($tmp[1]);
			}
		}
		
		// add other local areas from the store if we are multi-area
		if (! $this->conf['only_area']) {
			foreach (array_keys($this->areaInfo) as $val) {
				$sites[$val] = "index.php?area=$val&page=%s";
			}
		}
		
		// send back to the parser
		$this->parse->setRenderConf('Xhtml', 'Interwiki', 'sites', $sites);
	}
	
	
	function getViewLink($area, $page, $dt = null)
	{
		$text = "index.php?area=$area&page=$page";
		if ($dt) {
			$text .= "&dt=$dt";
		}
		return $text;
	}
	
	
	/**
	* 
	* Builds the $this->nav array of navigational elements.
	* 
	* @access public
	*
	* @return void
	* 
	*/
	
	function _navSetup()
	{
		// get the path to the current page
		$this->path = $this->getPath($this->area, $this->page);
		
		// reset the navigational elements
		$this->nav = array();
		
		// we use the view URL a lot
		$view_url = $this->parse->getRenderConf('xhtml', 'wikilink', 'view_url');
		
		// is there a path?  (if not, the page is not on the map)
		if (count($this->path) > 0) {
			
			// get nav elements leading to this page...
			foreach ($this->path as $key => $val) {
					
				if ($key == 0) {
				
					// get the map tops
					$this->nav[$key] = $this->_tops($val, $view_url);
					
				} elseif (isset($this->path[$key-1])) {
				
					// get children
					$prev_page = $this->path[$key-1];
					$this->nav[$key] = $this->_kids($prev_page, $val, $view_url);
					
				}
				
			}

			// and get the first nav element below this page.
			$prev_page = $val;
			$this->nav[$key+1] = $this->_kids($prev_page, $this->page,
				$view_url, true);
			
		} else {

			// Set the nav element to the top level page list for the area, so
			// that the list of top level pages is shown even if we move outside
			// the area map.
			$this->nav[0] = $this->_tops("", $view_url);
		}
			
		
		// nav to AreaMap, edit, history, and links
		$this->nav['map'] =	sprintf($view_url, 'AreaMap');
		$this->nav['history'] = sprintf($view_url, $this->page) . '&view=history';
		$this->nav['links'] = sprintf($view_url, $this->page) . '&view=links';
		$this->nav['source'] = sprintf($view_url, $this->page) . '&view=source';
		$this->nav['rss'] = sprintf($this->conf['absolute_url']."rss.php?area=%s&page=%s", $this->area, $this->page);
		$this->nav['edit'] = sprintf($this->conf['absolute_url']."edit.php?area=%s&page=%s", $this->area, $this->page);
	}
	
	
	function _tops($sel_page = null, $href = null)
	{
		$nav = array();
		foreach ($this->map[$this->area]['tops'] as $key => $val) {
			$nav[] = array(
				'page' => $val,
				'text' => $this->map[$this->area]['show'][$val],
				'href' => sprintf($href, $val),
				'selected' => ($val == $sel_page) ? 'selected' : ''
			);
		}
		return $nav;
	}
	
	function _kids($page, $sel_page = null, $href = null)
	{
		$nav = array();
		
		if (! isset($this->map[$this->area]['kids'][$page])) {
			return $nav;
		}
		
		// always add the parent
		$nav[] = array(
			'page' => $page,
			'text' => $this->map[$this->area]['show'][$page],
			'href' => sprintf($href, $page),
			'selected' => ($page == $sel_page) ? 'selected' : ''
		);
		
		// now build the hrefs
		foreach ($this->map[$this->area]['kids'][$page] as $key => $val) {
			$nav[] = array(
				'page' => $val,
				'text' => $this->map[$this->area]['show'][$val],
				'href' => sprintf($href, $val),
				'selected' => ($val == $sel_page) ? 'selected' : ''
			);
		}
		
		return $nav;
	}
	
	/**
	* 
	* Gets a navigational element by key.
	* 
	* If numeric, it's assumed to be a navigation level (0 is the first
	* level, 1 is the second, and so on). If a string, it's assumed to
	* be a navigational link ('map', 'links', and so on).
	* 
	* @access public
	*
	* @param int|string $key The navigational key to get.
	*
	* @return mixed An array of navigational elements for a level or a string
	* link.  If the key does not exists, returns an empty array for numeric
	* keys and a null for string keys.
	* 
	*/
	
	function getNav($key)
	{
		if (isset($this->nav[$key])) {
			return $this->nav[$key];
		}
		
		if (is_numeric($key)) {
			return array();
		} else {
			return null;
		}
	}
	
	
	/**
	* 
	* Can the current username view the view the current area and page?
	* 
	* @access public
	*
	* @return bool
	* 
	*/
	
	function userCanViewPage()
	{
		// use Yawp::authUsername() instead of $this->username because
		// we need to know if the user is authenticated or not.
		return $this->acl->pageView(Yawp::authUsername(), $this->area, 
			$this->page);
	}
	
	
	/**
	* 
	* Can the current user view the view the current area and page?
	* 
	* @access public
	*
	* @return bool
	* 
	*/
	
	function userCanEditPage()
	{
		// use Yawp::authUsername() instead of $this->username because
		// we need to know if the user is authenticated or not.
		return $this->acl->pageEdit(Yawp::authUsername(), $this->area, 
			$this->page);
	}
	
	
	/**
	* 
	* Is the current user a system administrator?
	* 
	* @access public
	*
	* @return bool
	* 
	*/
	
	function userIsWikiAdmin() {
		// use Yawp::authUsername() instead of $this->username because
		// we need to know if the user is authenticated or not.
		return $this->acl->wikiAdmin(Yawp::authUsername());
	}
	
	
	/**
	* 
	* Is the current user administrator of the current area?
	* 
	* @access public
	*
	* @return bool
	* 
	*/
	
	function userIsAreaAdmin() {
		// use Yawp::authUsername() instead of $this->username because
		// we need to know if the user is authenticated or not.
		return $this->acl->areaAdmin(Yawp::authUsername(), $this->area);
	}
	
	
	/**
	* 
	* Is the current user administrator on the current page?
	* 
	* @access public
	*
	* @return bool
	* 
	*/
	
	function userIsPageAdmin() {
		// use Yawp::authUsername() instead of $this->username because
		// we need to know if the user is authenticated or not.
		return $this->acl->pageAdmin(Yawp::authUsername(), $this->area, $this->page);
	}
	
	
	/**
	* 
	* Gets all the data for the current page; performs auto-caching.
	* 
	* @access public
	*
	* @return array An associative array of the page data.
	* 
	*/
	
	function getPage($area = null, $page = null, $dt = null)
	{
		if (is_null($area)) {
			$area = $this->area;
		}
		
		if (is_null($page)) {
			$page = $this->page;
		}
		
		if (is_null($dt)) {
			$dt = Yawp::getGet('dt', null);
		}
		
		$data = $this->store->getPage($area, $page, $dt);
		
		// "where" clause for creating or clearing the cache as needed
		$where = 'area = ' . $this->store->quote($data['area']) .
			' AND page = ' . $this->store->quote($data['page']) . 
			' AND dt = ' . $this->store->quote($data['dt']);
		
		// are we caching HTML?
		if ($this->getAreaInfo($area, 'cache_html')) {
			
			// yes, we're caching HTML.
			// is there HTML in place?
			if (! isset($data['html']) || empty($data['html'])) {
				
				// no, transform from wiki body to displayed HTML
				$data['html'] = $this->transform($data['body'], $data['page']);
				
				// now update it back to storage
				$result = $this->store->update(
					array('html' => $data['html']),
					$where
				);
			}
			
		} else {
			
			// no, we're not caching HTML.
			// clear the cached HTML if it exists.
			if (isset($data['html']) && ! empty($data['html'])) {
				$result = $this->store->update(
					array('html' => null),
					$where
				);
			}
			
			// now transform the body into html
			$data['html'] = $this->transform($data['body'], $data['page']);
		}
		
		// done!
		return $data;
	}
	
	
	
	/**
	* 
	* Get the list of date-time versions for the current page.
	* 
	* @access public
	*
	* @return array A sequential array of date-time versions for the
	* current page.
	* 
	*/
	
	function getVersionList($limit = null)
	{
		return $this->store->getVersionList($this->area, $this->page, 
			$limit);
	}
	
	
	/**
	* 
	* Loads the all info about all allowed areas in the wiki.
	* 
	* @access public
	*
	* @return void
	* 
	*/
	
	function loadAreaInfo($force_refresh = false)
	{
		if (is_null($this->areaInfo) || $force_refresh) {
			$tmp = $this->areas->getList();
			$this->areaInfo = array();
			foreach ($tmp as $key => $val) {
				// checks to see if an area, in general, is open to this
				// user.  basically, you have to turn off "page_view"
				// privs for * pages in the area.
				if ($this->acl->pageView(Yawp::authUsername(), $val['name'], '*')) {
					if (trim($val['title']) == '') {
						$val['title'] = $val['name'];
					}
					$this->areaInfo[$val['name']] = $val;
				}
			}
		}
	}
	
	
	/**
	* 
	* Gets information about a specific area in the wiki.
	* 
	* @access public
	*
	* @param string $area The area you want to get info about.  By default,
	* uses the current area.
	* 
	* @param string $key The key you want (e.g., 'name' or 'title').  If not
	* specified, returns the entire array of info about the area.
	* 
	* @return mixed The requested info.
	* 
	*/
	
	function getAreaInfo($area = null, $key = null)
	{
		// by default, use the current area
		if (is_null($area)) {
			$area = $this->area;
		}
		
		// is it a recognized area?
		if (! isset($this->areaInfo[$area])) {
			// no, should we return an empty array
			// or a boolean false?
			if (is_null($key)) {
				return $this->areas->getBlankRow();
			} else {
				return false;
			}
		}
		
		if (is_null($key)) {
			return $this->areaInfo[$this->area];
		} elseif (isset($this->areaInfo[$this->area][$key])) {
			return $this->areaInfo[$this->area][$key];
		} else {
			return false;
		}
	}
	
	
	/**
	* 
	* Transforms Wiki text into XHTML.
	* 
	* @access public
	*
	* @param string $text The wiki text to transform.
	*
	* @param string $page The page name; if 'AreaMap' then apply special
	* transformation rules.
	*
	* @return string The HTML transformation of the wiki text.
	* 
	*/
	
	function transform($text, $page = null)
	{
		if (strtolower($page) == 'areamap') {
			$html = $this->_transformAreaMap($text);
		} else {
			$html = $this->parse->transform($text);
		}
		
		return $html;
	}
	
	function _transformAreaMap($text)
	{
		// we need this information
		$view_url = $this->parse->getRenderConf('xhtml', 'freelink', 'view_url');
		$new_url = $this->parse->getRenderConf('xhtml', 'freelink', 'new_url');
		$pageList = $this->parse->getRenderConf('Xhtml', 'Freelink', 'pages');
		
		// treat every line as a free-linked line, with the
		// exception that we don't use any text after the first
		// pipe character.
		$text = htmlspecialchars(trim($text));
		$lines = explode("\n", $text);
		
		$pagelines = array();
		foreach ($lines as $line) {
			
			// count number of spaces at the beginning of the line
			// and set the level based on the count
			preg_match('/^( {0,})(.*)/', $line, $matches);
			$level = strlen($matches[1]);
			$spaces = str_pad('', $level);
			
			// trim off the depth-level spaces
			$line = trim($line);
			
			// skip blank lines
			if ($line == '') {
				continue;
			}
			
			// split the line into its 'page' and 'after' parts; the
			// 'after' is the part after the first pipe character on the
			// line (including the pipe character itself).
			$pos = strpos($line, '|');
			if ($pos === false) {
				$page = $line;
				$after = '';
			} else {
				$page = trim(substr($line, 0, $pos));
				$after = substr($line, $pos);
			}
			
			// make the proper link...
			if (in_array($page, $pageList)) {
				// page exists
				$link = sprintf($view_url, $page);
				$pagelines[] = "$spaces<a href=\"$link\">$page</a> $after";
			} else {
				// page does not exist
				$link = sprintf($new_url, $page);
				$pagelines[] = "$spaces$page<a href=\"$link\">?</a> $after";
			}
		}
		
		$html = "<pre><code>" . implode("\n", $pagelines) . "</code></pre>";
		return $html;
	}
	
	
	/**
	* 
	* Gets a diff on the sources for the current page.
	* 
	* @access public
	*
	* @param string $from The date-time of the version you are diffing from.
	*
	* @param string $from The date-time of the version you are diffing to.
	*
	* @return array An associative array: 'info' => array of info about
	* the diff, and 'diff' => array of difference objects.
	* 
	*/
	
	function getDiff($from_version, $to_version = null)
	{
		include_once 'Text/Diff.php';
		
		$info = array();
		
		// from
		$tmp = $this->store->getPage($this->area, $this->page, $from_version);
		$tmp['body'] = htmlspecialchars($tmp['body']);
		$from_lines = explode("\n", $tmp['body']);
		$info['from']['dt'] = $tmp['dt'];
		$info['from']['username'] = $tmp['username'];
		
		// to
		$tmp = $this->store->getPage($this->area, $this->page, $to_version);
		$tmp['body'] = htmlspecialchars($tmp['body']);
		$to_lines = explode("\n", $tmp['body']);
		$info['to']['dt'] = $tmp['dt'];
		$info['to']['username'] = $tmp['username'];
		
		// diff and return
		$diff =& new Text_Diff($from_lines, $to_lines);
		return array(
			'info' => $info,
			'diff' => $diff->getDiff()
		);
	}
	
	
	function getSearchResult($word, $area = null, $type = 'all')
	{
		// if only one area being shown, force to look
		// only in that area.
		if ($this->conf['only_area']) {
			$area = array($this->conf['only_area']);
		}
		
		$result = $this->search->getResult($word, $area, $type);
		
		$list = array();
		
		// only return pages that the user is allowed to view
		while ($row = $result->fetchRow()) {
			
			$ok = $this->acl->pageView(
				Yawp::authUsername(),
				$row['area'],
				$row['page']
			);
			
			if ($ok) {
				$row['path'] = array();
				foreach ($this->getPath($row['area'], $row['page']) as $val) {
					$row['path'][] = $this->map[$row['area']]['show'][$val];
				}
				$list[] = $row;
			}
		}
		
		return $list;
	}
	
	
	
	/**
	* 
	* Get the path hierarchy for a page.
	* 
	* @access public
	* 
	* @param string $area The area to work with.
	* 
	* @param string $page The page to get the path for.
	* 
	* @return array An array of page names leading to the requested
	* page.
	* 
	*/
	
	function getPath($area, $page)
	{
		$this->_buildMap($area);
		if (isset($this->map[$area]['path'][$page])) {
			return $this->map[$area]['path'][$page];
		} else {
			return array();
		}
	}
	
	
	function _buildMap($area = null)
	{
		if (is_null($area)) {
			$area = $this->area;
		}
		
		$tmp = $this->store->getPage($area, 'AreaMap');
		$text = $tmp['body'];
		unset($tmp);
		
		$this->map[$area] = array(
			'tops' => array(),
			'path' => array(),
			'kids' => array(),
			'show' => array()
		);
		
		$stack = array();
		$lines = explode("\n", $text);
		
		foreach ($lines as $line) {
			
			// count number of spaces at the beginning of the line
			// and set the level based on the count
			preg_match('/^( {0,})(.*)/', $line, $matches);
			$level = strlen($matches[1]);
			
			// trim off the depth-level spaces
			$line = trim($line);
			
			// skip blank lines
			if ($line == '') {
				continue;
			}
			
			// split the line into its 'page' and 'show' variants; the
			// 'show' is the part after the first pipe character on the
			// line.
			$pos = strpos($line, '|');
			if ($pos === false) {
				$page = $line;
				$show = $page;
			} else {
				$page = trim(substr($line, 0, $pos));
				$show = trim(substr($line, $pos+1));
			}
			
			// always have an array of 'kids' (children) for each page,
			// even if it's empty
			if (! isset($this->map[$area]['kids'][$page])) {
				$this->map[$area]['kids'][$page] = array();
			}
			
			// prune the stack so that the function does not break
			// when there are extra spaces where they shouldn't be
			while (count($stack) - 1 > $level) {
				array_pop($stack);
			}
			
			// set the stack-path for the current level
			$stack[$level] = $page;
			
			// this page is a kid to the previous level.
			// makes sure there is a stack set at the previous level
			// before adding to it.
			if ($level > 0 && isset($stack[$level-1])) {
				$this->map[$area]['kids'][$stack[$level-1]][] = $page;
			}
			
			// save the path to this page.
			$this->map[$area]['path'][$page] = $stack;
			
			// is this a top-level (root-level) page?
			if ($level == 0) {
				$this->map[$area]['tops'][] = $page;
			}
			
			// save the page 'show' text
			$this->map[$area]['show'][$page] = $show;
		}
		
		// normally, the 'show' text is the page name.
		// if 'show' text is specified in the AreaMap, we
		// will have used it already. finally, this
		// will set up the 'show' text as the title for
		// the page, if any has been chosen.
		$title = $this->search->getTitles($area);
		foreach ($this->map[$area]['show'] as $page => $val) {
			if ($page == $val &&
				isset($title[$page]) &&
				trim($title[$page]) != '') {
				$this->map[$area]['show'][$page] = $title[$page];
			}
		}
		
		// done!
		return;
	}
	
	// because using array_walk with just htmlspecialchars throws errors,
	// you can use yawiki::htmlspecialchars() instead.
	function htmlspecialchars(&$value)
	{
		$value = htmlspecialchars($value);
	}
}

?>
