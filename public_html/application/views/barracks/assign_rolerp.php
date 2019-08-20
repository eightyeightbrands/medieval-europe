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

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<div class='submenu'>
<?php echo html::anchor('/barracks/assign_rolerp/' . $structure -> id, kohana::lang('structures_royalpalace.assignrolerp')); ?>
&nbsp;&nbsp;
<?php echo html::anchor('/structure/list_roletitles/' . $structure -> id, kohana::lang('structures_royalpalace.listroletitles')); ?>
</div>
<br/>
	
<div id='helper'>
<?php echo kohana::lang('structures_barracks.rolesandtitles_rolehelper') ?>
</div>

<?php
echo form::open();

echo form::hidden('region_id', $structure->region->id );
echo form::hidden('structure_id', $structure->id );
 
echo kohana::lang('structures_royalpalace.appoint_text1');
echo form::input( array( 'id'=>'nominated1', 'name' => 'nominated', 'value' =>  $formroles['nominated'], 'style'=>'width:250px') );
?>
<br/>
<?php 
echo kohana::lang('structures_royalpalace.appoint_text2');
echo form::dropdown('role', $roles, $formroles['role']);
?>
<br/>
<?php
echo form::hidden('description', null );
?>
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

<br style='clear:both'/>
