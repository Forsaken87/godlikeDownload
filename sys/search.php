<?php

/*
 * MOVIE-BLOG.ORG --------------------------------------------------------------------------------------
 */

function searchMovieBlogLinks($searchText, $maxPages = 15) {
	$ar_links = array();
	$url = "http://www.movie-blog.org/index.php?s=".urlencode($searchText);
	$ar_links[] = $url;
	$dom_result = new DOMDocument();
	if (@$dom_result->loadHTMLFile($url)) {
		$ar_downloads = array();
		$ar_downloads_pages = array();
		$list_div = $dom_result->getElementsByTagName("div");
		for ($i_div = 0; $i_div < $list_div->length; $i_div++) {
			$cur_div = $list_div->item($i_div);
			$cur_class = $cur_div->attributes->getNamedItem("class");
			if ($cur_class != null) {
				if ($cur_class->nodeValue == "wp-pagenavi") {
					// Pager found, parse content
					$curPager = new DOMDocument();
					$curPager->appendChild( $curPager->importNode($cur_div, true) );
					$list_span = $curPager->getElementsByTagName("span");
					$cur_span = $list_span->item(0);
					if (preg_match('/Seite ([0-9]+) von ([0-9]+)/i', $cur_span->textContent, $ar_matches)) {
						$num_pages = (int)$ar_matches[2];
						$num_pages = ($num_pages > $maxPages ? $maxPages : $num_pages);
						for ($pageIndex = 2; $pageIndex <= $num_pages; $pageIndex++) {
							$ar_links[] = "http://www.movie-blog.org/page/".$pageIndex."/?s=".urlencode($searchText);
						}
					}
				}
			}
		}
	}
	return $ar_links;
}

function searchMovieBlog($url) {
	$dom_result = new DOMDocument();
	if (@$dom_result->loadHTMLFile($url)) {
		$ar_downloads = array();
		$list_div = $dom_result->getElementsByTagName("div");
		for ($i_div = 0; $i_div < $list_div->length; $i_div++) {
			$cur_div = $list_div->item($i_div);
			$cur_class = $cur_div->attributes->getNamedItem("class");
			if ($cur_class != null) {
				if ($cur_class->nodeValue == "post") {
					// Post found, parse content
					$curPost = new DOMDocument();
					$curPost->appendChild( $curPost->importNode($cur_div, true) );
					$ar_post = searchMovieBlogPost($curPost);
					if ($ar_post !== false) {
						require_once 'sys/database.php';
						$id_download = updateDownload($ar_post);
						if ($id_download !== false) {
							$ar_downloads[] = $id_download;
						}
					}
				}
			}
		}
		return $ar_downloads;
	} else {
		return array();
	}
}

function searchMovieBlogPost($curPost) {
	$ar_post = array(
		"URL"		=> "",
		"TITLE"		=> "",
		"SOURCE"	=> "movie-blog.org",
		"DESC"		=> "",
		"TIME"		=> "",
		"CATEGORYS"	=> array("Videos"),
		"COMMENTS"	=> array(),
		"DOWNLOAD"	=> array()
	);
	$list_div = $curPost->getElementsByTagName("div");
	for ($i_div = 0; $i_div < $list_div->length; $i_div++) {
		$cur_div = $list_div->item($i_div);
		$cur_class = $cur_div->attributes->getNamedItem("class");
		if (($cur_class != null) && ($cur_class->nodeValue == "entry_x")) {
			$ar_post["DESC"] = utf8_decode($cur_div->textContent);
		}
	}
	$list_link = $curPost->getElementsByTagName("a");
	for ($i_link = 0; $i_link < $list_link->length; $i_link++) {
		$cur_link = $list_link->item($i_link);
		$cur_rel = $cur_link->attributes->getNamedItem("rel");
		if (($cur_rel != null) && ($cur_rel->nodeValue == "bookmark")) {
			// Entry title
			$cur_link_url = $cur_link->attributes->getNamedItem("href");
			if ($cur_link_url == null) {
				// ERROR! Link not found!
				return false;
			}
			$ar_post["URL"] = utf8_decode($cur_link_url->nodeValue);
			$ar_post["TITLE"] = utf8_decode(str_replace(".", " ", $cur_link->textContent));
		}
		if (($cur_rel != null) && ($cur_rel->nodeValue == "category tag")) {
			// Entry category
			$ar_post["CATEGORYS"][] = utf8_decode($cur_link->textContent);
		}
	}
	if (!empty($ar_post["URL"]) && !empty($ar_post["TITLE"])) {
		return $ar_post;
	}
	return false;
}

