<?php echo html::script('media/js/jquery/plugins/markitup/jquery.markitup.js', FALSE)?>
<?php echo html::script('media/js/jquery/plugins/markitup/sets/bbcode/set.js', FALSE)?>
<script>

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

<div class="pagetitle"><?php echo kohana::lang('kingdomforum.addtopic')?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/><br/>
<div id='breadcrumb'>
<?php echo html::anchor('region/kingdomboards/' . $currentboard -> kingdom -> id, kohana::lang('kingdomforum.forumtitle', kohana::lang($currentboard -> kingdom -> name))) . ' > ' . kohana::lang('kingdomforum.addtopic');?>
</div>
<br/>

<?php echo form::open() ?>
<?php echo form::hidden('board_id', $currentboard -> id);?>
<div><?php echo form::label('title', kohana::lang('global.title')) ?> &nbsp; <?php 
	echo form::input(
	array( 
		'id' => 'title', 
		'name' => 'title', 
		'value' => $form['title'], 
		'class' => 'input-xxlarge',
		)
	);?>

</div>
<?php if (!empty ($errors['title'])) echo "<div class='error_msg'>".$errors['title']."</div>";?>

<br/>

<div><?php echo form::textarea(array( 
	'id' => 'body', 
	'name' => 'body', 
	'rows' => 20, 
	'cols' => 90),
(empty( $form['body']) ? '' : 	$form['body'] ) )?></div>
<?php if (!empty ($errors['body'])) echo "<div class='error_msg'>".$errors['body']."</div>";?>

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
	<?php echo form::submit( 
		array( 
			'id' => 'submit',  
			'name' => 'save', 
			'class' => 'button button-small', 		
			'value' => kohana::lang('global.save') )) ;
	?>	
</div>

<?php echo form::close() ?>

<div id='preview'></div>

<br style='clear:both'/>
