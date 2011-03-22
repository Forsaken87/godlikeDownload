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

?>