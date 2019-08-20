<script>	
 $(document).ready(function()
 {	
	$("#nominated").autocomplete( 
	{
		source: "index.php/jqcallback/listallchars/appointable",
		minLength: 2
	})	
});
</script>
 
<div class="pagetitle"><?php echo kohana::lang('structures_castle.nominees_pagetitle') ?></div>
<?php echo $submenu ?>

<div id='helper'>
<?php echo kohana::lang('structures_castle.assign_helper') ?>
</div>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>

<div class='submenu'>
<?php echo html::anchor('/castle/assignrole/' . $structure -> id, kohana::lang('structures_castle.assignrole'),
	array( 'class' => 'selected' )); ?>
&nbsp;&nbsp;
<?php echo html::anchor('/castle/list_subordinates/' . $structure -> id, kohana::lang('structures_castle.listsubordinates')); ?>
</div>

<br/>

<div>
<?php echo form::open();?>
<?php echo form::hidden('structure_id', $structure -> id ); ?>
<?php 
	echo kohana::lang('structures_castle.appoint_text1');
	echo "&nbsp;";
	echo form::input( array(
		'id'=>'nominated', 
		'name' => 'nominated', 
		'value' =>  $form['nominated'], 
		'class' => 'input-medium') );
?>

&nbsp;

<?php echo form::dropdown('role', $roles, $form['role']);?>

&nbsp;

<?php 
	echo kohana::lang('global.region') . "&nbsp;";
	echo form::dropdown('region', $controlledregions_cb, $form['region']);
?>
</div>

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

<br style='clear:both'/>
