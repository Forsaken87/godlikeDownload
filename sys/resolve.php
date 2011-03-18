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
	 * Tries to parse the given link with any filter available
	 * @param string $url		The url to be parsed
	 * @return array|string		The resolved url(s) or a html captcha form
	 */
	public function ParseLink($url) {
		foreach ($this->arFilter as $filter) {
			if ($filter->MatchLink($url)) {
				return $filter->ParseLink($url);
			}
		}
		return array($url);
	}
}

?>