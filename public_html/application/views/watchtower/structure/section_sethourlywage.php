<fieldset>
<legend><?= kohana::lang('structures_buildingsite.sethourlywage');?></legend>
<div class='center'>
<?php echo form::open('/structure/manage') ?>
<?php echo form::hidden('structure_id', $structure -> id ); ?>
<p><?php echo kohana::lang('structures_buildingsite.sethourlywagehelper'); ?></p>
<?php 
echo kohana::lang('structures_buildingsite.sethourlywage') . '&nbsp;' . 
form::input( array( 'id' => 'hourlywage', 'name'=>'hourlywage', 'value' => $form['hourlywage'], 'size' => 2, 'maxlength' => 2, 'style' => 'text-align:right')); 
?>
&nbsp;
<?php 
echo form::submit( array (
			'id' => 'submit', 
			'name' => 'sethourlywage',
			'class' => 'button button-medium', 			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.set'))."</td>";
?>
<?= form::close()?>
</div>
</fieldset>