/*
 * SERIENJUNKIES.ORG --------------------------------------------------------------------------------------
 */

function searchSerienJunkies($url) {
	$dom_result = new DOMDocument();
	if (@$dom_result->loadHTMLFile($url)) {
		$ar_downloads = array();
		$list_div = $dom_result->getElementsByTagName("div");
		for ($i_div = 0; $i_div < $list_div->length; $i_div++) {
			$cur_div = $list_div->item($i_div);
			$cur_class = $cur_div->attributes->getNamedItem("class");
			if ($cur_class != null) {
				if ($cur_class->nodeValue == "post") {
					// Post found, parse content
					$curPost = new DOMDocument();
					$curPost->appendChild( $curPost->importNode($cur_div, true) );
					$ar_post = searchSerienJunkiesPost($curPost);
					if ($ar_post !== false) {
						require_once 'sys/database.php';
						$id_download = updateDownload($ar_post);
						$ar_downloads[] = $id_download;
					}
				}
			}
		}
		return $ar_downloads;
	} else {
		return array();
	}
}

function searchSerienJunkiesPost($curPost) {
	$ar_post = array(
		"URL"		=> "",
		"TITLE"		=> "",
		"SOURCE"	=> "serienjunkies.org",
		"DESC"		=> "",
		"TIME"		=> "",
		"CATEGORYS"	=> array("Video", "Serie"),
		"COMMENTS"	=> array(),
		"DOWNLOAD"	=> array()
	);
	$list_div = $curPost->getElementsByTagName("div");
	for ($i_div = 0; $i_div < $list_div->length; $i_div++) {
		$cur_div = $list_div->item($i_div);
		$cur_class = $cur_div->attributes->getNamedItem("class");
		if (($cur_class != null) && ($cur_class->nodeValue == "entry")) {
			$ar_post["DESC"] = utf8_decode($cur_div->textContent);
		}
	}
	$list_link = $curPost->getElementsByTagName("a");
	for ($i_link = 0; $i_link < $list_link->length; $i_link++) {
		$cur_link = $list_link->item($i_link);
		$cur_rel = $cur_link->attributes->getNamedItem("rel");
		if (($cur_rel != null) && ($cur_rel->nodeValue == "bookmark")) {
			// Entry title
			$cur_link_url = $cur_link->attributes->getNamedItem("href");
			if ($cur_link_url == null) {
				// ERROR! Link not found!
				return false;
			}
			$ar_post["URL"] = $cur_link_url->nodeValue;
			$ar_post["TITLE"] = utf8_decode(str_replace(".", " ", $cur_link->textContent));
		}
	}
	if (!empty($ar_post["URL"]) && !empty($ar_post["TITLE"])) {
		return $ar_post;
	}
	return false;
}


