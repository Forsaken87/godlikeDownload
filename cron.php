<?php

@include "inc.server.php";
$mysql = @mysql_connect($db_host, $db_user, $db_pass);
if (!$mysql || !@mysql_select_db($db_name, $mysql)) {
	if (file_exists("install_done")) {
		die("<h1>Fatal database error!</h1>");
	}
}

include("cron/category_aliases.php");
include("cron/downloads_search.php");
include("cron/downloads_update.php");


?>