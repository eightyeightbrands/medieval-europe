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
 
<div class="pagetitle"><?php echo kohana::lang('admin.gamemessage')?></div>

<?php echo $submenu ?>

<div id='helper'>
Puoi specificare un messaggio che apparir&agrave; a tutti i giocatori. i BB code sono gestiti. Il messaggio 
va scritto in Inglese.
</div>

<br/>

<?php echo form::open(); ?>

<?php echo 'Breve descrizione - max 255 Char (BBcode gestiti) '; ?>
<?php	echo form::textarea( array( 'id' => 'summary', 
	'name' => 'summary', 'value' => $form['summary'], 'rows' => 3, 'cols' => 80 ) ); ?>
<?php if (!empty ($errors['summary'])) echo "<div class='error_msg'>".$errors['summary']."</div>";?>

<br/>
<?php echo 'Descrizione (BBCode gestiti)'; ?>
<?php	echo form::textarea( array( 'id' => 'body', 'name' => 'message', 'value' => $form['message'], 'rows' => 20, 'cols' => 130 ) ); ?>
<?php if (!empty ($errors['message'])) echo "<div class='error_msg'>".$errors['message']."</div>";?>

<br/><br/>

<center>
<?php echo form::submit( array (
	'id' => 'showpreview', 
	'class' => 'button button-small', 			
	'onclick' => 'return false' ),
	kohana::lang('global.preview')); 
?>
&nbsp;
<?php echo form::submit( array( 
	'id' => 'submit', 
	'class' => 'button button-small', 
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.confirm')));?>
</center>
<?php echo form::close() ?>

<br/>

<div id='preview'></div>

<br style="clear:both;" />
