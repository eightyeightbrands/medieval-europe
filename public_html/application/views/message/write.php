<?php echo html::script('media/js/jquery/plugins/markitup/jquery.markitup.js', FALSE)?>
<?php echo html::script('media/js/jquery/plugins/markitup/sets/bbcode/set.js', FALSE)?>

<script type='text/javascript'>

$(document).ready(function()
{	
	$('#body').markItUp(mySettings);
	$("#to").autocomplete({
			source: "index.php/jqcallback/listallchars",
			minLength: 2
		});	
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

<?php echo $submenu ?>

<?php if (!$bonus) { ?>
<div style='float:right;margin:0px 0px 10px 0px'>
<?php 
	echo html::anchor( 
		'bonus/acquire_professionaldesk_bonus/',
		kohana::lang('message.upgradedesk'), array( 'class' => 'button button-medium button-red') );
?>
</div>
<?php } ?>

<div id="helper">
<?php echo Kohana::lang("message.writemessage_helper") . '&nbsp;' . html::anchor('https://nbbc.sourceforge.net/readme.php?page=bbc', kohana::lang('global.bbcode_guide'), array('target' => '_blank' ) )?>
</div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<?php echo form::open() ?>
<table width="100%" border="0">
	
	<tr>
		<td width="10%" class='right'><?php echo form::label('to', Kohana::lang('message.to'));?></td>
		<td colspan="2"><?php echo form::input( array( 
			'id' => 'to', 
			'name' => 'to', 'value' =>  $form['to'], 'style'=>'width:200px') );
		
		$role =  Character_Model::get_info( Session::instance()->get('char_id') ) -> get_current_role();
		if ( !is_null( $role ) and ($role->tag == 'king' or $role->tag == 'vassal' ))
			echo '&nbsp;' . form::label('massive', Kohana::lang('message.massive')) . '&nbsp;' . form::checkbox('massive', true, false);
		?>
		</td>		
		
	</tr>
	<?php if (!empty ($errors['to'])) echo "<tr><td></td><td colspan='2'><div class='error_msg'>".$errors['to']."</div></td></tr>";?>	
	<tr>
		<td align="right"><?php echo form::label('subject', Kohana::lang('message.subject'));?></td>
		<td colspan="2" align="left"><?php echo form::input( array( 'id'=>'subject', 'name' => 'subject', 'value' =>  $form['subject'], 'style'=>'width:350px') );?>
	</tr>
	<?php if (!empty ($errors['subject'])) echo "<tr><td></td><td colspan='2'><div class='error_msg'>".$errors['subject']."</div></td></tr>";?>		
	<tr>
		<td align="right"><?php echo form::label('type', Kohana::lang('message.type'));?></td>
		<td colspan="2" align="left">
		<?php if ( $char -> sex == 'M' ) 
			echo form::dropdown( 'type',
				array( 
					'normal' => kohana::lang('message.typenormal'), 
					'weddingproposal' => kohana::lang('message.typeweddingproposal'), 
					'weddingannulment' => kohana::lang('message.typeweddingannulment'), 
				));
			else
			echo form::dropdown( 'type',
				array( 
					'normal' => kohana::lang('message.typenormal'), 
					'weddingannulment' => kohana::lang('message.typeweddingannulment')
				));
			
		?>
		</td>
	</tr>
	
	<tr>
		<td colspan='2' align='left'>
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
			'class' => 'button button-small', 			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('message.write_submit'))
		?>
		</td>		
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
</table>
<?php echo form::close(); ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<h5>Preview</h5>
<div id="preview"></div>

<br style='clear:both'/>
