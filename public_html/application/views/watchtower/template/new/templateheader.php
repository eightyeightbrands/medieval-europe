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
	
	<link href="/favicon.ico" rel="icon" type="image/x-icon" />

	<?php
	echo html::meta('description', 'Medieval Europe is an historical browser game with elements of rpg, strategy and deep mechanics.');
	echo html::meta('content-type', 'text/html; charset=utf-8' );
	
	echo html::meta('Content-Language', 'en');
	echo html::meta('X-UA-Compatible', 'IE=8');	
	echo html::meta('keywords', 'medieval, historical, browser game, rpg, strategy' );
	echo html::meta('robots', 'all');
	
	// fogli di stile		
	
	echo html::stylesheet('https://code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css', FALSE);	
	echo html::stylesheet('media/js/jquery/plugins/jquery.countdown/jquery.countdown.css', FALSE);
	echo html::stylesheet('media/js/tooltipster-master/dist/css/tooltipster.bundle.min.css', FALSE);
	echo html::stylesheet('media/js/tooltipster-master/dist/css/tooltipster-sideTip-borderless.min.css', FALSE);
	?>
	
	<!-- Font -->
	<link href="https://fonts.googleapis.com/css?family=Metamorphous" rel="stylesheet">
	
	<?
	
	//TODO: Rivedere
	
	/*
	foreach ($sheets as $key => $val)
	{ echo html::stylesheet('media/css/'.$key, $val, FALSE); }	
	*/
	
	echo html::stylesheet('media/newlayout/css/newlayout.css', FALSE);
	echo html::stylesheet('media/newlayout/css/character.css', FALSE);
	
	
	// Scripts	
	
	echo html::script('https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js', FALSE);			
	echo html::script('https://code.jquery.com/ui/1.12.0/jquery-ui.min.js', FALSE);	
	echo html::script('media/js/tooltipster-master/dist/js/tooltipster.bundle.min.js', FALSE);	
	echo html::script('media/js/jquery/plugins/cookie/js.cookie.js', FALSE);	echo html::script('media/js/common.js', FALSE);			
	?>
	
	<!-- Countdown: workaround per visualizzare il countdown nella title bar -->
	<script type="text/javascript">$.noRequestAnimationFrame = true;</script>
	
	<?	echo html::script('media/js/jquery/plugins/jquery.countdown/jquery.plugin.min.js', FALSE);		echo html::script('media/js/jquery/plugins/jquery.countdown/jquery.countdown.min.js', FALSE);			?>
	<!-- Fine Countdown -->	
	
	<?php 
		$db = Database::instance();		
		$db -> query("select '--gamelayout--'");
		$user = Auth::instance() -> get_user();
		$char_id = Session::instance()->get('char_id');
		$currentpendingaction = Character_Model::get_currentpendingaction( $char_id );		
		$charobj = Character_Model::get_data( $char_id ); 
		$promo = Configuration_Model::get_valid_promo();
		$currentposition = Character_Model::get_currentposition_d( $charobj -> id);		
		//var_dump($promo);exit;
		
		if (!empty($promo))
			$promoenddate = date( $promo -> enddate );
		else	
			$promoenddate = null;
			$promoenddate = null;
		
		
		//var_dump($promoenddate);exit;
		//var_dump($promo);exit;
	?>
	

<script type="text/javascript">
$(document).ready(function() 
{	

	$(
	// Gestione Pending Action
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
			
			$('#currentaction').text(shortmessage);
			$('#currentaction').attr('title', longmessage);	
			
			var endtime = <?php 
			if ( $currentpendingaction != 'NOACTION' )
				echo $currentpendingaction['endtime'];
			else
				echo 0;
			?> ;
			
			enddate = new Date( endtime * 1000 );
			
			$('#actioncountdown').countdown({
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
			$('#currentaction').html(shortmessage);
			$('#currentaction').attr('title', longmessage);
			$('#cancelaction').hide();			
		}
}),

<? if ($currentposition !== false ) 
{ 
?>
	$('#background').removeClass('landsea land sea').addClass('<?= $currentposition -> backgroundclass;?>');
<? 
}
else
{ 
?>
$('#background').removeClass('landsea land sea').addClass('land');
<?
}
?>


function refreshTitleBar(periods) { 
  $(document).attr('title', periods[4]+':'+periods[5]+":"+periods[6] + ' - ' + '<?php echo kohana::lang('page-homepage.gameheader') ?>' );	
}

$('#cancelaction').click(function()
{
res = confirm( '<?php echo kohana::lang('global.cancelactionconfirm')?>');
return (res); 
}),
$('#promomessage').click(function()
{
	window.location.replace( '<?php echo url::base(true)?>' + '/bonus/index');	
}),

/*
$('.command').click( function() {
	
	console.log("Opening URL: " + $(this).data('url'));
	
	var dialog = $('#dialog').load('<?php echo url::base(true)?>'+$(this).data('url'))	
	.dialog({
		title: $(this).data('title'),
		width: 900,
		height: 600,
		modal: true
	});
	console.log("title"+$(this).attr('title'));
	dialog.dialog('open'); 
 
	
});
*/
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
					$('#currentaction').text(shortmessage);
					$('#cancelaction').hide();
					$('#actioncountdown').text('');
					$(document).attr('title', 'Completed!' );
        }, error: function(http, message, exc) { 
            ;
    }}); 
		
    return;
}
	
});

