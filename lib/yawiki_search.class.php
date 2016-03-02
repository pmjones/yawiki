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
// $Id: yawiki_search.class.php,v 1.1 2004/10/01 18:28:50 pmjones Exp $
//
// =====================================================================


/**
* 
* This class lets you work with a table of searchable page content.
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
*
*/

require_once 'Yawp/Table.php';

class yawiki_search extends Yawp_Table {
	
	var $conf = array(
		'ignore' => array(
			'a',
			'an',
			'and',
			'but',
			'for',
			'how',
			'not',
			'on',
			'or',
			'page',
			'that',
			'this',
			'the',
			'what',
			'when',
			'where',
			'who',
			'why',
			'with'
		),
		'limit' => 50
	);
	
	/**
	* 
	* Column definitions.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	var $col = array(
		
		// area name in which this page belongs
		'area' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'require' => true
		),
		
		// short name of the page
		'page' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'require' => true
		),
		
		// saved at this date-time
		'dt' => array(
			'type'    => 'timestamp',
			'require' => true
		),
		
		// longer title for the page (used on page, maybe in AreaMap?)
		'title' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'default' => "''"
		),
		
		// body content
		'body' => array(
			'type'    => 'clob',
			'require' => true,
			'default' => "''"
		)
	);
	
	
	/**
	* 
	* Index definitions.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	var $idx = array(
		'area'   => 'normal',
		'page'   => 'normal',
		'dt'     => 'normal',
		'title'  => 'normal'
	);
	
	
	/**
	* 
	* SQL query definitions.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	var $sql = array(
	
		// list of rows
		'list' => array(
			'select' => 'area, page, dt, title',
			'order'  => 'dt DESC',
			'get'    => 'all'
		),
		
		// list of page titles
		'titles' => array(
			'select' => 'page, title',
			'order' => 'page',
			'get' => 'assoc'
		)
	);
	
	function getTitles($area)
	{
		// we get titles from here to make sure we have the most-recent
		// information.  the 'store' table has every title ever used, 
		// and that's not always necessary.
		$where = 'area = ' . $this->quote($area);
		return $this->select('titles', $where);
	}
	
	function getResult($word, $area = null, $type = 'all')
	{
		$where = $this->buildWhere($word, $area, $type);
		return $this->selectResult('list', $where);
	}
	
	
	function buildWhere($word, $area = null, $type = 'all')
	{
		$where = '';
		
		switch (strtolower($type)) {
			
		case 'exact':
			$where .= 'page LIKE ' . $this->quote("%$word%");
			$where .= ' OR title LIKE ' . $this->quote("%$word%");
			$where .= ' OR body LIKE ' . $this->quote("%$word%");
			break;
		
		case 'any':
			
			$tmp = $this->getSqlWordArray($word);
			if (! $tmp) return false;
			
			$where .= 'page LIKE ' . implode(' OR page LIKE ', $tmp);
			$where .= ' OR title LIKE ' . implode(' OR title LIKE ', $tmp);
			$where .= ' OR body LIKE ' . implode(' OR body LIKE ', $tmp);
			break;
		
		case 'all':
		
			$tmp = $this->getSqlWordArray($word);
			if (! $tmp) return false;
			
			$where .= '(page LIKE ' . implode(' AND page LIKE ', $tmp);
			$where .= ') OR (title LIKE ' . implode(' AND title LIKE ', $tmp);
			$where .= ') OR (body LIKE ' . implode(' AND body LIKE ', $tmp);
			$where .= ')';
			break;
	
		}
				
		// are we looking in specific areas?
		settype($area, 'array');
		
		foreach ($area as $key => $val) {
			if (trim($val) == '') {
				unset($area[$key]);
			} else {
				$area[$key] = $this->quote($val);
			}
		}
		
		if (count($area) > 0) {
			$where = 'area IN (' . implode(', ', $area) . ') AND (' . $where . ')';
		}
		
		// done!
		return $where;
	}
	
	
	function getSqlWordArray($words)
	{
		$word = explode(' ', $words);
		$tmp = array();
		foreach ($word as $val) {
			$ignore = in_array(strtolower($val), $this->conf['ignore']);
			if (trim($val) != '' && ! $ignore) {
				$tmp[] = $this->quote("%$val%");
			}
		}
		
		if (count($tmp) > 0) {
			return $tmp;
		} else {
			return false;
		}
	}
	
	function getIgnoredWords($words)
	{
		$word = explode(' ', $words);
		$tmp = array();
		foreach ($word as $val) {
			if (in_array(strtolower($val), $this->conf['ignore'])) {
				$tmp[] = $val;
			}
		}
		
		if (count($tmp) > 0) {
			return $tmp;
		} else {
			return false;
		}
	}
	
	function insert($data)
	{
		// strip out the newlines and extra spaces in the search body
		$data['body'] = str_replace("\n", ' ', $data['body']);
		$data['body'] = preg_replace("/ {2,}/", " ", $data['body']);
		return parent::insert($data);
	}
	
	
	function delete($area, $page)
	{
		$area = $this->quote($area);
		$page = $this->quote($page);
		return parent::delete("area = $area AND page = $page");
	}
	
	function deleteArea($area)
	{
		$area = $this->quote($area);
		return parent::delete("area = $area");
	}
}

?>