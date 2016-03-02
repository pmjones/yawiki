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
// $Id: yacs.class.php,v 1.3 2005/08/01 16:22:13 pmjones Exp $
//
// =====================================================================


/**
* 
* Yet Another Comment System.  This class provides support for flat
* (non-threaded) user comments to an arbitrary parent article.
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/


require_once 'Yawp/Table.php';

class yacs extends Yawp_Table {
	
	var $col = array(
	
		// unique comment ID
		'id' => array(
			'type'     => 'integer',
			'require'  => true,
			'qf_type'  => 'hidden'
		),
		
		// URL of the comment page (this is the "parent post")
		'parent' => array(
			'type'     => 'varchar',
			'size'     => 255,
			'require'  => true,
			'qf_type'  => 'hidden'
		),
		
		// date and time of comment
		'dt' => array(
			'type'     => 'timestamp',
			'require'  => true,
			'qf_type'  => 'hidden'
		),
		
		// ip address of the commentor
		'ip' => array(
			'type'     => 'varchar',
			'size'     => '15',
			'require'  => true,
			'qf_type'  => 'hidden',
		),
		
		// username
		'username' => array(
			'type'     => 'varchar',
			'size'     => 255,
			'qf_label' => 'Username:',
			'qf_type'  => 'text',
			'qf_attrs' => array('size' => '10')
		),
		
		// email
		'email' => array(
			'type'     => 'varchar',
			'size'     => 255,
			'qf_label' => 'Email:',
			'qf_type'  => 'text',
			'qf_attrs' => array('size' => '30')
		),
		
		// comment subject line
		'subj' => array(
			'type'     => 'varchar',
			'size'     => 255,
			'require'  => true,
			'qf_label' => 'Subject:',
			'qf_type'  => 'text',
			'qf_attrs' => array('size' => '60')
		),
		
		// comment text
		'body' => array(
			'type'     => 'clob',
			'require'  => true,
			'qf_label' => 'Comment:',
			'qf_type'  => 'textarea',
			'qf_attrs' => array('rows' => '10', 'cols' => '60')
		)
	);
	
	var $idx = array(
		'id'     => 'unique',
		'parent' => 'normal',
		'dt'     => 'normal'
	);
	
	var $sql = array(
		'list' => array(
			'select' => '*',
			'order'  => 'dt',
			'get'    => 'all'
		)
	);
	
	
	/**
	* 
	* Customized insert (forces a sequential ID on the data).
	* 
	*/
	
	function insert($data)
	{
		$data['id'] = $this->nextID();
		$data['dt'] = date('Y-m-d H:i:s');
		$data['ip'] = strip_tags($_SERVER['REMOTE_ADDR']);
		
		$result = parent::insert($data);
		if (PEAR::isError($result)) {
			return $result;
		} else {
			return $data['id'];
		}
	}
	
	
	/**
	* 
	* Customized update (not allowed).
	* 
	*/
	
	function update()
	{
		return $this->throwError("update not allowed");
	}
	
	
	/**
	* 
	* Customized delete by ID.
	* 
	*/
	
	function delete($id)
	{
		$where = 'id = ' . $this->quote($id);
		return parent::delete($where);
	}
	
	
	/**
	* 
	* Get comments for a parent as an array.
	* 
	*/
	
	function getList($parent, $order = null, $start = null, $count = null)
	{
		$filter = 'parent = ' . $this->quote($parent);
		return parent::select('list', $filter, $order, $start, $count);
	}
	
	
	/**
	* 
	* Get comments for a parent as an DB_Result object.
	* 
	*/
	
	function getListResult($parent, $order = null, $start = null, $count = null)
	{
		$filter = 'parent = ' . $this->quote($parent);
		return parent::selectResult('list', $filter, $order, $start, $count);
	}
	
}

?>