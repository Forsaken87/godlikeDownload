				<tr class="row<?=$row["EVEN"]?>">
					<td><?=$row["NAME"]?></td>
					<td><?=$row["REGEXP"]?></td>
					<td>
						<a onclick="ProcessRegexpNow(<?=$row["ID_CATEGORY_REGEXP"]?>);" style="cursor: pointer;" title="Jetzt anwenden!">
							<span class="ui-icon ui-icon-clock"></span>
						</a>
					</td>
				</tr>