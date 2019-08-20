<div class="pagetitle"><?php echo Kohana::lang("structures_shop.shop_configure")?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<?php echo $submenu?>

<div id="helper">
<?php echo Kohana::lang("structures_shop.shop_configure_helper")?>
</div>

<br/>

<p>
	<?php echo form::open(url::current()); ?>
	<?php echo form::label('promomessage', Kohana::lang('global.message'));?>
	<br/>
	<?php echo form::textarea( array('id' => 'promomessage', 'name'=>'promomessage', 'rows' => 15, 'cols' => 110), $form['promomessage'] ); ?>
	<?php if (!empty ($errors['promomessage'])) echo "<div class='error_msg'>".$errors['promomessage']."</div>";?>
	<br/>
	<?php 
	echo form::submit( array (
		'id' => 'submit', 
		'class' => 'submit', 			
		'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.edit'));
	?>
	<?php echo form::close(); ?>
</p>

<br style='clear:both'/>
