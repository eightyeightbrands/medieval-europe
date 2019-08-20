<script type='text/javascript'>

$(function(){
$('#debugmode').change(function()
{
	
	if($(this).is(":checked")) 
	{
		$('#costindoubloons').text('5');
		$('#costinsilvercoins').text('15');
		if ($('#repeats').text() > 1 )
			$('#repeats').val(1);
	}
	else
	{
		$('#costindoubloons').text('2');
		$('#costinsilvercoins').text('6');
	}
	

	
}),

$(".roundanchor").click( function () 
{	
	id = $(this).attr('id');
	//console.log($(this).attr('id'));    
	$("#round-"+id).show().siblings("div").hide();	
});

});
</script>

<div class="pagetitle">Test - Fight Report</div>

<i> BETT - v 1.3 - originally inspired by <?php echo Character_Model::create_publicprofilelink( null, 'Aegis Rex') ?></i>
<br/><br/>

<style>
label { 
	color:dark	blue;
}
</style>

<div id='helperwithpic'>
<div id='locationpic'>
<?php echo html::image('media/images/template/locations/trainingground.jpg') ?>
</div>
<div id='helper'>
<?php echo kohana::lang('structures_trainingground.sparringpartner_helper') ?>
</div>
</div>

<br style='clear:both'/><br/>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/><br/>

<?php echo form::open();?>
<?php echo form::hidden('structure_id', $structure_id);?>

<div style='width:50%;float:left;'>
<fieldset>
<legend><?php kohana::lang('structures_trainingground.setupequipment')?></legend>
<?php echo form::label('fighter1', kohana::lang('structures_trainingground.fighter') ); ?>
<?php echo form::input( array('name' => 'fighter1', 'class' => 'input-normal', 'readonly' => true, 'value' => $form['fighter1'] ) ); ?>
<br/>
<?php echo form::label('fightmode1', kohana::lang('structures_battlefield.currentfightmode') ); ?>
<?php echo form::dropdown( 'fightmode1', 
	array(
		'normal' => 'Normal',
		'defend' => 'Defend',
		'attack' => 'Attack'	
		),
		$form['fightmode1']
		);?>
<br/>
<?php echo form::label('staminaboost1', kohana::lang('character.staminaboost') ); ?>
<?php echo form::dropdown( 'staminaboost1', 
	array(
		false => 'No',		
		true => 'Yes',		
		$form['staminaboost1']
		));?>
<br/>
<?php echo form::label('parry1', kohana::lang('character.parryskillproficiency') ); ?>
<?php echo form::input( array('name' => 'parry1', 'class' => 'input-xsmall', 'value' => $form['parry1'] ) ); ?>
<br/>
<?php echo form::label('health1', kohana::lang('character.health') ); ?>
<?php echo form::input( array('name' => 'health1', 'class' => 'input-xsmall', 'value' => $form['health1'] ) ); ?>
<br/>
<?php echo form::label('faithlevel1', kohana::lang('religion.faith') ); ?>
<?php echo form::input( array('name' => 'faithlevel1', 'class' => 'input-xsmall', 'value' => $form['faithlevel1'] ) ); ?>
<br/>
<?php echo form::label('energy1', kohana::lang('global.energy') ); ?>
<?php echo form::input( array('name' => 'energy1', 'class' => 'input-xsmall', 'value' => $form['energy1'] ) ); ?>
<br/>
<?php echo form::label('str1', kohana::lang('character.create_charstr') ); ?>
<?php echo form::input( array('name' => 'str1', 'class' => 'input-xsmall', 'value' => $form['str1'] ) ); ?>
<br/>
<?php echo form::label('dex1', kohana::lang('character.create_chardex') ); ?>
<?php echo form::input( array('name' => 'dex1', 'class' => 'input-xsmall', 'value' => $form['dex1'] ) ); ?>
<br/>
<?php echo form::label('intel1', kohana::lang('character.create_charintel') ); ?>
<?php echo form::input( array('name' => 'intel1', 'class' => 'input-xsmall', 'value' => $form['intel1'] ) ); ?>
<br/>
<?php echo form::label('cost1', kohana::lang('character.create_charcost') ); ?>
<?php echo form::input( array('name' => 'cost1', 'class' => 'input-xsmall', 'value' => $form['cost1'] ) ); ?>
<br/>
<?php echo form::label('weapon1', kohana::lang('structures_trainingground.weapon') ); ?>
<?php echo form::dropdown( 'weapon1', $listweapons, $form['weapon1'] ); ?>
<br/>
<?php echo form::label('armorhead1', kohana::lang('structures_trainingground.armorhead') ); ?>
<?php echo form::dropdown( 'armorhead1', $listarmors['head'], $form['armorhead1'] ); ?>

