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
// $Id: yawiki_store.class.php,v 1.4 2006/02/27 00:31:06 delatbabel Exp $
//
// =====================================================================


/**
*
* This class lets you work with a table of pages.
*
* @author Paul M. Jones <pmjones@ciaweb.net>
*
*/

require_once 'Yawp/Table.php';

class yawiki_store extends Yawp_Table {

    /**
    *
    * Configuration keys for this class.
    *
    * @access public
    *
    * @var array
    *
    */

    var $conf = array(
        'default_area' => 'Main',
        'default_page' => 'HomePage'
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

        // saved by this user
        'username' => array(
            'type'    => 'varchar',
            'size'    => 255,
            'require' => true
        ),

        // longer title for the page (used on page, maybe in AreaMap?)
        'title' => array(
            'type'    => 'varchar',
            'size'    => 255,
            'default' => "''"
        ),

        // change note
        'note' => array(
            'type'    => 'varchar',
            'size'    => 255,
            'default' => "''"
        ),

        // body content
        'body' => array(
            'type'    => 'clob',
            'require' => true,
            'default' => "''"
        ),

        // cached html content
        'html' => array(
            'type'    => 'clob',
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
        'area' => 'normal',
        'page' => 'normal',
        'dt'   => 'normal'
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
            'select' => 'area, page, dt, username, title, note',
            'order'  => 'area, page',
            'get'    => 'all'
        ),

        // single row
        'item' => array(
            'select' => '*',
            'get'    => 'row'
        ),

        // list of page names
        'pagelist' => array(
            'select' => 'DISTINCT page',
            'order'  => 'page',
            'get'    => 'col'
        ),

        // list of area names
        'arealist' => array(
            'select' => 'DISTINCT area',
            'order'  => 'area',
            'get'    => 'col'
        )
    );


    /**
    *
    * Custom create to insert the HomePage and the AreaMap page.
    *
    * @access public
    *
    * @param bool|string $flag The DB_TABLE_CREATE_* flag.
    *
    * @return void
    *
    */

    function create($flag = false)
    {
        $result = parent::create($flag);

        if (! $result || PEAR::isError($result)) {

            return $result;

        } else {

            $area = Yawp::getConfElem('yawiki', 'default_area', $this->conf['default_area']);
            $page = Yawp::getConfElem('yawiki', 'default_page', $this->conf['default_page']);
            $dt = date('Y-m-d H:i:s');

            $this->insert(
                array(
                    'area' => $area,
                    'page' => $page,
                    'body' => "Welcome to $area:$page!",
                    'dt'   => $dt,
                    'username' => 'SYSTEM'
                )
            );

            $this->insert(
                array(
                    'area' => $area,
                    'page' => 'AreaMap',
                    'body' => $page,
                    'dt'   => $dt,
                    'username' => 'SYSTEM'
                )
            );

            return true;

        }
    }


    /**
    *
    * Get the list of page names in a area.
    *
    * @access public
    *
    * @param string $area The area name to get pages for.
    *
    * @return array The page names.
    *
    */

    function getPageList($area)
    {
        $area = $this->quote($area);
        return $this->select('pagelist', "area = $area");
    }


    function getAreaList()
    {
        return $this->select('arealist');
    }


    /**
    *
    * Get the list of all date-time versions for a page in descending
    * order.
    *
    * @access public
    *
    * @param string $area The area name to work with.
    *
    * @param string $page The page name to work with.
    *
    * @param int $limit The number of versions to return; defaults to
    * returning all versions.
    *
    * @return array The version date-time list.
    *
    */

    function getVersionList($area, $page, $limit = null, $max_dt = null)
    {
        $area = $this->quote($area);
        $page = $this->quote($page);
        $filter = "area = $area AND page = $page";
        if ($max_dt) {
            $max_dt = $this->quote($max_dt);
            $filter.= " AND dt < $max_dt";
        }
        $order = 'dt DESC';

        if (is_null($limit)) {
            return $this->select('list', $filter, $order);
        } else {
            return $this->select('list', $filter, $order, 0, $limit);
        }
    }


