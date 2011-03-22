<?php

$query = "SELECT * FROM `search_url` WHERE STAMP IS NULL OR STAMP<DATE_SUB(NOW(), interval 30 minute) LIMIT 2";
$result = @mysql_query($query);
while ($ar_search = @mysql_fetch_assoc($result)) {
	$search_results = array();
	switch ($ar_search['SOURCE']) {
		case 'movie-blog.org':
			$search_results = searchMovieBlog($ar_search['URL']);
			break;
		case 'serienjunkies.org':
			@mysql_query('UPDATE `download` SET STAMP_FOUND=DATE_SUB(CURDATE(), interval 4 day) WHERE STAMP_FOUND=CURDATE() AND SOURCE="serienjunkies.org"');
			$search_results = searchSerienJunkiesStart($ar_search['URL']);
			break;
		case 'drei.to':
			$search_results = searchDrei($ar_search['URL']);
			break;
		case 'gwarez.cc':
			$search_results = searchGWarez($ar_search['URL'], "");
			break;
		default:
			break;
	}
	$downloads = implode(",",$search_results);
	@mysql_query("INSERT INTO `search_url` (`URL`, `SOURCE`, `STAMP`, `RESULTS`) VALUES ".
		"('".mysql_escape_string($ar_search['URL'])."', '".mysql_escape_string($ar_search['SOURCE'])."', NOW(), '".mysql_escape_string($downloads)."') ".
		"ON DUPLICATE KEY UPDATE `STAMP`=NOW(), `RESULTS`='".mysql_escape_string($downloads)."'");
}

$query = "SELECT * FROM `search` WHERE STAMP IS NULL OR STAMP<DATE_SUB(NOW(), interval 7 day) LIMIT 2";
$result = @mysql_query($query);
while ($ar_search = @mysql_fetch_assoc($result)) {
	$cur_results = array();
	switch ($ar_search['SOURCE']) {
		case 'movie-blog.org':
			$urls = searchMovieBlogLinks($ar_search['TEXT']);
			foreach ($urls as $index => $url) {
				$cur_results = array_merge($cur_results, searchMovieBlog($url));
			}
			break;
		case 'serienjunkies.org':
			$urls = array("http://serienjunkies.org/search/".urlencode($ar_search['TEXT']));
			$cur_results = array();
			foreach ($urls as $index => $url) {
				$cur_results = array_merge($cur_results, searchSerienJunkies($url));
			}
			break;
		case 'drei.to':
			$urls = searchDreiLinks($ar_search['TEXT']);
			$cur_results = array();
			foreach ($urls as $index => $url) {
				$cur_results = array_merge($cur_results, searchDrei($url));
			}
			break;
		default:
			$cur_results = array();
			break;
	}
	$downloads = implode(",",$cur_results);
	@mysql_query("INSERT INTO `search` (`TEXT`, `SOURCE`, `STAMP`, `RESULTS`) VALUES ".
		"('".mysql_escape_string($ar_search['TEXT'])."', '".mysql_escape_string($ar_search['SOURCE'])."', NOW(), '".mysql_escape_string($downloads)."') ".
		"ON DUPLICATE KEY UPDATE `STAMP`=NOW(), `RESULTS`='".mysql_escape_string($downloads)."'");
}

?>