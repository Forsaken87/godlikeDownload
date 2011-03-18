<?php
if (!isUser()) die(header("location: index.php?show=login"));

if (!empty($_REQUEST['do'])) {
	switch ($_REQUEST['do']) {
		case "cat_show":
			@mysql_query("DELETE FROM `user_ignore` WHERE FK_USER=".$_SESSION['user']['ID_USER']." AND FK_CATEGORY=".(int)$_REQUEST['cat']);
			die("OK");
		case "cat_hide":
			$fk_group = get_query_field("SELECT FK_CATEGORY_GROUP FROM `category` WHERE ID_CATEGORY=".(int)$_REQUEST['cat']);
			if ($fk_group > 0) {
				@mysql_query("INSERT INTO `user_ignore` (FK_USER, FK_CATEGORY) VALUES (".$_SESSION['user']['ID_USER'].", ".(int)$_REQUEST['cat'].")");
				die("OK");
			}
	}
	die("FAIL");
}

?>
<script type="text/javascript">

$(function() {
	$(".category_check").button();
});

function ChangeCategory(button) {
	var action = (button.checked ? "cat_show" : "cat_hide");
	var cat_id = $(button).val();
	$.get("index.php?run=settings&ajax=1&do="+action+"&cat="+cat_id);
}

</script>

<h2>Neue Downloads aus folgenden Kategorien werden auf dem Dashboard angezeigt:</h2>
<div class="ui-widget ui-widget-content" align="center" style="padding: 4px;">
<?php
	$ar_categorys_ignored = array();
	$query = "SELECT * FROM `user_ignore` WHERE FK_USER=".$_SESSION['user']['ID_USER'];
	$result = @mysql_query($query);
	$id_group = null;
	while ($row = @mysql_fetch_assoc($result)) {
		$ar_categorys_ignored[] = $row["FK_CATEGORY"];
	}

	$query = "SELECT c.*, g.NAME as CAT_GROUP \n".
		"FROM `category` c \n".
		"	LEFT JOIN `category_group` g ON c.FK_CATEGORY_GROUP=g.ID_CATEGORY_GROUP \n".
		"WHERE c.FK_CATEGORY_GROUP IN (1,2) AND g.SHOW_SEARCH=1 \n".
		"	GROUP BY c.ID_CATEGORY \n".
		"	ORDER BY c.FK_CATEGORY_GROUP ASC, c.NAME ASC";
	$result = @mysql_query($query);
	$id_group = null;
	while ($row = @mysql_fetch_assoc($result)) {
		if ($id_group != $row["FK_CATEGORY_GROUP"]) {
			if ($id_group != null) {
				echo('<br style="clear: both;" />');
			}
			$id_group = $row["FK_CATEGORY_GROUP"];
			echo('<div class="ui-widget-header" style="margin: 2px 0px; color: #8EFF05;">'.utf8_encode(htmlspecialchars($row["CAT_GROUP"])).'</div>');
		}
		$row['SELECTED'] = !in_array($row['ID_CATEGORY'], $ar_categorys_ignored);
		include 'settings_cat_row.php';
	}
?>
	<br style="clear: both;" />
</div>