</script>

<script src="//connect.facebook.net/en_US/sdk.js"></script>
  <script>
   FB.init({
    appId:'1448064485408533',
    cookie:true,
    status:true,
    xfbml:true,
	version:"v2.4"
   });
   
   /*function FBInvite(){
    FB.ui({
     title: '<?php echo "Invite your Friends" ?>',
     method: 'apprequests',
     message: '<?php echo kohana::lang('global.comeplaymedievaleurope');?>',
    },function(response) {
     if (response) {
		alert('You Successfully Invited your friend(s)!');
     } else {
		alert('Failed To Invite');
     }
    });
   }
	 */
  </script>

<script type='text/javascript'>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-11143472-3', 'auto');
  ga('send', 'pageview');
</script>
<body>
<div id="dialog"></div>
<div id="header">	
		<!-- character info panel -->
		<div id="characterinfo">		
			<div style='float:left'><b><?=$charobj -> name;?></b></div>			
			<div style='float:right'>
				<?= html::image('media/newlayout/images/set1/icons/doubloons.png'); ?>
				<?php echo Character_Model::get_item_quantity_d( $char_id, 'doubloon' ); ?>
				<?= html::image('media/newlayout/images/set1/icons/silvercoins.png'); ?>
				<?php echo Character_Model::get_item_quantity_d( $char_id, 'silvercoin' ); ?>
			</div>			
			<div id="avatar"><?= Character_Model::display_avatar( $charobj->id, $size = 's') ?></div>
			<div id="characterbars">
				<table>
				<tr><td>Health</td><td class="valuelight text-right"><?=$charobj -> health;?>%</td></tr>
				<tr><td>Energy</td><td class="valuelight text-right"><?=$charobj -> energy/50*100;?>%</td></tr>
				<tr><td>Glut</td><td class="valuelight text-right"><?=$charobj -> glut/50*100;?>%</td></tr>					
				</table>
				
			</div>
			<div id="characteraction">					
				<div class="text-center" id="currentaction"></div>								
				<div id="actioncountdown" class="text-center"></div>					
				<div id="cancelaction" class="text-center">
					<?= html::anchor ( '/character/cancel_action/' , '[' . kohana::lang('global.cancel_action') . ']' ); ?>	
				</div>
			</div>	
			<div class="infolink" ><?= html::anchor('/character/publicprofile/' . $charobj -> id, 'Info');?></div>			
		</div>
						
		
		<!-- Navigation -->
		
		<ul id='navigation'>
			
		
		<li>
		<?
		echo html::anchor(
			'map/view', 
			html::image('media/newlayout/images/set1/buttons/icon-map.jpg'),					
			array(					
				'style' => 'margin-bottom:5px',
				'title' => Kohana::lang('global.travel')				
				), false );
		?>
		</li>
		<li>
		<?
			echo html::anchor(
				'region/view', 
				html::image('media/newlayout/images/set1/buttons/icon-village.jpg', 
					array(
						'title' => Kohana::lang('menu_logged.announcements'))),
				array(									
				'style' => 'margin-bottom:5px'), false );
		?>
		</li>
		<li>							
			<?
			echo html::anchor(
				'character/details', 
				html::image('media/newlayout/images/set1/buttons/icon-profile.jpg'),
				array(						
					'data-title' => Kohana::lang('menu_logged.mychar'),
					'title' => Kohana::lang('menu_logged.mychar'),											
					'escape' => true,
					), 
					
					false );
			?>
		</li>
		<li>			
		<?
			echo html::anchor(
				'character/inventory', 
				html::image('media/newlayout/images/set1/buttons/icon-inventory.jpg'), 						
				array(
				'class' => 'command',
				'data-title' => Kohana::lang('menu_logged.inventory'),
				'title' => Kohana::lang('menu_logged.inventory'),
				'data-url' => 'character/inventory',
				'style' => 'margin-bottom:5px'), false );
		?>			
		
		</li>
		<li>
			<?
				echo html::anchor(
					'message/received', 
					html::image('media/newlayout/images/set1/buttons/icon-messages.jpg', 
						array(
							'title' => Kohana::lang('menu_logged.messages'))),
					array(
					'class' => 'command',					
					'style' => 'margin-bottom:5px'), false );
			?>
			</li>
		<li>
			<?
				echo html::anchor(
					'event/show', 
					html::image('media/newlayout/images/set1/buttons/icon-events.jpg'), 							
					array(
					'class' => 'command',
					'data-title' => Kohana::lang('menu_logged.events'),
					'title' => Kohana::lang('menu_logged.events'),
					'style' => 'margin-bottom:5px'), false );
			?>
			</li>
			<li>
			<?
				echo html::anchor(
					'boardmessage/index/europecrier', 
					html::image('media/newlayout/images/set1/buttons/icon-announcements.jpg', 
						array(
							'title' => Kohana::lang('menu_logged.announcements'))),
					array(
					'class' => 'command',
					'style' => 'margin-bottom:5px'), false );
			?>
			</li>
			<li>
			<?
				echo html::anchor(
					'page/rankings', 
					html::image('media/newlayout/images/set1/buttons/icon-rankings.jpg', 
						array(
							'title' => Kohana::lang('menu_logged.rankings'))),
					array(
					'class' => 'command',
					'style' => 'margin-bottom:5px'), false );
			?>
			</li>
			<li>
			<?
				echo html::anchor(
					'bonus/getdoubloons', 
					html::image('media/newlayout/images/set1/buttons/icon-buy.jpg', 
						array(
							'title' => Kohana::lang('menu_logged.shop'))),
					array(
					'class' => 'command',					
					'style' => 'margin-bottom:5px'), false );
			?>
			</li>
			<li>
			<?
				echo html::anchor(
					'bonus/index',
					html::image('media/newlayout/images/set1/buttons/icon-shopbonus.jpg', 
						array(
							'title' => Kohana::lang('menu_logged.bonus'))),
					array(
					'class' => 'command',					
					'style' => 'margin-bottom:5px'), false );
			?>
			</li>
			<li>
			<?
				if ( Session::instance() -> get('isadmin') or Session::instance() -> get('isstaff') )
					echo html::anchor(
						'admin/console', 
						html::image('media/newlayout/images/set1/buttons/icon-admin.jpg', 
							array(
								'title' => Kohana::lang('menu_logged.console'))),
						array(
						'class' => 'command',						
						'escape' => true), false );
			?>
			</li>
		</ul>
			
		<!-- game panel -->
		<div id="rightpanel">
			<div id="servertime">
			<?php echo Utility_Model::format_datetime( time() ) ?>
			</div>
			<ul id="gamepanel">				
				<li>
				<?= html::anchor('/user/logout',
					html::image('media/newlayout/images/set1/buttons/logout.png'),
					array(
						'title' => 'Logout',
						'escape' => true )) ; ?>				
				</li>
				<li>
				<?= html::anchor('/user/profile/',
					html::image('media/newlayout/images/set1/buttons/account.png'),
					array(
						'title' => 'User Account',
						'escape' => true )) ; ?>				
				</li>									
			</ul>			
			<? if ($currentposition !== false ) { ?>
				<div id="currentposition">
					<div id="heraldry">
						<?= html::image('media/images/heraldry/' . $currentposition ->kimage . '-small.png') ; ?>
					</div>
					<div id="positioninfo">
						<b>
						<?= kohana::lang($currentposition->name); ?> - <?= kohana::lang($currentposition->kname) ?>
						<br/>
						ID: <?= $currentposition -> id ?>
						</b>						
					</div>
				</div>
			<? } ?>	
			<div class="infolink" ><?= html::anchor('#', 'Info');?></div>							
			
		</div>
		
	</div> <!-- end header -->
