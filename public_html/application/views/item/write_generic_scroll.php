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

<div class="pagetitle"><?php echo kohana::lang('message.write_pagetitle'); ?></div>

<? 
	echo html::anchor( 
		'character/inventory/',
		'Back',
		array('class' => 'button button-small')
	); 
?>

<br/><br/>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id="helper">
<?php echo Kohana::lang("message.writedocument_helper") . '&nbsp;' . html::anchor('https://nbbc.sourceforge.net/readme.php?page=bbc', kohana::lang('global.bbcode_guide'), array('target' => '_blank' ) )?>
</div>

<fieldset>

<?php echo form::open('item/write') ?>

<?= form::hidden('item_id', $item -> id ); ?>

<table border="0" style="margin-top:15px">
	<tr>
		<td><?php echo form::label('subject', Kohana::lang('global.title'));?></td>
		<td colspan="2" align="left"><?php echo form::input( array( 
			'id' => 'subject', 
			'name' => 
			'subject', 
			'value' =>  $form['subject'], 'style'=>'width:450px') );?>
	</tr>
	<?php if (!empty ($errors['subject'])) echo "<tr><td></td><td colspan='2'><div class='error_msg'>".$errors['subject']."</div></td></tr>";?>		
	<tr>
		<td><?php echo form::label('body',	Kohana::lang('message.body'));?></td>
		
		<td>
			<?php echo form::textarea( 
				array( 
			'id' => 'body',
			'name' => 'body', 		
			'style' => 'overflow-y: scroll',
			'value' => $form['body'] ))?>
		</td>
		
		
	</tr>
	<?php if (!empty ($errors['body'])) echo "<tr><td></td><td colspan='2'><div class='error_msg'>".$errors['body']."</div></td></tr>";?>		
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align:center">
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
			'class' => 'button button-medium',
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('message.write_document'))
		?>
		
		</td>		
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
</table>
<?php echo form::close(); ?>
</fieldset>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<h5>Preview</h5>
<div id="preview"></div>

<br style='clear:both'/>

<br style="clear:both;" />
