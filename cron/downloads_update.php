<?php

require_once 'sys/download.php';

$count_max = 25;

/*
 * REMOVE LINKS WITHOUT MATCHING DOWNLOAD
 */
$query = "SELECT l.ID_DOWNLOAD_LINK FROM `download_link` l\n".
	"	LEFT JOIN `download` d ON l.FK_DOWNLOAD=d.ID_DOWNLOAD\n".
	"	WHERE d.ID_DOWNLOAD IS NULL";
$result = @mysql_query($query);
$deleteLinks = array();
while ($row = @mysql_fetch_row($result)) {
	$deleteLinks[] = $row[0];
}
if (!empty($deleteLinks)) {
	$query = "DELETE FROM `download_link` WHERE ID_DOWNLOAD_LINK IN (".implode(",", $deleteLinks).")";
	if (@mysql_query($query) === false) {
		echo("[ERROR] Löschen von verwaisten Links fehlgeschlagen!");
	}
}

/*
 * REMOVE CATEGORYS OF MISSING DOWNLOADS
 */
$query = "SELECT c.FK_DOWNLOAD FROM `download_cat` c\n".
	"	LEFT JOIN `download` d ON c.FK_DOWNLOAD=d.ID_DOWNLOAD\n".
	"	WHERE d.ID_DOWNLOAD IS NULL\n".
	"	GROUP BY c.FK_DOWNLOAD";
$result = @mysql_query($query);
$deleteLinks = array();
while ($row = @mysql_fetch_row($result)) {
	$deleteLinks[] = $row[0];
}
if (!empty($deleteLinks)) {
	$query = "DELETE FROM `download_cat` WHERE FK_DOWNLOAD IN (".implode(",", $deleteLinks).")";
	if (@mysql_query($query) === false) {
		echo("[ERROR] Löschen von verwaisten Kategorie verknüpfungen fehlgeschlagen!");
	}
}

/*
 * UPDATE NEW DOWNLOADS
 */
require_once "sys/resolve.php";
$resolver = new LinkResolver();
$query = "SELECT * FROM `download` WHERE `STAMP_UPDATE` IS NULL AND UPDATING=0 ORDER BY `STAMP_FOUND` DESC, ID_DOWNLOAD DESC LIMIT 1";
for ($i = 0; $i < $count_max; $i++) {
	if ($row = @mysql_fetch_assoc(@mysql_query($query))) {
		@mysql_query("UPDATE `download` SET UPDATING=1 WHERE ID_DOWNLOAD=".$row["ID_DOWNLOAD"]);
		if ($row['SOURCE'] == "movie-blog.org") {
			readMovieBlog($row, $resolver);
		}
		if ($row['SOURCE'] == "serienjunkies.org") {
			readSerienJunkies($row, $resolver);
		}
		if ($row['SOURCE'] == "drei.to") {
			readDrei($row, $resolver);
		}
		if ($row['SOURCE'] == "gwarez.cc") {
			readGWarez($row, $resolver);
		}
		$result = @mysql_query("DELETE FROM `download` WHERE `STAMP_UPDATE` IS NULL AND ID_DOWNLOAD=".$row["ID_DOWNLOAD"]);
		if (@mysql_affected_rows() > 0) {
			echo("Download #".($i+1)." (".$row["TITLE"].") has no usable links!\n");
			@mysql_query("DELETE FROM `download_cat` WHERE FK_DOWNLOAD=".$row["ID_DOWNLOAD"]);
		} else {
			echo("Download #".($i+1)." (".$row["TITLE"].") is done!\n");
		}
		sleep(1);
	} else {
		// No more work to do
		break;
	}
}

?>