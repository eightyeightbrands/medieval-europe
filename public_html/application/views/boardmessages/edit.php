<?php echo html::script('media/js/jquery/plugins/markitup/jquery.markitup.js', FALSE)?>
<?php echo html::script('media/js/jquery/plugins/markitup/sets/bbcode/set.js', FALSE)?>

<script type="text/javascript">
$(document).ready(function() 
{ 

$('#message').markItUp(mySettings);   
});
</script>

<div class="pagetitle"><?php echo kohana::lang('global.edit')?></div>

<div id='helper'>
<?php echo kohana::lang('boardmessage.edithelper')?>
</div>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<div class='submenu'>
<?php 
echo html::anchor('boardmessage/index/other', kohana::lang('boardmessage.announcementboard'));
?>
</div>

<br/>

<div>
<?php echo form::open(); ?>
<?php echo form::hidden('id', $form['id']); ?>

<?php echo form::label( array( 
	'class' => 'form', 'name' => 'title' ), kohana::lang('boardmessage.messagetitle') ); ?>
<?php echo form::input( array( 'name' => 'title', 
'value' => $form['title'], 
'maxlength' => '50', 'style' => 'width:300px' ) )?>

<?php if (!empty ($errors['title'])) echo "<div class='error_msg'>".$errors['title']."</div>";?>

<br/>
<?php echo form::label( array( 
'class' => 'form', 'name' => 'message' ), kohana::lang('boardmessage.messagetext') ); ?>
<br/>

<?php	echo form::textarea( array( 'id' => 'message', 'name' => 'message', 'value' => $form['message'], 'rows' => 10, 'cols' => 90 ) ); ?>

<br/>

<?php if (!empty ($errors['message'])) echo "<div class='error_msg'>".$errors['message']."</div>";?>
<br/>
<?php echo kohana::lang('boardmessage.expireson',
	Utility_Model::format_datetime($form['validity'] * 24 * 3600 + $form['created'])) ?></b>

<br/>

<center>
<?php echo form::submit( array( 'id' => 'submit', 'class' => 'button button-small', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.edit')));?>
</center>
<?php echo form::close() ?>
</div>

<br style="clear:both;" />
