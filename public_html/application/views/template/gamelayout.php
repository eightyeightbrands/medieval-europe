<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "https://www.w3.org/TR/html4/strict.dtd">
<?php header('P3P: CP="NOI ADM DEV COM NAV OUR STP"'); ?>
<html>
<head>

	<title><?php
		if ( !isset( $title ) )
			echo kohana::lang('page-homepage.gameheader');
		else
			echo $title;
	?>
	</title>

	<meta name="theme-color" content="#000" />


	<link href="/favicon.ico" rel="icon" type="image/x-icon" />

	<?php
	$version = "2.9.5.1";
	echo html::meta('description', 'Medieval Europe is an historical browser game with elements of rpg, strategy and deep mechanics.');
	echo html::meta('content-type', 'text/html; charset=utf-8' );
	echo html::meta('viewport', 'width=device-width, initial-scale=1.0');
	echo html::meta('Content-Language', 'en');
	echo html::meta('X-UA-Compatible', 'IE=8');
	echo html::meta('keywords', 'medieval, historical, browser game, rpg, strategy' );
	echo html::meta('robots', 'all');

	// fogli di stile

	echo html::stylesheet('https://code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css', FALSE);
	echo html::stylesheet('media/js/jquery/plugins/jquery.countdown/jquery.countdown.css', FALSE);
	echo html::stylesheet('media/js/tooltipster-master/dist/css/tooltipster.bundle.min.css', FALSE);
	echo html::stylesheet('media/js/tooltipster-master/dist/css/tooltipster-sideTip-borderless.min.css', FALSE);

	echo html::stylesheet('media/css/gamelayout.css?v=2.9.5', FALSE);
	echo html::stylesheet('media/css/character.css?v=2.9.5', FALSE);
	echo html::stylesheet('media/css/submenu.css?v=2.9.5', FALSE);
	echo html::stylesheet('media/css/pagination.css?v=2.9.5', FALSE);
	echo html::stylesheet('media/css/structure.css?v=2.9.5', FALSE);
	echo html::stylesheet('media/css/battlereport.css?v=2.9.5', FALSE);
	echo html::stylesheet('media/css/map.css?v=2.9.5', FALSE);
	// Scripts
	echo html::script('https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js', FALSE);		echo html::script('https://code.jquery.com/ui/1.12.0/jquery-ui.min.js', FALSE);
	echo html::script("media/js/tooltipster-master/dist/js/tooltipster.bundle.min.js", FALSE);
	echo html::script('media/js/jquery/plugins/cookie/js.cookie.js', FALSE);	echo html::script('media/js/common.js', FALSE);
	?>
	<!-- Countdown: workaround per visualizzare il countdown nella title bar -->
	<script type="text/javascript">$.noRequestAnimationFrame = true;</script>
	<?	echo html::script('media/js/jquery/plugins/jquery.countdown/jquery.plugin.min.js', FALSE);		echo html::script('media/js/jquery/plugins/jquery.countdown/jquery.countdown.min.js', FALSE);			?>
	<!-- Fine Countdown -->
	<script src="https://apis.google.com/js/platform.js" async defer></script>


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



	<?php
		$db = Database::instance();
		$db -> query("select '--gamelayout--'");
		$user = Auth::instance() -> get_user();
		$char_id = Session::instance()->get('char_id');
		$currentpendingaction = Character_Model::get_currentpendingaction( $char_id );

		$character = Character_Model::get_data( $char_id );
		$activequest = Character_Model::get_active_quest($char_id);

		$promo = Configuration_Model::get_valid_promo();

		if (!empty($promo))
			$promoenddate = date( $promo -> enddate );
		else
			$promoenddate = null;
		if (!is_null($character))
		{
			if ($character -> sex == 'M' )
			{
				$pic = 'avatar_ginevra.png';
				$text = 'dailyrewardtext_m';
			}
			else
			{
				$pic = 'avatar_liutprando.png';
				$text = 'dailyrewardtext_f';
			}
		}

		//var_dump($promoenddate);exit;
		//var_dump($promo);exit;
	?>


