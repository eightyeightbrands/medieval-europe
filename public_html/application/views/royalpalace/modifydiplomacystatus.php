<div class="pagetitle">
<?php echo kohana::lang('diplomacy.modifydiplomacystatus'); ?>
</div>

<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'>
<div id='helpertext'>
<?php 
	echo kohana::lang('diplomacy.modifydiplomacystatus_helper', 
		kohana::lang( $diplomacystatusinfo -> targetkingdom_name)	); ?>
</div>
<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=Diplomacy',
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button' ) ) 
		?>
</div>
</div>

<br style='clear:both'/>

<div class='submenu'>
<?php echo html::anchor('royalpalace/diplomacy/' . $structure->id, kohana::lang('structures_royalpalace.submenu_diplomacy'), array('class' => 'selected' ));?>
&nbsp;
<?php echo html::anchor('royalpalace/giveaccesspermit/' . $structure->id, kohana::lang('structures_royalpalace.submenu_giveaccesspermit'))?>
</div>

<br/>
<fieldset>
<div class='center'>
<?php echo form::open() ?>
<?php echo form::hidden('structure_id', $structure->id) ?>
<?php echo form::hidden('diplomacystatus_id', $diplomacystatusinfo -> id ) ?>

<?php echo kohana::lang('diplomacy.currentstatus', kohana::lang('diplomacy.' . $diplomacystatusinfo -> type )) ?>

&nbsp;

<?php echo kohana::lang('diplomacy.newtype') ?>: &nbsp;

<?php echo form::dropdown( 'type', array( 
	'neutral' => kohana::lang('diplomacy.neutral'),
	'friendly' => kohana::lang('diplomacy.friendly'),
	'allied' => kohana::lang('diplomacy.allied'),
	'hostile' => kohana::lang('diplomacy.hostile') ),
	$form['type']
	
	);
?>

<?php echo form::submit(array ('id'=>'submit', 'class' => 'button button-medium', 
'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 
'name'=>'edit', 'value'=> kohana::lang('global.edit')))		?>
</div>
</fieldset>

<br style='clear:both'/>
