<?php
if (!isUser()) die(header("location: index.php?show=login"));
?>
<script type="text/javascript" src="js/search.js"></script>
<script type="text/javascript">

$(function() {
	CreateSearch("#dl_search");
	$(".category_check").button();
	SendSearch();
});

</script>
<?php
global $page;
?>
<div class="ui-widget ui-widget-content" style="position: absolute; left: 0px; width: 200px; top: 0px; bottom: 0px; overflow: auto;">
<?php
	$ar_categorys = explode(",", $_SESSION["CUR_SEARCH_CAT"]);

	$query = "SELECT c.*, g.NAME as CAT_GROUP, count(dc.FK_DOWNLOAD) AS DL_COUNT\n".
		"FROM `category` c \n".
		"	LEFT JOIN `category_group` g ON c.FK_CATEGORY_GROUP=g.ID_CATEGORY_GROUP \n".
		"	LEFT JOIN `download_cat` dc ON dc.FK_CATEGORY=c.ID_CATEGORY \n".
		"WHERE g.SHOW_SEARCH=1 \n".
		"	GROUP BY c.ID_CATEGORY \n".
		"	ORDER BY c.FK_CATEGORY_GROUP ASC, c.NAME ASC";
	$result = @mysql_query($query);
	$id_group = null;
	while ($row = @mysql_fetch_assoc($result)) {
		if ($id_group != $row["FK_CATEGORY_GROUP"]) {
			$id_group = $row["FK_CATEGORY_GROUP"];
			echo('<div class="ui-widget-header">'.utf8_encode(htmlspecialchars($row["CAT_GROUP"])).'</div>');
		}
		if (($row["IMPORTANT"] > 0) || ($row['DL_COUNT'] > 0)) {
			$row['SELECTED'] = in_array($row['ID_CATEGORY'], $ar_categorys);
			include 'categorys_row.php';
		}
	}
?>
</div>
<div class="ui-widget ui-widget-content" style="position: absolute; left: 200px; right: 0px; top: 0px; bottom: 0px;">
	<div class="ui-widget ui-header" style="position: relative; height: 32px;">
		<div style="font-size: 20px; font-weight: bold; position: absolute; left: 8px; width: 260px;">
			In den Kategorien suchen:
		</div>
		<div style="position: absolute; left: 268px; right: 8px;">
			<input onkeyup="CheckSearch(event);" style="width: 100%;" id="dl_search" placeholder="Bitte Suchbegriff eingeben ..." value="<?=$_SESSION["CUR_SEARCH"]?>" />
		</div>
	</div>
	<div id="dl_search_result">
	</div>
</div>