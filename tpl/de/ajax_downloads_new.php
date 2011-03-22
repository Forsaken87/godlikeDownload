<?php
if (!isUser()) die(header("location: index.php?show=login"));

$filename = "cache/user_new/".$_SESSION['user']['ID_USER'].".htm";

if (!file_exists($filename)) {
	echo "<h1>Cache wird erzeugt, kann bis zu 10 Minuten dauern.</h1>";
} else {
	echo(file_get_contents($filename));
	?>
	<script type="text/javascript">
		var load = new Date();
		var time_update = load.getTime() + <?=(600 - (time() - filemtime($filename)))?>000;
		var interval_update = window.setInterval(function() {
			var now = new Date();
			if (now.getTime() > time_update) {
				window.clearInterval(interval_update);
				UpdateDownloads();
			} else {
				var seconds = Math.floor((time_update - now.getTime()) / 1000);
				var minutes = Math.floor(seconds / 60);
				seconds = seconds - (minutes * 60);
				$("#downloads_update").html("Update in "+(minutes > 0 ? minutes+"Min. und " : "")+seconds+"Sek.");
			}
		}, 1000);

		window.setTimeout(function() {
			UpdateDownloads();
		}, <?=(600 - (time() - filemtime($filename)))?>000);
	</script>
	<?php
}
?>