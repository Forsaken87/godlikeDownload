<?php
if (isUser()) die(header("location: index.php?show=dashboard"));

$errors = array();
if (!empty($_POST)) {
	// USER
	if (empty($_POST['USER'])) {
		$errors[] = "Kein Benutzername angegeben";
	} elseif (strlen($_POST['USER']) < 4) {
		$errors[] = "Der Benutzername ist zu kurz! (Mindestens 4 Zeichen)";
	} elseif (get_query_field("SELECT ID_USER FROM `user` WHERE NAME LIKE '".mysql_escape_string($_POST['USER'])."'") > 0) {
		$errors[] = "Der Benutzername '".$_POST['USER']."' ist bereits vergeben!";
	}
	// PASS
	if (empty($_POST['PASS'])) {
		$errors[] = "Kein Passwort angegeben";
	} else {
		if (strlen($_POST['PASS']) < 6) $errors[] = "Das Passwort ist zu kurz! (Mindestens 6 Zeichen)";
		if (empty($_POST['PASS2'])) $errors[] = "Passwort-Wiederholung fehlt!";
		if ($_POST['PASS'] != $_POST['PASS2']) $errors[] = "Die angegebenen Passwörter stimmen nicht überein!";
	}
	// EMAIL
	if (empty($_POST['EMAIL'])) {
		$errors[] = "Keine E-Mail Adresse angegeben!";
	} elseif (strlen($_POST['EMAIL']) < 6) {
		$errors[] = "Dies ist keine gültige E-Mail Adresse!";
	} elseif (get_query_field("SELECT ID_USER FROM `user` WHERE EMAIL LIKE '".mysql_escape_string($_POST['EMAIL'])."'") > 0) {
		$errors[] = "Diese E-Mail Adresse ist bereits in Verwendung!";
	}
	if (empty($errors)) {
		$res = @mysql_query("INSERT INTO `user` (NAME, PASSWORD, EMAIL) VALUES ".
			"('".mysql_escape_string($_POST['USER'])."', '".mysql_escape_string(md5($_POST['PASS']))."', '".mysql_escape_string($_POST['EMAIL'])."')");
		if ($res === false) {
			$errors[] = "Datenbankfehler beim anlegen des Benutzers!";
		} else {
			die(header("location: index.php?show=login"));
		}
	}
}

?>
<script type="text/javascript">

$(function() {
	$(".jQueryButton").button();
});

</script>
<div class="ui-widget ui-widget-content" style="width: 648px; margin: auto;" align="center">
	<div class="ui-widget-header" align="center">
		<h1>Benutzer-Registrierung</h1>
	</div>
	<?php
	if (!empty($errors)) {
		echo("<div class='ui-state-error' align='left'>\n");
		foreach ($errors as $index => $error_message) {
			echo("- ".utf8_encode(htmlspecialchars($error_message))."<br />\n");
		}
		echo("</div>\n");
	}
	?>
	<form action="index.php?show=register" method="post">
		<table cellpadding="0" cellspacing="0" style="margin: 4px; width: 640px;">
			<tr>
				<th style="width: 340px;" class="ui-widget-header">
					<label for="regUser">Benutzername</label>
				</th>
				<td style="width: 300px;">
					<input id="regUser" name="USER" style="width: 300px;" placeholder="Benutzername ..." value="<?=$_REQUEST["USER"]?>" />
				</td>
			</tr>
			<tr>
				<th style="width: 340px;" class="ui-widget-header">
					<label for="regPass">Passwort</label>
				</th>
				<td style="width: 300px;">
					<input id="regPass" name="PASS" style="width: 300px;" type="password" placeholder="Passwort ..." />
				</td>
			</tr>
			<tr>
				<th style="width: 340px;" class="ui-widget-header">
					<label for="regPass2">Passwort-Wiederholung</label>
				</th>
				<td style="width: 300px;">
					<input id="regPass2" name="PASS2" style="width: 300px;" type="password" placeholder="Passwort-Wiederholung ..." />
				</td>
			</tr>
			<tr>
				<th style="width: 340px;" class="ui-widget-header">
					<label for="regEMail">E-Mail Adresse</label>
				</th>
				<td style="width: 300px;">
					<input id="regEMail" name="EMAIL" style="width: 300px;" type="email" placeholder="E-Mail Adresse ..." value="<?=$_REQUEST["EMAIL"]?>" />
				</td>
			</tr>
		</table>
		<div style="padding: 0px 0px 4px;">
			<input class="jQueryButton" type="submit" value="Registrierung abschicken" />
		</div>
	</form>
</div>