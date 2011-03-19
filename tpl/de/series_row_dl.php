				<tr>
					<td style="font-size: 14px;">
						<?php
						if (isMod()) {
							?>
							<a onclick="UpdateManualSeries(<?=$row['ID_DOWNLOAD']?>, <?=$_REQUEST['id']?>);" style="float: left; cursor: pointer; padding: 2px;" title="Download neu einlesen">
								<span class="ui-icon ui-icon-refresh"></span>
							</a>
							<?php
						}
						?>
						<a href="index.php?show=download&id=<?=$row['ID_DOWNLOAD']?>" title="<?=utf8_encode(htmlspecialchars($row['TITLE']))?>">
							<?=utf8_encode(htmlspecialchars( substr($row['TITLE'], 0, 96) )).(strlen($row['TITLE']) > 96 ? " [...]" : "")?>
						</a>
					</th>
					<td>
					<?php
						foreach ($row['LINKS'] as $hoster => $links) {
							?><input type="button" style="width: 100px; font-size: 12px;" onclick="GetLinks('<?=implode(",",$links)?>');" value="<?=$hoster?>" /><?php
						}
					?>
					</td>
				</tr>