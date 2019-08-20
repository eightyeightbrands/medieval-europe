<?php echo html::script('media/js/jquery/plugins/markitup/jquery.markitup.js', FALSE)?>
<?php echo html::script('media/js/jquery/plugins/markitup/sets/bbcode/set.js', FALSE)?>
<script>
$(document).ready(function()
{	
	$('#law_desc').markItUp(mySettings);   
	$("#showpreview").click(function() 
		{				
			$.ajax( //ajax request starting
			{
			url: "index.php/jqcallback/bbcodepreview", 
			type:"POST",
			data: { text: $("#law_desc").val() },
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


<div class="pagetitle"><?php echo Kohana::lang('structures_castle.addlaw_pagetitle') ?></div>

<?php echo $submenu ?>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>

<div class='submenu'>
<?php echo html::anchor('royalpalace/viewlaws/'.$structure->id, kohana::lang('structures_royalpalace.viewlaws'))?>
<?php echo html::anchor('royalpalace/addlaw/'.$structure->id, kohana::lang('structures.region_addlaw'),
array('class' => 'selected')); ?>
<?php echo html::anchor('royalpalace/taxes/'.$structure->id, kohana::lang('structures_royalpalace.submenu_taxes'))?>
</div>

<br/>
<fieldset>
<?php echo form::open() ?>
<?php echo form::hidden('structure_id', $structure->id) ?>
<div><?php echo form::label('law_name', kohana::lang('global.title')) ?></div>
<div>
<?php 
	echo form::input(array( 
		'id' => 'law_name', 
		'name' => 'law_name', 
		'value' => $form['law_name'], 
		'class' => 'input-xlarge' ) );
	if (!empty ($errors['law_name'])) 
		echo "<div class='error_msg'>".$errors['law_name']."</div>";
?>
</div>
	
<br/>
<div><?php echo form::label('law_desc', kohana::lang('global.description')) ?></div>
<div><?php echo form::textarea(
	array( 
		'id' => 'law_desc', 
		'name' => 'law_desc', 
		), 
		empty( $form['law_desc']) ? '' : 	$form['law_desc'] )?></div>

		<?php if (!empty ($errors['law_desc'])) echo "<div class='error_msg'>".$errors['law_desc']."</div>";?>

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
			'name' => 'add', 
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.proclaim'))
		?>
</div>

<?php echo form::close() ?>
<br/>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/><br/>
<h5>Preview</h5>
<div id="preview"></div>
</fieldset>

<br style='clear:both'/>
