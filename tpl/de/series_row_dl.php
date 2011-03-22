				<tr>
					<td style="padding-left: 4px;">
					<?php
					if ($row['SOURCE'] == "movie-blog.org") echo("<img src='img/movieblog.png' title='movie-blog.org' />");
					if ($row['SOURCE'] == "serienjunkies.org") echo("<img src='img/serienjunkies.png' title='serienjunkies.org' />");
					if ($row['SOURCE'] == "drei.to") echo("<img src='img/drei.png' title='drei.to' />");
					if ($row['SOURCE'] == "gwarez.cc") echo("<img src='img/gwarez.png' title='gwarez.cc' />");
					?>
					</td>
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
							?><input class="cnlLink" type="button"  style="padding: 1px; font-size: 12px;" onclick="GetLinks('<?=implode(",",$links)?>');" value="<?=$hoster?>" /><?php
						}
					?>
					</td>
				</tr>