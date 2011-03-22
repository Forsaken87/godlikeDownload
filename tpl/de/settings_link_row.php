				<tr class="row<?=$row["EVEN"]?>">
					<td><?=$row["NAME"]?></td>
					<td><?=$row["NAME_TO"]?></td>
					<td>
						<a onclick="ProcessCatLinkNow(<?=$row["FK_CATEGORY"]?>);" style="cursor: pointer;" title="Jetzt anwenden!">
							<span class="ui-icon ui-icon-clock"></span>
						</a>
					</td>
				</tr>