<?php
if (!isUser()) die(header("location: index.php?show=login"));
?>
<script type="text/javascript" src="js/search.js"></script>
<script type="text/javascript">

$(function() {
	CreateSearch("#dl_search");
});

</script>

<div class="ui-widget ui-header" style="position: relative;">
	<div style="font-size: 20px; font-weight: bold; position: absolute; left: 8px; width: 180px;">
		Download suchen:
	</div>
	<div style="position: absolute; left: 192px; right: 8px;">
		<input onkeyup="CheckSearch(event);" style="width: 100%;" id="dl_search" placeholder="Bitte Suchbegriff eingeben ..." value="<?=$_SESSION["CUR_SEARCH"]?>" />
	</div>
</div>
<div id="dl_search_result" style="margin-top: 32px;"><?php
	include('search_ajax.php');
?></div>