<?php echo html::script('media/js/jquery/plugins/markitup/jquery.markitup.js', FALSE)?>
<?php echo html::script('media/js/jquery/plugins/markitup/sets/bbcode/set.js', FALSE)?>

<script type='text/javascript'>

$(document).ready(function()
{	
	$('#group_message').markItUp(mySettings);
	$("#showpreview").click(function() 
	{				
		$.ajax( //ajax request starting
		{
		url: "index.php/jqcallback/bbcodepreview", 
		type:"POST",
		data: { text: $("#group_message").val() },
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

<div class="pagetitle"><?php echo Kohana::lang('groups.message_pagetitle')	. ': ' . $group->name ?></div>
<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div class="submenu">
	<?= $secondarymenu; ?>
</div>
<br/>
<div id="helper"><?php echo Kohana::lang('groups.message_helper'); ?></div>

<?php echo form::open(url::current(), array('class'=>'messagegroup_form')); ?>

<table>
	<tr>
	<td>
	<?= kohana::lang('message.subject'); ?>
	<?php echo form::input(
		array(
			'name' => 'group_subject',
			'value' => $form['group_subject'],
			'class' => 'input-xlarge',
			)
		);
	?>
	<?php if (!empty ($errors['group_subject'])) echo "<div class='error_msg'>".$errors['group_subject']."</div>";?></td>
	</td>
	</tr>
	<tr>		
		
		<td colspan='2' align='left'>
			<?php echo form::textarea( 
				array( 
			'id' => 'group_message',
			'name' => 'group_message', 		
			'style' => 'overflow-y: scroll',
			'value' => $form['group_message'] ))?>
		<?php if (!empty ($errors['group_message'])) echo "<div class='error_msg'>".$errors['group_message']."</div>";?></td>
	</tr>

	<tr>
		<td colspan=2 class="center" style="padding-top:10px;">
		<?php
		echo form::submit( array (
			'id' => 'showpreview', 
			'class' => 'button button-small', 			
			'onclick' => 'return false' ),
			kohana::lang('global.preview')); 
		?>
		&nbsp;
		<?php
		echo form::submit( array (
			'id' => 'submit', 
			'class' => 'button button-small', 			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('groups.send'));
		?></td>
	</tr>
</table>

<?php echo form::close(); ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<h5>Preview</h5>
<div id="preview"></div>

<br style='clear:both'/>