    /**
    *
    * Get the list of changes to a area (you can select what kinds of
    * changes to report).
    *
    * @access public
    *
    * @param string $type The type of changes to return; must be one of
    * 'edits', 'hours', or 'days'.
    *
    * @param int $amt The number of changes to return.
    *
    * @param string $area Which area to report on; if null or blank,
    * reports on all pages in all areas.
    *
    * @param string $page Which page in a area to report on; if null or
    * blank, reports on all pages in the specified area.  Ignored if
    * $area is null or blank.
    *
    * @return array The list of changes.
    *
    */

    function getChanges($type = 'edits', $amt = 24, $area = null, $page = null)
    {
        // $type can be hours, days, or pages
        // $amt is the number of those.

        // set the baseline filter for the area
        $filter = "";
        if (! is_null($area) && trim($area) != '') {
            $filter = "area = '$area' AND ";

            // also set for a page in the area
            if (! is_null($page) && trim($page) != '') {
                $filter .= "page = '$page' AND ";
            }
        }

        // set the baseline limits
        $start = null;
        $count = null;

        // build the added filter for type
        switch ($type) {

        case 'hours':
            $dt = date("Y-m-d H:i:s", time() - ($amt * 60 * 60));
            $filter .= "dt >= '$dt'";
            unset($dt);
            break;

        case 'days':
            $dt = date("Y-m-d H:i:s", time() - ($amt * 60 * 60 * 24));
            $filter .= "dt >= '$dt'";
            unset($dt);
            break;

        case 'edits':
        default:
            $filter .= "1=1";
            $start = 0;
            $count = $amt;
            break;
        }

        // get the results
        return $this->select('list', $filter, 'dt DESC', $start, $count);
    }


    /**
    *
    * Gets a page from the table.
    *
    * @access public
    *
    * @param string $area The area in which the page exists.
    *
    * @param string $page The page name in the area.
    *
    * @param string $dt The date-time version for the page in
    * "yyyy-mm-dd hh:ii:ss" format; if null, retrieves the most-recent
    * page.  If no matching date-time is found, retrieves the next earlier
    * matching page version ("last snapshot").
    *
    * @return array The page data.
    *
    */

    function getPage($area, $page, $dt = null)
    {
        if (is_null($dt)) {
            $dt = date('Y-m-d H:i:s');
        }

        $area = $this->quote($area);
        $page = $this->quote($page);
        $dt = $this->quote($dt);

        return $this->select(
            'item', "area = $area AND page = $page AND dt <= $dt",
            'dt DESC', 0, 1
        );
    }


    /**
    *
    * Clears the HTML for an entire area, or a page within an area.
    *
    * @access public
    *
    * @param string $area The area to clear HTML for.
    *
    * @param string $page The page name to clear HTMl for; if not specified,
    * clears HTML for all pages in the area (all date-time versions).
    *
    * @return void
    *
    */

    function clearHtml($area, $page = null)
    {
        $data = array('html' => null);
        $where = 'area = ' . $this->quote($area);
        if (! is_null($page)) {
            $where .= ' AND page = ' . $this->quote($area);
        }
        $this->update($data, $where);
    }


    /**
    *
    * Clears the HTML for a whole list of areas and pages.
    *
    * @access public
    *
    * @param array $list A sequential array; each element is itself an array
    * where element 0 is the area and element 1 is the page.
    *
    * @return void
    *
    */

    function clearHtmlBatch($list)
    {
        $data = array('html' => null);
        $tmp = array();
        foreach ($list as $val) {
            $tmp[] = '(area = ' . $this->quote($val['area']) .
                ' AND page = ' . $this->quote($val['page']) . ')';
        }
        $where = implode(' OR ', $tmp);
        $this->update($data, $where);
    }


    function delete($area, $page)
    {
        $area = $this->quote($area);
        $page = $this->quote($page);
        $where = "area = $area AND page = $page";
        return parent::delete($where);
    }

    function deleteArea($area)
    {
        $area = $this->quote($area);
        return parent::delete("area = $area");
    }
}

?>