<script type="text/javascript">
$(document).ready(function()
{
$(
	function()
	{
		var action = '<?php
			if ( $currentpendingaction != 'NOACTION' )
				echo $currentpendingaction['action'];
			else
				echo 'nocurrentpendingaction';
		?>';

		if ( action != 'nocurrentpendingaction' )
		{

			// setta il messaggio dell' azione in corso
			var shortmessage = '<?php
			if ( $currentpendingaction != 'NOACTION' )
				echo kohana::lang( 'regionview.' . $currentpendingaction['action'] . '_shortmessage' ) ?>';
			var longmessage =  '<?php
			if ( $currentpendingaction != 'NOACTION')
				echo kohana::lang( 'regionview.' . $currentpendingaction['action'] . '_longmessage' ) ?>';

			$('#actiondescription').text(shortmessage);
			$('#actiondescription').attr('title', longmessage);

			var endtime = <?php
			if ( $currentpendingaction != 'NOACTION' )
				echo $currentpendingaction['endtime'];
			else
				echo 0;
			?> ;

			enddate = new Date( endtime * 1000 );

			$('#defaultCountdown').countdown({
				until: enddate,
				serverSync: serverTime,
				format: 'dHMS',
				onTick: refreshTitleBar,
				compactLabels: ['d', 'h', 'm', 's'],
				compact: true,
				onExpiry: forceCompleteAction
			});

		}
		else
		{
			var shortmessage = '<?php echo kohana::lang('page.no_action');?>';
			var longmessage =  '<?php echo kohana::lang('page.no_action');?>';
			$('#actiondescription').html(shortmessage);
			$('#actiondescription').attr('title', longmessage);
			$('#cancelAction').hide();
		}
}),
$('#cancelAction').click(function()
{
res = confirm( '<?php echo kohana::lang('global.cancelactionconfirm')?>');
return (res);
}),
$('#promomessage').click(function()
{
	window.location.replace( '<?php echo url::base(true)?>' + '/bonus/index');
});

$.ajaxSetup({ cache: true });
$.getScript('//connect.facebook.net/en_US/sdk.js',
	function(){
	FB.init({
		appId: '<?= kohana::config('medeur.facebook_app_id'); ?>',
		version: 'v2.7' // or v2.1, v2.2, v2.3, ...
	});
});

$('#invite').click( function() {
	FB.ui({method: 'apprequests',
	message: '<?= Kohana::lang('character.facebookinvitemessage');?>',
	}, function(response){
		console.log(response.request);
		console.log(response.to);
		var actionurl = '<?php echo url::base(true)?>jqcallback/savefacebookinvite/';
    $.ajax(
		{
			url: actionurl,
			type:"POST",
      async: false,
			data: { request: response.request, to: response.to },
      success: function(text)
			{},
		});
	});
});

});

function refreshTitleBar(periods) {
  $(document).attr('title', periods[4]+':'+periods[5]+":"+periods[6] + ' - ' + '<?php echo kohana::lang('page-homepage.gameheader') ?>' );
}

function serverTime() {
    var time = null;
		var actionurl = '<?php echo url::base(true)?>jqcallback/get_servertime/';
    $.ajax(
		{
			url: actionurl,
      async: false, dataType: 'text',
      success: function(text)
			{
        time = new Date(text);
      },
			error: function(http, message, exc)
			{
				time = new Date();
			}
		});
    return time;
}

function forceCompleteAction() {
		var actionurl = '<?php echo url::base(true)?>character/complete_action/1';
		$.ajax({url: actionurl,
        async: false, dataType: 'text',
        success: function(text) {
					var shortmessage = '<?php echo kohana::lang('page.actioncompleted'); ?>';
					var longmessage = '<?php echo kohana::lang('page.no_action');?>';
					$('#actiondescription').text(shortmessage);
					$('#cancelAction').hide();
					$('#defaultCountdown').text('');
					$(document).attr('title', 'Completed!' );
        }, error: function(http, message, exc) {
            ;
    }});

    return;
}
</script>


