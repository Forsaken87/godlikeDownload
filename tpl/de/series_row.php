		<a class="seriesEntry" style="cursor: pointer;" onclick="ShowSeries(<?=$row["ID_CATEGORY"]?>);">
			<div class="ui-state-default">
				<span><?=utf8_encode(htmlspecialchars($row["NAME"]))?></span>
			</div>
		</a>