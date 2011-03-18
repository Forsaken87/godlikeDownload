<?php
if (!isUser()) die(header("location: index.php?show=login"));

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
		$query = "SELECT * FROM `user_ignore` WHERE FK_USER=".$_SESSION['user']['ID_USER'];
		$result = @mysql_query($query);
		$id_group = null;
		while ($row = @mysql_fetch_assoc($result)) {
			$ar_cats_ignored[] = $row["FK_CATEGORY"];
		}
		$queryJoinIgnored = "";
		$queryWhereIgnored = "";
		if (!empty($ar_cats_ignored)) {
			 $queryJoinIgnored .= "	LEFT JOIN `download_cat` dc1 ON d.ID_DOWNLOAD=dc1.FK_DOWNLOAD AND ".
			 	"dc1.FK_CATEGORY IN (".implode(",",$ar_cats_ignored).")\n".
			 	"	LEFT JOIN `category` c1 ON c1.ID_CATEGORY=dc1.FK_CATEGORY AND c1.FK_CATEGORY_GROUP=1\n";
			 $queryJoinIgnored .= "	LEFT JOIN `download_cat` dc2 ON d.ID_DOWNLOAD=dc2.FK_DOWNLOAD AND ".
			 	"dc2.FK_CATEGORY NOT IN (".implode(",",$ar_cats_ignored).")\n".
			 	"	LEFT JOIN `category` c2 ON c2.ID_CATEGORY=dc2.FK_CATEGORY AND c2.FK_CATEGORY_GROUP=2\n";
			 $queryWhereIgnored = " WHERE dc1.FK_CATEGORY IS NULL AND dc2.FK_CATEGORY IS NOT NULL";
		}
		$query = 	"SELECT d.* \n".
					"FROM `download` d\n".
					$queryJoinIgnored.$queryWhereIgnored."\n".
					"GROUP BY d.ID_DOWNLOAD\n".
					"ORDER BY d.STAMP_FOUND DESC, d.STAMP_UPDATE DESC LIMIT ".(empty($_REQUEST['rows']) ? 15 : $_REQUEST['rows']);
		if ($result = @mysql_query($query)) {
			$count = get_query_field("SELECT FOUND_ROWS()");
			$even = 0;
			while ($row = @mysql_fetch_assoc($result)) {
				$row["EVEN"] = $even;
				$row["TITLE_MAX"] = (!empty($_REQUEST['length']) ? $_REQUEST['length'] : 30);
				$even = abs($even-1);
				include 'ajax_downloads_new_row.php';
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