function searchSerienJunkiesStart($url) {
	$dom_result = new DOMDocument();
	if (@$dom_result->loadHTMLFile($url)) {
		$date_cur = date("Y-m-d");
		$ar_downloads = array();
		$list_fieldsets = $dom_result->getElementsByTagName("fieldset");
		$i_fieldset_count = ($list_fieldsets->length < 2 ? $list_fieldsets->length : 2);
		for ($i_fieldset = 0; $i_fieldset < $i_fieldset_count; $i_fieldset++) {
			$cur_fieldset = $list_fieldsets->item($i_fieldset);
			$curPost = new DOMDocument();
			$curPost->appendChild( $curPost->importNode($cur_fieldset, true) );
			$list_legend = $curPost->getElementsByTagName("legend");
			if ($list_legend->length > 0) {
				// Get date
				$legend = $list_legend->item(0)->textContent;
				if (preg_match('/([A-ZÄÖÜa-zäöü]+)\, ([0-9]+)\.([0-9]+)\.([0-9]+)/', $legend, $ar_matches)) {
					// Date found
					$date_day = $ar_matches[2];
					$date_month = $ar_matches[3];
					$date_year = $ar_matches[4];
					if (!empty($date_day) && !empty($date_month) && !empty($date_year)) {
						// Date okay
						$date_cur = sprintf("%04d-%02d-%02d", $date_year, $date_month, $date_day);
					}
				}
			}
			$list_link = $curPost->getElementsByTagName("a");
			for ($i_link = 0; $i_link < $list_link->length; $i_link++) {
				$cur_link = $list_link->item($i_link);
				$cur_href = $cur_link->attributes->getNamedItem("href");
				if ($cur_href != null) {
					if (preg_match('/^http\:\/\/serienjunkies.org\/serie\//i', $cur_href->nodeValue)) {
						// Post found, parse content
						$ar_post = array(
							"URL"			=> $cur_href->nodeValue."#".$i_link,	// TODO: Currently required to update the STAMP_FOUND for all new episodes of a series, do it with one parsing!
							"TITLE"			=> utf8_decode(str_replace(".", " ", $cur_link->textContent)),
							"SOURCE"		=> "serienjunkies.org",
							"STAMP_FOUND"	=> $date_cur,
							"DESC"			=> "",
							"TIME"			=> "",
							"CATEGORYS"		=> array("Videos", "Serien"),
							"COMMENTS"		=> array(),
							"DOWNLOAD"		=> array()
						);
						require_once 'sys/database.php';
						$id_download = updateDownload($ar_post);
						$ar_downloads[] = $id_download;
					}
				}
			}
		}
		return $ar_downloads;
	} else {
		return array();
	}
}

/*
 * DREI.TO  --------------------------------------------------------------------------------------
 */

function searchDreiLinks($searchText, $maxPages = 15) {
	$url = "http://drei.to/?action=search&searchtxt=".urlencode($searchText)."&uploader=";
	$ar_links[] = $url;
	$dom_result = new DOMDocument();
	if (@$dom_result->loadHTMLFile($url)) {
		$ar_downloads = array();
		$ar_downloads_pages = array();
		$list_div = $dom_result->getElementsByTagName("div");
		for ($i_div = 0; $i_div < $list_div->length; $i_div++) {
			$cur_div = $list_div->item($i_div);
			$cur_class = $cur_div->attributes->getNamedItem("class");
			if ($cur_class != null) {
				if ($cur_class->nodeValue == "listsiteelements") {
					// Pager found, parse content
					if (preg_match('/Seite ([0-9]+) von ([0-9]+)/i', $cur_div->textContent, $ar_matches)) {
						$num_pages = (int)$ar_matches[2];
						$num_pages = ($num_pages > $maxPages ? $maxPages : $num_pages);
						for ($pageIndex = 2; $pageIndex <= $num_pages; $pageIndex++) {
							$ar_links[] = "http://drei.to/?action=search&searchtxt=".urlencode($searchText)."&uploader=&site="+$pageIndex;
						}
					}
				}
			}
		}
	}
	return $ar_links;
}

