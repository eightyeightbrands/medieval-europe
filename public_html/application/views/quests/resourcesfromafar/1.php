<?php echo $header ?>

<h5><?php echo kohana::lang('global.description')?></h5>

<br>

<p>
<?php echo kohana::lang('quests.' . $questname . '_1_description',	
	html::file_anchor(
		'media/images/map/MEMap-0.7.20.pdf',
		kohana::lang('global.map'), 
		array('target' => 'new') ),	
	html::image('media/images/template/quests/' . $questname . '_1_1.png', array( 'class' => 'border') )
);
?>
</p>

<br style='clear:both'/>

