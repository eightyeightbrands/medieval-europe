
<?php
	$char = Character_Model::get_info( Session::instance() -> get('char_id') );		
?>

<div class="pagetitle"><?php echo kohana::lang('quests.welcomepagetitle')?></div>

			<br/>
			<div>
				<div id='frame' style='float:left;margin-right:1%'>
					<?php echo html::image('media/images/template/quests/avatar_ginevra.png', 
					array('class' => 'charpic') )?>
				</div>		
			
				<div style='float:left;width:70%;'>

					<?php 
					echo kohana::lang('quests.welcomepagetext',
					$char -> name,
					kohana::lang($char -> region -> name));		
					?>
				</div>
			</div>
			
			<div style='clear:both'></div>

			<div class='right'><?php echo html::image( 'media/images/template/quests/firma_ginevra.png' );?></div>
						
			<div style='margin:5px 50px' class='center'>
				<?php echo html::anchor
					(
					'/quests/activatetutorial', 
					kohana::lang('quests.welcomepage-activatetutorial'),
						array('class' => 'button button-medium')
					); ?>
								
					
				<?php echo html::anchor(
					'page/display/game-rules', 
					 kohana::lang('page.gamerules'), 
							array(
								'class' => 'button button-medium',
								'target' => 'new' )
					); ?>
					
				<?php echo html::anchor(
					'https://wiki.medieval-europe.eu/index.php?title=Communication_Rules', 
					kohana::lang('page.communicationrules'), 
							array(
								'class' => 'button button-medium',
								'target' => 'new' )
					); ?>
					
			</div>			
	
<br style='clear:both'/>
