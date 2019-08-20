<?php echo $header ?>

<h5><?php echo kohana::lang('global.description')?></h5>

<br>

<p>
<?php echo kohana::lang('quests.' . $questname . '_2_description',
	html::image(
		'media/images/template/quests/' . $questname . '_1_2.png', 
		array( 'class' => 'border'))
 );?>
</p>

<br style='clear:both'/>

