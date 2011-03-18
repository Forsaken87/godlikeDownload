<?php
/**
 * Used variables:
 * $limit_page, $limit_pages
 */
if ($limit_pages > 0) {
$limit_pagelist_first = ($limit_page > 5 ? $limit_page - 5 : 1);
$limit_pagelist_last = (($limit_pages - $limit_page) > 5 ? $limit_page + 5 : $limit_pages);
?>
<div class='ui-widget ui-widget-header ui-corner-all'>
	<a onclick='SendSearch(1)' class="pager_button">Erste</a>
<?php
if ($limit_pagelist_first > 1) {
	echo("<span>&nbsp;...&nbsp;</span>");
}
for ($cur_page = $limit_pagelist_first; $cur_page <= $limit_pagelist_last; $cur_page++) {
	$classes = "pager_button";
	if ($cur_page == $limit_page) $classes .= " pager_button_active";
	echo("	<a onclick='SendSearch(".$cur_page.")' class='".$classes."'>".$cur_page."</a>\n");
}
if ($limit_pages > $limit_pagelist_last) {
	echo("<span>&nbsp;...&nbsp;</span>");
}
?>
	<a onclick='SendSearch(<?=$limit_pages?>)' class='pager_button'>Letzte</a>
</div>
<script type='text/javascript'>
	$('.pager_button').button();
	$('.pager_button_active').button("option", "disabled", true);
</script>
<?php
}
?>