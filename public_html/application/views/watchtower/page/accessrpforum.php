<div class="pagetitle"><?php echo Kohana::lang("page.accessrpforum")?></div>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>
<br/>

<p class='center'><?php echo kohana::lang('page.rpforumdisclaimer') ?></p>

<p><?php echo form::open(url::current()); ?></p>
<p class='center'>
<?php
echo form::submit( array (
			'id' => 'submit', 
			'class' => 'submit', 			
			'name' => 'createaccount', 
                        'value' => kohana::lang('global.register')));
?>
<?php echo form::close(); ?>
</p>


