<?php

$ar_links = array();
$query = "SELECT ID_DOWNLOAD_LINK FROM `download_link` WHERE FK_DOWNLOAD=".$ar_download['ID_DOWNLOAD']." AND TITLE LIKE '".mysql_escape_string($row['TITLE'])."'";
$result = @mysql_query($query);
while ($row_link = @mysql_fetch_assoc($result)) $ar_links[] = $row_link["ID_DOWNLOAD_LINK"];
$row['IDS'] = implode(",",$ar_links);
?>
<div class="ui-state-default">
	<a style="cursor: pointer;" onmouseover="CheckJD(this);" onclick="GetLinks('<?=$row['IDS']?>');">
		<?=utf8_encode(htmlspecialchars($row['TITLE']))?>
	</a>
</div>