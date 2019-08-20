<?php echo html::script('media/js/jquery/plugins/markitup/jquery.markitup.js', FALSE)?>
<?php echo html::script('media/js/jquery/plugins/markitup/sets/bbcode/set.js', FALSE)?>
<script>

$(document).ready(function()
{	
	$('#history').markItUp(mySettings);
	$("#showpreview").click(function() 
	{				
		$.ajax( //ajax request starting
		{
		url: '<?php echo url::base() ?>' + "index.php/jqcallback/bbcodepreview", 
		type:"POST",
		data: { text: $("#history").val() },
		success: 
			function(data) 
			{					
				console.log( data );
				$("#preview").html(data); 				
			}
		}	
		);						
	});	
});
</script>

<div class="pagetitle"><?php echo Kohana::lang("character.char_history")?></div>

<div id="helper">
<?php echo Kohana::lang("character.change_history_helper") . '&nbsp;' . html::anchor('https://nbbc.sourceforge.net/readme.php?page=bbc', kohana::lang('global.bbcode_guide'), array('target' => '_blank' ))?>
</div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<?php echo form::open(url::current()); ?>
<div class="top10 center">
	<b><?php echo form::label('history', Kohana::lang('character.char_history'));?></b>
	<?php echo form::textarea( array( 
		'id' => 'history',
		'name'=>'history', 'value' => $form['history']) ); ?>
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
