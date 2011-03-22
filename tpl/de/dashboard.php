<?php
if (!isUser()) die(header("location: index.php?show=login"));
?>
<script type="text/javascript">

$(function() {
	// Request new downloads
	$("#button_clear_new").button();
	$("#downloads_update").button();
	$("#downloads_update > span").css("padding", "2px");
	UpdateDownloads();
});

function ClearNewDownloads(id_newest) {
	// Calculate number of rows fitting on screen
	var row_count = Math.floor(($("#downloads_new").height() - 24) / 24);
	// Calculate an secure number of characters available for the title
	var title_len = Math.floor(($("#downloads_new").width() - 320) / 9);
	$("#downloads_new").html("");
	var url = "index.php?run=ajax_downloads_new&ajax=1&rows="+row_count+"&length="+title_len+"&do=clearNew&newest="+id_newest;
	$.get(url, function(result) {
		$("#downloads_new").html(result);
	});
}

function UpdateDownloads() {
	// Calculate number of rows fitting on screen
	var row_count = Math.floor(($("#downloads_new").height() - 24) / 24);
	// Calculate an secure number of characters available for the title
	var title_len = Math.floor(($("#downloads_new").width() - 320) / 9);
	$("#downloads_new").html("Wird geladen...");
	var url = "index.php?run=ajax_downloads_new&ajax=1&rows="+row_count+"&length="+title_len;
	$.get(url, function(result) {
		$("#downloads_new").html(result);
	});
}

</script>
<style type="text/css">

.button_clear_new { font-size: 12px !important; }

</style>
<div style="position: absolute; left: 8px; top: 8px; bottom: 8px; right: 8px;">
	<div class="ui-widget ui-widget-content" style="position: absolute; left: 4px; top: 4px; bottom: 4px; right: 50%;">
		<div class="ui-widget-header" style="font-weight: bold; font-size: 20px;">
			Neue Downloads
			<a id="downloads_update" style="float: right; padding: 2px;" title="Neu laden" onclick="UpdateDownloads();">
				<span class="ui-icon ui-icon-arrowrefresh-1-e"></span>
			</a>
		</div>
		<div id="downloads_new" style="position: absolute; left: 4px; top: 32px; bottom: 4px; right: 4px; overflow: auto;">
		</div>
	</div>
</div>