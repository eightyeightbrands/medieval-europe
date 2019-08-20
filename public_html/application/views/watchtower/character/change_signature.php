<?php echo html::script('media/js/jquery/plugins/markitup/jquery.markitup.js', FALSE)?>
<?php echo html::script('media/js/jquery/plugins/markitup/sets/bbcode/set.js', FALSE)?>
<script>
$(document).ready(function()
{	
	$('#signature').markItUp(mySettings);
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

<div class="pagetitle"><?php echo Kohana::lang("character.change_signature_titlepage")?></div>

<div id="helper">
<?php echo Kohana::lang("character.change_signature_helper") . '&nbsp;' . html::anchor('https://nbbc.sourceforge.net/readme.php?page=bbc', kohana::lang('global.bbcode_guide'), array('target' => '_blank' )) ?>
</div>
<br/>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/><br/>
<?php echo form::open(); ?>
<div class="top10 center">
	
	<b><?php echo form::label('signature', Kohana::lang('character.char_signature'));?></b>
	<br/>
	<?php echo form::textarea( array( 
		'id' => 'signature', 'name'=>'signature', 
		'value' => $form['signature']) ); ?>
	<?php if (!empty ($errors['signature'])) echo "<div class='error_msg'>".$errors['signature']."</div>";?>
	
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
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.edit'))."</td>";
	?>
	</div>
</div>

<?php echo form::close(); ?>

<br/>

<h5>Preview</h5>
<div id="preview"></div>

<br style="clear:both;" />
