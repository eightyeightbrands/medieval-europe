<fieldset>
<legend><?php echo kohana::lang('structures.configureinformativemessage')?></legend>

<?php echo form::open('/structure/manage') ?>
<?php echo form::hidden('structure_id', $structure -> id) ?>

<div id='helper'><?php echo kohana::lang('structures.informativemessage_helper') ?></div>

<?php echo form::textarea( array( 'name'=>'informativemessage', 'value' => $structure -> message	, 'style' => 'width:98%;height:100px;' ) ); ?>
<br/>

<div class='center'>

<?php echo form::submit( array (
			'id' => 'submit', 
			'name' => 'edit_informativemessage', 
			'class' => 'button button-small', 	
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.edit'));
?>
</div>
<?php echo form::close() ?>

</fieldset>