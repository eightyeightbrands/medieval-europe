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

<div class="pagetitle"><?php echo kohana::lang('structures_royalpalace.infoannouncement_pagetitle')?></div>

<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'><?php echo kohana::lang('structures_royalpalace.infoannouncement_helper') ?></div>

<div class='submenu'>
<?php echo html::anchor('royalpalace/welcomeannouncement/' . $structure->id, kohana::lang('structures_royalpalace.submenu_welcomemessage')	)?>
<?php echo html::anchor(
	'royalpalace/infoannouncement/' . $structure->id, kohana::lang('structures_royalpalace.submenu_infoannouncement'),
	array( 'class' => 'selected' ))?>
<?php echo html::anchor(
	'royalpalace/add_slogan/' . $structure->id, kohana::lang('structures_royalpalace.submenu_addslogan'))?>		
</div>

<br/>

<fieldset>
<legend><?php echo kohana::lang('structures_royalpalace.configurelanguages'); ?></legend>
<?php echo form::open() ?>
<?php echo kohana::lang('structures_royalpalace.alternativelanguage1'); ?>
<?php echo form::dropdown( 'language1', $languages, $structure -> region -> kingdom -> language1 ); ?>
<br/>
<?php echo kohana::lang('structures_royalpalace.alternativelanguage2'); ?>
<?php echo form::dropdown( 'language2', $languages, $structure -> region -> kingdom -> language2 ); ?>
<center>
<?php echo form::submit( 
	array( 'id' => 'submit',  'name' => 'configurelanguages', 'class' => 'button button-small', 
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ,
	'value' => kohana::lang('global.save') )) ;
?>	
</center>
<?php echo form::hidden( 'structure_id', $structure -> id ); ?> 
<?php echo form::close() ?>
</fieldset>

<fieldset>
<legend><?php echo kohana::lang('structures_royalpalace.configureinformativetext'); ?></legend>
<?php echo form::open(); ?>
<?php echo form::hidden( 'id', $form['id']); ?> 

<div><?php echo form::label('title', kohana::lang('global.title')) ?></div>
<div>
<?php echo form::input(
	array( 
		'id' => 'title', 
		'name' => 'title', 
		'value' => $form['title'],
		'style' => 'width:300px',
		'maxlength' => '50'
		));?>
<br/>
</div>

<div><?php echo form::label('title', kohana::lang('message.body')) ?></div>

<div>
<?php echo form::textarea( 
	array( 
		'id' => 'body',
		'name' => 'body', 
		'rows' => '20', 
		'value' => $form['body']) )?>
</div>

<?php echo form::hidden( 'structure_id', $structure -> id ); ?> 
	
<center>
<?php
	echo form::submit( array (
		'id' => 'showpreview', 
		'class' => 'button button-small', 			
		'onclick' => 'return false' ),
		kohana::lang('global.preview')); 
?>
&nbsp;
<?php echo form::submit( 
	array( 'id' => 'submit',  'name' => 'configureinformativetext', 'class' => 'button button-small', 
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ,
	'value' => kohana::lang('global.save') )) ;
?>	
</center>

<?php echo form::close() ?>

</fieldset>

<br/>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<h5>Preview</h5>

<div id="preview"></div>

<br style='clear:both'/>
