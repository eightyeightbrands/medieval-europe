<head>
<script>

$(document).ready(
 
	function()
	{	
		$("#independentregion").autocomplete(
		{
			source: "index.php/jqcallback/listallregions/independent",
			minLength: 2
		});
		
		$("#captain").autocomplete( 
		{
			source: "index.php/jqcallback/listallchars/captains",
			minLength: 2
		});	
	}
 
);
</script>
</head>

<div class="pagetitle">
	<?php echo kohana::lang('structures_royalpalace.conquer_ir_pagetitle') ?>
</div>

<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'>
  <div id='helpertext'>
		<?php echo kohana::lang('structures_royalpalace.conquer_ir_helper') ?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=En_US_Conquering_Independent_Region',
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button' ) ) 
		?>
	</div>
	<div style='clear:both'></div>
</div>

<div class='center'>
<?= 
	html::anchor(
	'royalpalace/conquer_ir/' . $structure->id, 
	kohana::lang('structures_royalpalace.submenu_conquer_ir'),
	array( 
		'class' => 'button button-medium selected',
		));
?>
&nbsp;&nbsp;
<?= 
	html::anchor('royalpalace/conquer_r/' . $structure->id, 
	kohana::lang('structures_royalpalace.submenu_conquer_or'),
	array( 
		'class' => 'button button-medium',
		));
?>

<?= 
	 html::anchor('royalpalace/raid/' . $structure->id, 
		kohana::lang('structures_royalpalace.submenu_raid'),
	array( 
		'class' => 'button button-medium',
		));
?>
</div>
<br/>

<fieldset>
<?php echo form::open('royalpalace/conquer_ir') ?>
<?php echo form::hidden('structure_id', $structure->id ); ?>
<?php 
	echo Kohana::lang('structures_royalpalace.appoint') . '&nbsp;' ; 
	echo form::input( array( 'id'=>'captain', 'name' => 'captain', 'value' =>  $form['captain'], 'style'=>'width:150px') ) . '&nbsp;' ; 
	if (!empty ($errors['captain'])) echo "<div class='error_msg'>" . $errors['captain'] . "</div>"; 	
	echo Kohana::lang('structures_royalpalace.independentregion') . '&nbsp;' ; 
	echo form::input( array( 'id'=>'independentregion', 
		'name' => 'independentregion', 
		'value' =>  $form['independentregion'], 
		'style' => 'width:150px') ) . '&nbsp;' ; 
	if (!empty ($errors['independentregion'])) echo "<div class='error_msg'>" . $errors['independentregion'] . "</div>"; 
	echo '<br/><br/>';
	echo Kohana::lang('structures_royalpalace.notes') . '&nbsp;' ; 
	echo '<br/>';
	echo form::textarea( array( 
		'id' => 'notes', 
		'name' => 'notes', 
		'value' =>  $form['notes'], 
		'cols' => '90', 
		'rows' => '10') );
	
?>

<br/>

<div class='center'>
<?php	
	echo form::submit( array( 
		'id' => 'submit', 
		'class'=> 'button button-small', 
		'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 
		'value' => Kohana::lang('global.order')));
?>
</div>
<?php echo form::close(); ?>
</fieldset>
<br style='clear:both'/>
