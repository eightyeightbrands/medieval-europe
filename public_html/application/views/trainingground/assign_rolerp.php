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
<?php echo html::anchor('/trainingground/assign_rolerp/' . $structure -> id, kohana::lang('structures_royalpalace.assignrolerp')); ?>
&nbsp;&nbsp;
<?php echo html::anchor('/structure/list_roletitles/' . $structure -> id, kohana::lang('structures_royalpalace.listroletitles')); ?>
</div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png', 'style'=>'margin:15px 0')); ?>



<?php echo html::image( 'media/images/other/royalpalace-gdr1.jpg', array( 'align' => 'right', 'class' => 'bonusimage' ) ); ?>

<div id='helper'>
<?php echo kohana::lang('structures_trainingground.rolesandtitles_rolehelper') ?>
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
<br/>
<?php 
echo form::submit( array (
			'id' => 'submit', 
			'class' => 'button button-medium' , 			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.appoint'));

echo form::close(); 
?>

<br/>
