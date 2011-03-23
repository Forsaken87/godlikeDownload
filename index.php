<?php

// Include base functions
include "sys/base.php";

// Initialize globals
global $lang, $page, $page_index, $skin_file;

@include "inc.server.php";
$mysql = @mysql_connect($db_host, $db_user, $db_pass);
if (!$mysql || !@mysql_select_db($db_name, $mysql)) {
	if (file_exists("install_done")) {
		die("<h1>Fatal database error!</h1>");
	}
}

// Include libarys
require_once "sys/database.php";

// Start session (21 days lifetime)
$lifetime = (21 * 7 * 24 * 60 * 60);
session_name("DLSESSION");
session_start();
setcookie(session_name(),session_id(),time()+$lifetime);

// Check status for new downloads
$id_last_download = get_query_field("SELECT MAX(ID_DOWNLOAD_LINK) FROM `download_link`");
if (empty($_COOKIE['LAST_DOWNLOAD']) || ($_COOKIE['LAST_DOWNLOAD'] > $id_last_download)) {
	$_COOKIE['LAST_DOWNLOAD'] = $id_last_download - 25;
	setcookie('LAST_DOWNLOAD', $id_last_download - 25);
}

// Get target page
$page = ($_SESSION['show'] ? $_SESSION['show'] : "login");
$lang = ($_SESSION['lang'] ? $_SESSION['lang'] : "de");
// Get language files
global $ar_nav, $ar_playertypes;
include "tpl/".$lang."/_playerTypes.php";
// Get skin file to be used
$skin_file = ($_REQUEST['ajax'] ? "ajax" : "default");
unset($_REQUEST['ajax']);

if (!empty($_REQUEST['show'])) {
	// Change active page
	$page = $_SESSION['show'] = $_REQUEST['show'];
}
if (!empty($_REQUEST['lang'])) {
	// Change active language
	$page = $_SESSION['lang'] = $_REQUEST['lang'];
	if (empty($_POST) && (count($_REQUEST) == 1)) {
		die(header("location: index.php".($skin_file == "ajax" ? "?ajax=1" : "")));
	}
}
if (($skin_file == "ajax") && !empty($_REQUEST['run'])) {
	$page = $_REQUEST['run'];
}

include("nav_".$lang.".php");
set_page($page);

// Get user entry
$_SESSION['user'] = get_user($_SERVER['REMOTE_ADDR']);
// Read current skin
$skin = file_get_contents("skin/".$skin_file.".htm");
// Insert language
$skin = str_replace("{lang}", $lang, $skin);
// Get info bar
$skin = str_replace("{infobar}", get_page("infobar", $lang), $skin);
// Get navigation
$skin = str_replace("{nav}", get_nav($ar_nav), $skin);
$skin = str_replace("{nav_index}", (isset($page_index) ? $page_index : -1), $skin);
$skin = str_replace("{nav_id}", (isset($page_index) ? $page_index+1 : 0), $skin);
// Get content
$skin = str_replace("{content}", get_page($page, $lang), $skin);

die($skin);

?>