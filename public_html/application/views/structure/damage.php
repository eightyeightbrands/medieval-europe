<div id='helper'><?php echo kohana::lang('ca_damage.damagestructure_helper') ?></div>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/><br/>

<div>
	<?php echo form::open(); ?>
	<?php
	echo kohana::lang('ca_damage.damagestructure' );
	echo '&nbsp;';
	echo form::input( array('id' => 'hours', 'name' => 'hours', 'size' => '2', 'maxlength' => 1 ) );
	echo form::hidden('structure_id', $structure -> id);
	?>
	<?php
	echo form::submit( array (
		'id' => 'submit', 	
		'class' => 'submit', 			
		'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.work')) ;		
	echo form::close();
	?>
</div>

<br style='clear:both/'>