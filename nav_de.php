<?php

// Define menu
$ar_nav = array();
if (!isUser()) {
	$ar_nav[] = array("IDENT" => "login", "NAME" => "Login");
	$ar_nav[] = array("IDENT" => "register", "NAME" => "Registrieren");
} else {
	$ar_nav[] = array("IDENT" => "dashboard", "NAME" => "Dashboard");
	$ar_nav[] = array("IDENT" => "search", "NAME" => "Suchen");
	$ar_nav[] = array("IDENT" => "download", "NAME" => "Download");
	$ar_nav[] = array("IDENT" => "series", "NAME" => "Serien");
	$ar_nav[] = array("IDENT" => "categorys", "NAME" => "Kategorien");
	$ar_nav[] = array("IDENT" => "settings", "NAME" => "Einstellungen");
	$ar_nav[] = array("IDENT" => "help", "NAME" => "Hilfe");
}

?>
