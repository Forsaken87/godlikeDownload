		<tr class="row<?=$row["EVEN"]?>">
			<td style="padding-left: 4px;">
			<?php
			if ($row['SOURCE'] == "movie-blog.org") echo("<img src='img/movieblog.png' title='movie-blog.org' />");
			if ($row['SOURCE'] == "serienjunkies.org") echo("<img src='img/serienjunkies.png' title='serienjunkies.org' />");
			if ($row['SOURCE'] == "drei.to") echo("<img src='img/drei.png' title='drei.to' />");
			if ($row['SOURCE'] == "gwarez.cc") echo("<img src='img/gwarez.png' title='gwarez.cc' />");
			?>
			</td>
			<td>
				<a href="index.php?show=download&id=<?=$row['ID_DOWNLOAD']?>" title="<?=utf8_encode(htmlspecialchars($row['TITLE']))?>">
					<?=utf8_encode(htmlspecialchars( substr($row['TITLE'], 0, $row["TITLE_MAX"]) )).(strlen($row['TITLE']) > $row["TITLE_MAX"] ? " [...]" : "")?>
				</a>
			</td>
			<td style="font-size: 12px;">
			<?php
				$query = "SELECT c.* FROM `category` c, `download_cat` d \n".
					"WHERE c.ID_CATEGORY=d.FK_CATEGORY AND \n".
						"c.FK_CATEGORY_GROUP=1 AND d.FK_DOWNLOAD=".$row["ID_DOWNLOAD"]." \n".
					"ORDER BY c.NAME ASC";
				if ($result_cat = @mysql_query($query)) {
					while($cat = @mysql_fetch_assoc($result_cat)) {
						?>
						<a class="ui-state-highlight" style="padding: 1px 2px; margin-right: 1px;" href="index.php?categorys&cat=<?=$cat['ID_CATEGORY']?>"><?=utf8_encode(htmlspecialchars($cat['NAME']))?></a>
						<?php
					}
				}
			?>
			</td>
			<td nowrap="nowrap"><?=date('d.m.Y', strtotime($row['STAMP_FOUND']))?></td>
		</tr>