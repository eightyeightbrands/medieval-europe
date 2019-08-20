<?php echo $header ?>

<h5><?php echo kohana::lang('global.description')?></h5>

<br>

<p>
<?php echo kohana::lang('quests.' . $questname . '_1_description',
	html::image('media/images/template/quests/inventory_button.png', array( 'class' => 'border') ),
	html::image('media/images/template/quests/' . $questname . '_1_1.png', array( 'class' => 'border') ),
	html::image('media/images/template/quests/exploreregion_button.png', array( 'class' => 'border') ),
	html::image('media/images/template/quests/' . $questname . '_1_2.png', array( 'class' => 'border') ),
	html::image('media/images/template/quests/' . $questname . '_1_3.png', array( 'class' => 'border') ),
	html::image('media/images/template/quests/' . $questname . '_1_4.png', array( 'class' => 'border') )
	);	
?>
</p>

<br style='clear:both'/>

