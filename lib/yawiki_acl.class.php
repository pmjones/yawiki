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
// $Id: yawiki_acl.class.php,v 1.1 2004/10/01 18:28:50 pmjones Exp $
//
// =====================================================================

/**
* 
* This class keeps track of the access control list for a wiki.  We can
* only specify authorization here; authentication is up to the PEAR Auth
* module.  There is no "cascading" of privileges through the AreaMap, as
* the actual wiki namearea is flat.
* 
* In this ACL implementation, the last control assigned "wins".  If you
* "deny *" then "allow user", the user is allowed. If you "deny user"
* then "allow *", everyone is allowed.
*
* A '*' is a wildcard, meaning "all" of that element (users, wikis, or pages).
*
* A '+' is a special wildcard, meaning "all authenticated (identified) users". It
* has no meaning for elements not related to users.
*
* 
* @todo Set it up so that if user creates a page that user is assigned
* 'auth' privileges on it (unless the user is anonymous!)
*
* @todo Set up a new privilege "page_add" that allows a user to add pages to a
* wiki.
*
*/

define('YAWIKI_ACL_DENY',  0);
define('YAWIKI_ACL_ALLOW', 1);

require_once 'Yawp/Table.php';

class yawiki_acl extends Yawp_Table {
	
	
	/**
	* 
	* An associative array where the key is a username; the value is a
	* sequential array of associative arrays, where the keys and values
	* are table trows.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	var $_acl = array();
	
	
	/**
	* 
	* Column definitions for the table and its input form.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	var $col = array(
		'id' => array(
			'type'     => 'integer',
			'require'  => true,
			'qf_label' => 'ID',
			'qf_type'  => 'hidden'
		),
		'seq' => array(
			'type'     => 'integer',
			'require'  => true,
			'qf_label' => 'Sequence',
			'qf_type'  => 'text',
			'qf_attrs' => array('size' => 4)
		),
		'flag' => array(
			'type'     => 'decimal',
			'size'     => 1,
			'scope'    => 0,
			'require'  => true,
			'default'  => "'0'",
			'qf_label' => 'Flag',
			'qf_type'  => 'select',
			'qf_vals'  => array(0 => 'deny', 1 => 'allow')
		),
		'username' => array(
			'type'     => 'varchar', // * for all, + for all authenticated
			'size'     => 255,
			'require'  => true,
			'qf_label' => 'Username',
			'qf_type'  => 'text',
			'qf_attrs' => array('size' => 16)
		),
		'priv' => array(
			'type'     => 'varchar',
			'size'     => 10,
			'require'  => true,
			'qf_label' => 'Privilege',
			'qf_type'  => 'select',
			'qf_vals'  => array(
				'page_view'  => 'view page',
				'page_edit'  => 'edit page',
				'page_admin' => 'page admin',
				'area_admin' => 'area admin',
				'wiki_admin' => 'wiki admin'
			)
		),
		'area' => array(
			'type'     => 'varchar', // * for all
			'size'     => 255,
			'qf_label' => 'Area'
		),
		'page' => array(
			'type'     => 'varchar', // * for all
			'size'     => 255,
			'qf_label' => 'Page',
			'qf_type'  => 'text',
			'qf_attrs' => array('size' => 32)
		)
	);
	
	
	/**
	* 
	* Index definitions for the table.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	var $idx = array(
		'id'       => 'unique',
		'username' => 'normal',
		'flag'     => 'normal',
		'priv'     => 'normal',
		'area'     => 'normal',
		'page'     => 'normal'
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
	
	// by sequence number, then by ID as entered. this keeps them in
	// proper order so that last one wins.
	var $sql = array(
		'list' => array(
			'select' => '*',
			'order'  => 'seq, id',
			'get'    => 'all'
		)
	);
	
	
	/**
	* 
	* Creates the table for this object and inserts the default access
	* list.
	* 
	* @access public
	* 
	* @param string|bool $flag One of the DB_TABLE_CREATE flags: safe,
	* drop, or false.
	* 
	* @return mixed True if the table was created, false if not,
	* PEAR_Error if an error was generated trying to create the table.
	*
	* @see DB_Table::create()
	* 
	*/
	
