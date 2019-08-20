<!DOCTYPE html>

<head>

	<title><?php 
		if ( !isset( $title )) 
			echo 'Medieval Europe - Affiliate System';
		else
			echo $title ?>
	</title>

	<link href="/favicon.ico" rel="icon" type="image/x-icon" />

	<?php
	echo html::meta('description', 'Medieval Europe is an historical game with elements of roleplay and strategy set in medieval age.');
	echo html::meta('viewport', 'width=device-width, initial-scale=1.0');	
	echo html::meta('content-type', 'text/html; charset=utf-8');
	echo html::meta('Content-Language', 'en');
	echo html::meta('X-UA-Compatible', 'IE=8');
	echo html::meta('keywords', 'medieval, historical, roleplay game, strategy' );
	echo html::meta('robots', 'all');
	
	?>
	
	<!-- Bootstrap -->
	<!-- Latest compiled and minified CSS -->
	
	<?= html::stylesheet('https://code.jquery.com/ui/1.12.0/themes/base/jquery-ui', FALSE); ?>
	<?= html::stylesheet("https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min", FALSE); ?>
	<?= html::stylesheet("media/css/affil	iates", FALSE); ?>

	<?= html::script('https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js', FALSE); ?>
	<?= html::script('https://code.jquery.com/ui/1.12.0/jquery-ui.min.js', FALSE); ?>
	
	
	<?= html::script('https://code.jquery.com/jquery-2.1.4.min.js', FALSE); ?>
	<?= html::script('https://code.jquery.com/ui/1.11.4/jquery-ui.min.js', FALSE); ?>

	<script type="text/javascript">
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
          ga('set', 'anonymizeIp', true);
	  ga('create', 'UA-11143472-3', 'medieval-europe.eu');
	  ga('send', 'pageview');

	</script>

</head>

<body>

<div class="container">
	
	<div class="row" id="header">
						
			<div class="col-xs-12 col-md-12 text-center">
				<?= html::anchor(kohana::config('medeur.supporturl'), 'Support', array( 'target' => 'blank'  ) );?>	- 
				<?= html::anchor(kohana::config('medeur.gameurl'), 'Game', array( 'target' => 'blank'  ) );?>	- 
				<?= html::anchor('affiliate/login', 'Login');?>
			</div>
			
			
	</div>

	<div class="row">	
		<div id="content" class="col-xs-12 ">						
			<?php $message = Session::instance()->get('user_message'); echo $message ?>					
			<?php echo $content ?>	
			<br style='clear:both'/>			
		</div>
	</div>	

	
</div> <!-- container-->	

<?= html::script('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', FALSE); ?>		
<?= html::script('https://code.jquery.com/ui/1.11.4/jquery-ui.min.js', FALSE); ?>
	
</body>
</html>
