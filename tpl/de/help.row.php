<div class="ui-widget ui-widget-content" style="width: 532px; padding: 4px; margin: 4px; float: left; height: 240px;">
	<div class="ui-widget-header" style="margin: -4px -4px 4px -4px;"><?=utf8_encode($row['IDENT'])?></div>
	<div style="font-family: monospace;">
		<?=utf8_encode(get_html($row["MESSAGE"]))?>
	</div>
</div>