
<!DOCTYPE html>

<head>
<meta name="verifyownership"
 content="02c92071ea213f01aa84f08ac2c3097e"/>

	<title><?php
		if ( !isset( $title ))
			echo kohana::lang('page-homepage.gameheader');
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
	<?= html::stylesheet("media/css/homepage", FALSE); ?>

	<?= html::script('https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js', FALSE); ?>
	<?= html::script('https://code.jquery.com/ui/1.12.0/jquery-ui.min.js', FALSE); ?>


	<?= html::script('https://code.jquery.com/jquery-2.1.4.min.js', FALSE); ?>
	<?= html::script('https://code.jquery.com/ui/1.11.4/jquery-ui.min.js', FALSE); ?>
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>

	<!--Start Cookie Script-->
	<script type="text/javascript" charset="UTF-8" src="//cookie-script.com/s/3d6744b5683a21c9237c1198f11070ec.js"></script>
	<!--End Cookie Script-->

	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-11143472-3"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());
	  gtag('config', 'UA-11143472-3');
	</script>


  <!-- Facebook Pixel Code -->
  <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '699496567081418');
    fbq('track', 'PageView');
  </script>
  <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=699496567081418&ev=PageView&noscript=1"
  /></noscript>
  <!-- End Facebook Pixel Code -->

  <!-- Adsense code -->
  <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
  <script>
    (adsbygoogle = window.adsbygoogle || []).push({
      google_ad_client: "ca-pub-3403853548398248",
      enable_page_level_ads: true
    });
  </script>

</head>

<body>

<div class="container">

	<div class="row" id="header">
			<div class="col-xs-12 col-md-8 text-center">
				medieval-europe.eu - v2.9.5.3 - Env: <span class="value"><?php echo kohana::config('medeur.environment'); ?></span> -
				Server: <span class="value"><?= kohana::config('medeur.servername'); ?></span> - <?= html::anchor(
					'page/serverinfo', kohana::lang('global.serverconfiguration')); ?>
				</span> -
				<?= html::anchor('https://wiki.medieval-europe.eu', 'Wiki', array( 'target' => 'blank'  ) );?>	-
				<?= html::anchor(kohana::config('medeur.supporturl'), 'Support', array( 'target' => 'blank'  ) );?>	-
				<?= html::anchor(kohana::config('medeur.officialrpforumurl'), 'Forum', array( 'target' => 'blank'  ) );?>
			</div>

			<div class="col-xs-12 col-md-4 text-right">

				<ul id="languages" class="list-inline">
					<li>

					<?= html::anchor('character/change_language/it_IT', html::image(array('src' => 'media/images/flags-lang/it.png'), array('title' => 'Italian', 'class' => 'img-responsive img-flag', 'alt' => 'Italy flag')));
					?>
					</li>
					<li>
					<?= html::anchor('character/change_language/en_US', html::image(array('src' => 'media/images/flags-lang/gb.png'), array('title' => 'English', 'class' => 'img-responsive', 'alt' => 'Great Britain flag')));
					?>
					</li>
					<li>
					<?= html::anchor('character/change_language/fr_FR', html::image(array('src' => 'media/images/flags-lang/fr.png'), array('title' => 'French', 'class' => 'img-responsive')));?>
					</li>
					<!--
					<li>
					<?= html::anchor('character/change_language/ro_RO', html::image(array('src' => 'media/images/flags-lang/ro.png'), array('title' => 'Romanian', 'class' => 'img-responsive', 'alt' => 'Romania flag')));?>
					</li>
					-->
					<li>
					<?= html::anchor('character/change_language/bg_BG', html::image(array('src' => 'media/images/flags-lang/bg.png'), array('title' => 'Bulgarian', 'class' => 'img-responsive', 'alt' => 'Bulgary flag')));?>
					</li>
					<li>
					<?= html::anchor('character/change_language/de_DE', html::image(array('src' => 'media/images/flags-lang/de.png'), array('title' => 'Deutsch', 'class' => 'img-responsive', 'alt' => 'Germany flag')));?>
					</li>
					<li>
					<?=	html::anchor('character/change_language/ru_RU', html::image(array('src' => 'media/images/flags-lang/ru.png'), array('title' => 'Russian', 'class' => 'img-responsive', 'alt' => 'Russia flag')));?>
					</li>
					<!--
					<li>
					<?= html::anchor('character/change_language/tr_TR', html::image(array('src' => 'media/images/flags-lang/tr.png'), array('title' => 'Turkish', 'class' => 'img-responsive', 'alt' => 'Turkey flag')));?>
					</li>
					-->
					<li>
					<?= html::anchor('character/change_language/cz_CZ', html::image(array('src' => 'media/images/flags-lang/cz.png'), array('title' => 'Czech', 'class' => 'img-responsive', 'alt' => 'Czech flag')));?>
					</li>
					<!--
					<li>
					<?= html::anchor('character/change_language/cz_CZ', html::image(array('src' => 'media/images/flags-lang/sk.png'), array('title' => 'Slovak', 'class' => 'img-responsive', 'alt' => 'Slovacchia flag')));?>
					</li>
					-->
					<li>
					<?= html::anchor('character/change_language/pt_PT', html::image(array('src' => 'media/images/flags-lang/pt.png'), array('title' => 'Portuguese', 'class' => 'img-responsive', 'alt' => 'Portugal flag')));?>
					</li>
					<li>
					<?= html::anchor('character/change_language/gr_GR', html::image(array('src' => 'media/images/flags-lang/gr.png'), array('title' => 'Greek', 'class' => 'img-responsive', 'alt' => 'Greece flag')));?>
					</li>
				</ul>
			</div>
	</div>

	<div class="row block push">
		<div id="content" class="col-xs-12 ">
			<?php echo $content ?>
			<br style='clear:both'/>
		</div>
	</div>

	<div id="footer" class="row">
		<div class="col-xs-8 col-xs-offset-2 text-center">
			We accept crypto:
			<?= html::image('media/images/template/btc.png', array('width' => '20px', 'alt' => 'Bitcoin accepted', 'title' => 'Bitcoin Accepted')) ?>
			<?= html::image('media/images/template/bch.png', array('width' => '20px', 'alt' => 'Bitcoin Cash accepted', 'title' => 'Bitcoin Cash Accepted')) ?>
			<?= html::image('media/images/template/ethereum.png', array('width' => '20px', 'alt' => 'Ethereum accepted', 'title' => 'Ethereum Accepted')) ?>
			<?= html::image('media/images/template/litecoin.png', array('width' => '20px', 'alt' => 'Litecoin accepted', 'title' => 'Litecoin Accepted')) ?>
			<?php /*<?= html::image('media/images/template/waves.png', array('width' => '20px', 'alt' => 'Waves accepted', 'title' => 'Waves Accepted')) ?> plus others */ ?>
			 -
			<?= html::anchor('/page/display/privacy-and-cookies', kohana::lang('page-homepage.privacy'));?>
			 -
			&copy; medieval-europe.eu is a product of <a href="https://eightyeightbrands.com">Eighty Eight Brands</a> <?= date("Y"); ?>
		</div>
	</div>

</div> <!-- container-->

<?= html::script('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', FALSE); ?>
<?= html::script('https://code.jquery.com/ui/1.11.4/jquery-ui.min.js', FALSE); ?>

</body>
</html>
