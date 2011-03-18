<?php
if (!isUser()) die(header("location: index.php?show=login"));

if (!empty($_REQUEST['links'])) {
	// Get download links
	$ar_links = explode(",", $_REQUEST['links']);
	die("test");
}

if (empty($_REQUEST['id'])) die(header("location: index.php?show=categorys"));

$query = "SELECT * FROM `download` WHERE ID_DOWNLOAD=".(int)$_REQUEST['id'];
$ar_download = @mysql_fetch_assoc(@mysql_query($query));
if (($ar_download['STAMP_UPDATE'] == null) || ($_REQUEST["force"])) {
	require_once 'sys/download.php';
	if ($ar_download['SOURCE'] == "movie-blog.org") {
		$ar_download = readMovieBlog($ar_download);
	}
	if ($ar_download['SOURCE'] == "serienjunkies.org") {
		$ar_download = readSerienJunkies($ar_download);
	}
	if ($ar_download['SOURCE'] == "drei.to") {
		$ar_download = readDrei($ar_download);
	}
	if ($ar_download['SOURCE'] == "gwarez.cc") {
		$ar_download = readGWarez($ar_download);
	}
	$ar_download["CATEGORY"] = array();
	$result = @mysql_query("SELECT d.* FROM `category` c, `download_cat` d WHERE c.ID_CATEGORY=d.FK_CATEGORY AND d.FK_DOWNLOAD=".$ar_download['ID_DOWNLOAD']);
	while ($ar_category = @mysql_fetch_assoc($result)) {
		$ar_download["CATEGORY"][] = $ar_category;
	}
	$ar_download["DOWNLOAD"] = array();
	$result = @mysql_query("SELECT * FROM `download_link` WHERE FK_DOWNLOAD=".$ar_download['ID_DOWNLOAD']." GROUP BY `TITLE`");
	while ($ar_link = @mysql_fetch_assoc($result)) {
		$ar_download["DOWNLOAD"][] = $ar_link;
	}
} else {
	$ar_download["CATEGORY"] = array();
	$result = @mysql_query("SELECT c.* FROM `category` c, `download_cat` d WHERE c.ID_CATEGORY=d.FK_CATEGORY AND d.FK_DOWNLOAD=".$ar_download['ID_DOWNLOAD']);
	while ($ar_category = @mysql_fetch_assoc($result)) {
		$ar_download["CATEGORY"][] = $ar_category;
	}
	$ar_download["DOWNLOAD"] = array();
	$result = @mysql_query("SELECT * FROM `download_link` WHERE FK_DOWNLOAD=".$ar_download['ID_DOWNLOAD']." GROUP BY `TITLE`");
	while ($ar_link = @mysql_fetch_assoc($result)) {
		$ar_download["DOWNLOAD"][] = $ar_link;
	}
}
?>
<script language="javascript" src="http://127.0.0.1:9666/jdcheck.js"></script>
<script type="text/javascript">
function AddLinks(crypted) {
	$.post("http://127.0.0.1:9666/flash/add", {
		crypted: crypted,
		jk: "function f(){ return '44291154874044321';}",
		passwords: 'Unbekannt',
		source: "<?=$ar_download['URL']?>"
	}, function(result) {
		alert(result);
	});
}

function CheckJD(link) {
	if (typeof jdownloader == "undefined") {
		$(link).css("color", "red");
	} else {
		$(link).css("color", "");
	}
}

function GetLinks(linkIds) {
	$.get("index.php?run=download&ajax=1&links="+encodeURIComponent(linkIds), function(result) {
		if (typeof linkIds == "string") {
			$("#download_popup").dialog().html(result);
		} else {
			var links = result.links;
			alert(links.join(","));
		}
	});
}

</script>
<div id="download_popup" style="display: none;"></div>
<div class="ui-widget ui-widget-content" style="position: absolute; left: 0px; width: 200px; top: 0px; bottom: 0px;">
	<iframe id="hidden" name="hidden" style="display: none;"></iframe>
	<div class="ui-widget-header">
		Quelle
	</div>
	<div class="ui-state-default">
		<a target="source" href="<?=utf8_encode($ar_download["URL"])?>">
			Quelle öffnen
		</a>
	</div>
	<div class="ui-widget-header">
		Click'n'load
	</div>
	<?php
		function base16Encode($arg){
			$ret="";
			for($i=0;$i<strlen($arg);$i++){
				$tmp=ord(substr($arg,$i,1));
				$ret.=dechex($tmp);
			}
			return $ret;
		}

		foreach ($ar_download["DOWNLOAD"] as $index => $row) {
			include 'download_row.php';
		}
	?>
</div>
<div class="ui-widget ui-widget-content" style="position: absolute; left: 200px; right: 0px; top: 0px; bottom: 0px; overflow: auto;">
	<h1><?=utf8_encode(htmlspecialchars($ar_download['TITLE']))?></h1>

	<?=utf8_encode(strip_tags($ar_download['DESC'], '<a><blockquote><br><div><img><p><strong><table><tbody><thead><tr><th><td>'))?>
	<br style="clear: both;" />
</div>