<?php
if (!isUser()) die(header("location: index.php?show=login"));

if (!empty($_REQUEST['id'])) {
	$query = 	"SELECT d.* FROM `download` d\n".
				"	LEFT JOIN `download_cat` dc ON d.ID_DOWNLOAD=dc.FK_DOWNLOAD\n".
				"WHERE dc.FK_CATEGORY=".(int)$_REQUEST['id']."\n".
				"ORDER BY d.SOURCE ASC, d.STAMP_FOUND DESC, d.TITLE DESC";
	$result = @mysql_query($query);
	$arDownloads = array();
	$arLinks = array();
	while ($row = @mysql_fetch_assoc($result)) {
		$row["LINKS"] = array();
		$queryLinks = "SELECT * FROM `download_link` l WHERE l.FK_DOWNLOAD=".$row["ID_DOWNLOAD"];
		$resultLinks = @mysql_query($queryLinks);
		while ($rowLink = @mysql_fetch_assoc($resultLinks)) {
			$hoster = strtolower($rowLink["TITLE"]);
			if (empty($row["LINKS"][$hoster])) {
				// New hoster
				$row["LINKS"][$hoster] = array();
			}
			$row["LINKS"][$hoster][] = $rowLink["ID_DOWNLOAD_LINK"];
			if (empty($arLinks[$hoster])) {
				// New hoster
				$arLinks[$hoster] = array();
			}
			$arLinks[$hoster][] = $rowLink["ID_DOWNLOAD_LINK"];
		}
		$arDownloads[] = $row;
	}
	?>
	<div align="center">
		<div class="ui-widget ui-widget-content" align="center">
			<h1>Click'n'Load - Alle Staffeln, Folgen und Extras</h1>
			<?php
			foreach ($arLinks as $hoster => $links) {
				?><input class="cnlLink" type="button" style="width: 200px;" onclick="GetLinks('<?=implode(",",$links)?>');" value="<?=$hoster?>" /><?php
			}
			?>
			<br />
		</div>
		<br />
		<h1>Click'n'Load - Ausgewählte Folgen</h1>
		<table width="100%" class="ui-widget ui-widget-content" cellpadding="0" cellspacing="0">
			<thead>
				<tr class="ui-widget-header">
					<th></th>
					<th>Folge</th>
					<th>Download</th>
				</tr>
			</thead>
			<tbody>
			<?php
				foreach ($arDownloads as $row) {
					include "series_row_dl.php";
				}
			?>
			</tbody>
		</table>
		<br />
	</div>
	<?php
	die();
}

?>
<script type="text/javascript">

var last_search = "";

function UpdateFilter(input) {
	var search = input.value.toLowerCase();
	if (last_search.length < search.length) {
		$("#series_list a.seriesEntry:visible").each(function() {
			var title = $(this).children("div span:first").html().toLowerCase();
			if (title.indexOf(search) >= 0) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	} else if (search != last_search) {
		$("#series_list a.seriesEntry").each(function() {
			var title = $(this).children("div span:first").html().toLowerCase();
			if (title.indexOf(search) >= 0) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	}
	last_search = search;
}

function UpdateManualSeries(idDownload, id) {
	$.get('index.php?run=download&ajax=1&id='+idDownload+'&force=1', function() {
		ShowSeries(id);
	});
}

function ShowSeries(id) {
	var url = "index.php?run=series&ajax=1&id="+id;
	$("#modal_loading").show();
	$("#series_content").load(url, function() {
		$("#series_content input[type='button']").button();
		$("#modal_loading").hide();
	});
}

</script>
<div class="ui-widget-header" style="position: fixed; left: 16px; top: 96px; height: 32px; right: 75%; margin-right: 4px;" align="center">
	<input style="width: 99%;" onkeyup="UpdateFilter(this);" id="series_filter" placeholder="Suchen ..." />
</div>
<div id="series_list" class="ui-widget ui-widget-content" style="position: fixed; left: 16px; top: 128px; bottom: 16px; right: 75%; margin-right: 4px; overflow: auto;">
	<?php
		$query = "SELECT c.*, count(dc.FK_DOWNLOAD) AS DL_COUNT\n".
			"FROM `category` c \n".
			"	LEFT JOIN `download_cat` dc ON dc.FK_CATEGORY=c.ID_CATEGORY \n".
			"WHERE c.FK_CATEGORY_GROUP=5\n".
			"GROUP BY c.ID_CATEGORY ORDER BY c.NAME ASC";
		$result = @mysql_query($query);
		while ($row = @mysql_fetch_assoc($result)) {
			if ($row["DL_COUNT"] > 0) {
				include("series_row.php");
			}
		}
	?>
</div>
<div id="series_content" style="position: absolute; left: 25%; top: 4px; bottom: 4px; right: 24px; margin-left: 4px;">
</div>