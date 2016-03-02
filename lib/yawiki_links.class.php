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
// $Id: yawiki_links.class.php,v 1.1 2004/10/01 18:28:50 pmjones Exp $
//
// =====================================================================

/**
* 
* This class lets you see which area pages link to each other.
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/

require_once 'Yawp/Table.php';

class yawiki_links extends Yawp_Table {
	
	
	/**
	* 
	* Column definitions.
	* 
	*/
	
	var $col = array(
		'src_area' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'require' => true
		),
		
		'src_page' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'require' => true
		),
		
		'tgt_area' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'require' => true
		),
		
		'tgt_page' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'require' => true
		)
	);
	
	
	/**
	* 
	* Index definitions.
	* 
	*/
	
	var $idx = array(
		'src_area' => 'normal',
		'src_page' => 'normal',
		'tgt_area' => 'normal',
		'tgt_page' => 'normal'
	);
	
	
	/**
	* 
	* SQL query definitions.
	* 
	*/
	
	var $sql = array(
		'list' => array(
			'select'   => '*',
			'order'    => 'src_area, src_page, tgt_area, tgt_page',
			'get'      => 'all'
		),
		
		'item' => array(
			'select'   => '*',
			'get'      => 'row'
		),
		
		'targets' => array(
			'select'   => "DISTINCT tgt_area AS area, tgt_page AS page",
			'order'    => 'tgt_area, tgt_page',
			'get'      => 'all'
		),
		
		'sources' => array(
			'select'   => "DISTINCT src_area AS area, src_page AS page",
			'order'    => 'src_area, src_page',
			'get'      => 'all'
		)
	);
	
	
	/**
	* 
	* Get the links leading out of a specific page (i.e., the targets
	* from a source).
	* 
	* @access public
	* 
	* @param string $area The area in which the source page exists.
	* 
	* @param string $page The area page we're using as the starting
	* point.
	* 
	* @return array The array of target page names (outbound links).
	* 
	*/
	
	function outbound($area, $page)
	{
		$area = $this->quote($area);
		$page = $this->quote($page);
		return $this->select('targets', "src_area = $area AND src_page = $page");
	}
	
	
	/**
	* 
	* Get the links that lead into a specific page (i.e., the sources to
	* a target).
	* 
	* @access public
	* 
	* @param string $area The area in which the target page exists.
	* 
	* @param string $page The area page we're using as the ending
	* point.
	* 
	* @return array The array of target page names (outbound links).
	* 
	*/
	
	function inbound($area, $page)
	{
		$area = $this->quote($area);
		$page = $this->quote($page);
		return $this->select('sources', "tgt_area = $area AND tgt_page = $page");
	}
	
	
	/**
	* 
	* Refresh the links for source page with new target pages.
	* 
	* @todo This is a braindead way to do it; instead, should get the
	* current links, delete the ones that have been removed, update the
	* ones that exist, and insert the new ones.
	* 
	* @access public
	* 
	*/
	
	function refresh($area, $page, $targets = array())
	{
		// remove all previous links
		$s = $this->quote($area);
		$p = $this->quote($page);
		parent::delete("src_area = $s AND src_page = $p");
		
		// add new targets
		foreach ($targets as $tgt) {
			$this->insert(
				array(
					'src_area' => $area,
					'src_page'  => $page,
					'tgt_area' => $tgt['area'],
					'tgt_page'  => $tgt['page']
				)
			);
		}
	}
	
	function delete($area, $page)
	{
		$area = $this->quote($area);
		$page = $this->quote($page);
		$where = "(src_area = $area AND src_page = $page) OR (tgt_area = $area AND tgt_page = $page)";
		return parent::delete($where);
	}
	
	function deleteArea($area)
	{
		$area = $this->quote($area);
		$where = "src_area = $area OR tgt_area = $area";
		return parent::delete($where);
	}
}

?>