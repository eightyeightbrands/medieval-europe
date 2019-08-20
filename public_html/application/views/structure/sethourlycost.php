<div class="pagetitle"><?php echo $structure -> getName()?></div>

<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id="helper"><?php echo Kohana::lang('structures_trainingground.sethourlycost_helper') ?></div>


<?php echo form::open(); ?>
<?php echo form::hidden('structure_id', $structure -> id ); ?>
<br/>


<div class='center'>
<?php echo kohana::lang('structures_trainingground.hourlycost') . '&nbsp;' . 
form::input( 
	array( 'id' => 'hourlycost', 
		'name'=>'hourlycost', 
		'value' => $form['hourlycost'], 
		'size' => 2, 
		'maxlength' => 5,
		'style' => 'text-align:right')) . '&nbsp;' . kohana::lang( 'structures_trainingground.coinsperhour' ) ; 
?>
&nbsp;
<?php 
echo form::submit( array (
			'id' => 'submit', 
			'class' => 'button button-medium', 			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.set'))."</td>";
?>
</div>
<?php echo form::close(); ?>
</fieldset>

<br style='clear:both'/>

