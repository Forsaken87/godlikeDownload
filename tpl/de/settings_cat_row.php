<div style="width: 180px; float: left;">
	<input type="checkbox" class="category_check" onchange="ChangeCategory(this);" id="category_<?=$row['ID_CATEGORY']?>" value="<?=$row['ID_CATEGORY']?>" <?=(!$row['SELECTED'] ? '' : ' checked="checked"')?> />
	<label style="width: 90%; font-size: 12px;" for="category_<?=$row['ID_CATEGORY']?>">
		<?=utf8_encode(htmlspecialchars($row['NAME']))?>
	</label>
</div>