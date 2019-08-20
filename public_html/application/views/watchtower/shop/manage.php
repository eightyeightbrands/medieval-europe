<div class="pagetitle"><?php echo kohana::lang("structures_actions.global_info");?></div>

<?php echo $submenu ?>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>


<div id='helperwithpic'>
	<div id='locationpic'>
	<?php echo html::image('media/images/template/locations/shop.jpg') ?>
	</div>

	<div id='helper'>
	<?php echo kohana::lang('structures_shop.manage_helper') ?>
	</div>

	<br style="clear:both;" />
</div>

<?= $section_description; ?>

<br style="clear:both;" />
