<!DOCTYPE html>

<html>
<head>

	<title></title>
	
	<link href="/favicon.ico" rel="icon" type="image/x-icon" />

	<?php
	echo html::meta('description', 'Medieval Europe is an historical browser game with elements of rpg, strategy and deep mechanics.');
	echo html::meta('content-type', 'text/html; charset=utf-8' );
	echo html::meta('viewport', 'width=device-width, initial-scale=1.0');
	echo html::meta('Content-Language', 'en');
	echo html::meta('X-UA-Compatible', 'IE=8');	
	echo html::meta('keywords', 'medieval, historical, browser game, rpg, strategy' );
	echo html::meta('robots', 'all');
	
	// fogli di stile
		
	echo html::stylesheet('media/js/bootstrap/css/bootstrap.css', FALSE);	
	echo html::stylesheet('media/js/bootstrap/css/theme.min.css', FALSE);		
	echo html::stylesheet('media/js/bootstrap/css/custom.css', FALSE);		
	
?>	
</head>

<body>

<div class="container">

<?= $content ; ?>

</div>
<?
	// Scripts
	echo html::script('media/js/jquery/jquery-2.1.4.min.js', FALSE);
	echo html::script('media/js/bootstrap/js/bootstrap.min.js', FALSE);
?>	
</body>
</html>