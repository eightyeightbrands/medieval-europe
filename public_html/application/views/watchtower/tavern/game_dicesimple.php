<div class="pagetitle"><?php echo kohana::lang("structures_tavern.game_dicesimple_pagetitle");?></div>
<?php echo $submenu ?>
<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>


<br/>

<div class='submenu'>
<?php echo html::anchor('/tavern/game_dice/'.$structure->id . '/simple', kohana::lang('structures_tavern.game_dicesimple'), array('class' => 'selected' ));?>
&nbsp;
<?php echo html::anchor('/tavern/game_dice/'.$structure->id . '/elite', kohana::lang('structures_tavern.game_diceelite'))?>
</div>
<br/>
<div id='helperwithpic'>
<div id='locationpic'>
<?php echo html::image('media/images/template/locations/tavern_dices.jpg') ?>
</div>

<div id='helper'>
<?php echo kohana::lang('structures_tavern.game_dicesimple_helper', $jackpot, 
html::anchor('/tavern/game_dice/' . $structure -> id . '/elite', kohana::lang('structures_tavern.room') ),
html::anchor('/tavern/show_winners/' . $structure -> id . '/dicesimple', kohana::lang('structures_tavern.winners')) ); ?>
</div>
<br style='clear:both'/>
<div id='caption'>Tavern Scene - David Teniers the Younger (1610-1690)</div>
</div>

<br/><br/>
<p class='center'>
<?php echo kohana::lang('structures_tavern.dice_dicelaunch') ?>
</p>



<?php

	echo form::open();
	echo form::hidden('structure_id', $structure -> id );
	echo form::hidden('type', 'simple' );
	echo '<center>';
	echo form::submit( array (
	'id' => 'submit', 
	'class' => 'button button-medium', 	
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('structures_tavern.launch_dices'));
	echo '</center>';
	echo form::close();
	

?>
