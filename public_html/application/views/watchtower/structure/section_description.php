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

<fieldset>
<legend><?php echo kohana::lang('structures.configuredescription')?></legend>
<?php echo form::open('/structure/manage') ?>
<?php echo form::hidden('structure_id', $structure -> id) ?>
<p>
<?php echo kohana::lang('structures.structuredescription_helper') ?>
</p>


<?php echo form::textarea(
	array( 
		'id' => 'body',
		'name' => 'description', 
		'value' => $structure -> description ) ); ?>

<br/>
<div class='center'>
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
			'name' => 'edit_description', 
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.edit'))
		?>
</div>
<?php echo form::close() ?>
<h5>Preview</h5>
<div id='preview'></div>
</fieldset>