<?php

global $id_user;

$query = "SELECT * FROM `user`";
$result = @mysql_query($query);

while ($ar_row = @mysql_fetch_assoc($result)) {
	$id_user = $ar_row["ID_USER"];
	$filename = "cache/user_new/".$id_user.".htm";
	if (!file_exists($filename) || ((time() - filemtime($filename)) > 600)) {
		$html = get_page("_cache_user_new", "de");
		file_put_contents($filename, $html);
	}
}


?>