	function create($flag)
	{
		$result = parent::create($flag);
		
		if (! $result || PEAR::isError($result)) {
			return $result;
		}
		
		$data = array(
		
			// allow one username as wiki_admin; if the yawiki_acl config for
			// create_wiki_admin is not set, defaults to 'root' as the
			// username
			array(
				'id'       => $this->nextID(),
				'seq'      => 1,
				'flag'     => YAWIKI_ACL_ALLOW,
				'username' => Yawp::getConfElem(
					'yawiki_acl', 'create_wiki_admin', 'root'
				),
				'priv'     => 'wiki_admin'
			),
			
			// allow all users (including anonymous) to view all pages
			array(
				'id'       => $this->nextID(),
				'seq'      => 100,
				'flag'     => YAWIKI_ACL_ALLOW,
				'username' => '*',
				'priv'     => 'page_edit',
				'area'     => '*',
				'page'     => '*'
			),
			
			// allow all users (including anonymous) to edit all pages
			array(
				'id'       => $this->nextID(),
				'seq'      => 200,
				'flag'     => YAWIKI_ACL_ALLOW,
				'username' => '*',
				'priv'     => 'page_view',
				'area'     => '*',
				'page'     => '*'
			)
			
		);
		
		// insert each row, check for errors, fail on error
		foreach ($data as $key => $val) {
			$result = $this->insert($val);
			if (PEAR::isError($result)) {
				return $result;
			}
		}
		
		// done!
		return true;
	}
	
	
	/**
	* 
	* Does the specified user have privilege to administer the wiki?
	*
	* @access public
	* 
	* @param string $username Check the ACL against this username.
	*
	* @return bool True is the username has the privilege, false if not.
	*
	* @see _getFlag()
	* 
	*/
	
	function wikiAdmin($username)
	{
		return $this->_getFlag($username, 'wiki_admin');
	}
	
	
	/**
	* 
	* Does the specified username have privilege to administer a area?
	*
	* @access public
	* 
	* @param string $username Check the ACL against this username.
	*
	* @return bool True is the username has the privilege, false if not.
	*
	* @see _getFlag()
	*
	* @see wikiAdmin()
	* 
	*/
	
	function areaAdmin($username, $area)
	{
		return $this->_getFlag($username, 'area_admin', $area)
			|| $this->wikiAdmin($username);
	}
	
	
	/**
	* 
	* Does the specified username have privilege to give other usernames
	* view/edit privs to a page?
	*
	* @access public
	* 
	* @param string $username Check the ACL against this username.
	*
	* @param string $area Check the ACL against this area.
	*
	* @param string $page Check the ACL against this page.
	*
	* @return bool True is the username has the privilege, false if not.
	*
	* @see _getFlag()
	*
	* @see wikiAdmin()
	* 
	*/
	
	function pageAdmin($username, $area, $page)
	{
		return $this->_getFlag($username, 'page_admin', $area, $page)
			|| $this->areaAdmin($username, $area)
			|| $this->wikiAdmin($username);
	}
	
	
	/**
	* 
	* Does the specified username have privilege to edit a wiki page?
	* (Also allows usernames to add any page to the wiki.)
	*
	* @access public
	* 
	* @param string $username Check the ACL against this username.
	*
	* @param string $area Check the ACL against this area.
	*
	* @param string $page Check the ACL against this page.
	*
	* @return bool True is the username has the privilege, false if not.
	*
	* @see _getFlag()
	*
	* @see wikiAdmin()
	* 
	*/
	
	function pageEdit($username, $area, $page)
	{
		return $this->_getFlag($username, 'page_edit', $area, $page)
			|| $this->pageAdmin($username, $area, $page)
			|| $this->areaAdmin($username, $area)
			|| $this->wikiAdmin($username);
	}
	
	
	/**
	* 
	* Does the specified username have privilege to view a page?
	*
	* @access public
	* 
	* @param string $username Check the ACL against this username.
	*
	* @param string $area Check the ACL against this area.
	*
	* @param string $page Check the ACL against this page.
	*
	* @return bool True is the username has the privilege, false if not.
	*
	* @see _getFlag()
	*
	* @see wikiAdmin()
	* 
	*/
	
	function pageView($username, $area, $page)
	{
		return $this->_getFlag($username, 'page_view', $area, $page)
			|| $this->pageEdit($username, $area, $page)
			|| $this->pageAdmin($username, $area, $page)
			|| $this->areaAdmin($username, $area)
			|| $this->wikiAdmin($username);
	}
	
	
	/**
	* 
	* Gets the privilege flag for a given type, username, area, and page. 
	* In some cases, the area or the page may not be necessary for the
	* privilege type.
	* 
	* @access private
	* 
	* @param string $username The username whose ACL you want to check.
	* 
	* @param string $priv The privilege type.
	* 
	* @param string $area The related area, if required by the type.
	* 
	* @param string $page The related page, if required by the type.
	* 
	* @return bool True is the privilege type is allowed, false if denied.
	* 
	*/
	
