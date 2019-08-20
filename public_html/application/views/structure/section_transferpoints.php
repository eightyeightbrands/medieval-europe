<fieldset>
<?php echo form::open() ?>
<?php echo form::hidden('structure_id', $structure -> id) ?>
<legend><?php echo kohana::lang('religion.transferpoints')?></legend>
<div id='helper'><?php echo kohana::lang('religion.transferpoints_helper') ?></div>
<center>
<?php echo kohana::lang('religion.transferpoints') ?>&nbsp;
<?php echo form::input( array( 'name'=>'points', 'value' => $form['points'], 'style' => 'width:50px;text-align:right' ) );?>&nbsp;
<?php echo kohana::lang('global.structure') ?>&nbsp;
<?php echo form::dropdown('targetstructure_id', $churchstructures );?>
<?php echo form::submit( array (
			'id' => 'submit', 
			'name' => 'transfer', 
			'class' => 'button button-medium', 	
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.transfer'));
?>
</fieldset>