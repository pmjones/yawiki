<?php

require_once 'Yawp/Table.php';

class yawiki_locks extends Yawp_Table {
	
	var $conf = array(
		'timeout' => 15
	);
	
	
	/**
	* 
	* Column definitions.
	* 
	*/
	
	var $col = array(
			
		// area in which this page belongs
		'area' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'require' => true
		),
		
		// short name of the page
		'page' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'require' => 'true'
		),
		
		'username' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'require' => true
		),
		
		'dt' => array(
			'type'    => 'timestamp',
			'require' => true
		),
	);
	
	
	/**
	* 
	* Index definitions.
	* 
	*/
	
	var $idx = array(
		'area'    => 'normal',
		'page'     => 'normal',
		'username' => 'normal',
	);
	
	
	/**
	* 
	* SQL query definitions.
	* 
	*/
	
	var $sql = array(
		'list' => array(
			'select' => '*',
			'get'    => 'all'
		),
		'item' => array(
			'select' => '*',
			'get'    => 'row'
		)
	);
	
	
	/**
	* 
	* Sets the edit lock on a page to the current date-time.
	* 
	* @access public
	* 
	* @param string $area The area for the page.
	* 
	* @param string $page The page in the area.
	* 
	* @param string $username The username requesting the lock.
	* 
	* @return mixed Void on success, a PEAR_Error on failure.
	* 
	*/
	
	function capture($area, $page, $username)
	{
		$locked = $this->authUsername($area, $page);
		
		if (! $locked) {
		
			$data = array(
				'area'     => $area,
				'page'     => $page,
				'dt'       => date('Y-m-d H:i:s'),
				'username' => $username,
			);
			
			$result = $this->insert($data);
			
			if (PEAR::isError($result)) {
				return $result;
			} else {
				return true;
			}
			
		} elseif ($locked == $username) {
		
			// page is locked, but by the same user as requesting
			// the lock.  refresh the lock to the current date-time.
			$data = array('dt' => date('Y-m-d H:i:s'));
			
			// build a filter
			$area = $this->quote($area);
			$page = $this->quote($page);
			$where = "area = $area AND page = $page";
			
			// update the table
			$result = $this->update($data, $where);
			
			if (PEAR::isError($result)) {
				return $result;
			} else {
				return true;
			}
		
		} else {
		
			// page is locked by another user
			return false;
		
		}
	}
	
	
	/**
	* 
	* Releases the edit lock on a page.
	* 
	* @access public
	* 
	* @param string $area The area for the page.
	* 
	* @param string $page The page in the area.
	* 
	* @param string $dt The date-time version for the page to lock.
	* 
	* @return mixed Void on success, a PEAR_Error on failure.
	* 
	*/
	
	function release($area, $page)
	{
		$area = $this->quote($area);
		$page = $this->quote($page);
		return $this->delete("area = $area AND page = $page");
	}
	
	
	/**
	* 
	* Unset all edit locks for a given username; typically called on logout.
	* 
	* @access public
	* 
	* @param string $username The username to unlock.
	* 
	* @return mixed Void on success, or a PEAR_Error on failure.
	* 
	*/
	
	function releaseAllForUser($username)
	{
		$username = $this->quote($username);
		$this->delete("username = $username");
	}
	
	
	/**
	* 
	* Get the username for a locked page.  Releases timed-out locks.
	* 
	* @access public
	* 
	* @param string $area The area for the page.
	* 
	* @param string $page The page in the area.
	* 
	* @return mixed String username who locked the page, or boolean
	* false if the page is not locked (or was released automatically).
	* 
	*/
	
	function authUsername($area, $page)
	{
		// get the most recent time for the page lock
		$a = $this->quote($area);
		$p = $this->quote($page);
		$result = $this->selectResult('item', "area = $a AND page = $p",
			'dt DESC');
		
		// if there are no results, the page is not locked
		if ($result->numRows() == 0) {
			return false;
		}
		
		// difference in seconds, converted to minutes
		$row = $result->fetchRow();
		$mins = (int) ((time() - strtotime($row['dt'])) / 60);
		
		// if timed out, release the page, otherwise return
		// the username who has the page in lock.
		if ($mins > (int) $this->conf['timeout']) {
			$this->release($area, $page);
			return false;
		} else {
			return $row['username'];
		}
	}
}

?>