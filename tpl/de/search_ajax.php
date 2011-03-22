<?php
if (!isUser()) die(header("location: index.php?show=login"));

require_once 'sys/search.php';

if (!empty($_SESSION["LAST_SEARCH"]) && ((time() - $_SESSION["LAST_SEARCH"]) > 60)) {
	$_SESSION["CUR_SEARCH"] = "";
	$_SESSION["CUR_SEARCH_CAT"] = "";
}

$searchText = (isset($_REQUEST["q"]) ? trim($_REQUEST["q"]) : $_SESSION["CUR_SEARCH"]);
$searchCat = (isset($_REQUEST["cat"]) ? trim($_REQUEST["cat"]) : $_SESSION["CUR_SEARCH_CAT"]);
$_SESSION["CUR_SEARCH"] = $searchText;
$_SESSION["CUR_SEARCH_CAT"] = $searchCat;
$_SESSION["LAST_SEARCH"] = time();

if ($_REQUEST['do'] == 'complete') {
	$ar_matches = array();
	$query = "SELECT * FROM `search` WHERE TEXT LIKE '".mysql_escape_string($_REQUEST['term'])."%' GROUP BY TEXT";
	$result = @mysql_query($query);
	while ($row = @mysql_fetch_assoc($result)) {
		$ar_matches[] = $row["TEXT"];
	}
	$query = "SELECT * FROM `download` WHERE MATCH TITLE AGAINST ('".mysql_escape_string($_REQUEST['term'])."')>1";
	$result = @mysql_query($query);
	while ($row = @mysql_fetch_assoc($result)) {
		if (preg_match('/(^| )([a-z0-9äöü\-\_\.\,]*'.preg_quote($_REQUEST['term'], "/").'[a-z0-9äöü\-\_\.\,]*)($| )/i', $row["TITLE"], $ar_preg)) {
			if (!in_array($ar_preg[2], $ar_matches)) {
				$ar_matches[] = $ar_preg[2];
			}
		}
	}
	header('Content-type: application/json');
	die(json_encode($ar_matches));
}

$downloads = "";
$search_started = false;
$search_engines = array('movie-blog.org', 'serienjunkies.org', 'drei.to');

$_SESSION['SEARCH_RESULT'] = array();
$_SESSION['SEARCH_LINKS'] = array();
$_SESSION['SEARCH_LINKS_COUNT'] = 0;

if (!empty($searchText)) {
	foreach ($search_engines as $index => $search_engine) {
		$query = "SELECT (DATE_ADD(STAMP, interval 180 minute) < NOW()) AS NEW_SEARCH, STAMP, RESULTS FROM `search` ".
			"WHERE TEXT LIKE '".mysql_escape_string($searchText)."' AND SOURCE LIKE '".mysql_escape_string($search_engine)."'";
		$ar_last_search = @mysql_fetch_assoc(@mysql_query($query));
		if (!$ar_last_search || $ar_last_search['NEW_SEARCH'] || ($ar_last_search['STAMP'] == null)) {
			$search_started = true;
			$_SESSION['SEARCH_RESULT'][$search_engine] = array();
			switch ($search_engine) {
				case 'movie-blog.org':
					$_SESSION['SEARCH_LINKS'][$search_engine] = searchMovieBlogLinks($searchText, 5);
					break;
				case 'serienjunkies.org':
					$_SESSION['SEARCH_LINKS'][$search_engine] = array("http://serienjunkies.org/search/".urlencode($searchText));
					break;
				case 'drei.to':
					$_SESSION['SEARCH_LINKS'][$search_engine] = searchDreiLinks($searchText, 5);
					break;
				case 'gwarez.cc':
					$_SESSION['SEARCH_LINKS'][$search_engine] = searchGWarezLinks($searchText, 5);
					break;
				default:
					$_SESSION['SEARCH_LINKS'][$search_engine] = array();
					break;
			}
			$_SESSION['SEARCH_LINKS_COUNT'] += count($_SESSION['SEARCH_LINKS'][$search_engine]);
			@mysql_query("INSERT INTO `search` (`TEXT`, `SOURCE`, `STAMP`) ".
				"VALUES ('".mysql_escape_string($searchText)."', '".mysql_escape_string($search_engine)."', NULL) ".
				"ON DUPLICATE KEY UPDATE STAMP=NULL");
		}
		if (!empty($ar_last_search) && !empty($ar_last_search['RESULTS'])) {
			$downloads .= (empty($downloads) ? $ar_last_search['RESULTS'] : ",".$ar_last_search['RESULTS']);
		}
	}
}