<!-- Load Facebook SDK for JavaScript -->
<div id="fb-root"></div>
<script>(function(d, s, id) {
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) return;
	js = d.createElement(s); js.id = id;
	js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.7";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

<body>

	<!-- ME Chat button -->

	<?php if ($char_id !== 0)
	{
	?>

	<div id='chatwrapper' style='position:relative'>
		<div id="showchat">ME Chat</div>
		<div id='sidechat' class="cd-panel" style='display:none'>

			<header class="cd-panel-header">
				<div style='text-align:right;margin-right:3px;'>
					<?php echo html::anchor('#', 'Hide',	array('id' => 'hidechat')	); ?>&nbsp;
					<?php echo html::anchor('#', 'Full Chat',	array('class' => 'fullchat')	); ?>&nbsp;
					<?php echo html::anchor('https://wiki.medieval-europe.eu/index.php?title=Chat', 'Help',
						array('target' => 'new')); ?>
				</div>
			</header>

			<iframe id='sidechatiframe' src=''>
			</iframe>

			</div>
	</div>

	<?php
	}
	?>

	<!-- END ME Chat -->

	<div id="container">

	<?php
		if ( $char_id != 0 and Character_Model::get_premiumbonus( $char_id, 'basicpackage' ) === false )

	{
	?>
	<!--
	<div id='ads'>
<!--
		<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
		<ins class="adsbygoogle"
			 style="display:inline-block;width:728px;height:90px"
			 data-ad-client="ca-pub-1237485763042414"
			 data-ad-slot="7662216624"></ins>
		<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
		</script>
	</div>
	<br style='clear:both'/>
-->
	<?php } ?>
	<div id="header">

		<div id="languages">
		<?php
		echo html::anchor('/character/change_language/it_IT', html::image(array('src' => 'media/images/flags-lang/it.png'), array('title' => 'Italian', 'class' => 'image-flag')));
		echo html::anchor('/character/change_language/en_US', html::image(array('src' => 'media/images/flags-lang/gb.png'), array('title' => 'English', 'class' => 'image-flag')));
		?>
		</div>

		<?php
			$langtooltip =
			html::anchor('/character/change_language/hu_HU', html::image(array('src' => 'media/images/flags-lang/hu.png'), array('title' => 'Hun', 'class' => 'image-flag')))
			. '&nbsp;' .
			html::anchor('character/change_language/cz_CZ', html::image(array('src' => 'media/images/flags-lang/cz.png'), array('title' => 'Czech', 'class' => 'image-flag', 'alt' => 'Czech flag')))
			. '&nbsp;' .
			html::anchor('/character/change_language/de_DE', html::image(array('src' => 'media/images/flags-lang/de.png'), array('title' => 'Deutsch', 'class' => 'image-flag')))
			. '&nbsp;' .
			html::anchor('/character/change_language/pt_PT', html::image(array('src' => 'media/images/flags-lang/pt.png'), array('title' => 'Portuguese', 'class' => 'image-flag')))
			. '<br/>' .
			html::anchor('/character/change_language/ru_RU', html::image(array('src' => 'media/images/flags-lang/ru.png'), array('title' => 'Russian', 'class' => 'image-flag')))
			. '&nbsp;' .
			html::anchor('/character/change_language/bg_BG', html::image(array('src' => 'media/images/flags-lang/bg.png'), array('title' => 'Bulgarian', 'class' => 'image-flag')))
			. '&nbsp;' .
			html::anchor('/character/change_language/fr_FR', html::image(array('src' => 'media/images/flags-lang/fr.png'), array('title' => 'French', 'class' => 'image-flag')))
			. '&nbsp;' .
			html::anchor('/character/change_language/gr_GR', html::image(array('src' => 'media/images/flags-lang/gr.png'), array('title' => 'Greek', 'class' => 'image-flag')));


		?>

		<div id='expandlanguages' title='<?php echo $langtooltip?>'>+</div>

		<div id='jclock'><?php echo Utility_Model::format_datetime( time() ) ?></div>

		<div id="menuh">
			<span class="left">
				<?php
					echo html::anchor('https://wiki.medieval-europe.eu', 'Wiki', array( 'target' => '_blank')) . ' - ' ;
					echo html::anchor(kohana::config('medeur.supporturl'), Kohana::lang('user.supporttool'), array( 'target' => '_blank')) . ' - ';
					echo html::anchor('https://blog.medieval-europe.eu', 'Blog', array( 'target' => '_blank')) . ' - ';
					echo html::anchor('character/listall', Kohana::lang('character.listall')) . ' - ' ;
					echo html::anchor('newchat/init','IG Chat') . ' - ';
					echo html::anchor('https://discord.gg/qcySrCD','Discord Chat', array(
						'target' => 'new')) . ' - ';
				?>
			</span>

			<span class="right">
			<?php

			echo  html::anchor('user/profile', Kohana::lang('menu_logged.profile')) . ' - ';
			echo html::anchor('user/logout', Kohana::lang('menu_logged.logout'));
			?>
			</span>
		</div>
	</div>

	<div id="main">
		<div id="leftcol">

			<div id="stats-bar">
				<div id="coin-block">
					<div  style="margin-bottom:3px">
					<?php echo html::image(array('src' => 'media/images/template/doubloon.png'), array('title'=>kohana::lang('items.doubloon_name')))?>
					<span style="font-size:14px;font-weight:bold"><?php echo Character_Model::get_item_quantity_d( $char_id, 'doubloon' ); ?></span>
					</div>

					<div style="margin-bottom:3px">
					<?php echo html::image(array('src' => 'media/images/template/coins.png'), array('title'=>kohana::lang('items.silvercoin_name')))?>
					<span id='silvercoins' style="font-size:14px;font-weight:bold"><?php echo Character_Model::get_item_quantity_d( $char_id, 'silvercoin' ); ?></span>
					</div>

					<div style="clear:left">
					<?php echo html::image(array('src' => 'media/images/template/copper.png'), array('title'=>kohana::lang('items.coppercoin_name')))?>
					<span id='coppercoins' style="font-size:14px;font-weight:bold"><?php echo Character_Model::get_item_quantity_d( $char_id, 'coppercoin' ); ?></span>
					</div>
				</div>

				<div id="stat-block">
					<?php

					if ( !is_null ( $character ) )
					{
						$health = $character->health;
						$glut = $character->glut;
						$energy = $character->energy;
					}
					else { $health=$glut=$energy=0; }
					?>

					<div class='mainstat'>

						<span style='float:left;width:22px;'>
							<?php echo html::image('media/images/template/hearth.png') ?>
						</span>
						<div class="boxstat">
							<div class="healthstat" style="width:<?php echo round(100*(max(0,$health)/100))?>px"></div>
						</div>
						<div style="text-align:right;font-size:0.8em;">
							<?=round(100*(max(0,$health)/100));?>%
						</div>
					</div>

					<div style="clear:both"></div>

					<div class='mainstat'>
						<span style='float:left;width:22px;'>
							<?php echo html::image('media/images/template/energy.png') ?>
						</span>
						<div class="boxstat">
							<div class="energystat" style="width:<?php echo round(100*(max(0,$energy)/50))?>px"></div>
						</div>
						<div style="text-align:right;font-size:0.8em;"><?=round(100*(max(0,$energy)/50));?>%</div>
					</div>

					<div style="clear:both"></div>

					<div class='mainstat'>
						<span style='float:left;width:22px;'><?php echo html::image('media/images/template/glut.png') ?></span>
						<div class="boxstat">
							<div class="glutstat" style="width:<?php echo round(100*(max(0,$glut)/50))?>px"></div>
						</div>
						<div style="text-align:right;font-size:0.8em;"><?=round(100*(max(0,$glut)/50));?>%</div>
					</div>

				</div>

				<div id="action-block">
					<?php
						$message = '';
						if ( $currentpendingaction != 'NOACTION')
							$message = kohana::lang('regionview.' . $currentpendingaction['action'] . '_longmessage' );
						if ( $message != '' )
							$tooltip_text = My_I18n_Model::translate( $message );
						else
							$tooltip_text = kohana::lang('page.no_action');
					?>

					<div class='menutip' style='width:170px;float:left;' >
						<?php
						echo "<div id='actiondescription' style='margin-bottom:5px;' title='" . $tooltip_text. "'></div>";

						echo "<div id='defaultCountdown' style='padding:0px;margin-right:5px;border:0px solid #000'></div>";

						echo "<div id='cancelAction' style='text-align:right;'>".
								html::anchor ( '/character/cancel_action/' , '[' . kohana::lang('global.cancel_action') . ']' ) .
							"</div>" ;
						?>

					</div>
				</div>
			</div>


			<!-- Administrative Message -->

			<div id='adminmessage'>
				<?php


					$adminmessage = Admin_Message_Model::get_last_message();

					if ( (time() - $adminmessage['timestamp'] ) / ( 24 * 3600 ) < 1 )
					{
				?>
					<div id='adminmessagenew'>

					</div>
				<?php } ?>

				<div id='admincontent'>
					<?php
						echo Utility_Model::format_datetime( $adminmessage['timestamp'] )  . ' - ' . Utility_Model::bbcode( $adminmessage['summary'] )
					?>
				</div>
				<div id='adminfooter'>
					<?php echo html::anchor( 'admin/read_adminmessage/' . $adminmessage['id'], kohana::lang('global.read')) ?>&nbsp;
					<?php echo html::anchor( 'admin/list_allmessages' , kohana::lang('admin.oldmessages')) ?>
				</div>
			</div>


			<!-- Current Quest -->

			<?
			if (!is_null($activequest)) { ?>
			<div id="currentquest">
				<table>
				<tr>
					<td width="5%">
						<?php echo html::image(
						'media/images/template/quests/' . $pic,
						array('style' => 'height:50px;padding:1px;border:1px solid #999') )?>
					</td>
					<td width="50%" class="center">
					<?= kohana::lang('quests.activequest',
						kohana::lang('quests.'.$activequest['name'].'_name'),
						kohana::lang('quests.'.$activequest['name'].'_description'));
					?>
					</td>
					<td width="25%" class='center'>
						<?
							$speedbonus = Character_Model::get_stat_from_cache($character -> id, 'speedbonus');
							if ($speedbonus -> loaded and $speedbonus -> stat1 > time() )
							{
						?>
						<span class='evidence'>
							<?= kohana::lang('quests.speedbonus',
									$speedbonus -> value,
									Utility_Model::countdown($speedbonus -> stat1));
							?>
						</span>
						<? } ?>
					</td>

					<td width="20%" class="center">
						<?= html::anchor(
							'quests/view/' . $activequest['name'],
							kohana::lang('global.continue'),
							array( 'class' => 'button button-small'));
						?>
					</td>
				</tr>
				</table>
			</div>
			<? } ?>


			<!-- Event Banner -->
			<?
			if (Kohana::config('medeur.displayeventbanner'))
			{
			?>
			<div id='eventbanner'>
			<?
				echo html::anchor(
					'https://docs.google.com/forms/d/e/1FAIpQLSdoDq44H7I6NzgPDcbQbJtXFTPEI2PZZkjHK20hzNPcCZb_oA/viewform',
					html::image('media/images/template/eventbanner.png?v=2'),
					array(
						'escape' => false,
						'target' => 'new'
					)
				);
			?>
			</div>
			<?
			}
			?>
			<!-- Promo -->

			<?php
			$now = date("Y-m-d H:i:s");
			if ( !empty($promo) and $promo -> startdate < $now and $promo -> enddate > $now ) { ?>
			<div id='promomessage' >
				<div id='promocountdown'><?php
				echo "Promo ends in " . Utility_Model::countdown( strtotime($promo -> enddate) );?></div>
			</div>
			<?php } ?>



			<div id='region-info'>
				<?php

				$r = Character_Model::get_currentposition_d( $char_id );
				//$r = $character -> get_currentposition() ;
				if ( $r )
				{

					echo html::image(array('src' => 'media/images/heraldry/'. $r -> kimage.'-large.png'), array('title' => kohana::lang( $r -> kname), 'class'=>'heraldry' ));
					echo '<div id="regionname"><h2>'. Kohana::lang($r -> name) .'</h2></div>';
					echo '<div id="kingdomname">'. Kohana::lang($r -> kname) .'<br /><span id="kweather"></span></div>';

					echo '<div class="rlinks">';

					// Links relativi alla regione
					// Visualizzo i links sottostanti solo se la regione � terrestre

					if ($r -> type == "land")
					{

						if ( $r -> kname != 'kingdoms.kingdom-independent' )
						{
							echo html::anchor( "/region/info", Kohana::lang('structures_actions.castle_cityinfo')). ' - ';
							echo html::anchor( "/region/privatestructures", Kohana::lang('regionview.privatestructures')). ' - ' ;


						}
						// Links azioni

						if ( $r -> kname != 'kingdoms.kingdom-independent' )
							echo html::anchor( "/character/changeregion", Kohana::lang('charactions.change_city')). ' - ';

						echo html::anchor( "/region/retire", Kohana::lang('structures_actions.region_retire'), array('title' => Kohana::lang('structures_actions.region_retire_info'))) . ' - ' ;

					}

					echo '</div>';

					echo '<div class="rclima">';
						echo html::image(array('src' => 'media/images/template/'.$r->clima.'.png'), array('title' => kohana::lang('regionview.'.$r->clima)));
					echo '</div>';

					echo '<div class="rtype">';
						if ($r->type == "sea")
							echo html::image(array('src' => 'media/images/template/sea.png'), array('title' => kohana::lang('regionview.searegion')));
						else
							echo html::image(array('src' => 'media/images/template/'.$r->geography.'.png'), array('title' => kohana::lang('regionview.'.$r->geography)));
					echo '</div>';
				?>
				<script type="text/javascript">

					$(document).ready(function() {
						$.getJSON("https://medieval-europe.eu/index.php/weather/?region=<?php echo Kohana::lang($r -> name); ?>", function( data ) {
							//console.log(data.status + ' - ' + data.msg);
							if(data.status == true) {
								document.getElementById('kweather').innerHTML = '<small>' + data.msg + '</small>';
								//console.log(data.msg);
							}
						});
					});

				</script>
				<?
				}
				?>
			</div>



			<div id=content>
				<div id="contenttop"></div>
				<div id="contentcenter">
					<div id="wrapper">
					<?php $message = Session::instance()->get('user_message'); echo $message ?>
					<?php
						$db -> query("select '--content--'");
						echo $content ;
						$db -> query("select '--content--'");
					?>
					</div>
				</div>
				<div id="contentbottom"></div>
			</div>

			<div style="clear: both;">
				<?php if(!empty($character)) { ?>
				<div style="clear: both; padding-top: 15px;">
					<iframe src="https://discordapp.com/widget?id=157995381089632257&theme=dark&username=<?php echo urlencode($character->name); ?>" width="100%" height="500" allowtransparency="true" frameborder="0"></iframe>
				</div>
				<? } ?>

			</div>

		</div>

		<div id="rightcol">
			<div id="menuprincipale">

				<div style='margin-top:5px;font-weight:bold;color:#fff;text-align:center'>
				<?php
				if ( $character )
				{
					if (Character_Model::get_basicpackagetitle( $char_id ) != '')
						echo kohana::lang(Character_Model::get_basicpackagetitle( $char_id ));
					echo '<br/>' . $character->name;
				}
				?>

				</div>

				<!-- avatar -->
				<? if (!is_null($character)) { ?>
				<div id='avatar'>
					<?php echo Character_Model::display_avatar( $character->id, $size = 'l', $class = 'charpic' ) ?>
				</div>
				<? } ?>

				<div class="social">
					<span class="google">
						<g:plusone size="medium" href="https://plus.google.com/+MedievaleuropeEuGame?hl=it"></g:plusone>
					</span>
					<span class="Facebook">
						<iframe src="https://www.facebook.com/plugins/like.php?href=https://www.facebook.com/MedievalEurope&amp;show_faces=false" scrolling="no" frameborder="0" style="height: 21px; width: 100px" allowTransparency="true"></iframe>
					</span>
				</div>

				<div id='mainbuttons'>

					<?php
					// Bottone della regione
					echo html::anchor('region/view',
						html::image('media/images/template/icon-region.jpg',
						array(
						'title' => Kohana::lang('menu_logged.position')), false));


					echo html::anchor('map/view', html::image('media/images/template/icon-travel.jpg'));

					// Bottone dei messaggi
					echo html::anchor('message/received',html::image('media/images/template/icon-message.jpg',
						array(
						'title' => Kohana::lang('menu_logged.messages'))));

					$unreadmessages = Character_Model::get_unreadmessages_d( $char_id );

					if ( $unreadmessages > 0 )

					echo '<div id="newmsg">'. $unreadmessages .'</div>';

					if ( !is_null($character) and $character->rpforumregistered )
						echo html::anchor('character/accessrpforum',
							html::image('media/images/template/icon-forum.jpg', array( 'title' => Kohana::lang('menu_logged.rpforum'))),
						  array('target'=>'_blank')) ;
					else
						echo html::anchor('character/accessrpforum',
							html::image('media/images/template/icon-forum.jpg', array( 'title' => Kohana::lang('menu_logged.rpforum')))
						  ) ;
					?>
				</div>

				<div id='smallbuttons'>

					<?php
					// Griglia di iconcine

					echo html::anchor('character/details', html::image('media/images/template/icon-profile.jpg',
						array(
						'title' => Kohana::lang('menu_logged.mychar')), false));

					echo html::anchor('character/inventory', html::image('media/images/template/icon-inventory.jpg',
						array(
						'title' => Kohana::lang('menu_logged.inventory')), false));

					echo html::anchor('group/mygroups', html::image('media/images/template/icon-groups.jpg',
						array(
						'title' => Kohana::lang('menu_logged.groups')), false));

					echo html::anchor('event/show',	html::image('media/images/template/icon-events.jpg',
						array(
						'title' => Kohana::lang('global.events')), false));

					//$unreadevents = $character -> get_unreadevents();
					$unreadevents = Character_Model::get_unreadevents_d( $char_id );

					if ( $unreadevents > 0 )
						echo '<div id="newevt">'. $unreadevents .'</div>';

					echo html::anchor('boardmessage/index/europecrier', html::image(
						'media/images/template/icon-announcements.jpg',
						array(
						'title' => Kohana::lang('menu_logged.announcements')), false));

					echo html::anchor('page/rankings', html::image('media/images/template/icon-rankings.jpg',
					array(
						'title' => Kohana::lang('menu_logged.rankings')), false));

					echo html::anchor('bonus/getdoubloons', html::image('media/images/template/icon-buy.jpg',
					array(
						'title' => Kohana::lang('menu_logged.shop')), false));

					echo html::anchor('bonus/index/1', html::image('media/images/template/icon-shopbonus.jpg',
					array(
						'title' => Kohana::lang('page.shop_bonus')),false));

					// hardcoded 1 per evitare query.
					if ( Session::instance() -> get('isadmin') or Session::instance() -> get('isstaff') )
						echo html::anchor('admin/console', html::image('media/images/template/icon-admin.jpg',
					array(
						'title' => Kohana::lang('admin.console')), false));

					?>
				</div>

				<div id='social'>


				</div>

				<div id="toplists">
					<?php
					echo html::image('media/images/template/icon-vote.png');

					/*
					echo html::anchor( 'mining/mine',
							html::image('media/images/items/pickaxe.png',
							array(
								'style' => 'margin-left:15px;margin-bottom:5px;width:22px',
								'title' => kohana::lang('page.minesilvercoins'))),
						array( 'target' => '_blank', 'class' => 'votingbutton') );
					*/

					echo html::anchor( 'toplist/vote/energy',
					html::image('media/images/template/icon-vote-energy.png',
						array(
							'style' => 'margin-left:5px;margin-bottom:5px',
							'title' => kohana::lang('page.toplistvote')),
					array('target' => '_blank', 'class' => 'votingbutton') ));

					?>

				</div>
			</div>

			<div style="clear: both;"></div>

			<div style="width: 200px; padding-top: 15px;">
				<?php
					if ( $char_id != 0 and Character_Model::get_premiumbonus( $char_id, 'basicpackage' ) === false ) {
				?>
				<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
				<!-- medieval-europe.eu sidebar -->
				<ins class="adsbygoogle"
				     style="display:block"
				     data-ad-client="ca-pub-3403853548398248"
				     data-ad-slot="6435927639"
				     data-ad-format="auto"
				     data-full-width-responsive="true"></ins>
				<script>
				(adsbygoogle = window.adsbygoogle || []).push({});
				</script>
				<?php } ?>
			</div>

		</div>


</div>


<?php $db -> query("select '--gamelayout--'"); ?>

<?php if ( kohana::config('medeur.displaybenchmark') )
{
	echo '<div>';
	$this->profiler = new Profiler;
	$this->profiler -> render();
	echo '</div>';
}
?>
</body>
</html>
