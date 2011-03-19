<?php

function get_query_field($query) {
	$result = @mysql_query($query);
	if ($result && ($ar_row = @mysql_fetch_row($result))) {
		return $ar_row[0];
	}
	return null;
}

function updateCategory($name, $id_group = 3) {
	$query = "SELECT c.ID_CATEGORY FROM `category` c\n".
		"LEFT JOIN `category_alias` a ON a.FK_CATEGORY=c.ID_CATEGORY\n".
		"WHERE a.NAME LIKE '".mysql_escape_string($name)."' OR c.NAME LIKE '".mysql_escape_string($name)."'";
	$result = @mysql_query($query);
	if ($result !== false) {
		$ar_category = mysql_fetch_assoc($result);
		if (!empty($ar_category)) {
			return $ar_category["ID_CATEGORY"];
		} else {
			$query = "INSERT INTO `category` (`NAME`, `FK_CATEGORY_GROUP`) VALUES ('".mysql_escape_string($name)."', ".$id_group.")";
			if (mysql_query($query) !== false) {
				return mysql_insert_id();
			}
		}
	}
	die("DEBUG: Query failed! ".$query);
	return false;
}

function updateDownload($ar_download) {
	$query = "INSERT INTO `download` (`URL`, `SOURCE`, `TITLE`, `DESC`, `STAMP_FOUND`, `STAMP_UPDATE`) ".
		"VALUES ('".mysql_escape_string($ar_download['URL'])."', '".mysql_escape_string($ar_download['SOURCE'])."', ".
			"'".mysql_escape_string($ar_download['TITLE'])."', '".mysql_escape_string($ar_download['DESC'])."', ".
			(empty($ar_download["STAMP_FOUND"]) ? "CURDATE()" : "'".mysql_escape_string($ar_download["STAMP_FOUND"])."'").", ".
			(empty($ar_download["DOWNLOAD"]) ? "NULL" : "NOW()").") ".
		"ON DUPLICATE KEY UPDATE `TITLE`='".mysql_escape_string($ar_download['TITLE'])."', `DESC`='".mysql_escape_string($ar_download['DESC'])."', ".
			"`STAMP_UPDATE`=".(empty($ar_download["DOWNLOAD"]) ? "NULL" : "NOW()").", `UPDATING`=0 ".
			(!empty($ar_download["STAMP_FOUND"]) ? ", `STAMP_FOUND`='".mysql_escape_string($ar_download["STAMP_FOUND"])."'" : "");
	$result = @mysql_query($query);
	if ($result === false) {
		die("DEBUG: Query failed: ".$query);
		return false;
	} else {
		if (mysql_affected_rows() > 0) {
			$id_download = get_query_field("SELECT ID_DOWNLOAD FROM `download` WHERE URL LIKE '".mysql_escape_string($ar_download['URL'])."'");
		} else {
			$id_download = mysql_insert_id();
		}
		if (!is_numeric($id_download) || ($id_download <= 0)) {
			return false;
		}
		if (!empty($ar_download["CATEGORYS"])) {
			// Remove existing categorys before
			$query_category_delete = "DELETE FROM `download_cat` WHERE FK_DOWNLOAD=".$id_download;
			@mysql_query($query_category_delete);
			// Add new categorys
			foreach ($ar_download["CATEGORYS"] as $index => $name) {
				$id_category = updateCategory($name);
				if ($id_category != false) {
					$query_category = "INSERT INTO `download_cat` (`FK_DOWNLOAD`, `FK_CATEGORY`) VALUES (".$id_download.", ".$id_category.")";
					@mysql_query($query_category);
					$query_category_links = "SELECT FK_CATEGORY_TO FROM `category_link` WHERE FK_CATEGORY=".$id_category;
					$result_category_links = @mysql_query($query_category_links);
					while ($ar_category_link = @mysql_fetch_row($result_category_links)) {
						$query_category = "INSERT INTO `download_cat` (`FK_DOWNLOAD`, `FK_CATEGORY`) VALUES (".$id_download.", ".$ar_category_link[0].")";
						@mysql_query($query_category);
					}
				}
			}
		}
		if (!empty($ar_download["DOWNLOAD"])) {
			foreach ($ar_download["DOWNLOAD"] as $index => $ar_link) {
				$query_link = "INSERT INTO `download_link` (`FK_DOWNLOAD`, `URL`, `TITLE`, `IS_CONTAINER`) VALUES ".
					"(".$id_download.", '".mysql_escape_string($ar_link['URL'])."', '".mysql_escape_string($ar_link['TITLE'])."', ".($ar_link['IS_CONTAINER'] ? $ar_link['IS_CONTAINER'] : 0).")";
				@mysql_query($query_link);
			}
		}
		return $id_download;
	}
}

?>