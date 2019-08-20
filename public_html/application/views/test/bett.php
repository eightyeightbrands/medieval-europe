<script type='text/javascript'>

$(function(){
$('#debugmode').change(function()
{
	
	if($(this).is(":checked")) 
	{
		$('#costindoubloons').text('15');
		$('#costinsilvercoins').text('45');
		$('#repeats').val(1);
	}
	else
	{
		$('#costindoubloons').text('5');
		$('#costinsilvercoins').text('15');
	}
})
});
</script>

<div class="pagetitle">Test - Fight Report</div>

<i> BETT - v 1.2 - originally inspired by <?php echo Character_Model::create_publicprofilelink( null, 'Aegis Rex') ?></i>
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

<br style='clear:both'/>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>

<?php echo form::open();?>

<div style='width:50%;float:left;'>
<fieldset>
<legend>Setup Your Equipment</legend>
<?php echo form::label('fighter1', 'Fighter: ' ); ?>
<?php echo form::input( array('name' => 'fighter1', 'class' => 'input-large', 'value' => $form['fighter1'] ) ); ?>
<br/>
<?php echo form::label('fighter1', 'Health: ' ); ?>
<?php echo form::input( array('name' => 'health1', 'class' => 'input-xsmall', 'value' => $form['health1'] ) ); ?>
<br/>
<?php echo form::label('fighter1', 'Energy: ' ); ?>
<?php echo form::input( array('name' => 'energy1', 'class' => 'input-xsmall', 'value' => $form['energy1'] ) ); ?>
<br/>
<?php echo form::label('fighter1', 'Strength: ' ); ?>
<?php echo form::input( array('name' => 'str1', 'class' => 'input-xsmall', 'value' => $form['str1'] ) ); ?>
<br/>
<?php echo form::label('fighter1', 'Dexterity: ' ); ?>
<?php echo form::input( array('name' => 'dex1', 'class' => 'input-xsmall', 'value' => $form['dex1'] ) ); ?>
<br/>
<?php echo form::label('fighter1', 'Intelligence: ' ); ?>
<?php echo form::input( array('name' => 'intel1', 'class' => 'input-xsmall', 'value' => $form['intel1'] ) ); ?>
<br/>
<?php echo form::label('fighter1', 'Constitution: ' ); ?>
<?php echo form::input( array('name' => 'cost1', 'class' => 'input-xsmall', 'value' => $form['cost1'] ) ); ?>
<br/>
<?php echo form::label('weapon1', 'Weapon: ' ); ?>
<?php echo form::dropdown( 'weapon1', $listweapons, $form['weapon1'] ); ?>
<br/>
<?php echo form::label('armorhead1', 'Armor, Head: ' ); ?>
<?php echo form::dropdown( 'armorhead1', $listarmors['head'], $form['armorhead1'] ); ?>

<br/>
<?php echo form::label('armorlegs1', 'Armor, Legs: ' ); ?>
<?php echo form::dropdown( 'armorlegs1', $listarmors['legs'], $form['armorlegs1'] ); ?>

<br/>
<?php echo form::label('armorfeet1', 'Armor, Feet: ' ); ?>
<?php echo form::dropdown( 'armorfeet1', $listarmors['feet'], $form['armorfeet1'] ); ?>

<br/>
<?php echo form::label('armorshield1', 'Armor, Shield: ' ); ?>
<?php echo form::dropdown( 'armorshield1', $listarmors['left_hand'], $form['armorshield1'] ); ?>
</fieldset>
</div>


<div style='width:49%;float:left;margin-left:1%'>
<fieldset>
<legend>Setup Sparring Partner Equipment </legend>
<?php echo form::label('fighter2', 'Fighter: ' ); ?>
<?php echo form::input( array('name' => 'fighter2', 'class' => 'input-large', 'value' => $form['fighter2'] ) ); ?>
<br/>
<?php echo form::label('fighter2', 'Health: ' ); ?>
<?php echo form::input( array('name' => 'health2', 'class' => 'input-xsmall', 'value' => $form['health2'] ) ); ?>
<br/>
<?php echo form::label('fighter2', 'Energy: ' ); ?>
<?php echo form::input( array('name' => 'energy2', 'class' => 'input-xsmall', 'value' => $form['energy2'] ) ); ?>
<br/>
<?php echo form::label('fighter2', 'Strength: ' ); ?>
<?php echo form::input( array('name' => 'str2', 'class' => 'input-xsmall', 'value' => $form['str2'] ) ); ?>
<br/>
<?php echo form::label('fighter2', 'Dexterity: ' ); ?>
<?php echo form::input( array('name' => 'dex2', 'class' => 'input-xsmall', 'value' => $form['dex2'] ) ); ?>
<br/>
<?php echo form::label('fighter2', 'Intelligence: ' ); ?>
<?php echo form::input( array('name' => 'intel2', 'class' => 'input-xsmall', 'value' => $form['intel2'] ) ); ?>
<br/>
<?php echo form::label('fighter2', 'Constitution: ' ); ?>
<?php echo form::input( array('name' => 'cost2', 'class' => 'input-xsmall', 'value' => $form['cost2'] ) ); ?>
<br/>
<?php echo form::label('weapon2', 'Weapon: ' ); ?>
<?php echo form::dropdown( 'weapon2', $listweapons, $form['weapon2'] ); ?>
<br/>
<?php echo form::label('armorhead2', 'Armor, Head: ' ); ?>
<?php echo form::dropdown( 'armorhead2', $listarmors['head'], $form['armorhead2'] ); ?>

