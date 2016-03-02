<?php

require_once 'Yawp/Table.php';

class yawiki_hits extends Yawp_Table {
	
	
	/**
	* 
	* Column definitions.
	* 
	*/
	
	var $col = array(
		
		// unix time
		'u' => array(
			'type'    => 'integer',
			'require' => true
		),
		
		// year
		'y' => array(
			'type'    => 'decimal',
			'size'    => 4,
			'scope'   => 0,
			'require' => true
		),
		
		// month
		'm' => array(
			'type'    => 'decimal',
			'size'    => 2,
			'scope'   => 0,
			'require' => true
		),
		
		// day
		'd' => array(
			'type'    => 'decimal',
			'size'    => 2,
			'scope'   => 0,
			'require' => true
		),
		
		// hour
		'h' => array(
			'type'    => 'decimal',
			'size'    => 2,
			'scope'   => 0,
			'require' => true
		),
		
		// minute
		'i' => array(
			'type'    => 'decimal',
			'size'    => 2,
			'scope'   => 0,
			'require' => true
		),
		
		// second
		's' => array(
			'type'    => 'decimal',
			'size'    => 2,
			'scope'   => 0,
			'require' => true
		),
		
		// day-of-week
		'w' => array(
			'type'    => 'decimal',
			'size'    => 1,
			'scope'   => 0,
			'require' => true
		),
		
		// area name
		'area' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'require' => true
		),
		
		// page name
		'page' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'require' => true
		),
		
		// version timestamp
		'dt' => array(
			'type'    => 'timestamp',
			'require' => true
		),
		
		// username
		'username' => array(
			'type'    => 'varchar',
			'size'    => 255
		),
		
		// remote address
		// 111.222.333.444
		'ip' => array(
			'type' => 'char',
			'size' => 15
		),
		
		// session ID
		'sessid' => array(
			'type' => 'char',
			'size' => 32
		),
		
		// referer site
		'referer_host' => array(
			'type' => 'varchar',
			'size' => 255
		),
		
		// referer path
		'referer_path' => array(
			'type' => 'varchar',
			'size' => 255
		),
		
		// referer query string
		'referer_qstr' => array(
			'type' => 'varchar',
			'size' => 255
		),
		
		
			
	);
	
	
	/**
	* 
	* Index definitions.
	* 
	*/
	
	var $idx = array(
		'y'             => 'normal',
		'm'             => 'normal',
		'd'             => 'normal',
		'h'             => 'normal',
		'w'             => 'normal',
		'area'          => 'normal',
		'page'          => 'normal',
		'username'      => 'normal',
		'dt'            => 'normal',
		'ip'            => 'normal',
		'sessid'        => 'normal',
		'referer_host'  => 'normal'
	);
	
	
	/**
	* 
	* SQL queries.
	* 
	*/
	
	var $sql = array(
		
		'areas' => array(
			'select' => 'MIN(u) AS min, MAX(u) AS max, COUNT(u) AS hits, area',
			'group' => 'area',
			'order' => 'area',
			'get' => 'all'
		),
		
		'pages' => array(
			'select' => 'MIN(u) AS min, MAX(u) AS max, COUNT(u) AS hits, area, page',
			'group' => 'area, page',
			'order' => 'area, page',
			'get' => 'all'
		),
		
		'list' => array(
			'select' => '*',
			'get' => 'all',
		),
	);
		
	/**
	* 
	* Log a page hit by area, page, version, and username.
	* 
	*/
	
	function log($area, $page, $dt, $username)
	{
		$now = time();
		
		// blank (default) referer values
		$tmp0 = array(
			'host' => '',
			'path' => '',
			'query' => ''
		);
		
		// actual referer values, which may or may not
		// be set for host, path, and query
		$tmp1 = parse_url(
			Yawp::getSG('server', 'HTTP_REFERER', '')
		);
		
		// merge the referer arrays so that the actual values,
		// if any, override the default values.
		$referer = array_merge($tmp0, $tmp1);
		
		// set up the insertion data
		$data = array(
			'area'         => $area,
			'page'         => $page,
			'dt'           => $dt,
			'username'     => $username,
			'u'            => $now,
			'y'            => date('Y', $now),
			'm'            => date('m', $now),
			'd'            => date('d', $now),
			'h'            => date('H', $now),
			'i'            => date('i', $now),
			's'            => date('s', $now),
			'w'            => date('w', $now),
			'sessid'       => session_id(),
			'ip'           => Yawp::getSG('server', 'REMOTE_ADDR', ''),
			'referer_host' => $referer['host'],
			'referer_path' => $referer['path'],
			'referer_qstr' => $referer['query']
		);
		
		// insert and return
		return $this->insert($data);
	}
	
	function getAreaOverview($days = null)
	{
		if ($days === null) {
			return $this->select('areas');
		} else {
			$start = time() - (60 * 60 * 24 * $days);
			$where = 'u >= ' . $this->quote($start);
			return $this->select('areas', $where);
		}
	}
	
	function getPagesInArea($area)
	{
		$where = 'area = ' . $this->quote($area);
		return $this->select('pages', $where, 'hits DESC');
	}
	
	function getList($area = null, $order = null, $start = null, $count = null)
	{
		if ($area) {
			$filter = 'area = ' . $this->quote($area);
		} else {
			$filter = null;
		}
		
		return $this->select('list', $filter, $order, $start, $count);
	}
	
	function getReferrals($days = null, $area = null)
	{
		if ($days) {
			$start = time() - (60 * 60 * 24 * $days);
		} else {
			$start = 0;
		}
		
		$filter = 'u >= ' . $this->quote($start);
		
		if ($area) {
			$filter .= ' AND area = ' . $this->quote($area);
		}
		
		return $this->select('list', $filter, 'u DESC');
	}
}

?>