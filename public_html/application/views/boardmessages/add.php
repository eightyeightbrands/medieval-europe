<?php echo html::script('media/js/jquery/plugins/markitup/jquery.markitup.js', FALSE)?>
<?php echo html::script('media/js/jquery/plugins/markitup/sets/bbcode/set.js', FALSE)?>

<script type="text/javascript">
$(document).ready(function() 
{ 

$('#message').markItUp(mySettings);   
});
</script>

<div class="pagetitle"><?php echo kohana::lang('boardmessage.add')?></div>
<div id='helper'>
<?php echo kohana::lang('boardmessage.addhelper')?>
</div>
<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div class='submenu'>
<?php 
echo html::anchor('boardmessage/index/other', kohana::lang('boardmessage.announcementboard'));
?>
</div>

<br/>

<?php echo form::open(); ?>
<?php echo form::hidden('category', 'other' ); ?>
<?php echo form::label( array(  'class' => 'form', 'name' => 'title' ), kohana::lang('boardmessage.messagetitle') ); ?>
<?php echo form::input( array( 'name' => 'title', 
	'value' => $form['title'], 
	'maxlength' => '50', 'style' => 'width:300px' ) )?>

<?php if (!empty ($errors['title'])) echo "<div class='error_msg'>".$errors['title']."</div>";?>

<br/>

<?php echo form::label( array( 
'class' => 'form', 'name' => 'message' ), kohana::lang('boardmessage.messagetext') ); ?>

<br/>
		

<?php echo form::textarea( array( 'id' => 'message', 'name' => 'message', 'value' => $form['message'], 'rows' => 10, 'cols' => 90 ) ); ?>
<?php if (!empty ($errors['message'])) echo "<div class='error_msg'>".$errors['message']."</div>";?>
<br/>
<?php echo kohana::lang('boardmessage.messagevalidity1'); ?>
<?php echo form::input( array( 'id' => 'validity', 'name' => 'validity', 
'value' => $form['validity'],'maxlength' => '2', 'style' => 'width:30px;text-align:right' ) )?>
<?php if (!empty ($errors['validity'])) echo "<div class='error_msg'>".$errors['validity']."</div>";?>

<br/>
<center>
<?php echo form::submit( array( 'id' => 'submit', 'class' => 'button button-small', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.add')));?>
</center>
<?php echo form::close() ?>

<br style="clear:both;" />
