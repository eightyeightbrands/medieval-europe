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

<div id='helper'>
<?php echo kohana::lang('structures_religion_2.rolesandtitles_rolehelper') ?>
</div>


<div class='submenu'>
<?php echo html::anchor('/religion_2/assign_rolerp/' . $structure -> id, kohana::lang('structures_royalpalace.assignrolerp'),
array( 'class' => 'selected' )); ?>
&nbsp;&nbsp;
<?php echo html::anchor('/structure/list_roletitles/' . $structure -> id, kohana::lang('structures_royalpalace.listroletitles')); ?>
</div>


<br/>
<fieldset>
<br/>
<?php
echo form::open();

echo form::hidden('region_id', $structure -> region -> id );
echo form::hidden('structure_id', $structure -> id );
echo kohana::lang('structures_royalpalace.appoint_text1');
echo form::input( array( 'id'=>'nominated1', 'name' => 'nominated', 'value' =>  $formroles['nominated'], 'style'=>'width:250px') );
?>
&nbsp;
<?php 
echo kohana::lang('structures_royalpalace.appoint_text2');
echo "&nbsp;";
echo form::dropdown('role', $roles, $formroles['role']);
?>
&nbsp;
<?php
echo form::hidden('description', null );
?>
<br/>
<br/>
<div class='center'>
<?php 
echo form::submit( array (
			'id' => 'submit', 
			'class' => 'button button-medium', 			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.appoint'));
?>
</div>			
<?php			
echo form::close(); 
?>
</fieldset>

<br style='clear:both'/>
