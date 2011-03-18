<?php

$query = "SELECT a.*, c.ID_CATEGORY FROM `category` c\n".
	"	RIGHT JOIN `category_alias` a ON c.NAME=a.NAME\n".
	"GROUP BY a.ID_CATEGORY_ALIAS";
$result = @mysql_query($query);
while ($row = @mysql_fetch_assoc($result)) {
	// Find collisions
	$queryCollisions = "SELECT ID_DOWNLOAD FROM `download` d
	LEFT JOIN `download_cat` dc ON dc.FK_DOWNLOAD = d.ID_DOWNLOAD AND dc.FK_CATEGORY=".$row['ID_CATEGORY']."
	LEFT JOIN `download_cat` dc2 ON dc2.FK_DOWNLOAD = d.ID_DOWNLOAD AND dc2.FK_CATEGORY=".$row['FK_CATEGORY']."
WHERE (dc.FK_DOWNLOAD IS NOT NULL) AND (dc2.FK_DOWNLOAD IS NOT NULL)";
	$resultCollisions = @mysql_query($queryCollisions);
	$idsCollision = array();
	while ($ar_download = @mysql_fetch_row($resultCollisions)) {
		$idsCollision[] = $ar_download[0];
	}
	if (!empty($idsCollision)) {
		$queryDelete = "DELETE FROM `download_cat` WHERE FK_CATEGORY=".$row['ID_CATEGORY']." AND FK_DOWNLOAD IN (".implode(", ", $idsCollision).")";
		@mysql_query($queryDelete);
	}
	// Update categorys
	$queryUpdate = "UPDATE `download_cat` SET FK_CATEGORY=".$row['FK_CATEGORY']." WHERE FK_CATEGORY=".$row['ID_CATEGORY'];
	@mysql_query($queryUpdate);
}

?>