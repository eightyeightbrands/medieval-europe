<head>
<script>

$(document).ready(function()
{	
	$("#nominated1").autocomplete( {
			source: "index.php/jqcallback/listallchars",
			minLength: 2
		});	
	$("#nominated2").autocomplete({
			source: "index.php/jqcallback/listallchars",
			minLength: 2
		});	
});
</script>
</head>
 
<div class="pagetitle"><?php echo kohana::lang('structures_royalpalace.rolesandtitles_pagetitle') ?></div>

<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div class='submenu'>
<?php echo html::anchor( $structure -> structure_type -> supertype . '/assign_rolerp/' . $structure -> id, kohana::lang('structures_royalpalace.assignrolerp'),
array('class' => 'selected')); ?>
<?php echo html::anchor('/structure/list_roletitles/' . $structure -> id, kohana::lang('structures_royalpalace.listroletitles'));?>
&nbsp;
<?php echo html::anchor('/royalpalace/customizenobletitles/' . $structure -> id, kohana::lang('structures_royalpalace.customizenobletitles_pagetitle')); ?>
</div>

<fieldset>
<legend><?=kohana::lang('ca_assignrolerp.royaltitles');?></legend>
<div id='helper'>
<?php echo kohana::lang('structures_royalpalace.rolesandtitles_rolehelper') ?>
</div>

<?php
echo form::open();

echo form::hidden('region_id', $structure->region->id );
echo form::hidden('structure_id', $structure->id );
?>

<?= kohana::lang('structures_royalpalace.appoint_text1');?>&nbsp;
<?= form::input( array( 'id'=>'nominated1', 'name' => 'nominated', 'value' =>  $formroles['nominated'], 'class' => 'input-medium') );?>
&nbsp;
<?= kohana::lang('structures_royalpalace.appoint_text2');?>&nbsp;
<?= form::dropdown('role', $roles, $formroles['role']);?>

<br/>

<?php
echo form::hidden('description', null );
?>
<br/>
<div class='center'>
<?php 
echo form::submit( array (
			'id' => 'submit', 
			'class' => 'button button-medium' , 			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.appoint'));
echo form::close(); 
?>
</div>
</fieldset>


<br/>

<fieldset>
<legend><?=kohana::lang('ca_assignrolerp.nobletitles');?></legend>

<div id='helper'>
<?php echo kohana::lang('structures_royalpalace.rolesandtitles_titlehelper') ?>
</div>

<?php
echo form::open();
echo form::hidden('region_id', $structure->region->id );
echo form::hidden('structure_id', $structure -> id );
?>

<?= kohana::lang('structures_royalpalace.appoint_text1'); ?>&nbsp;
<?= form::input( array( 'id'=>'nominated2', 'name' => 'nominated', 'value' =>  $formtitles['nominated'], 'class' => 'input-medium') );
?>

<?= kohana::lang('structures_royalpalace.appoint_text2'); ?> &nbsp; 
<?= form::dropdown('role', $titles, $formtitles['title']);?>
<br/>
<?php
echo kohana::lang('structures_royalpalace.appoint_text3');
	echo form::input( array( 
		'id'=>'place', 
		'name' => 'place', 
		'value' =>  $formtitles['place'],
		'class' => 'input-medium'
	)
);
?>
<br/>
<br style='clear:both'/>

<div class='center'>
<?php 
echo form::submit( array (
			'id' => 'submit', 
			'class' => 'button button-medium' , 			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.appoint'));
echo form::close(); 
?>
</div>

</fieldset>

<br/>
