<?php
if (isUser()) die(header("location: index.php?show=dashboard"));


$errors = array();
if (!empty($_POST)) {
	// USER
	if (empty($_POST['USER'])) {
		$errors[] = "Kein Benutzername angegeben";
	} elseif (strlen($_POST['USER']) < 4) {
		$errors[] = "Der Benutzername ist zu kurz! (Mindestens 4 Zeichen)";
	} elseif (!get_query_field("SELECT ID_USER FROM `user` WHERE NAME LIKE '".mysql_escape_string($_POST['USER'])."'")) {
		$errors[] = "Es wurde kein Benutzer mit dem Namen '".$_POST['USER']."' gefunden!";
	}
	// PASS
	if (empty($_POST['PASS'])) {
		$errors[] = "Kein Passwort angegeben";
	} else {
		if (strlen($_POST['PASS']) < 6) $errors[] = "Das Passwort ist zu kurz! (Mindestens 6 Zeichen)";
	}
	if (empty($errors) && (empty($_SESSION["FAIL_LOCK"]) || ((time() - $_SESSION["FAIL_LOCK"]) > 5))) {
		$res = @mysql_query("SELECT * FROM `user` WHERE NAME LIKE '".mysql_escape_string($_POST['USER'])."' AND ".
			"PASSWORD LIKE '".mysql_escape_string(md5($_POST['PASS']))."'");
		$ar_user = @mysql_fetch_assoc($res);
		if (!empty($ar_user)) {
			$_SESSION['user'] = $ar_user;
			die(header("location: index.php?show=dashboard"));
		} else {
			$_SESSION["FAIL_LOCK"] = time();
			$errors[] = "Falsches Passwort!";
		}
	}
}

?>
<script type="text/javascript">

$(function() {
	$(".jQueryButton").button();
});

function RegisterNow() {
	document.location.href = "index.php?show=register";
}

</script>
<div class="ui-widget ui-widget-content" style="width: 408px; margin: auto;" align="center">
	<div class="ui-widget-header" align="center">
		<h1>Benutzer-Login</h1>
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
	<form action="index.php?show=login" method="post">
		<table cellpadding="0" cellspacing="0" style="margin: 4px; width: 400px;">
			<tr>
				<th style="width: 200px;" class="ui-widget-header">
					<label for="loginUser">Benutzername</label>
				</th>
				<td style="width: 200px;">
					<input id="loginUser" name="USER" style="width: 200px;" placeholder="Benutzername ..." value="<?=$_REQUEST["USER"]?>" />
				</td>
			</tr>
			<tr>
				<th style="width: 200px;" class="ui-widget-header">
					<label for="loginPass">Passwort</label>
				</th>
				<td style="width: 200px;">
					<input id="loginPass" name="PASS" style="width: 200px;" type="password" placeholder="Passwort ..." />
				</td>
			</tr>
		</table>
		<div style="padding: 0px 0px 4px;">
			<input class="jQueryButton" type="submit" value="Einloggen" />
			<input class="jQueryButton" type="button" value="Registrieren" onclick="RegisterNow();" />
		</div>
	</form>
</div>