<br/>
<?php echo form::label('armortorso1', kohana::lang('structures_trainingground.armortorso') ); ?>
<?php echo form::dropdown( 'armortorso1', $listarmors['torso'], $form['armortorso1'] ); ?>

<br/>
<?php echo form::label('armorlegs1', kohana::lang('structures_trainingground.armorlegs') ); ?>
<?php echo form::dropdown( 'armorlegs1', $listarmors['legs'], $form['armorlegs1'] ); ?>

<br/>
<?php echo form::label('armorfeet1', kohana::lang('structures_trainingground.armorfeet') ); ?>
<?php echo form::dropdown( 'armorfeet1', $listarmors['feet'], $form['armorfeet1'] ); ?>

<br/>
<?php echo form::label('armorshield1', kohana::lang('structures_trainingground.armorshield') ); ?>
<?php echo form::dropdown( 'armorshield1', $listarmors['left_hand'], $form['armorshield1'] ); ?>
</fieldset>
</div>

<div style='width:49%;float:left;margin-left:1%'>
<fieldset>
<legend><?php kohana::lang('structures_trainingground.setupequipment')?></legend>
<?php echo form::label('fighter2', kohana::lang('structures_trainingground.fighter') ); ?>
<?php echo form::input( array('name' => 'fighter2', 'class' => 'input-normal', 'value' => $form['fighter2'] ) ); ?>
<br/>
<?php echo form::label('fightmode2', kohana::lang('structures_battlefield.currentfightmode') ); ?>
<?php echo form::dropdown( 'fightmode2', 
	array(
		'normal' => 'Normal',
		'defend' => 'Defend',
		'attack' => 'Attack'	
		),
		$form['fightmode2']
		);?>
<br/>
<?php echo form::label('staminaboost2', kohana::lang('character.staminaboost') ); ?>
<?php echo form::dropdown( 'staminaboost2', 
	array(
		false => 'No',
		true => 'Yes',
		$form['staminaboost2']
		));?>
<br/>
<?php echo form::label('parry2', kohana::lang('character.parryskillproficiency') ); ?>
<?php echo form::input( array('name' => 'parry2', 'class' => 'input-xsmall', 'value' => $form['parry2'] ) ); ?>		
<br/>
<?php echo form::label('health2', kohana::lang('character.health') ); ?>
<?php echo form::input( array('name' => 'health2', 'class' => 'input-xsmall', 'value' => $form['health2'] ) ); ?>
<br/>
<?php echo form::label('faithlevel2', kohana::lang('religion.faith') ); ?>
<?php echo form::input( array('name' => 'faithlevel2', 'class' => 'input-xsmall', 'value' => $form['faithlevel2'] ) ); ?>
<br/>
<?php echo form::label('energy2', kohana::lang('global.energy') ); ?>
<?php echo form::input( array('name' => 'energy2', 'class' => 'input-xsmall', 'value' => $form['energy2'] ) ); ?>
<br/>
<?php echo form::label('str2', kohana::lang('character.create_charstr') ); ?>
<?php echo form::input( array('name' => 'str2', 'class' => 'input-xsmall', 'value' => $form['str2'] ) ); ?>
<br/>
<?php echo form::label('dex2', kohana::lang('character.create_chardex') ); ?>
<?php echo form::input( array('name' => 'dex2', 'class' => 'input-xsmall', 'value' => $form['dex2'] ) ); ?>
<br/>
<?php echo form::label('intel2', kohana::lang('character.create_charintel') ); ?>
<?php echo form::input( array('name' => 'intel2', 'class' => 'input-xsmall', 'value' => $form['intel2'] ) ); ?>
<br/>
<?php echo form::label('cost2', kohana::lang('character.create_charcost') ); ?>
<?php echo form::input( array('name' => 'cost2', 'class' => 'input-xsmall', 'value' => $form['cost2'] ) ); ?>
<br/>
<?php echo form::label('weapon2', kohana::lang('structures_trainingground.weapon') ); ?>
<?php echo form::dropdown( 'weapon2', $listweapons, $form['weapon2'] ); ?>
<br/>
<?php echo form::label('armorhead2', kohana::lang('structures_trainingground.armorhead') ); ?>
<?php echo form::dropdown( 'armorhead2', $listarmors['head'], $form['armorhead2'] ); ?>

