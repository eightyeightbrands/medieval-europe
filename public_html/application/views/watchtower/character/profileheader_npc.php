<div class="pagetitle">
<?php echo $character->name . '&nbsp;-&nbsp;' . kohana::lang('page.public_profile') ?>
</div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='badges' class='right'>
<?= 
html::anchor(
	'character/attackchar/' . $viewingchar -> id . '/' . $character -> id, 
	html::image('media/images/badges/character/badge_duel.png', 
		array( 'title' => kohana::lang('charactions.attack'))),
	array('escape' => false)
);
?>
</div>		
