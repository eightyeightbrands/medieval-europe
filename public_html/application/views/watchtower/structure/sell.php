<div class="pagetitle"><?php echo kohana::lang("structures.sell_pagetitle");?></div>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/><br/>

<p>
<?php 
echo kohana::lang('structures.sell_helper', $sellingprice ); 
echo form::open('/structure/sell');
echo form::hidden('structure_id', $structure -> id );
?>

<center>
<?php
?>	
</p>

<center>

<?php 
	echo form::submit( array (
	'id' => 'submit', 
	'class' => 'button button-small', 			
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.sell'));
	echo form::close();
?>
</center>

</p>

</center>

<br style='clear:both'/>

