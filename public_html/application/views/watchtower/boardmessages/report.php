<div class="pagetitle"><?php echo kohana::lang('admin.gamemessage')?></div>

<div id='helper'>
<?php echo kohana::lang('boardmessage.reporthelper')?>
</div>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/>
<br/>
<div>
<?php echo form::open(); ?>
<?php echo form::hidden('id', $form['id'])?>
<?php echo form::label( array( 'name' => 'reason' ), kohana::lang('boardmessage.reportreason') ); ?>
<?php echo form::input( array( 'name' => 'reason', 
'value' => $form['reason'], 
'maxlength' => '50', 'style' => 'width:500px' ) )?>
<?php if (!empty ($errors['reason'])) echo "<div class='error_msg'>".$errors['reason']."</div>";?>

<br/><br/>

<center>
<?php echo form::submit( array( 'id' => 'submit', 'class' => 'button button-small', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.report')));?>
</center>
<?php echo form::close() ?>
</div>

<br style="clear:both;" />
