<div class="pagetitle"><?php echo kohana::lang("structures_actions.global_info");?></div>

<?php echo $submenu ?>


<div id='helperwithpic'>
	
	<div id='locationpic'>
	<?php 
		if ( $structure -> image =='' )
			echo html::image('media/images/template/locations/academy.jpg');
		else
			echo html::image($structure -> image );
	?>		
	</div>

	
	<div style='clear:both'></div>
</div>
<br style="clear:both;" />

<fieldset>
<legend><?php echo kohana::lang('structures.configuredescription')?></legend>
<?php echo form::open('/structure/change_description') ?>
<?php echo form::hidden('structure_id', $structure -> id) ?>
<div id='helper'><?php echo kohana::lang('structures.structuredescription_helper') ?></div>
<?php echo form::textarea( array( 'name'=>'description', 'value' => $structure -> description, 'style' => 'width:98%;height:100px;' ) ); ?>
<br/>
<center>
<?php echo form::submit( array (
			'id' => 'submit', 
			'name' => 'submit_description', 
			'class' => 'submit', 	
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.edit'));
?>
</center>
<?php echo form::close() ?>
</fieldset>

<fieldset>
<legend><?php echo kohana::lang('structures.configureinformativemessage')?></legend>
<?php echo form::open('/structure/change_infomessage') ?>
<?php echo form::hidden('structure_id', $structure -> id) ?>
<div id='helper'><?php echo kohana::lang('structures.informativemessage_helper') ?></div>
<?php echo form::textarea( array( 'name'=>'message', 'value' => $structure -> message	, 'style' => 'width:98%;height:100px;' ) ); ?>
<br/>
<center>
<?php echo form::submit( array (
			'id' => 'submit', 
			'name' => 'submit_infomessage', 
			'class' => 'submit', 	
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.edit'));
?>
</center>
<?php echo form::close() ?>
</fieldset>

<fieldset>
<legend><?php echo kohana::lang('structures.loadpicture')?></legend>
<?php echo form::open('/structure/change_image') ?>
<?php echo form::hidden('structure_id', $structure -> id); ?>
<div id='helper'><?php echo kohana::lang('structures.structurepicture_helper') ?></div>
<?php echo form::input( array( 'name' => 'image', 'value' => $structure -> image, 'style' => 'width:400px' ) ); ?>
<br/>
<center>
<?php echo form::submit( array (
			'id' => 'submit', 
			'name' => 'submit_image', 
			'class' => 'submit', 	
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.edit'));
?>
</center>
<?php echo form::close() ?>
</fieldset>