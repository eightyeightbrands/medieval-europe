<div class="pagetitle"><?php echo kohana::lang('structures_royalpalace.slogan_pagetitle')?></div>

<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'><?php echo kohana::lang('structures_royalpalace.slogan_helper') ?></div>

<div class='submenu'>
<?php echo html::anchor('royalpalace/welcomeannouncement/' . $structure->id, kohana::lang('structures_royalpalace.submenu_welcomemessage') )?>
<?php echo html::anchor(
	'royalpalace/infoannouncement/' . $structure->id, kohana::lang('structures_royalpalace.submenu_infoannouncement'))?>
	<?php echo html::anchor(
	'royalpalace/addslogan/' . $structure->id, kohana::lang('structures_royalpalace.submenu_addslogan'),
		array( 'class' => 'selected' ))?>
</div>
<br/>
<fieldset>
<div class='center'>
<?php echo form::open('royalpalace/add_slogan'); ?>
<?php 
echo form::textarea( 
	array( 
		'id' => 'slogan',
		'name' => 'slogan', 		
		'rows' => '3', 
		'cols' => '90',
		'value' => $slogan) 
);
?>

<?php echo form::hidden( 'structure_id', $structure -> id ); ?> 
	
<?php echo form::submit( 
	array( 
		'id' => 'submit',  
		'name' => 'save', 
		'class' => 'button button-medium', 
		'onclick' => 'return confirm(\'' . kohana::lang('global.confirm_operation').'\')' ,
		'value' => kohana::lang('global.save') )) ;
?>	

<?php echo form::close() ?>
</div>
</fieldset>
<br style='clear:both'/>