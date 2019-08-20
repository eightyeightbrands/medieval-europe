<?php echo $header ?>

<h5><?php echo kohana::lang('global.description')?></h5>

<br>

<p>
<?php echo kohana::lang('quests.' . $questname . '_4_description',
	html::image('media/images/template/quests/characterpanel_button.png', array( 'class' => 'border') ),
	html::image('media/images/template/quests/' . $questname . '_4_1.png', array( 'class' => 'border') ),
	html::image('media/images/template/quests/' . $questname . '_4_2.png', array( 'class' => 'border'))	
	);
?>
</p>

<br style='clear:both'/>

