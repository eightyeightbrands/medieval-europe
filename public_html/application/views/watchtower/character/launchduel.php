<script>

$(function() {
$("#date" ).datepicker({dateFormat: "yy-mm-dd"});
$("#location").autocomplete(
{
	source: "index.php/jqcallback/listallregions",
	minLength: 2,	
})	
});
</script>

<div class="pagetitle"><?php echo kohana::lang('character.launchduel') ?></div>

<div id='helper'>
	<?= kohana::lang('character.launchduel_helper',
	Character_Model::create_publicprofilelink( $targetchar -> id, $targetchar -> name )
	);
	?>
</div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/>
	
<?php echo form::open(); ?>
	
	<table width="100%" border="0">
	<tr>
	<td width='20%'>
	<?php echo form::label('date', Kohana::lang('character.dueldate'));?>
	</td>
	<td>
	<?php echo form::input( array( 'id' => 'date', 'name' => 'date', 
		'value' => $form['date'] )); ?>	
	<?php if (!empty ($errors['date'])) echo "<div class='error_msg'>".$errors['date']."</div>";?>
	</td>
	</tr>
	
	<tr>
	<td>
	<?php echo form::label('time', Kohana::lang('character.dueltime'));?>
	</td>
	<td>
	<?php echo form::input( array( 'id' => 'time', 'name' => 'time',
		'class' => 'input-xsmall tright', 'maxlength' => 5, 'value' => $form['time'] )); ?>	
	<?php if (!empty ($errors['time'])) echo "<div class='error_msg'>".$errors['time']."</div>";?>
	</td>
	</tr>
	
	<tr>
	<td>
	<?php echo form::label('location', Kohana::lang('global.location'));?>
	</td>
	<td>
	<?php echo form::input( array( 'id' => 'location', 'name' => 'location',
		'value' => $form['location'], 'class' => 'input-large')); ?>
	<?php if (!empty ($errors['location'])) echo "<div class='error_msg'>".$errors['location']."</div>";?>
	
	
	</td>
	</tr>
	</table>
	<br/>
	
	<div class='center'>
		<?php 		
		echo form::submit( array (
				'id' => 'submit', 
				'class' => 'button button-small', 			
				'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.send'))."</td>";
		?>
	</div>

<?php echo form::close(); ?>
	
<br style='clear:both'/>
	
