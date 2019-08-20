<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "https://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title><?php echo Kohana::lang('page-homepage.gameheader');?></title>
	<link href="/favicon.ico" rel="icon" type="image/x-icon" />
	<?php echo html::meta('description', 'Medieval Europe is a browser game based on Dark Ages life experience.' )?>
	<?php echo html::meta('content-type', 'text/html; charset=iso-8859-1' )?>	
	<?php echo html::meta('Content-Language', 'en') ?>
	<?php echo html::meta('X-UA-Compatible', 'IE=8') ?>
	<?php echo html::meta('title', 'Strategy Game . Free Online Multiplayer Strategy Games | Medieval Europe') ?>
	<?php echo html::meta('keywords', 'pbbg, medieval, dark ages, rpg, game, webgame, free, browser, free mmorpg games, free multiplayer games, free online mmorpg, free online rpg games, games for multiplayer, mmorpg free, multiplayer rpg games, play multiplayer games, rpg online games, top mmorpg, strategy games, multiplayer games, online strategy games, free online multiplayer strategy game, game medieval, medieval war, historic, Kingdom' )?>
	<?php echo html::meta('robots', 'all' )?>		
	<?php echo html::stylesheet("media/css/page.css?rel=v2.8.6.1", FALSE); ?>
	<?php echo html::script('media/js/jquery.clock.js', FALSE)?>	
	<?php echo html::script('media/js/jquery.countdown.js', FALSE)?>	

	<script type="text/javascript">
	$(document).ready(function() 
	{ 
	
	$(".votingbutton").click( function () {		   
			$(this).remove();
		});
	});
	
	function finishAction() {
				$('#actiondescription').text('<?php echo kohana::lang('global.completed_action')?>');	
	}
	
	$(
		
		function() { 
			<?php			
				$pa_endtime = Session::instance()->get('pendingaction_endtime');			
				if ( ! $pa_endtime)	$pa_endtime = 0;
				echo "var endtime = ".  $pa_endtime . " ;";
				echo "var now = " .  time() . ";"; 
				// se c'� una action pending in sessione stampala
				if (Session::instance()->get('pendingaction_s_message')) 
				{
					if (strlen( My_i18n_Model::translate(Session::instance()->get('pendingaction_s_message' ) ) ) > 13 )
						$message = substr( My_i18n_Model::translate(Session::instance()->get('pendingaction_s_message')), 0, 13 ) . '... ';
					else
						$message = My_i18n_Model::translate(Session::instance()->get('pendingaction_s_message'));					
					echo "var message = '" . $message . "';" ;
				}
				else
				{
					echo "var message = '" . kohana::lang('page.no_action') . "';" ;									
				}
			?> 		
			
			if ( endtime > now )
			{	
				$('#actiondescription').text(message);
				d = new Date( endtime * 1000);						
				$('#defaultCountdown').countdown({until: d, compact: true	, format: 'dhms', onExpiry: finishAction}) ;
			}
			else
				$('#actiondescription').text(message);

		}
			
	);
	

	
	$(document).ready(
	 function($) {
		var options = { }
    $('.jclock').jclock( options );
     }
		);
	
	
	</script>
	</head>
<?php 
$char = Character_Model::get_info( Session::instance()->get('char_id') );
?>