<br/>
<?php echo form::label('armorlegs2', 'Armor, Legs: ' ); ?>
<?php echo form::dropdown( 'armorlegs2', $listarmors['legs'], $form['armorlegs2'] ); ?>

<br/>
<?php echo form::label('armorfeet2', 'Armor, Feet: ' ); ?>
<?php echo form::dropdown( 'armorfeet2', $listarmors['feet'], $form['armorfeet2'] ); ?>

<br/>
<?php echo form::label('armorshield2', 'Armor, Shield: ' ); ?>
<?php echo form::dropdown( 'armorshield2', $listarmors['left_hand'], $form['armorshield2'] ); ?>
</fieldset>
</div>

<br style='clear:both;'/>

<div>
<?php echo form::label('repeats', 'Repeat how many times? ' ); ?>
<?php echo form::input( array( 'id' => 'repeats', 'name' => 'repeats', 'class' => 'input-xsmall', 'value' => $form['repeats'] ) ); ?>
&nbsp;
<?php echo form::label('debugmode', 'Debug Mode?: ' ); ?>
<?php echo form::checkbox( 'debugmode', 'debug' ) ?>&nbsp;
Cost in Doubloons: <b><span id='costindoubloons'>5</span></b>&nbsp;
Cost in Silvercoins:<b><span id='costinsilvercoins'>15</span></b>
</div>


<div style='margin-top:10px' class='center'>
<?php echo form::submit( array ('id' => 'submit', 'class' => 'button button-medium' , 'name'=>'fightd' ), 'Fight (Pay with Doubloons)' );?>
<?php echo form::submit( array ('id' => 'submit', 'class' => 'button button-medium' , 'name'=>'fightsc' ), 'Fight (Pay with Silver Coins)' );?>
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
<?php if ( isset( $data['wins']['none']) )
{?>
Ties: <?php echo $data['fighter1']['ties']; ?><br/>
<?php } ?>
Total Rounds:  <?php echo $data['totalrounds']; ?><br/>
Total Critical hits: <?php echo $data['totalcriticalhits']['total']; ?><br/>
Wins: <?php echo $data['wins'][$data['fighter1']]['wins']; ?> (<?php echo $data['wins'][$data['fighter1']]['wins']/$data['repeats']*100; ?>%)<br/>
Critical Hits: <?php echo $data['totalcriticalhits'][$data['fighter1']]; ?> (<?php 
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
<?php if ( isset( $data['wins']['none']) )
{?>
Ties: <?php echo $data['fighter2']['ties']; ?><br/>
<?php } ?>
Total Rounds:  <?php echo $data['totalrounds']; ?><br/>
Total Critical hits: <?php echo $data['totalcriticalhits']['total'];?><br/>
Wins <?php echo $data['fighter2']; ?>: <?php echo $data['wins'][$data['fighter2']]['wins']; ?> (<?php echo $data['wins'][$data['fighter2']]['wins']/$data['repeats']*100; ?>%)<br/>
Critical Hits: <?php echo $data['totalcriticalhits'][$data['fighter2']]; ?> (<?php 
	if ($data['totalcriticalhits']['total'] > 0) 
		echo $data['totalcriticalhits'][$data['fighter2']]/$data['totalcriticalhits']['total']*100?>%)<br/>
Stunned Received: <?php echo $data['totalstunreceived'][$data['fighter2']]; ?><br/>
Stunned Rounds: <?php echo $data['totalstunnedrounds'][$data['fighter2']]; ?>
<?php if ( $data['totalstunreceived'][$data['fighter2']] > 0 )
echo ", Average: " . $data['totalstunnedrounds'][$data['fighter2']]/$data['totalstunreceived'][$data['fighter2']]; 
?>

</div>
<?php } ?>
</fieldset>
<br/>
<fieldset>
<legend>Round (only 1st match)</legend>
<?php echo $data['report']; ?>
</fieldset>

<br/> 
