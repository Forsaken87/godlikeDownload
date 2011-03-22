<?php

/**
 *
 * Used to create interfaces used when filtering url's for forwards/containers.
 * Assign the name of your filter class name to the variable $filterClass in your php file.
 * Filters are automatically loaded from the "filter" in the root.
 * @author Forsaken
 *
 */
interface LinkFilter {
	/**
	 *
	 * Checks if this filter is capable of parsing this url
	 * @param string 	$url	The url to be checked
	 * @return boolean			True if this filter "knows" how to resolve the url
	 */
	public function MatchLink($url);
	/**
	 *
	 * Parses the forward/container to get the direct links
	 * @param string	$url	The url containing a forward/container
	 * @return array|string		Either an array of direct links or a html captcha form
	 */
	public function ParseLink($url);
	/**
	 *
	 * Submits the given captcha solution and parses the result
	 * @param int		$urlReferer
	 * @param string	$url
	 * @param string	$captchaIdent
	 * @param string	$captchaResult
	 */
	public function SubmitCaptcha($urlReferer, $url, $captchaIdent, $captchaText);
}

/**
 *
 * Resolves container and forward services
 * @author Forsaken
 *
 */
class LinkResolver {
	private $arFilter;

	/**
	 *
	 * Constructor
	 */
	function LinkResolver() {
		$this->arFilter = array();
		$filterDir = dir("filter");
		while (false !== ($entry = $filterDir->read())) {
			if (preg_match('/\.php$/i', $entry)) {
				$filterClass = "";
				include "filter/".$entry;
				if (!empty($filterClass)) {
					$this->arFilter[] = new $filterClass();
				}
			}
		}
		$filterDir->close();
	}

	/**
	 *
	 * Checks if any filter is capable of parsing this url
	 * @param string 	$url	The url to be checked
	 * @return boolean			True if this filter "knows" how to resolve the url
	 */
	public function MatchLink($url) {
		foreach ($this->arFilter as $filter) {
			if ($filter->MatchLink($url))
				return true;
		}
		return false;
	}

	/**
	 *
	 * Checks if any filter is capable of parsing an url within the array
	 * @param array 	$url	The url to be checked
	 * @return boolean			True if this filter "knows" how to resolve the url
	 */
	public function MatchLinks($urls) {
		foreach ($urls as $url) {
			if ($this->MatchLink($url))
				return true;
		}
		return false;
	}

	/**
	 *
	 * Tries to parse the given link with any filter available
	 * @param string $url		The url to be parsed
	 * @return array|string		The resolved url(s) or a html captcha form
	 */
	public function ParseLink($url) {
		$arLinks = array($url);
		while ($this->MatchLinks($arLinks)) {
			$arLinksNew = array();
			foreach ($arLinks as $index => $urlCur) {
				$matched = false;
				foreach ($this->arFilter as $filter) {
					if ($filter->MatchLink($urlCur)) {
						$matched = true;
						$arLinksResolved = $filter->ParseLink($urlCur);
						if (is_array($arLinksResolved)) {
							// Add resolved one's
							foreach ($arLinksResolved as $url) {
								$urlTrim = trim($url);
								if (!empty($urlTrim)) $arLinksNew[] = $urlTrim;
							}
						} else {
							// Captcha found
							return $arLinksResolved;
						}
						break;
					}
				}
				// Keep old url
				if (!$matched) $arLinksNew[] = $urlCur;
			}
			$arLinks = $arLinksNew;
		}

		return $arLinks;
	}

	/**
	 *
	 * Submits the given captcha solution and parses the result
	 * @param int		$urlReferer
	 * @param string	$url
	 * @param string	$captchaIdent
	 * @param string	$captchaResult
	 */
	public function SubmitCaptcha($urlReferer, $url, $captchaIdent, $captchaText) {
		foreach ($this->arFilter as $filter) {
			if ($filter->MatchLink($urlReferer)) {
				$arLinksResolved = $filter->SubmitCaptcha($urlReferer, $url, $captchaIdent, $captchaText);
				if (is_array($arLinksResolved)) {
					// Add resolved urls (if available)
					$arLinksNew = array();
					foreach ($arLinksResolved as $url) {
						$urlTrim = trim($url);
						if (!empty($urlTrim)) $arLinksNew[] = $urlTrim;
					}
					return $arLinksNew;
				}
			}
		}
		return array($urlReferer);
	}
}

?>