<br/>
<?php echo form::label('armortorso2', kohana::lang('structures_trainingground.armortorso') ); ?>
<?php echo form::dropdown( 'armortorso2', $listarmors['torso'], $form['armortorso2'] ); ?>

<br/>
<?php echo form::label('armorlegs2', kohana::lang('structures_trainingground.armorlegs') ); ?>
<?php echo form::dropdown( 'armorlegs2', $listarmors['legs'], $form['armorlegs2'] ); ?>

<br/>
<?php echo form::label('armorfeet2', kohana::lang('structures_trainingground.armorfeet') ); ?>
<?php echo form::dropdown( 'armorfeet2', $listarmors['feet'], $form['armorfeet2'] ); ?>

<br/>
<?php echo form::label('armorshield2', kohana::lang('structures_trainingground.armorshield') ); ?>
<?php echo form::dropdown( 'armorshield2', $listarmors['left_hand'], $form['armorshield2'] ); ?>
</fieldset>
</div>

<br style='clear:both;'/>

<div>
<?php echo form::label('repeats', kohana::lang('structures_trainingground.repeattimes') ); ?>
<?php echo form::input( array( 'id' => 'repeats', 'name' => 'repeats', 'class' => 'input-xsmall', 'value' => $form['repeats'] ) ); ?>
&nbsp;
<?php echo form::label('debugmode', kohana::lang('structures_trainingground.debugmode') ); ?>
<?php echo form::checkbox( 'debugmode', 'debug' ) ?>&nbsp;
<?php echo kohana::lang('structures_trainingground.costindoubloons') ?><b><span id='costindoubloons'>5</span></b>&nbsp;
<?php echo kohana::lang('structures_trainingground.costinsilvercoins') ?><b><span id='costinsilvercoins'>15</span></b>
</div>

<div style='margin-top:10px' class='center'>
<?php echo form::submit( array (
	'id' => 'submit', 
	'class' => 'button button-large' , 
	'name'=>'fightd',
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ),
	kohana::lang('structures_trainingground.traindoubloons') );?>
	
<?php echo form::submit( array (
	'id' => 'submit', 
	'class' => 'button button-large' , 
	'name'=>'fightsc',
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ),
	kohana::lang('structures_trainingground.trainsilvercoins') );?>
</div>

<?php echo form::close()?>

<br/>

<hr/>

<fieldset>

<legend>Stats</legend>
<?php if ( isset( $data ) )
{
?>
<div style='width:50%;float:left;'>
<h3><?php echo $data['fighter1']?></h3>
Repeats:  <?php echo $data['repeats']; ?><br/>
Elapsed: <?php echo $data['elapsed'] ;?> secs<br/>
Total Rounds:  <?php echo $data['totalrounds']; ?> Avg:<?php echo $data['totalrounds']	/$data['repeats']; ?><br/>
Elapsed per Rounds: <?php echo $data['elapsedperround']; ?><br/>
Hits (Missed/Blocked/Total): 
<?php if ($data['totalhits'][$data['fighter1']]['total'] > 0) 
{ 
?>
	<?php 
	echo $data['totalhits'][$data['fighter1']]['missed'] . "/" . $data['totalhits'][$data['fighter1']]['blocked'] ."/" . $data['totalhits'][$data['fighter1']]['total']; 
	?>
	( 
	<?php 
		echo round(
			(
				$data['totalhits'][$data['fighter1']]['missed'] + 
				$data['totalhits'][$data['fighter1']]['blocked']
			)
			/
			$data['totalhits'][$data['fighter1']]['total']*100,2 ); 
	?>%)<br/>
<?php } ?>
<?php if ( isset( $data['wins']['none']) )
{?>
Ties: <?php echo $data['fighter1']['ties']; ?><br/>
<?php 
} ?>
<?php } ?>	

