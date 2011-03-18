<?php
if (!isUser()) die(header("location: index.php?show=login"));

require_once 'sys/search.php';

$ar_result = array(
	'count'		=> 0,
	'count_max'	=> 0,
	'percent'	=> 0,
	'done'		=> false
);
if (!empty($_SESSION['SEARCH_LINKS'])) {
	$ar_result['count_max'] = $_SESSION['SEARCH_LINKS_COUNT'];
	if (!empty($_SESSION['SEARCH_LINKS']['movie-blog.org'])) {
		$url = array_shift($_SESSION['SEARCH_LINKS']['movie-blog.org']);
		$_SESSION['SEARCH_RESULT']['movie-blog.org'] = array_merge($_SESSION['SEARCH_RESULT']['movie-blog.org'], searchMovieBlog($url));
		$ar_result['count'] += count($_SESSION['SEARCH_LINKS']['movie-blog.org']);
	}
	if (!empty($_SESSION['SEARCH_LINKS']['serienjunkies.org'])) {
		$url = array_shift($_SESSION['SEARCH_LINKS']['serienjunkies.org']);
		$_SESSION['SEARCH_RESULT']['serienjunkies.org'] = array_merge($_SESSION['SEARCH_RESULT']['serienjunkies.org'], searchSerienJunkies($url));
		$ar_result['count'] += count($_SESSION['SEARCH_LINKS']['serienjunkies.org']);
	}
	if (!empty($_SESSION['SEARCH_LINKS']['drei.to'])) {
		$url = array_shift($_SESSION['SEARCH_LINKS']['drei.to']);
		$_SESSION['SEARCH_RESULT']['drei.to'] = array_merge($_SESSION['SEARCH_RESULT']['drei.to'], searchDrei($url));
		$ar_result['count'] += count($_SESSION['SEARCH_LINKS']['drei.to']);
	}
	$ar_result['count_max'] = ($ar_result['count_max'] >= $ar_result['count'] ? $ar_result['count_max'] : $ar_result['count']);
	$ar_result['percent'] = round(($ar_result['count_max'] - $ar_result['count']) / $ar_result['count_max'] * 100);
	$ar_result['done'] = ($ar_result['count'] == 0);
	if ($ar_result['done']) {
		if (is_array($_SESSION['SEARCH_RESULT']['movie-blog.org'])) {
			$downloads_movblog = implode(",", array_values($_SESSION['SEARCH_RESULT']['movie-blog.org']));
			$query = "INSERT INTO `search` (`TEXT`, `SOURCE`, `STAMP`, `RESULTS`) VALUES ".
				"('".mysql_escape_string($_SESSION["CUR_SEARCH"])."', 'movie-blog.org', NOW(), '".mysql_escape_string($downloads_movblog)."') ".
				"ON DUPLICATE KEY UPDATE `STAMP`=NOW(), `RESULTS`='".mysql_escape_string($downloads_movblog)."'";
			@mysql_query($query);
		}
		if (is_array($_SESSION['SEARCH_RESULT']['serienjunkies.org'])) {
			$downloads_serienjunkies = implode(",", array_values($_SESSION['SEARCH_RESULT']['serienjunkies.org']));
			$query = "INSERT INTO `search` (`TEXT`, `SOURCE`, `STAMP`, `RESULTS`) VALUES ".
				"('".mysql_escape_string($_SESSION["CUR_SEARCH"])."', 'serienjunkies.org', NOW(), '".mysql_escape_string($downloads_serienjunkies)."') ".
				"ON DUPLICATE KEY UPDATE `STAMP`=NOW(), `RESULTS`='".mysql_escape_string($downloads_serienjunkies)."'";
			@mysql_query($query);
		}
		if (is_array($_SESSION['SEARCH_RESULT']['drei.to'])) {
			$downloads_drei = implode(",", array_values($_SESSION['SEARCH_RESULT']['drei.to']));
			$query = "INSERT INTO `search` (`TEXT`, `SOURCE`, `STAMP`, `RESULTS`) VALUES ".
				"('".mysql_escape_string($_SESSION["CUR_SEARCH"])."', 'drei.to', NOW(), '".mysql_escape_string($downloads_drei)."') ".
				"ON DUPLICATE KEY UPDATE `STAMP`=NOW(), `RESULTS`='".mysql_escape_string($downloads_drei)."'";
			@mysql_query($query);
		}
	}
}
header('Content-type: application/json');
die(json_encode($ar_result));

?>