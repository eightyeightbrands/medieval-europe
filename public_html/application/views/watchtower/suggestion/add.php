<?php echo html::script('media/js/jquery/plugins/markitup/jquery.markitup.js', FALSE)?>
<?php echo html::script('media/js/jquery/plugins/markitup/sets/bbcode/set.js', FALSE)?>
<script type='text/javascript'>

$(document).ready(function()
{	
	$('#body').markItUp(mySettings);
	
	$("#showpreview").click(function() 
	{				
		$.ajax( //ajax request starting
		{
		url: "index.php/jqcallback/bbcodepreview", 
		type:"POST",
		data: { text: $("#body").val() },
		success: 
			function(data) 
			{																	
				$("#preview").html(data); 				
			}
		}	
		);						
	});	
});
	
</script>

<div class="pagetitle"><?php echo kohana::lang('suggestions.addmodify')?></div>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/>
<div class='center'>
<? $status = $this->uri->segment(3);?>
<? if ($status == 'new') $class='button selected'; else	$class='button'; ?>

<?php echo html::anchor('suggestion/index/new', kohana::lang('suggestions.new'), array( 'class' => $class ) ); ?>

<? if ($status == 'fundable') $class='button selected'; else $class='button'; ?>

<?php echo html::anchor('suggestion/index/fundable', kohana::lang('suggestions.fundable'), array( 'class' => $class ) ); ?>

<? if ($status == 'funded') $class='button selected'; else	$class='button'; ?>	
<?php echo html::anchor('suggestion/index/funded', kohana::lang('suggestions.funded'), array( 'class' => $class ) ); ?>

<? $class='button selected';?>
<?php echo html::anchor('suggestion/add/', kohana::lang('suggestions.addmodify'),	array( 'class' => $class ) ); ?>
		
</div>	

<br/>

<fieldset class='alternaterow_1'>
<?php echo form::open(); ?>

<?php echo form::hidden('author', $char -> id) ?>
<br/>

<?php echo form::label( array( 
	'class' => 'form', 'name' => 'title' ), kohana::lang('global.title') ); ?>
<?php echo form::input( array( 'name' => 'title', 'value' => $form['title'], 'maxlength' => '50', 'class' => 'input-xlarge' ) )?>
<?php if (!empty ($errors['title'])) echo "<div class='error_msg'>".$errors['title']."</div>";?>

<br/>

<?php	echo form::textarea( array( 'id' => 'body', 'name' => 'body', 'value' => $form['body'], 'style' => 'overflow-y: scroll') ); ?>
<?php if (!empty ($errors['body'])) echo "<div class='error_msg'>".$errors['body']."</div>";?>

<br/>

<?php echo form::label( array( 
	'class' => 'form', 'name' => 'discussionurl' ), kohana::lang('suggestions.discussionurl') ); ?>
<?php echo form::input( array( 'name' => 'discussionurl', 
'value' => $form['discussionurl'], 'class' => 'input-xlarge' ) )?>
<?php if (!empty ($errors['discussionurl'])) echo "<div class='error_msg'>".$errors['discussionurl']."</div>";?>
<br/>


<div class='center'>
<?php echo form::submit( array ('id' => 'showpreview', 'class' => 'button button-small', 'onclick' => 'return false' ),	kohana::lang('global.preview')); ?>
&nbsp;
<?php echo form::submit( array( 'id' => 'submit', 'class' => 'button button-small', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.create')));?>
</div>
<?php echo form::close() ?>
</fieldset>
<br/>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<h5>Preview</h5>
<div id="preview"></div>

<br style='clear:both'/>