function searchDrei($url) {
	$dom_result = new DOMDocument();
	if (@$dom_result->loadHTMLFile($url)) {
		$ar_downloads = array();
		$list_div = $dom_result->getElementsByTagName("td");
		for ($i_div = 0; $i_div < $list_div->length; $i_div++) {
			$cur_td = $list_div->item($i_div);
			$cur_class = $cur_td->attributes->getNamedItem("class");
			if ($cur_class != null) {
				if ($cur_class->nodeValue == "entry_list") {
					// Post found, parse content
					$curPost = new DOMDocument();
					$curPost->appendChild( $curPost->importNode($cur_td->parentNode, true) );
					$ar_post = searchDreiPost($curPost);
					if ($ar_post !== false) {
						require_once 'sys/database.php';
						$id_download = updateDownload($ar_post);
						if ($id_download !== false) {
							$ar_downloads[] = $id_download;
						}
					}
				}
			}
		}
		return $ar_downloads;
	} else {
		return array();
	}
}

function searchDreiPost($curPost) {
	$ar_post = array(
		"URL"		=> "",
		"TITLE"		=> "",
		"SOURCE"	=> "drei.to",
		"DESC"		=> "",
		"TIME"		=> "",
		"CATEGORYS"	=> array(),
		"COMMENTS"	=> array(),
		"DOWNLOAD"	=> array()
	);
	$list_div = $curPost->getElementsByTagName("div");
	for ($i_div = 0; $i_div < $list_div->length; $i_div++) {
		$cur_div = $list_div->item($i_div);
		$cur_class = $cur_div->attributes->getNamedItem("class");
		if (($cur_class != null) && ($cur_class->nodeValue == "tooltip")) {
			$curDesc = new DOMDocument();
			$curDesc->appendChild( $curDesc->importNode($cur_div, true) );
			$ar_post["DESC"] = utf8_decode($curDesc->saveHTML());
		}
	}
	$list_span = $curPost->getElementsByTagName("span");
	for ($i_span = 0; $i_span < $list_span->length; $i_span++) {
		$cur_span = $list_span->item($i_span);
		$cur_class = $cur_span->attributes->getNamedItem("class");
		if (($cur_class != null) && ($cur_class->nodeValue == "span_category")) {
			// Entry category
			$ar_post["CATEGORYS"][] = utf8_decode($cur_span->textContent);
		}
	}
	$list_link = $curPost->getElementsByTagName("a");
	for ($i_link = 0; $i_link < $list_link->length; $i_link++) {
		$cur_link = $list_link->item($i_link);
		// Entry title
		$cur_link_url = $cur_link->attributes->getNamedItem("href");
		if ($cur_link_url != null) {
			$ar_post["URL"] = utf8_decode($cur_link_url->nodeValue);
			if (strpos($ar_post["URL"], "ebooks.drei.to") !== false) $ar_post["CATEGORYS"][] = "E-Books";
			if (strpos($ar_post["URL"], "games.drei.to") !== false) $ar_post["CATEGORYS"][] = "Spiele";
			if (strpos($ar_post["URL"], "kino.drei.to") !== false) $ar_post["CATEGORYS"][] = "Kino";
			if (strpos($ar_post["URL"], "kino.drei.to") !== false) $ar_post["CATEGORYS"][] = "Filme";
			if (strpos($ar_post["URL"], "kino.drei.to") !== false) $ar_post["CATEGORYS"][] = "Videos";
			if (strpos($ar_post["URL"], "movie.drei.to") !== false) $ar_post["CATEGORYS"][] = "Filme";
			if (strpos($ar_post["URL"], "movie.drei.to") !== false) $ar_post["CATEGORYS"][] = "Videos";
			if (strpos($ar_post["URL"], "mobile.drei.to") !== false) $ar_post["CATEGORYS"][] = "Mobil";
			if (strpos($ar_post["URL"], "music.drei.to") !== false) $ar_post["CATEGORYS"][] = "Musik";
			if (strpos($ar_post["URL"], "serien.drei.to") !== false) $ar_post["CATEGORYS"][] = "Serien";
			if (strpos($ar_post["URL"], "serien.drei.to") !== false) $ar_post["CATEGORYS"][] = "Videos";
			if (strpos($ar_post["URL"], "porn.drei.to") !== false) $ar_post["CATEGORYS"][] = "XXX";
			if (strpos($ar_post["URL"], "porn.drei.to") !== false) $ar_post["CATEGORYS"][] = "Videos";
			$ar_post["TITLE"] = utf8_decode(htmlspecialchars(str_replace(".", " ", $cur_link->textContent)));
			break;
		}
	}
	if (!empty($ar_post["URL"]) && !empty($ar_post["TITLE"])) {
		return $ar_post;
	}
	return false;
}

