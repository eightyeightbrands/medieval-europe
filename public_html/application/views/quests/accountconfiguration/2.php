<?php echo $header ?>

<h5><?php echo kohana::lang('global.description')?></h5>

<br>

<p>
<?php echo kohana::lang('quests.' . $questname . '_2_description',
	html::image('media/images/template/quests/characterpanel_button.png', array( 'class' => 'border') ),
	html::image('media/images/template/quests/' . $questname . '_2_1.png', array( 'class' => 'border') ),
	html::image('media/images/template/quests/' . $questname . '_2_2.png', array( 'class' => 'border') ),
	html::image('media/images/template/quests/' . $questname . '_2_3.png', array( 'class' => 'border') ),
	html::image('media/images/template/quests/' . $questname . '_2_4.png', array( 'class' => 'border') )
	);
?>
</p>

<br style='clear:both'/>