<? if (!is_null($data) ) { ?>
Total damage dealt: <?php echo $data['totaldamage'][$data['fighter1']]['total']; ?> Avg: <?php echo $data['totaldamage'][$data['fighter1']]['total']/$data['repeats'] ?><br/>
Wins: <?php echo $data['wins'][$data['fighter1']]['wins']; ?> (<?php echo $data['wins'][$data['fighter1']]['wins']/$data['repeats']*100; ?>%)<br/>
Critical Hits: <?php echo $data['totalcriticalhits'][$data['fighter1']]; ?>/<?php echo $data['totalcriticalhits']['total']?> (<?php 
	if ($data['totalcriticalhits']['total'] > 0) 
		echo $data['totalcriticalhits'][$data['fighter1']]/$data['totalcriticalhits']['total']*100?>%)<br/>
Stunned Received: <?php echo $data['totalstunreceived'][$data['fighter1']]; ?><br/>
Stunned Rounds: <?php echo $data['totalstunnedrounds'][$data['fighter1']]; ?>
<?php if ( $data['totalstunreceived'][$data['fighter1']] > 0 )
echo ", Average: " . $data['totalstunnedrounds'][$data['fighter1']]/$data['totalstunreceived'][$data['fighter1']]; 
?>
</div>
<div style='width:50%;float:left;'>
<h3><?php echo $data['fighter2']?></h3>
Repeats:  <?php echo $data['repeats']; ?><br/>
Elapsed: <?php echo $data['elapsed'] ;?> secs<br/>
<?php if ( isset( $data['wins']['none']) )
{?>
Ties: <?php echo $data['fighter2']['ties']; ?><br/>
<?php } ?>
Total Rounds:  <?php echo $data['totalrounds']; ?> Avg:<?php echo $data['totalrounds']/$data['repeats']; ?><br/>
Elapsed per Rounds: <?php echo $data['elapsedperround']; ?><br/>
Hits (Missed/Blocked/Total): 
<?php if ($data['totalhits'][$data['fighter2']]['total'] > 0) 
{ 
?>
	<?php 
	echo $data['totalhits'][$data['fighter2']]['missed'] . "/" . $data['totalhits'][$data['fighter2']]['blocked'] ."/" . $data['totalhits'][$data['fighter2']]['total']; 
	?>
	( 
	<?php 
		echo round(
			(
				$data['totalhits'][$data['fighter2']]['missed'] + 
				$data['totalhits'][$data['fighter2']]['blocked']
			)
			/
			$data['totalhits'][$data['fighter2']]['total']*100,2 ); 
	?>%)<br/>
<?php } ?>
Total damage dealt: <?php echo $data['totaldamage'][$data['fighter2']]['total']; ?> Avg: <?php echo $data['totaldamage'][$data['fighter2']]['total']/$data['repeats']; ?><br/>
Wins: <?php echo $data['wins'][$data['fighter2']]['wins']; ?> (<?php echo $data['wins'][$data['fighter2']]['wins']/$data['repeats']*100; ?>%)<br/>
Critical Hits: <?php echo $data['totalcriticalhits'][$data['fighter2']]; ?>/<?php echo $data['totalcriticalhits']['total']?> (<?php 
	if ($data['totalcriticalhits']['total'] > 0) 
		echo $data['totalcriticalhits'][$data['fighter2']]/$data['totalcriticalhits']['total']*100?>%)<br/>
Stunned Received: <?php echo $data['totalstunreceived'][$data['fighter2']]; ?><br/>
Stunned Rounds: <?php echo $data['totalstunnedrounds'][$data['fighter2']]; ?>

<?php if ( $data['totalstunreceived'][$data['fighter2']] > 0 )
echo ", Average: " . $data['totalstunnedrounds'][$data['fighter2']]/$data['totalstunreceived'][$data['fighter2']]; 
?>
</div>

</fieldset>
<br/>
<fieldset>
<legend>Match</legend>
<div>
<?php 
	echo $data['report'];
?>
<br style='clear:both'/>
</fieldset>
<? } ?>
<br/> 
