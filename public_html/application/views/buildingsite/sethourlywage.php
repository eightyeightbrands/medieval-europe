<div class="pagetitle"><?php echo Kohana::lang("structures_buildingsite.sethourlywage_pagetitle")?></div>

<?php echo $submenu ?>

<div id="helper"><?php echo Kohana::lang("structures_buildingsite.sethourlywage_helper", $project -> hourlywage) ?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/>
<br/>

<?php echo form::open(url::current()); ?>
<?php echo form::hidden('structure_id', $structure -> id ); ?>
<?php 
echo kohana::lang('structures_buildingsite.sethourlywage') . '&nbsp;' . 
form::input( array( 'id' => 'hourlywage', 'name'=>'hourlywage', 'value' => $form['hourlywage'], 'size' => 2, 'maxlength' => 2, 'style' => 'text-align:right')); 
?>
<br/>
<center>
<?php 
echo form::submit( array (
			'id' => 'submit', 
			'class' => 'submit', 			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.set'))."</td>";
?>
</center>
<?php echo form::close(); ?>

<br style="clear:both;" />
