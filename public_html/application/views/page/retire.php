<div class="pagetitle"><?php echo kohana::lang('ca_retire.retire_pagetitle')?></div>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>
<br/>
<div id='helperwithpic'>
	
	<div id='locationpic'>
	<?php echo html::image( 'media/images/template/locations/manage.jpg', array( 'class' => 'locationpic'  )); ?>
	</div>

	
	<div id='helper'>
	<?php echo kohana::lang( 'ca_retire.retire_text', Utility_Model::format_datetime($retireaction -> endtime) ) ?>	
	</div>

</div>

<br style='clear:both'/>