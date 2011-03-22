<?php

global $id_user;

$max_id = 0;
$max_id_global = get_query_field("SELECT MAX(ID_DOWNLOAD_LINK) FROM `download_link`");
if ($_REQUEST['do'] == 'clearNew') {
	$_COOKIE['LAST_DOWNLOAD'] = $id_last_download = $_REQUEST['newest'];
	setcookie('LAST_DOWNLOAD', $id_last_download);
}

?>
<script type="text/javascript">
$(function() {
	$(".button_clear_new").button();
});
</script>
<table class="ui-widget ui-widget-content" style="width: 100%;" cellpadding="0" cellspacing="0">
	<thead>
		<tr class="ui-widget-header">
			<th></th>
			<th>Titel</th>
			<th>Kategorien</th>
			<th>Eingestellt am</th>
		</tr>
	</thead>
	<tbody>
	<?php
		$ar_cats_ignored = array();
		$query = "SELECT * FROM `user_ignore` WHERE FK_USER=".$id_user;
		$result = @mysql_query($query);
		while ($row = @mysql_fetch_assoc($result)) {
			$ar_cats_ignored[] = $row["FK_CATEGORY"];
		}
		$queryJoinIgnored = "";
		$queryWhereIgnored = "";
		if (!empty($ar_cats_ignored)) {
			$queryCategorys = "SELECT * FROM `category` WHERE ID_CATEGORY NOT IN (".implode(",",$ar_cats_ignored).")";
			$result = @mysql_query($queryCategorys);
			$ar_cats = array();
			while ($row = @mysql_fetch_assoc($result)) {
				$id_group = $row["FK_CATEGORY_GROUP"];
				if (empty($ar_cats[$id_group])) $ar_cats[$id_group] = array();
				$ar_cats[$id_group][] = $row["ID_CATEGORY"];
			}
			$queryWhereIgnored = " AND dc.FK_CATEGORY IN (".implode(",",array_merge($ar_cats[1], $ar_cats[2])).")\n";
		}
		$query =	"SELECT d.*\n".
					"FROM `download` d\n".
					"	LEFT JOIN `download_cat` dc ON d.ID_DOWNLOAD=dc.FK_DOWNLOAD\n".
					"WHERE d.STAMP_FOUND>DATE_SUB(CURDATE(), interval 1 day)".$queryWhereIgnored.
					"GROUP BY d.ID_DOWNLOAD\n".
					"ORDER BY d.STAMP_UPDATE DESC, d.STAMP_UPDATE DESC\n".
					"LIMIT 100";
		if ($result = @mysql_query($query)) {
			$count = get_query_field("SELECT FOUND_ROWS()");
			$even = 0;
			while ($row = @mysql_fetch_assoc($result)) {
				$row["EVEN"] = $even;
				$row["TITLE_MAX"] = (!empty($_REQUEST['length']) ? $_REQUEST['length'] : 40);
				$even = abs($even-1);
				include '_cache_user_new_row.php';
			}
		}
	?>
	</tbody>
</table>
<input type="hidden" id="num_new_downloads" value="<?=$count?>" />
<?php
if ($max_id > 0) {
?>
<!--
<div style="text-align: center; margin-top: 4px;">
	<input type="button" class="button_clear_new" onclick="ClearNewDownloads(<?=$max_id?>);" value="Angezeigte als gesehen markieren" />
	<input type="button" class="button_clear_new" onclick="ClearNewDownloads(<?=$max_id_global?>);" value="Liste neuer Einträge leeren" />
</div>
 -->
<?php
}
?>