<body>
<div id="container">
	<div id="header">
		<div class="languages">
		<?php
			echo html::anchor('language/it', html::image(array('src' => 'media/images/flags-lang/it.png'), array('title' => 'Ita', 'class' => 'image-flag')));
			echo html::anchor('language/en', html::image(array('src' => 'media/images/flags-lang/gb.png'), array('title' => 'Eng', 'class' => 'image-flag')));
			echo html::anchor('language/de', html::image(array('src' => 'media/images/flags-lang/de.png'), array('title' => 'Deu', 'class' => 'image-flag')));
		?>
		</div>
		
		<div id='servertime'>
			<div class='jclock'></div>
			<div class='currentdate'><?php echo Utility_Model::format_date( time() )?></div>								
		</div>

		<div id="menuh">
			<ul>
				<li>
				<?php 
					if ( kohana::config('medeur.environment') == 'test' )
						echo html::anchor('https://testforum.medieval-europe.eu','Forum', array('target'=>'blank'));
					else
					{
						echo html::anchor(Kohana::config('medeur.officialrpforumurl'),'Forum', array('target'=>'blank')) . ' - ' ; 							
						if ( $char and $char -> rpforumregistered )
							echo html::anchor('character/accessrpforum','RP Forum', array('target'=>'blank')) ;
						else
							echo html::anchor('character/accessrpforum','RP Forum') ;
					}
				?>
				-</li>
				<li>
				<?php 
					echo html::anchor( 'page/display/help','Help', array('target'=>'new')) . ' - '; 
					echo html::anchor( kohana::lang('page.help_wikiurl'), 'Wiki', array('target'=>'new')) . ' - '; 
				?>
				</li>				
				<li>
				<?php
					echo html::anchor('user/profile', Kohana::lang('menu_logged.profile')) . ' - ';
					echo html::anchor('character/listall', Kohana::lang('character.listall')) . ' - ';
					echo html::anchor('https://support.medieval-europe.eu', Kohana::lang('user.supporttool'), array( 'target' => 'new', 'style' => 'color: #ffff00' ) ) . ' - ';
					echo html::anchor('page/shop', Kohana::lang('menu_logged.shop'), array(  'style' => 'color: #ffff00' ) ) . ' - ';
					echo html::anchor('user/logout', Kohana::lang('menu_logged.logout'));
				?>
				</li>
			</ul>
			</div>
			

	</div>
	<div id="content">
		
		<table border=0>
		<tr valign="top">
			<td id="main">
				
				<div id='adminmessage'>
				<?php if ( kohana::lang('adminmsg.message') != '' and Auth::instance()->logged_in() )
					echo "<p class='evidence'>".kohana::lang('adminmsg.message')."</p>";
				?>
				</div>
				<div id='flashmessage'>
				<?php $message = Session::instance()->get('user_message'); echo $message ?>
				</div>
				<?php echo $content ?>
			</td>

			<td id="navigation">

				<!--	Se l' utente � loggato visualizza il men� di navigazione/gioco-->
				<!--  Menu navigazione -->
				<?php if (Auth::instance()->logged_in()) 
				{
					
					$menu = new view('template/menu_logged');
				?>
				
					
					
					<!-- Box azione pending -->
					<div id="charinfo">
					
						<div class='paddingbox'>

							<?php if ( $char ) { ?>
							<!-- visualizza avatar -->
							<div style='margin-bottom:2px'>
								<b><?php echo $char->get_name() ?></b>
							</div>
							
							<div id='avatar'>
							
								<div style='float:left;width:50px;border:0px solid #000'>
									<?php 							
									$file = "media/images/characters/".$char->id."_s.jpg";	
									if ( file_exists( $file) )
										echo html::image('media/images/characters/'.$char->id.'_s.jpg?t='.time(), array( 'alt' => 'avatar', 'class' => 'avatar', 'align'=>'left'), false);
									else
										echo html::image('media/images/characters/aspect/noimage_s.jpg', array( 'alt' => 'avatar', 'class' => 'avatar', 'align'=>'left'), false);

									?>
								</div>
								
								<div  style='float:left;border:0px solid #000;width:100px;margin-left:7px' >								
									
									<div id = 'avatarinfo' title='<?php echo kohana::lang('items.doubloon_name') ?>' >
										<div class='boxstatlabel' style='margin-left:4px' >
												<?php echo html::image('media/images/other/doubloon.png')?>
										</div>
										<div style='padding: 2px 0px 0px 2px'>
											&nbsp;&nbsp;&nbsp;<b><?php echo Session::instance()->get('doubloons', 0)?></b>&nbsp; 
										</div>
									</div>
									
									<div style='clear:both;margin:0;padding:0;height:0'>&nbsp;</div>

									<div id = 'avatarinfo' title='<?php echo kohana::lang('items.silvercoin_name') ?>' >
										<div class='boxstatlabel' style='margin-left:4px'>
											<?php echo html::image('media/images/other/silvercoin.png')?>
										</div>
										<div style='padding: 2px 0px 0px 4px'>
											&nbsp;&nbsp;&nbsp;<b><?php echo Session::instance()->get('silvercoins', 0)?></b>&nbsp; 
										</div>
									</div>
									
									<div style='clear:both;margin:0;padding:0;height:0'>&nbsp;</div>

									<div id = 'avatarinfo' title='<?php echo kohana::lang('items.coppercoin_name') ?>' >
										<div class='boxstatlabel' style='margin-left:4px' >
											<?php echo html::image('media/images/other/coppercoin.png')?>
										</div>
										<div style='padding: 2px 0px 0px 4px'>
											&nbsp;&nbsp;&nbsp;<b><?php echo Session::instance()->get('coppercoins', 0)?></b>&nbsp; 
										</div>
									</div>									
								</div>
							</div>
							
							
							
							<!-- visualizza action -->
							
							<?php
							$message = Session::instance()->get('pendingaction_l_message') ;
							if ( $message )
								$tooltip_text = My_I18n_Model::translate( $message ) . 
									'</br>' . 
									html::anchor ( '/character/cancel_action',	kohana::lang('global.cancel_action'), array( 'class' => 'st_common_command') );
									else
										$tooltip_text = kohana::lang('page.no_action');
							?>
							
							<div id='charaction'>
							<div class='boxstatlabel'>
									<?php 									
									echo html::image('media/images/other/crono.png');?>
								</div>								
								<div class='menutip'	style='width:150px;border:1px solid;padding:1px 0px 0px 2px;float:left;border:0px solid #000;' >
								<?php
									echo "<div id='actiondescription' title='" . $tooltip_text. "'></div>";
									echo "<div id='defaultCountdown' style='float:right;padding:0px;margin-right:5px;border:0px solid #000'></div>" ;
								?>														
								</div>	
							</div>
							
							<div style='clear:both;margin:0;padding:0;height:0;width:140px;'></div>

							<!-- visualizza barre -->
							<div id='charstats'>
								<?php 
									if ( $char )
									{
										$health = $char->health;
										$glut = $char->glut;
										$energy = $char->energy;
									}
									else
									{ $health=$glut=$energy=0; }
								?>
								
					
								<div class='mainstat'  title='<?php echo kohana::lang('character.health') .': ' . round( $health/100, 2 ) *100 . '%' ?>' >
									<div class='boxstatlabel'>
										<?php echo html::image('media/images/other/hearth.png') ?>
									</div>									
									<div class="boxstat">
										<div class="healthstat"style="width:<?php echo round(140*($health/100))?>px"></div>	
									</div>
								</div>
								
							<div style='clear:both;margin:0;padding:0;height:0;width:140px;'></div>
								
								<div class='mainstat' title='<?php echo kohana::lang('page.energy') .': ' . round( $energy/50, 2 ) *100 . '%' ?>'>
									<div class='boxstatlabel'>
										<?php echo html::image('media/images/other/energy.png') ?>
									</div>	
									<div class="boxstat"><div class="energystat"  style="width:<?php echo round(140*($energy/50))?>px"></div></div>
								</div>
								
							
							<div style='clear:both;margin:0;padding:0;height:0;width:140px;'></div>
								
								<div class='mainstat' title='<?php echo kohana::lang('page.glut') .': ' . round( $glut/50,2 ) *100 . '%' ?>' >				
									<div class='boxstatlabel'>
										<?php echo html::image('media/images/other/glut.png') ?>
									</div>	
									<div class="boxstat">
										<div class="glutstat" style="width:<?php echo round(140*($glut/50))?>px"></div>
									</div>			
								</div>
								
							</div>

							<div style='clear:both;margin:0;padding:0;height:0;width:140px;'></div>

							<?php } ?>		
							<hr class='hr2'/>
							<div id='menugame'>
								<?php echo $menu?>
							</div>
							<hr class='hr2'/>
							<div id='toplists'>			
							<div style='width:168px;text-align:center'>
							<?php							
							
							$toplist_id_c = Cfgtoplist_Model::hook_toplist('silvercoin');							
							$toplist_id_e = Cfgtoplist_Model::hook_toplist('energy');							
							
							if ( !is_null( $toplist_id_c ) or !is_null( $toplist_id_e ) )
								echo '<br/><b>' . kohana::lang('page.toplistvote') . '</b><br/><br/>' ;
														
							if ( !is_null ($toplist_id_c ) )
								echo html::anchor( 'toplist/vote/' . $toplist_id_c,	
									html::image('media/images/other/bonus_coins.png' ),
								array( 'target' => '_blank', 'class' => 'votingbutton') );
							
							if ( !is_null ($toplist_id_e ) )
								echo 
									html::anchor( 'toplist/vote/' . $toplist_id_e,
									html::image('media/images/other/bonus_energy.png', 
										array( 	'style' => 'margin-left:20px')),
									array( 'target' => '_blank', 'class' => 'votingbutton') );							
							?>
							</div>
							</div>
						</div>
						
						
						
						
						</div>
					</div>
					
					<?php } ?>
			</td>
		</tr>
		</table>

	</div>

	<div id="footer">
		<p>
			<div style="float:left; margin-left:40px;">
				<?php echo html::anchor('page/display/contacts',Kohana::lang('page.pagecontact')); ?> | 
				<?php echo html::anchor('page/display/credits',Kohana::lang('page.pagecredits')); ?> | 
				<?php echo html::anchor('page/display/tos',Kohana::lang('page.pagedisclaimer')); ?>
			</div>
		</p>
	</div>
</div>
</body>
</html>

