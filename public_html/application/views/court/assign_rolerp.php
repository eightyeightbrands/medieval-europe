<head>
<script>

$(document).ready(function()
{	
	$("#nominated1").autocomplete( {
			source: "index.php/jqcallback/listallchars",
			minLength: 2
		});
});
</script>
</head>
 
<div class="pagetitle"><?php echo kohana::lang('structures_royalpalace.rolesandtitles_pagetitle') ?></div>

<?php echo $submenu ?>

<div class='submenu'>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<?php echo html::anchor('/court/assign_rolerp/' . $structure -> id, kohana::lang('structures_royalpalace.assignrolerp')); ?>
&nbsp;&nbsp;
<?php echo html::anchor('/structure/list_roletitles/' . $structure -> id, kohana::lang('structures_royalpalace.listroletitles')); ?>
</div>

<br/>


<div id='helper'>
<?php echo kohana::lang('structures_court.rolesandtitles_rolehelper') ?>
</div>

<?php
echo form::open();

echo form::hidden('region_id', $structure->region->id );
echo form::hidden('structure_id', $structure->id );
 echo form::hidden('description', null );
?>

<?= kohana::lang('structures_royalpalace.appoint_text1'); ?>
&nbsp;
<?= form::input( array( 'id'=>'nominated1', 'name' => 'nominated', 'value' =>  $formroles['nominated'], 'class' => 'input-large') );
?>
&nbsp;
<?= kohana::lang('structures_royalpalace.appoint_text2');?>
&nbsp;
<?= form::dropdown('role', $roles, $formroles['role']); ?>

<br/>
<?php

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
<br/>
