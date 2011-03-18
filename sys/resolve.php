<?php

class LinkResolver {
	private $arFilter;

	function LinkResolver() {
		$this->arFilter = array();
		$filterDir = dir("filter");
		while (false !== ($entry = $filterDir->read())) {
			if (preg_match('/\.php$/i', $entry)) {
				include "filter/".$entry;
				$this->arFilter[] = new $filterClass();
			}
		}
		$filterDir->close();
	}
}

?>