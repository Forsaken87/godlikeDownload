		<a class="seriesEntry" style="cursor: pointer;" onclick="ShowSeries(<?=$row["ID_CATEGORY"]?>);">
			<div class="ui-state-default">
				<?=utf8_encode(htmlspecialchars($row["NAME"]))?>
			</div>
		</a>