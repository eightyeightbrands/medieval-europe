<div class="pagetitle"><?php echo Kohana::lang("character.change_avatar_titlepage")?></div>

<div id="helper"><?php echo Kohana::lang("character.change_avatar_helper") ?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div class="center">
	<?php
	echo form::open_multipart(url::current());
	echo form::upload( array('name' => 'avatar_image'), '' );
	if (!empty ($errors['description'])) echo "<div class='error_msg'>".$errors['description']."</div>";
	?>
	<br/><br/>
	<?= form::submit( array (
		'name' => 'edit_image',
		'id' => 'submit', 
		'class' => 'button button-small', 			
		'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.edit'))."</td>";
	echo form::close();
	?>
</div>

<br style="clear:both;" />
