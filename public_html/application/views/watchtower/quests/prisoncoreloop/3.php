<?php echo $header ?>

<h5><?php echo kohana::lang('global.description')?></h5>

<br>

<p>
<?php echo kohana::lang('quests.' . $questname . '_3_description',
	html::image('media/images/template/quests/' . $questname . '_3_1.png', array( 'class' => 'border') ),
	html::image('media/images/template/quests/' . $questname . '_3_2.png', array( 'class' => 'border') ),
	html::image('media/images/template/quests/' . $questname . '_3_3.png', array( 'class' => 'border') )
	);	
?>
</p>

<br style='clear:both'/>