	function _getFlag($username, $priv, $area = '', $page = '')
	{
		// if $username is empty or null, only look for anonymous-username privs
		if (trim($username) == '') {
			$username = '*';
		}
		
		// load up the controls for the username
		$this->_loadUser($username);
		
		// if the requested control type is not present, default to "deny"
		if (! isset($this->_acl[$username][$priv])) {
			return 0;
		}
		
		// default to "deny"
		$flag = 0;
		
		// loop through the control types for the username
		foreach ($this->_acl[$username][$priv] as $key => $val) {
		
			switch ($priv) {
			
			// for these types, no area or page is needed;
			// they apply to the wiki as a whole
			case 'wiki_admin':
				$flag = (int) $val['flag'];
				break;
			
			// these types apply to individual areas
			// within the wiki
			case 'area_admin':
				// the ACL area must match the requested area, or
				// the ACL area must apply to all areas
				if (($val['area'] == $area || $val['area'] == '*')) {
					$flag = (int) $val['flag'];
				}
				break;
			
			// these types apply to specific pages within a area
			case 'page_admin':
			case 'page_edit':
			case 'page_view':
				// the ACL area must match the requested area, or
				// the ACL area must apply to all areas;
				// AND
				// the ACL page must match the requested page, or
				// the ACL page must apply to all pages
				if (($val['area'] == $area || $val['area'] == '*') &&
					($val['page'] == $page || $val['page'] == '*')) {
					$flag = (int) $val['flag'];
				}
				break;
			}
		}
		
		return $flag;
	}
	
	
	/**
	* 
	* Simple convenience method to get a list of all ACL entries.
	* 
	* @return array
	* 
	*/
	
	function getList()
	{
		return $this->select('list');
	}
	
	
	/**
	* 
	* Insert a new ACL row.
	*
	* @access public
	* 
	* @param array $data An array of key-value pairs (key = row name,
	* val = row value)
	*
	* @return object|int A PEAR_Error object on failure, or the new row
	* ID on success.
	*
	* @see DB_Table::insert()
	* 
	*/
	
	function insert($data)
	{
		// set the next ID
		$data['id'] = $this->nextID();
		
		// recast to proper type
		$this->recast($data);
		
		// unset based on priv types
		switch ($data['priv']) {
		
		case 'wiki_admin':
			unset($data['area']);
			unset($data['page']);
			break;
		
		case 'area_admin':
			unset($data['page']);
			break;
		
		}
		
		// validate and insert
		$result = parent::insert($data);
		
		// was the insert valid?
		if (PEAR::isError($result)) {
			return $result;
		} else {
			return $data['id'];
		}
	}
	
	
	/**
	* 
	* Pre-validate a row to be inserted.
	*
	* @access public
	* 
	* @param array $data An array of key-value pairs (key = row name,
	* val = row value)
	*
	* @return object|int A PEAR_Error object on failure, or the new row
	* ID on success.
	*
	* @see DB_Table::validInsert()
	* 
	*/
	
	function validInsert($data)
	{
		// username must be non-blank
		if (! isset($data['username']) || trim($data['username']) == '') {
			return $this->throwError(
				DB_TABLE_ERR_INS_DATA_INVALID,
				'"username" -- cannot be blank or null'
			);
		}
		
		return parent::validInsert($data);
	}
	
	
	/**
	* 
	* Delete an ACL row.
	*
	* @access public
	* 
	* @param int $id The row ID to delete.
	*
	* @return mixed
	*
	* @see DB_Table::delete()
	* 
	*/
	
	function delete($id)
	{
		$where = 'id = ' . $this->quote($id);
		return parent::delete($where);
	}
	
	
	function deleteArea($area)
	{
		$area = $this->quote($area);
		return parent::delete("area = $area");
	}
	
	/**
	* 
	* Loads the control list for a given username into the $_acl array.
	* 
	* @access private
	* 
	* @param string $username The username whose access list you want to load;
	* if blank or null, loads the ACL for username '*' (anonymous users).
	* 
	* @return void
	* 
	*/
	
	function _loadUser($username)
	{
		if (trim($username) == '') {
			$username = '*';
		}
		
		if (! isset($this->_acl[$username])) {
		
			// set up the filter.
			// add for all anonymous users.
			$filter = "username = '*'";
			
			if ($username != '*') {
				// also add for identified usernames.
				$filter .= " OR username = '+' OR username = " . $this->quote($username);
			}
			
			// get the query results
			$result = $this->selectResult('list', $filter);
			
			// add the results to the username list.
			$this->_acl[$username] = array();
			while ($val = $result->fetchRow()) {
				$this->_acl[$username][$val['priv']][] = $val;
			}
		}
	}
}