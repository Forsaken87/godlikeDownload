<?php
if (!isUser()) die(header("location: index.php?show=login"));

if (!empty($_REQUEST['links'])) {
	require_once "sys/resolve.php";
	$resolver = new LinkResolver();
	// Get download links
	$query = "SELECT l.*, count(c.URL) as RESOLVED_LINKS FROM `download_link` l \n".
		"	LEFT JOIN `download_container` c ON c.FK_DOWNLOAD_LINK=l.ID_DOWNLOAD_LINK \n".
		"WHERE l.ID_DOWNLOAD_LINK IN (".mysql_escape_string($_REQUEST['links']).") \n".
		"GROUP BY l.ID_DOWNLOAD_LINK";
	$result = @mysql_query($query);
	if ($result !== false) {
		$arLinks = array();
		$arContainers = array();
		while ($row = @mysql_fetch_assoc($result)) {
			if ($row["RESOLVED_LINKS"] > 0) {
				$queryLinks = "SELECT URL FROM `download_container` WHERE FK_DOWNLOAD_LINK=".$row["ID_DOWNLOAD_LINK"];
				$resultLinks = @mysql_query($queryLinks);
				while ($rowLink = @mysql_fetch_row($resultLinks)) {
					$arLinks[] = $rowLink[0];
				}
			} else {
				if ($row["URL"] == $_REQUEST["captchaUrl"]) {
					$resolvedUrls = $resolver->SubmitCaptcha($row["URL"], $_REQUEST["captchaUrl"], $_REQUEST["captchaIdent"], $_REQUEST["captchaText"]);
				} else {
					$resolvedUrls = $resolver->ParseLink($row["URL"]);
				}
				if (is_array($resolvedUrls)) {
					$arLinks = array_merge($arLinks, $resolvedUrls);
					if ($resolvedUrls[0] != $row["URL"]) {
						// Reolved to a new links
						if (!in_array($row["ID_DOWNLOAD_LINK"], $arContainers)) {
							$arContainers[] = $row["ID_DOWNLOAD_LINK"];
							$query = "UPDATE `download_link` SET IS_CONTAINER=1 WHERE ID_DOWNLOAD_LINK=".$row["ID_DOWNLOAD_LINK"];
							@mysql_query($query);
							$query = "DELETE FROM `download_container` WHERE FK_DOWNLOAD_LINK=".$row["ID_DOWNLOAD_LINK"].")";
							@mysql_query($query);
						}
						foreach ($resolvedUrls as $url) {
							$query = "INSERT INTO `download_container` (FK_DOWNLOAD_LINK, URL) VALUES (".$row["ID_DOWNLOAD_LINK"].", '".mysql_escape_string($url)."')";
							@mysql_query($query);
						}
					}
				} else {
					// Missing captcha
					die($resolvedUrls);
				}
			}
		}
		// All links resolved as far as possible
		header("content-type: application/json");
		die(json_encode($arLinks));
	}
}

if (empty($_REQUEST['id'])) die(header("location: index.php?show=categorys"));

$query = "SELECT * FROM `download` WHERE ID_DOWNLOAD=".(int)$_REQUEST['id'];
$ar_download = @mysql_fetch_assoc(@mysql_query($query));
if (($ar_download['STAMP_UPDATE'] == null) || ($_REQUEST["force"])) {
	require_once 'sys/download.php';
	require_once "sys/resolve.php";
	$resolver = new LinkResolver();
	if ($ar_download['SOURCE'] == "movie-blog.org") {
		$ar_download = readMovieBlog($ar_download, $resolver);
	}
	if ($ar_download['SOURCE'] == "serienjunkies.org") {
		$ar_download = readSerienJunkies($ar_download, $resolver);
	}
	if ($ar_download['SOURCE'] == "drei.to") {
		$ar_download = readDrei($ar_download, $resolver);
	}
	if ($ar_download['SOURCE'] == "gwarez.cc") {
		$ar_download = readGWarez($ar_download, $resolver);
	}
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
<script type="text/javascript">


</script>
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
		foreach ($ar_download["DOWNLOAD"] as $index => $row) {
			include 'download_row.php';
		}
	?>
	<div class="ui-widget-header">
		Kategorien
	</div>
	<?php
		foreach ($ar_download["CATEGORY"] as $index => $row) {
			include 'download_row_cat.php';
		}
	?>
</div>
<div class="ui-widget ui-widget-content" style="position: absolute; left: 200px; right: 0px; top: 0px; bottom: 0px; overflow: auto;">
	<h1><?=utf8_encode(htmlspecialchars($ar_download['TITLE']))?></h1>

	<?=utf8_encode(strip_tags($ar_download['DESC'], '<a><blockquote><br><div><img><p><strong><table><tbody><thead><tr><th><td>'))?>
	<br style="clear: both;" />
</div>