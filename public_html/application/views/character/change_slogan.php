<div class="pagetitle"><?php echo Kohana::lang("character.change_slogan_titlepage")?></div>

<div id="helper"><?php echo Kohana::lang("character.change_slogan_helper") ?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div class="top10 center">
<?php echo form::open(url::current()); ?>

<span style="margin-right:5px"><?php echo form::label('slogan', Kohana::lang('character.slogan'));?></span>
<span style="margin-right:5px"><?php echo form::input( 
	array( 
		'name'=>'slogan', 
		'value' => $form['slogan'], 
		'class' => 'input-xlarge',
		'maxlength' => 40 )		
		); ?>
	</span>
<br/>
<?php if (!empty ($errors['slogan'])) echo "<div class='error_msg'>".$errors['slogan']."</div>";?>
<br/>
<div class="center">
<?php 
echo form::submit( array (
			'id' => 'submit', 
			'class' => 'button button-small', 			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.edit'))."</td>";
?>
<?php echo form::close(); ?>
</div>

</div>

<br style="clear:both;" />
