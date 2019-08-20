<div class="pagetitle"><?php echo kohana::lang('charactions.change_city') ?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'>
  <div id='helpertext'>
		<?php echo kohana::lang('charactions.change_city_helper', kohana::lang( $dest_region->name ), $cost);?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=Explore_or_View_Region#Transfer_Residence',
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button' ) ) 
		?>
	</div>
	<div style='clear:both'></div>
</div>



<div class="center">
<?php
	echo form::open('/character/changeregion');
	
	echo '<br/>'; 
	
	echo form::submit( array (
			'id' => 'submit', 
			'name' => 'transfer', 
			'class' => 'button button-medium' , 
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('charactions.transfer'))."</td>";				
	
	echo form::close();
?>
</div>

<br style="clear:both;" />
