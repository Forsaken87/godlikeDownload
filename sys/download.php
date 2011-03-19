<?php

/*
 * MOVIE-BLOG.ORG
 */

function readMovieBlog($ar_download, $resolver) {
	$dom_result = new DOMDocument();
	if (@$dom_result->loadHTMLFile($ar_download['URL'])) {
		$ar_links = array();
		$contentDiv = $dom_result->getElementById("content");
		if ($contentDiv != null) {
			// Post found, parse content
			$curPost = new DOMDocument();
			$curPost->appendChild( $curPost->importNode($contentDiv, true) );
			$ar_download = readMovieBlogPost($ar_download, $curPost);
			if ($ar_download !== false) {
				require_once 'sys/database.php';
				foreach ($ar_download['DOWNLOAD'] as $downloadIndex => $downloadRow) {
					$ar_download['DOWNLOAD'][$downloadIndex]['IS_CONTAINER'] = ($resolver->MatchLink($downloadRow['URL']) ? 1 : 0);
				}
				$id_download = updateDownload($ar_download);
				$ar_downloads[] = $id_download;
			}
		}
		return $ar_download;
	} else {
		return array();
	}
}

function readMovieBlogPost($ar_post, $curPost) {
	$ar_post["DOWNLOAD"] = array();
	$ar_post["CATEGORYS"] = array("Videos");
	$infoDiv = $curPost->getElementById("info");
	if ($infoDiv != null) {
		$infoText = $infoDiv->textContent;
		if (preg_match('/Datum\: ([A-ZÄÖÜa-zäöü]+)\, ([0-9]+)\. ([A-Za-zäöü]+) ([0-9]+) ([0-9]+\:[0-9]+)/', $infoText, $ar_matches)) {
			// Date found
			$ar_month = array(
				"Januar"=>1, "Februar"=>2, "März"=>3, "April"=>4, "Mai"=>5, "Juni"=>6,
				"Juli"=>7, "August"=>8, "September"=>9, "Oktober"=>10, "November"=>11, "Dezember"=>12
			);
			$date_day = $ar_matches[2];
			$date_month = $ar_month[ $ar_matches[3] ];
			$date_year = $ar_matches[4];
			if (!empty($date_day) && !empty($date_month) && !empty($date_year)) {
				// Date okay
				$ar_post["STAMP_FOUND"] = sprintf("%04d-%02d-%02d", $date_year, $date_month, $date_day);
			}
		}
	}
	$list_div = $curPost->getElementsByTagName("div");
	for ($i_div = 0; $i_div < $list_div->length; $i_div++) {
		$cur_div = $list_div->item($i_div);
		// Get description
		$cur_class = $cur_div->attributes->getNamedItem("class");
		if (($cur_class != null) && ($cur_class->nodeValue == "entry")) {
			$curDesc = new DOMDocument();
			$curDesc->appendChild( $curDesc->importNode($cur_div, true) );
			$ar_post["DESC"] = utf8_decode($curDesc->saveHTML());
			// Get Download links
			$list_link = $cur_div->getElementsByTagName("a");
			for ($i_link = 0; $i_link < $list_link->length; $i_link++) {
				$cur_link = $list_link->item($i_link);
				// Link url
				$cur_link_url = $cur_link->attributes->getNamedItem("href");
				if ($cur_link_url != null) {
					if (!empty($cur_link->textContent)) {
						if ((substr($cur_link_url->nodeValue, 0, 4) == "http") && !preg_match('/^http\:\/\/www\.movie-blog\.org\//i', $cur_link_url->nodeValue) &&
							(strtolower(trim($cur_link_url->nodeValue)) != "imdb") && (strtolower(trim($cur_link_url->nodeValue)) != "nfo") &&
							(strtolower(trim($cur_link_url->nodeValue)) != "anisearch") && (strtolower(trim($cur_link_url->nodeValue)) != "ofdb") &&
							(strtolower(trim($cur_link_url->nodeValue)) != "screen") && (strtolower(trim($cur_link_url->nodeValue)) != "screens")) {
							// Only external links
							$ar_link = array(
								"URL"	=> utf8_decode($cur_link_url->nodeValue),
								"TITLE"	=> utf8_decode(trim($cur_link->textContent))
							);
							$ar_post["DOWNLOAD"][] = $ar_link;
							if (strpos(strtolower($ar_link["TITLE"]), "rapidshare") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Rapidshare.com");
							if (strpos(strtolower($ar_link["TITLE"]), "netload") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Netload.in");
							if (strpos(strtolower($ar_link["TITLE"]), "uploaded") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Uploaded.to");
							if (strpos(strtolower($ar_link["TITLE"]), "ul.to") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Uploaded.to");
							if (strpos(strtolower($ar_link["TITLE"]), "megaupload") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Megaupload.com");
							if (strpos(strtolower($ar_link["TITLE"]), "depositfiles") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Depositfiles.com");
							if (strpos(strtolower($ar_link["TITLE"]), "filesonic") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Filesonic.com");
							if (strpos(strtolower($ar_link["TITLE"]), "hotfile") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Hotfile.com");
							if (strpos(strtolower($ar_link["TITLE"]), "share-online") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Share-online.biz");
							if (strpos(strtolower($ar_link["TITLE"]), "x7.to") !== false) $ar_post["CATEGORYS"][] = utf8_decode("x7.to");
						}
					}
				}
			}
		}
	}
	$list_link = $curPost->getElementsByTagName("a");
	for ($i_link = 0; $i_link < $list_link->length; $i_link++) {
		$cur_link = $list_link->item($i_link);
		$cur_rel = $cur_link->attributes->getNamedItem("rel");
		if (($cur_rel != null) && ($cur_rel->nodeValue == "bookmark")) {
			// Entry title
			$cur_link_url = $cur_link->attributes->getNamedItem("href");
			if ($cur_link_url != null) {
				$ar_post["TITLE"] = utf8_decode(str_replace(".", " ", $cur_link->textContent));
			}
		}
		if (($cur_rel != null) && ($cur_rel->nodeValue == "category tag")) {
			// Entry category
			$ar_post["CATEGORYS"][] = utf8_decode($cur_link->textContent);
		}
	}
	return $ar_post;
}

/*
 * SERIENJUNKIES.ORG
 */

function readSerienJunkies($ar_download, $resolver) {
	$dom_result = new DOMDocument();
	$source = file_get_contents($ar_download['URL']);
	if (@$dom_result->loadHTML($source)) {
		require_once 'sys/database.php';
		$ar_links = array();
		$ar_download_base = $ar_download;
		$ar_download_base["CATEGORYS"] = array("Videos", "Serien");
		$list_div = $dom_result->getElementsByTagName("div");
		for ($i_div = 0; $i_div < $list_div->length; $i_div++) {
			$cur_div = $list_div->item($i_div);
			$cur_class = $cur_div->attributes->getNamedItem("class");
			if ($cur_class != null) {
				if ($cur_class->nodeValue == "post") {
					$curPost = new DOMDocument();
					$curPost->appendChild( $curPost->importNode($cur_div, true) );
					$list_link = $curPost->getElementsByTagName("a");
					for ($i_link = 0; $i_link < $list_link->length; $i_link++) {
						$cur_link = $list_link->item($i_link);
						$cur_rel = $cur_link->attributes->getNamedItem("rel");
						$cur_href = $cur_link->attributes->getNamedItem("href");
						if (($cur_href != null) && ($cur_rel != null) && ($cur_rel->nodeValue == "bookmark")) {
							$ar_download_base["URL"] = $cur_href->nodeValue;
							$ar_title = explode(" ~ ", str_replace("?", "~", utf8_decode($cur_link->textContent)));
							$ar_download_base["TITLE"] = $ar_title[0];
							$ar_download_base["CATEGORYS"] = array("Videos", "Serien");
							updateCategory($ar_download_base["TITLE"], 5);
							$ar_download_base["CATEGORYS"][] = $ar_download_base["TITLE"];
						}
					}
					$list_div2 = $curPost->getElementsByTagName("div");
					for ($i_div2 = 0; $i_div2 < $list_div2->length; $i_div2++) {
						$cur_div2 = $list_div2->item($i_div2);
						$cur_class2 = $cur_div2->attributes->getNamedItem("class");
						if ($cur_class2->nodeValue == "post-content") {
							// Post found, parse content
							$ar_download_base["DESC"] = utf8_decode($curPost->saveHTML());
							$listP = $cur_div2->childNodes;
							for ($i_p = 2; $i_p < $listP->length; $i_p++) {
								$curP = $listP->item($i_p);
								$curEp = new DOMDocument();
								$curEp->appendChild( $curEp->importNode($curP, true) );
								if (preg_match('/Download\:/i', $curP->textContent)) {
									$ar_download = readSerienJunkiesPost($ar_download_base, $curEp);
									$ar_download["URL"] .= "#".($i_p-1);
									if (($ar_download !== false) && !empty($ar_download['DOWNLOAD'])) {
										foreach ($ar_download['DOWNLOAD'] as $downloadIndex => $downloadRow) {
											$ar_download['DOWNLOAD'][$downloadIndex]['IS_CONTAINER'] = ($resolver->MatchLink($downloadRow['URL']) ? 1 : 0);
										}
										$id_download = updateDownload($ar_download);
										$ar_downloads[] = $id_download;
									}
								}
							}
						}
					}
				}
			}
		}
		return $ar_download;
	} else {
		return array();
	}
}

function readSerienJunkiesPost($ar_post, $curPost) {
	$ar_post["DOWNLOAD"] = array();
	$list_strong = $curPost->getElementsByTagName("strong");
	if ($list_strong->length > 0) {
		$ar_post["TITLE"] = $ar_post["TITLE"]." - ".utf8_decode(str_replace(".", " ", $list_strong->item(0)->textContent));
	}
	$list_link = $curPost->getElementsByTagName("a");
	for ($i_link = 0; $i_link < $list_link->length; $i_link++) {
		$cur_link = $list_link->item($i_link);
		$cur_target = $cur_link->attributes->getNamedItem("target");
		if (($cur_target != null) && ($cur_target->nodeValue == "_blank")) {
			// Entry title
			$cur_link_url = $cur_link->attributes->getNamedItem("href");
			if ($cur_link_url != null) {
				if (!empty($cur_link->textContent)) {
					if (preg_match('/^http\:\/\/download\.serienjunkies\.org\/f\-/i', $cur_link_url->nodeValue) ||
						preg_match('/^http\:\/\/serienjunkies\.org\/safe\//i', $cur_link_url->nodeValue)) {
						$ar_link = array(
							"URL"	=> utf8_decode($cur_link_url->nodeValue),
							"TITLE"	=> utf8_decode(trim(str_replace("| ", "", $cur_link->nextSibling->textContent)))
						);
						$ar_post["DOWNLOAD"][] = $ar_link;
						if (strpos(strtolower($ar_link["TITLE"]), "rapidshare") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Rapidshare.com");
						if (strpos(strtolower($ar_link["TITLE"]), "netload") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Netload.in");
						if (strpos(strtolower($ar_link["TITLE"]), "ul.to") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Uploaded.to");
						if (strpos(strtolower($ar_link["TITLE"]), "uploaded") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Uploaded.to");
						if (strpos(strtolower($ar_link["TITLE"]), "megaupload") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Megaupload.com");
						if (strpos(strtolower($ar_link["TITLE"]), "depositfiles") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Depositfiles.com");
						if (strpos(strtolower($ar_link["TITLE"]), "filesonic") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Filesonic.com");
						if (strpos(strtolower($ar_link["TITLE"]), "hotfile") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Hotfile.com");
						if (strpos(strtolower($ar_link["TITLE"]), "share-online") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Share-online.biz");
						if (strpos(strtolower($ar_link["TITLE"]), "x7.to") !== false) $ar_post["CATEGORYS"][] = utf8_decode("x7.to");
					}
				}
			}
		}
	}
	return $ar_post;
}


/*
 * DREI.TO
 */

function readDrei($ar_download, $resolver) {
	$dom_result = new DOMDocument();
	if (@$dom_result->loadHTMLFile($ar_download['URL'])) {
		$ar_links = array();
		$content_td = $dom_result->getElementById("content");
		if ($content_td != null) {
			// Post found, parse content
			$curPost = new DOMDocument();
			$curPost->appendChild( $curPost->importNode($content_td, true) );
			$ar_download = readDreiPost($ar_download, $curPost);
			$ar_download["DOWNLOAD"][] = array(
				"URL"	=> utf8_decode($ar_download['URL']),
				"TITLE"	=> "Drei.to"
			);
			if ($ar_download !== false) {
				require_once 'sys/database.php';
				foreach ($ar_download['DOWNLOAD'] as $downloadIndex => $downloadRow) {
					$ar_download['DOWNLOAD'][$downloadIndex]['IS_CONTAINER'] = ($resolver->MatchLink($downloadRow['URL']) ? 1 : 0);
				}
				$id_download = updateDownload($ar_download);
				$ar_downloads[] = $id_download;
			}
			return $ar_download;
		}
	}
	return array();
}

function readDreiPost($ar_post, $curPost) {
	$ar_post["DESC"] = "";
	$ar_post["DOWNLOAD"] = array();
	$list_div = $curPost->getElementsByTagName("div");
	for ($i_div = 0; $i_div < $list_div->length; $i_div++) {
		$cur_div = $list_div->item($i_div);
		$cur_class = $cur_div->attributes->getNamedItem("class");
		if (($cur_class != null) && ($cur_class->nodeValue == "content") && (empty($ar_post["DESC"]))) {
			$curDesc = new DOMDocument();
			$curDesc->appendChild( $curDesc->importNode($cur_div, true) );
			$ar_post["DESC"] = utf8_decode($curDesc->saveHTML());
			$infoText = $curDesc->textContent;
			if (preg_match('/Hochgeladen am ([0-9]+)\.([0-9]+)\.([0-9]+)/', $infoText, $ar_matches)) {
				// Date found
				$date_day = $ar_matches[1];
				$date_month = $ar_matches[2];
				$date_year = $ar_matches[3];
				if (!empty($date_day) && !empty($date_month) && !empty($date_year)) {
					// Date okay
					$ar_post["STAMP_FOUND"] = sprintf("%04d-%02d-%02d", $date_year, $date_month, $date_day);
				}
			}
			if (strpos(strtolower($infoText), "rapidshare") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Rapidshare.com");
			if (strpos(strtolower($infoText), "netload") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Netload.in");
			if (strpos(strtolower($infoText), "uploaded") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Uploaded.to");
			if (strpos(strtolower($infoText), "ul.to") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Uploaded.to");
			if (strpos(strtolower($infoText), "megaupload") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Megaupload.com");
			if (strpos(strtolower($infoText), "depositfiles") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Depositfiles.com");
			if (strpos(strtolower($infoText), "filesonic") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Filesonic.com");
			if (strpos(strtolower($infoText), "hotfile") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Hotfile.com");
			if (strpos(strtolower($infoText), "share-online") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Share-online.biz");
			if (strpos(strtolower($infoText), "x7.to") !== false) $ar_post["CATEGORYS"][] = utf8_decode("x7.to");
		}
	}
	return $ar_post;
}

/*
 * GWAREZ.CC
 */

function readGWarez($ar_download, $resolver) {
	$curl = curl_init($ar_download['URL']);
	if ($curl !== false) {
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$result_source = curl_exec($curl);
		$dom_result = new DOMDocument();
		if (($result_source !== false) && @$dom_result->loadHTML($result_source)) {
			$content_td = $dom_result->getElementById("content");
			if ($content_td != null) {
				// Post found, parse content
				$curPost = new DOMDocument();
				$curPost->appendChild( $curPost->importNode($content_td, true) );
				$ar_download = readGWarezPost($ar_download, $curPost, $curl);
				if ($ar_download !== false) {
					require_once 'sys/database.php';
					foreach ($ar_download['DOWNLOAD'] as $downloadIndex => $downloadRow) {
						$ar_download['DOWNLOAD'][$downloadIndex]['IS_CONTAINER'] = ($resolver->MatchLink($downloadRow['URL']) ? 1 : 0);
					}
					$id_download = updateDownload($ar_download);
					$ar_downloads[] = $id_download;
				}
				return $ar_download;
			}
		}
	}
	return array();
}

function readGWarezPost($ar_post, $curPost, $curl) {
	$ar_post["DESC"] = $curPost->saveHtml();
	$ar_post["CATEGORYS"][] = "Spiele";
	$ar_post["DOWNLOAD"] = array();
	$list_link = $curPost->getElementsByTagName("a");
	for ($i_link = 0; $i_link < $list_link->length; $i_link++) {
		$cur_link = $list_link->item($i_link);
		$cur_link_url = $cur_link->attributes->getNamedItem("href");
		if (($cur_link_url != null) && (preg_match('/^mirror\/[0-9]+\//', $cur_link_url->nodeValue))) {
			$url = "http://gwarez.cc/".$cur_link_url->nodeValue;
			if (curl_setopt($curl, CURLOPT_URL, $url) && curl_exec($curl)) {
				// Forwarding
				$url = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
				$ar_link = array(
					"URL"	=> utf8_decode($url),
					"TITLE"	=> utf8_decode(trim($cur_link->textContent))
				);
				$ar_post["DOWNLOAD"][] = $ar_link;
				if (strpos(strtolower($ar_link["TITLE"]), "rapidshare") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Rapidshare.com");
				if (strpos(strtolower($ar_link["TITLE"]), "netload") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Netload.in");
				if (strpos(strtolower($ar_link["TITLE"]), "uploaded") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Uploaded.to");
				if (strpos(strtolower($ar_link["TITLE"]), "ul.to") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Uploaded.to");
				if (strpos(strtolower($ar_link["TITLE"]), "megaupload") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Megaupload.com");
				if (strpos(strtolower($ar_link["TITLE"]), "depositfiles") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Depositfiles.com");
				if (strpos(strtolower($ar_link["TITLE"]), "filesonic") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Filesonic.com");
				if (strpos(strtolower($ar_link["TITLE"]), "hotfile") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Hotfile.com");
				if (strpos(strtolower($ar_link["TITLE"]), "share-online") !== false) $ar_post["CATEGORYS"][] = utf8_decode("Share-online.biz");
				if (strpos(strtolower($ar_link["TITLE"]), "x7.to") !== false) $ar_post["CATEGORYS"][] = utf8_decode("x7.to");
			}
		}
	}
	return $ar_post;
}

?>