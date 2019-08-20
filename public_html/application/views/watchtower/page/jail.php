<div class="pagetitle"><?php echo kohana::lang('structures_barracks.jail_pagetitle')?></div>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>
<br/>
<div id='helperwithpic'>
	
	<div id='locationpic'>
	<?php echo html::image( 'media/images/template/locations/prison.jpg', array( 'class' => 'locationpic'  )); ?>
	</div>
	
	<div id='helper'>
	<?php echo kohana::lang( 'structures_barracks.jail_text', 		
		$sentence -> text,
		Utility_Model::format_datetime( $sentence -> imprisonment_end ) ); 
	?>
	</div>

</div>

<br style='clear:both'/>
