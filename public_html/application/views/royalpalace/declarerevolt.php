<div class="pagetitle"><?php echo kohana::lang('structures_royalpalace.declarerevolt_pagetitle');?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>
<br/>

<?php
	
	echo "<p>" . kohana::lang('structures_royalpalace.declarerevolt_text', $cost) . "</p>" ;			
	echo form::open('/royalpalace/declarerevolt');
	echo form::hidden('structure_id', $structure -> id );
	echo '<center>';
	echo form::submit( array (
	'id' => 'submit', 
	'class' => 'submit', 				
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('structures_royalpalace.declarerevolt'));
	echo '</center>';
	echo form::close();
?>

<br style ='clear:both'/>
