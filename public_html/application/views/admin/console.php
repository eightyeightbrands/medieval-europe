<script>
$(document).ready(function()
{
	
	$(".character").autocomplete({
		source: "index.php/jqcallback/listallchars",
		minLength: 2
	});	

	$(".region").autocomplete({
		source: "index.php/jqcallback/listallregions",
		minLength: 2
	});	
	
});	
</script>

<div class="pagetitle"><?php echo kohana::lang('admin.console')?></div>

<?php echo $submenu ?>

<fieldset>
<legend>Scegli Skin</legend>
<?= form::open() ?>
<?= form::dropdown('skin', array( 'classic' => 'Classic', 'new' => 'New') ); ?>
<?php echo form::submit( array( 
	'id' => 'ban', 
	'class' => 'button button-small', 
	'name' => 'selectskin', 
	'value' => 'Seleziona', 
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')'));?>
<?php echo form::close()?>
</fieldset>

<fieldset>
<legend>Resetta password personaggio a 1234</legend>
<?php echo form::open() ?>

Nome Personaggio: <?php echo form::input( array( 'id' => 'charactername', 'class' => 'input-large character', 'name' => 'charactername'));?>
<div class='center'>
<?php echo form::submit( array( 
	'id' => 'ban', 
	'class' => 'button button-small', 
	'name' => 'resetpassword', 
	'value' => 'Resetta', 
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')'));?>
</div>
<?php echo form::close()?>
</fieldset>

<fieldset>
<legend>Resuscita un personaggio</legend>
<?php echo form::open() ?>

Nome Personaggio: <?php echo form::input( array( 'id' => 'charactername', 'class' => 'input-large', 'name' => 'charactername'));?>
<br/>
Recupero Pagato? 
<?= form::dropdown('ispaid', array( true => 'Sì', false => 'No') ); ?>
<br/>
Anonimizzare?
<?= form::dropdown('anonymize', array( true => 'Sì', false => 'No') ); ?>
<br/>
Regione di Nascita (Obbligatorio)
<?php echo form::input( array( 'id' => 'regionname', 'class' => 'region input-large', 'name' => 'regionname'));?>
<br/>
Nuovo Nome  (Obbligatorio)
<?php echo form::input( array( 'id' => 'newname', 'class' => 'input-large', 'name' => 'newname'));?>
</fieldset>
<div class='center'>
<?php echo form::submit( array( 
	'id' => 'ban', 
	'class' => 'button button-small', 
	'name' => 'restorechar', 
	'value' => 'Resuscita', 
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')'));?>
</div>
<?php echo form::close()?>


<fieldset>
<legend>Banna un personaggio dal gioco</legend>
<?php echo form::open() ?>

Nome Personaggio: <?php echo form::input( array( 'id' => 'charactername', 'class' => 'character input-large', 'name' => 'charactername'));?>
<br/>
Causale: <?php echo form::input( array( 'id' => 'banreason', 'class' => 'input-xlarge', 'name' => 'banreason'));?>
<br/>
Banna fino a: <?php echo form::input( array( 'id' => 'bandate', 'name' => 'bandate', 'class' => 'input-normal right', 'placeholder' => 'dd-mm-yyyy hh:mm:ss')); ?>
<div class='center'>
<?php echo form::submit( array( 
	'id' => 'ban', 
	'class' => 'button button-small', 
	'name' => 'bancharactergame', 
	'value' => 'Banna', 
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')'));?>
</div>
<?php echo form::close()?>
</fieldset>

<fieldset>
<legend>Ban a character from chat</legend>
<?php echo form::open() ?>

Name: <?php echo form::input( array( 'id' => 'charactername', 'class' => 'character input-large', 'name' => 'charactername'));?>
<br/>
Reason: <?php echo form::input( array( 'id' => 'banreason', 'class' => 'input-xlarge', 'name' => 'banreason'));?>
<br/>
Ban upto: <?php echo form::input( array( 'id' => 'bandate', 'name' => 'bandate', 'class' => 'input-normal right', 'placeholder' => 'dd-mm-yyyy hh:mm:ss')); ?>
<div class='center'>
<?php echo form::submit( array( 
	'id' => 'ban', 
	'class' => 'button button-small', 
	'name' => 'bancharacterchat', 
	'value' => 'Banna', 
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')'));?>
</div>
<?php echo form::close()?>
</fieldset>


<fieldset>
<legend>Unblock suspended actions</legend>
<?php echo form::open() ?>
<div class='center'>
<?php echo form::submit( array( 'id' => 'unblockactions', 'class' => 'button button-small', 'name' => 'unblockactions', 'value' => 'Sblocca', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')'));?>
</div>
<?php echo form::close()?>
</fieldset>

<fieldset>
<legend>Change character name</legend>
<i>Change character name on game and forum.</i>
<?php echo form::open() ?>
Old name
<?php echo form::input( array( 'id'=>'oldcharactername', 'class' => 'character', 'name' => 'oldcharactername', 'style'=>'width:300px') ); ?>
<br/>
New name
<?php echo form::input( array( 'id' => 'newcharactername', 'name' => 'newcharactername', 'style'=>'width:300px') ); ?>
<br/>
<br/>
<div class='center'>
<?php echo form::submit( array( 'id' => 'changename', 'class' => 'button button-small', 'name' => 'changename', 'value' => 'Change', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')'));?>
</div>
<?php echo form::close()?>
</fieldset>

<fieldset>
<legend>Change character email</legend>
<i>Change character email on game and forum.</i>
<?php echo form::open() ?>
Character Name
<?php echo form::input( array( 'id'=>'oldcharactername', 'class' => 'character', 'name' => 'charactername', 'style'=>'width:300px') ); ?>
<br/>
New email
<?php echo form::input( array( 'id' => 'newemail', 'name' => 'newemail', 'style'=>'width:300px') ); ?>
<br/>
<br/>
<div class='center'>
<?php echo form::submit( array( 'id' => 'changeemail', 'class' => 'button button-small', 'name' => 'changeemail', 'value' => 'Change', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')'));?>
</div>
<?php echo form::close()?>
</fieldset>

<fieldset>
<legend>Kill a Character</legend>
<i><span class='evidence'>Kill a character from game and forum.</span></i>
<?php echo form::open() ?>
Name
<?php	echo form::input( array( 'id'=>'character', 'class' => 'character', 'name' => 'character', 'style'=> 'width:300px') ); ?>
<br/>
<br/>
<div class='center'>
<?php echo form::submit( array( 'id' => 'kill', 'class' => 'button button-small', 'name' => 'kill', 'value' => 'Kill', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')'));?>
</div>
<?php echo form::close()?>
</fieldset>


<br style="clear:both;" />