/*
 * GWAREZ.CC  --------------------------------------------------------------------------------------
 */

function searchGWarezLinks($searchText, $maxPages = 15) {
	$url = "http://gwarez.cc/search/";
	return array($url);
}

function searchGWarez($url, $searchText) {
	if (!empty($searchText)) {
		$context = stream_context_create(array(
			'http' => array(
				'method' 		=> 'POST',
				'content' 		=> http_build_query(array('begriff' => $searchText)),
				'content-type'	=> 'application/x-www-form-urlencoded'
			),
		));
		$source = file_get_contents($url, false, $context);
	} else {
		$source = file_get_contents($url);
	}
	$dom_result = new DOMDocument();
	if (@$dom_result->loadHTML($source)) {
		$ar_downloads = array();
		$dom_list = new DOMDocument();
		$dom_list->appendChild( $dom_list->importNode($dom_result->getElementById("content"), true) );
		$list_link = $dom_list->getElementsByTagName("a");
		for ($i_link = 0; $i_link < $list_link->length; $i_link++) {
			$cur_link = $list_link->item($i_link);
			$cur_href = $cur_link->attributes->getNamedItem("href");
			if ($cur_href != null) {
				if (preg_match('/^\/[0-9]+\//i', $cur_href->nodeValue)) {
					// Post found, parse content
					$curPost = new DOMDocument();
					$curPost->appendChild( $curPost->importNode($cur_link, true) );
					$ar_post = searchGWarezPost($curPost, $cur_href->nodeValue);
					if ($ar_post !== false) {
						require_once 'sys/database.php';
						$id_download = updateDownload($ar_post);
						if ($id_download !== false) {
							$ar_downloads[] = $id_download;
						}
					}
				}
			}
		}
		return $ar_downloads;
	} else {
		return array();
	}
}

function searchGWarezPost($curPost, $curURL) {
	$ar_post = array(
		"URL"		=> utf8_decode("http://gwarez.cc".$curURL),
		"TITLE"		=> "",
		"SOURCE"	=> "gwarez.cc",
		"UPLOADER"	=> "",
		"DESC"		=> "",
		"TIME"		=> "",
		"CATEGORYS"	=> array("Spiele"),
		"COMMENTS"	=> array(),
		"DOWNLOAD"	=> array()
	);
	$list_div = $curPost->getElementsByTagName("div");
	for ($i_div = 0; $i_div < $list_div->length; $i_div++) {
		$cur_div = $list_div->item($i_div);
		$cur_id = $cur_div->attributes->getNamedItem("id");
		if ($cur_id != null) {
			if ($cur_id->nodeValue == "list-titel-big") {
				$cur_title = $cur_div->firstChild;
				$cur_title_str = trim($cur_title->textContent);
				while (($cur_title != null) && empty($cur_title_str)) {
					$cur_title = $cur_title->nextSibling;
					$cur_title_str = trim($cur_title->textContent);
				}
				if ($cur_title != null) {
					$ar_post["TITLE"] = $ar_post["DESC"] = utf8_decode(trim($cur_title->textContent));
				}
			}
			if ($cur_id->nodeValue == "list-titel") {
				if (empty($ar_post["UPLOADER"])) {
					$ar_post["UPLOADER"] = utf8_decode(trim($cur_div->firstChild->textContent));
				} else {
					$ar_post["CATEGORYS"][] = utf8_decode(trim($cur_div->firstChild->textContent));
				}
			}
		}
	}
	if (!empty($ar_post["URL"]) && !empty($ar_post["TITLE"])) {
		return $ar_post;
	}
	return false;
}

?>