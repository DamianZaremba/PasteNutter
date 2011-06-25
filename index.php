<?php
/*
  * This file is part of PasteNutter.
  *
  * PasteNutter is free software: you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation, either version 3 of the License, or
  * (at your option) any later version.
  *
  * PasteNutter is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  * GNU General Public License for more details.
  *
  * You should have received a copy of the GNU General Public License
  * along with PasteNutter.  If not, see <http://www.gnu.org/licenses/>. 
  */

date_default_timezone_set('Europe/London');
require_once('config.inc.php');
require_once($base_dir . '/lib/smarty/Smarty.class.php');
require_once($base_dir . '/lib/geshi/geshi.php');
require_once($base_dir . '/lib/recaptchalib.php');

$smarty = new Smarty;
$smarty->setTemplateDir($base_dir . "/templates/");
$smarty->setCompileDir("/tmp/scott_pb/"); 
$smarty->assign("web_root", $base_url);

$db = @mysql_connect($db_host, $db_user, $db_pass);
if($db === False) {
	die("Could not connect to database D:");
}

if(@mysql_select_db($db_scheme) === False) {
	die("Could not select the db D:");
}

if(isset($_SERVER) && array_key_exists('PATH_INFO', $_SERVER)) {
	$pasteID = $_SERVER['PATH_INFO'];
} else if(isset($_SERVER) && array_key_exists('ORIG_PATH_INFO', $_SERVER)) {
	$pasteID = $_SERVER['ORIG_PATH_INFO'];
} else if(isset($_SERVER) && array_key_exists('REQUEST_URI', $_SERVER)) {
	$fget = strpos($_SERVER['REQUEST_URI'], '?');
	$pasteID = substr($_SERVER['REQUEST_URI'], 0, $fget);
}

if(!isset($pasteID)) {
	$controller_path = realpath($base_dir . "/controllers/new.inc.php");
} else {
	$controller_path = realpath($base_dir . "/controllers/view.inc.php");
	$pasteID = preg_replace("([^0-9])", "", $pasteID);
}

if(!$controller_path) {
	$url = "404";
	$controller_path = realpath($base_dir . "/controllers/" . $url . ".inc.php");
}


if(!$controller_path) {
	header("HTTP/1.1 404 Not Found");
	print "Not found";
	die();
}

require_once($controller_path);
?>
