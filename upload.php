<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<body>

<?php
// =====================================================================
// 
// This program is part of Yet Another Wiki (Yawiki).  For more
// information, please visit http://yawiki.com/ at your convenience.
// 
// Copyright (C) 2004 Del <del@babel.com.au>
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
// $Id: upload.php,v 1.6 2005/07/07 21:36:26 pmjones Exp $
//
// =====================================================================

$uploads_dir = "uploads";

$file_dir=dirname($_SERVER["SCRIPT_FILENAME"]) . "/" . $uploads_dir . "/";
$script_path=dirname($_SERVER["PHP_SELF"]);
$here = "http://" . $_SERVER["SERVER_NAME"];
if (($script_path == "") or ($script_path == "/")) {
	$there = $here . "/";
} else {
	$there = $here . $script_path . "/";
}
$url_prefix=$there . $uploads_dir . "/";

if (! is_dir($file_dir)) {
	print "Cannot upload files -- directory $file_dir does not exist.<br>\n";
}

if (! is_writable($file_dir)) {
	print "Cannot upload files -- directory $file_dir cannot be written to.<br>\n";
}

//
// Sanitise a string to be used as a file name.
//
function sanitise_filename ($input) {
	return preg_replace ('/[^A-Za-z0-9\055\056]/', '', $input);
}

//
// Do the upload if there is one.
//
if (! empty($_FILES["userfile"]["name"])) {
	$userfile_name = basename($_FILES["userfile"]["name"]);
	$sanefile_name = sanitise_filename($userfile_name);
	if(stristr($userfile_name, ".php")) {
		print "Sorry, you are not allowed to upload PHP scripts.<br>\n";
	}
	$targetfile = $file_dir . "/" . $sanefile_name;
	if (@move_uploaded_file($_FILES["userfile"]["tmp_name"], $targetfile)) {
		print "File $sanefile_name successfully uploaded.<br>\n";
		print "use <h3><a href=\"$url_prefix$sanefile_name\" target=\"_blank\">" .
		  "$url_prefix$sanefile_name</a></h3> to reference the file<br>\n";
		chmod($targetfile, 0640);
	} else {
		print "Upload of $sanefile_name failed.<br>\n";
	}
}

?>

<hr>

<form enctype="multipart/form-data" action="upload.php" method=post>

<input type="hidden" name="max_file_size" value="2000000">

send this file: <input name="userfile" type="file"><br>

<input type="submit" value="send file">

</form>

<p>
[<a href="<?php print $there; ?>">Return to the Wiki</a>]

</body>

</html>