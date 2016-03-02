<?php

require_once 'Yawp/Table.php';

class yawiki_areas extends Yawp_Table {
	
	
	/**
	* 
	* Column definitions.
	* 
	*/
	
	var $col = array(
		
		// short name of the area
		'name' => array(
			'type'     => 'varchar',
			'size'     => '32',
			'require'  => 'true',
			'qf_type'  => 'text',
			'qf_label' => 'Name:',
			'qf_attrs' => array('size' => 36),
			'qf_rules' => array(
				'regex' => array(
					'Please use letters, numbers, and underscores only.',
					'/^[a-zA-Z_0-9]+$/'
				)
			)
		),
		
		// long title for the area (used as RSS channel title)
		'title' => array(
			'type'     => 'varchar',
			'size'     => '255',
			'qf_label' => 'Title:',
			'qf_attrs' => array('size' => 36)
		),
		
		// tag-line or short description for the area
		'tagline' => array(
			'type'     => 'varchar',
			'size'     => '255',
			'qf_label' => 'Subtitle/Tagline:',
			'qf_attrs' => array('size' => 36)
		),
		
		// are comments on for this area?
		'comments' => array(
			'type'     => 'boolean',
			'default'  => "'1'",
			'qf_label' => 'Allow Comments:'
		),
		
		// default email for the area watcher (used for comments notification
		// and RSS webmaster email)
		'email' => array(
			'type'     => 'varchar',
			'size'     => '255',
			'qf_label' => 'Email Comments To:',
			'qf_attrs' => array('size' => 36),
			'qf_rules' => array(
				'email' => 'Should be blank, or a valid email address.'
			)
		),
		
		// subject-line prefix for emails
		'email_subj' => array(
			'type'     => 'varchar',
			'size'     => '255',
			'qf_label' => 'Email Subject Prefix:',
			'qf_attrs' => array('size' => 36)
		),
		
		// are we doing HTML caching?
		'cache_html' => array(
			'type' => 'boolean',
			'default' => "'0'",
			'qf_label' => "Cache HTML:"
		)
	);
	
	
	/**
	* 
	* Index definitions.
	* 
	*/
	
	var $idx = array(
		'name' => 'unique'
	);
	
	
	/**
	* 
	* SQL query definitions.
	* 
	*/
	
	var $sql = array(
	
		'list' => array(
			'select' => '*',
			'order' => 'name',
			'get' => 'all'
		),
		
		'item' => array(
			'select' => '*',
			'get' => 'row'
		),
		
		'names' => array(
			'select' => 'name',
			'order' => 'name',
			'get' => 'col'
		)
	);
	
	function create($flag)
	{
		$result = parent::create($flag);
		
		if (PEAR::isError($result) || ! $result) {
			return $result;
		}
		
		$ins = array(
			'name' => Yawp::getConfElem('yawiki', 'default_area', 'Main')
		);
		
		return $this->insert($ins);
	}
	
	
	function getList()
	{
		return $this->select('list');
	}
	
	function getNames()
	{
		return $this->select('names');
	}
	
	function getItem($name)
	{
		$name = $this->quote($name);
		return $this->select('item', "name = $name");
	}
	
	function update($data, $name)
	{
		$name = $this->quote($name);
		$this->recast($data);
		return parent::update($data, "name = $name");
	}
	
	function delete($name)
	{
		$name = $this->quote($name);
		return parent::delete("name = $name");
	}
}

?>