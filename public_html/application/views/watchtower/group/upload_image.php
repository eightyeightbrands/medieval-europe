<div class="pagetitle"><?php echo Kohana::lang("groups.upload_image")?></div>

<?php echo $submenu ?>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id="helper"><?php echo Kohana::lang("groups.upload_image_helper", $group -> name) ?></div>

<div class="submenu">
	<?= $secondarymenu; ?>
</div>

<br/>

<div class="center">
<?php echo form::open_multipart(url::current()); ?>	
<?php echo form::upload( array('name' => 'group_image'), '' );?>
<?php if (!empty ($errors['group_image'])) echo "<div class='error_msg'>".$errors['group_image']."</div>";?>
<br/><br/>

<div class='center'>
<?php 
echo form::submit
( 
	array (
		'id' => 'submit', 
		'class' => 'button button-medium', 			
	), 
	kohana::lang('global.upload')
);

echo form::close(); ?>
</div>
</div>
	
<br style="clear:both;" />
