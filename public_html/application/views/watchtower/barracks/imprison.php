<div class="pagetitle"><?php echo kohana::lang('structures_barracks.imprison_pagetitle')?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'><?php echo kohana::lang('structures_barracks.imprison_helper')?></div>

<p>
<?php 
	echo form::open('barracks/imprison');
	echo form::hidden('offender_id', $offender -> id ); 
	echo kohana::lang('structures_barracks.imprison_text', $offender -> name ); 	
	echo form::input(  array( 'id' => 'hours', 'name' => 'hours', 'size' => 2, 'maxlength' => 2, 'style' => 'text-align:right' , 'value' => $form['hours']) ) ;
	echo '<br/>'; 
	echo kohana::lang('structures_barracks.warrant_id');
	echo '&nbsp;'; 
	echo form::input(  array( 'id' => 'warrant_id', 'name' => 'warrant_id', 'size' => 10, 'value' => $form['warrant_id']) ) ;
?>
</p>

<p class='center'>
<?php 
	echo form::submit(
		array ('id'=>'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.
			kohana::lang('global.confirm_operation').'\')', 'name'=>'submit', 'value'=> kohana::lang('global.confirm')))."</td>";
	echo form::close();
?>
</p>



<br style="clear:both;" />
