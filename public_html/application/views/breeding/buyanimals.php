<div class="pagetitle"><?php echo kohana::lang("structures_actions.global_info");?></div>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helperwithpic'>
	<div id='locationpic'>
	<?php echo html::image('media/images/template/locations/' . $structureinstance -> structure_type -> type . '.jpg') ?>
	</div>

	<div>
		<?php echo kohana::lang('structures.buyanimals_helper', $price) ?>		
	</div>
	<div style='clear:both'></div>
</div>

<?= form::open() ?>
<div class='center'>
	<?= form::hidden('type', $structureinstance -> structure_type -> type); ?>	
	<?= form::submit( 
		array ('id' => 'submit', 'class' => 'button button-medium', 
			'name'=> 'buy', 'value'=> kohana::lang('global.buy'),
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')',
			));
	?>
</div>
<?= form::close(); ?>
<br style="clear:both;" />
