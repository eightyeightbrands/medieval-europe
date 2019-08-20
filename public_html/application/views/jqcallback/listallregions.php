<?php
	foreach ( $regions as $n )
		echo kohana::lang($n->name) . "|". $n->id . "|" . $n->capital . "\n";
?>
