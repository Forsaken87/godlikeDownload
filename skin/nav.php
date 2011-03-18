<?php
global $page;

if ($page == $row["IDENT"]) { 
?>				<li><a href="#active"><?=$row["NAME"]?></a></li><?php
} else {
?>				<li><a href="index.php?show=<?=urlencode($row["IDENT"])?>"><?=$row["NAME"]?></a></li><?php
} 
?>