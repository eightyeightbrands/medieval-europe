<div class="pagetitle"><?php echo kohana::lang("structures_tavern.showwinners_pagetitle");?></div>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>


<div class='submenu'>
<?php echo html::anchor('/tavern/game_dice/'.$structure->id . '/simple', kohana::lang('structures_tavern.game_dicesimple'));?>
&nbsp;
<?php echo html::anchor('/tavern/game_dice/'.$structure->id . '/elite', kohana::lang('structures_tavern.game_diceelite'), 
	array('class' => 'selected' ))?>
</div>

<br/>

<div id='helperwithpic'>
<div id='locationpic'>
<?php echo html::image('media/images/template/locations/tavern_dices.jpg') ?>
</div>
<div id='helper'>
<?php echo kohana::lang('structures_tavern.winners_helper'); ?>	
</div>
<br style='clear:both'/>
<div id='caption'>Tavern Scene - David Teniers the Younger (1610-1690)</div>
</div>
<br/>
<?php if ( count( $winners ) == 0 ) 
{
?>

<p class='center'><?php echo kohana::lang('structures_tavern.nowinners') ?></p>

<?php
}
else
{
	echo "<p>";
	foreach ( $winners as $winner )
		echo kohana::lang( 'structures_tavern.winners_' . $type, 
			$winner -> winner,
			$winner -> amount,
			Utility_Model::format_date( $winner -> windate ),
			kohana::lang( $winner -> region_name ) ) . '<br/>';	
		echo "</p>";
	}
?>
<br style='clear:both'/>
