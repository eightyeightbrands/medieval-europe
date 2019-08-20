<?php echo html::script('media/js/jquery/plugins/markitup/jquery.markitup.js', FALSE)?>
<?php echo html::script('media/js/jquery/plugins/markitup/sets/bbcode/set.js', FALSE)?>
<script>
$(document).ready(function()
{	
	$('#description').markItUp(mySettings);
	$("#showpreview").click(function() 
	{				
		$.ajax( //ajax request starting
		{
		url: '<?php echo url::base() ?>' + "index.php/jqcallback/bbcodepreview", 
		type:"POST",
		data: { text: $("#description").val() },
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

<div class="pagetitle"><?php echo Kohana::lang("character.change_description_titlepage")?></div>

<div id="helper">
<?php echo Kohana::lang("character.change_description_helper") . '&nbsp;' . html::anchor('https://nbbc.sourceforge.net/readme.php?page=bbc', kohana::lang('global.bbcode_guide'), array('target' => '_blank' )) ?>
</div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<?php echo form::open(url::current()); ?>

<div>
	
	
	<?php echo form::textarea( 
		array( 
		'id' => 'description',
		'name'=>'description', 
		'value' => $form['description'], 
		'rows' => 25, 
		'cols' => 90,
		) ); 
	?>
	
	<?php if (!empty ($errors['description'])) echo "<div class='error_msg'>".$errors['description']."</div>";?>
	
	<div class='center'>
	<?= form::submit( array (
			'id' => 'showpreview', 
			'class' => 'button button-small', 			
			'onclick' => 'return false' ),
			kohana::lang('global.preview')); 
	?>
	&nbsp;
	<?= form::submit( array (
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
