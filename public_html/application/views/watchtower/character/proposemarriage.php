<?php echo html::script('media/js/jquery/plugins/markitup/jquery.markitup.js', FALSE)?>
<?php echo html::script('media/js/jquery/plugins/markitup/sets/bbcode/set.js', FALSE)?>
<script>


$(document).ready(function()
{	
	$('#proposal').markItUp(mySettings);
	$("#showpreview").click(function() 
		{				
			$.ajax( //ajax request starting
			{
			url: "index.php/jqcallback/bbcodepreview", 
			type:"POST",
			data: { text: $("#signature").val() },
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


<div class="pagetitle"><?php echo Kohana::lang("character.proposemarriage")?></div>

<div id="helper">
<?php echo kohana::lang('character.proposemarriage_helper', $bride -> name)?>
</div>
<br/>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/><br/>
<?php echo form::open(); ?>
<div class="top10 center">
	
	<b><?php echo form::label('body', Kohana::lang('message.body'));?></b>
	<br/>
	<?php echo form::hidden('bride_id', $bride -> id );?>
	<?php echo form::textarea( array( 
		'id' => 'proposal', 
		'name'=>'proposal', 
		'cols' => 80,
		'rows' => 10,
		'value' => $form['proposal']) ); ?>
		
	<?php if (!empty ($errors['proposal'])) echo "<div class='error_msg'>".$errors['proposal']."</div>";?>
	
	<div class="top10">
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
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.send'))."</td>";
	?>
	</div>
</div>

<?php echo form::close(); ?>

<br/>

<h5>Preview</h5>
<div id="preview"></div>

<br style="clear:both;" />
