<fieldset class='center'>
<legend><?php echo kohana::lang('structures.loadpicture')?></legend>
<div id='helper'><?php echo kohana::lang('structures.structurepicture_helper') ?></div>

<?
echo form::open('/structure/manage');
echo form::hidden('structure_id', $structure -> id);
echo form::input( array('name' => 'structureimage','class'=> 'input input-large')); 
echo form::submit( 
	array (
		'id' => 'submit', 
		'name' => 'setstructureimage',
		'class' => 'button button-medium', 			
	), 
	kohana::lang('global.edit')
);
echo form::close();
?>
</fieldset>