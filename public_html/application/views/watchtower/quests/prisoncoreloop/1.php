<?php echo $header ?>

<h5><?php echo kohana::lang('global.description')?></h5>

<br>

<p>
<?php echo kohana::lang('quests.' . $questname . '_1_description',
	html::anchor('/newchat/init', kohana::lang('global.chat'), array('target' => 'new')),
	html::anchor('https://wiki.medieval-europe.eu/index.php?title=Making_money', kohana::lang('global.guide'), array('target' => 'new')),
	html::image('media/images/template/quests/' . $questname . '_1_1.png', array( 'class' => 'border') ) );
	
?>
</p>

<br style='clear:both'/>