if ($search_started) {
	?>
	<div class="ui-widget ui-widget-content">
		<script type="text/javascript">
			$(function() {
				$("#progress_update").progressbar({ value: 0 });
				UpdateProgress();
			});
		</script>
		<div id="progress_update"></div>
		<h1>Downloadliste wird erneuert. Noch <span id="progress_pages"><?=$_SESSION['SEARCH_LINKS_COUNT']?></span> Seiten &uuml;brig</h1>
	</div>
	<?php
} else {
	$cat = (!empty($searchCat) ? mysql_escape_string($searchCat) : "");
	if (!empty($cat) || !empty($searchText)) {
	?>
	<div class="ui-state-highlight ui-corner-all" style="margin: 2px; padding: 2px;">
		<strong>Suchergebniss</strong> f&uuml;r die Suche
		<?php
			if (!empty($searchText)) {
				echo 'nach "'.utf8_encode(htmlspecialchars($searchText)).'"';
			}
			if (!empty($searchCat)) {
				$kat_names = array();
				$kat_res = @mysql_query('SELECT NAME FROM `category` WHERE ID_CATEGORY IN ('.$searchCat.')');
				while ($kat_row = @mysql_fetch_row($kat_res)) {
					$kat_names[] = $kat_row[0];
				}
				echo 'in den Kategorien &quot;'.utf8_encode(htmlspecialchars(implode(", ",$kat_names))).'&quot;';
			}
		?>
	</div>
	<?php
	}
	?>

	<table class="ui-widget ui-widget-content" style="width: 100%;" cellpadding="0" cellspacing="0">
		<thead>
			<tr class="ui-widget-header">
				<th></th>
				<th>Titel</th>
				<th>Kategorien</th>
				<th>Eingestellt am</th>
				<th>Letztes Update</th>
			</tr>
		</thead>
		<tbody>
		<?php
		if (!empty($cat) || !empty($searchText)) {
			echo("<tbody>");
			/*
			 * Display results
			 */
			$limit_page = ($_REQUEST['page'] ? $_REQUEST['page'] : 1);
			$limit_count = (empty($_REQUEST['rows']) ? 25 : $_REQUEST['rows']);
			$limit_start = ($limit_page - 1) * $limit_count;
			$limit_downloads = 0;
			// Search all categorys
			$ar_join = array();
			$ar_where = array();
			$ar_order = array();
			if (!empty($cat)) {
				$ar_cats = explode(",", $cat);
				foreach ($ar_cats as $index => $id_cat) {
					$ar_join[] = "LEFT JOIN `download_cat` c".$index." ON c".$index.".FK_DOWNLOAD=".
						($index == 0 ? "d.ID_DOWNLOAD" : "c".($index-1).".FK_DOWNLOAD");
					$ar_where[] = "c".$index.".FK_CATEGORY=".$id_cat;
				}
			}
			if (!empty($searchText)) {
				$ar_order[] = "MATCH d.`TITLE` AGAINST ('".mysql_escape_string($searchText)."') DESC";
				$ar_where[] = "( MATCH d.`TITLE` AGAINST ('".mysql_escape_string($searchText)."') > 1".
						(!empty($downloads) ? " OR d.ID_DOWNLOAD IN (".$downloads.") " : " ").")";
			}
			$ar_order[] = "d.STAMP_FOUND DESC";
			$query = "SELECT SQL_CALC_FOUND_ROWS d.* FROM `download` d ".implode(" ", $ar_join)." WHERE ".implode(" AND ", $ar_where).
				" ORDER BY ".implode(", ", $ar_order)." LIMIT ".$limit_start.",".$limit_count;
			if ($result = mysql_query($query)) {
				$ar_words = explode(" ", $searchText);
				$ar_count = @mysql_fetch_row(@mysql_query("SELECT FOUND_ROWS()"));
				if (!empty($ar_count)) {
					$limit_downloads = (int)$ar_count[0];
				}
				$even = 0;
				while($row = mysql_fetch_assoc($result)) {
					$row["EVEN"] = $even;
					$even = abs($even-1);
					$row["TITLE_MAX"] = (!empty($_REQUEST['length']) ? $_REQUEST['length'] : 30);
					$row["TITLE_PLAIN"] = htmlspecialchars($row['TITLE']);
					$row["TITLE_TEXT"] = htmlspecialchars( substr($row['TITLE'], 0, $row["TITLE_MAX"]) ).(strlen($row['TITLE']) > $row["TITLE_MAX"] ? " [...]" : "");
					$row["TITLE"] = $row["TITLE_PLAIN"];
					foreach ($ar_words as $index => $word_plain) {
						$word = htmlspecialchars($word_plain);
						$row["TITLE"] = str_replace($word, "<strong>$word</strong>", $row["TITLE"]);
						$row["TITLE_TEXT"] = str_replace($word, "<strong>$word</strong>", $row["TITLE_TEXT"]);
					}
					include 'search_row.php';
				}
			} else {
			?>
			<tr>
				<td colspan="5">
					<div class="ui-state-error">
						Die Suche lieferte keine Ergebnisse!
					</div>
				</td>
			</tr>
			<?php
			}
			$limit_pages = floor(($limit_downloads - 1) / $limit_count) + 1;
			echo("</tbody>");
		}
		?>
		</tbody>
	</table>
	<?php
	$page = (empty($_REQUEST["for"]) ? "search" : $_REQUEST["for"]);
	include("pager_search.php");
}
?>