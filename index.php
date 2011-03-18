<?php

function get_html($text) {
	return str_replace("\n", "<br />\n", str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", str_replace(" ", "&nbsp;", htmlspecialchars($text))));
}

function get_nav($ar_nav) {
	global $page, $page_index;
	$nav = "";
	foreach ($ar_nav as $index => $row) {
		$row["INDEX"] = $index + 1;
		if ($row["IDENT"] == $page) {
			// is current page
			$page_index = $index;
		}
		$nav .= get_nav_entry($row);
	}
	return $nav;
}

function get_nav_entry($row) {
	global $page;
	ob_start();
	include "skin/nav.php";
	$nav_entry = ob_get_contents();
	ob_end_clean();
	return $nav_entry;
}

function get_page($name, $lang) {
	ob_start();
	if (preg_match('/[a-z0-9-_]+/i', $name) && file_exists("tpl/".$lang."/".$name.".php")) {
		include "tpl/".$lang."/".$name.".php";
	} else {
		include "tpl/".$lang."/404.php";
	}
	$page_content = ob_get_contents();
	ob_end_clean();
	return $page_content;
}

function get_user() {
	global $db_prefix;
	if (!empty($_SESSION['user'])) {
		$user_name = $_SESSION['user']['NAME'];
		$user_pass = $_SESSION['user']['PASSWORD'];
		$query = "SELECT * FROM `user` WHERE NAME LIKE '".mysql_escape_string($user_name)."' AND PASSWORD LIKE '".mysql_escape_string($user_pass)."'";
		$result = @mysql_query($query);
		if ($ar_user = @mysql_fetch_assoc($result)) {
			return $ar_user;
		}
	}
	return array(
		"NAME"			=> "Gast",
		"PASSWORD"		=> "",
		"RANK"			=> "guest"
	);
}

function set_page($name) {
	global $ar_nav, $page, $skin_file;
	foreach ($ar_nav as $navIndex => $navCur) {
		if ($page == $ar_nav['IDENT']) {
			if (!empty($navCur['SKIN'])) $skin_file = $navCur['SKIN'];
		}
	}
}

function isUser() {
	return (($_SESSION['user']["RANK"] != "guest"));
}

function isVIP() {
	return (($_SESSION['user']["RANK"] == "vip") || isMod());
}

function isMod() {
	return (($_SESSION['user']["RANK"] == "moderator") || isAdmin());
}

function isAdmin() {
	return (($_SESSION['user']["RANK"] == "admin") || isRoot());
}

function isRoot() {
	return ($_SESSION['user']["RANK"] == "root");
}

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

// Start session (21 days)
session_set_cookie_params(21 * 7 * 24 * 60 * 60);
session_start();

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
$skin = str_replace("{nav_index}", $page_index, $skin);
$skin = str_replace("{nav_id}", $page_index+1, $skin);
// Get content
$skin = str_replace("{content}", get_page($page, $lang), $skin);

die($skin);

?>