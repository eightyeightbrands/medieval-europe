<script>
$(document).ready(function()
 {	
	
	$('input[name=attackcost]').val(200);
	$('#cost').html( '<?php echo kohana::lang('global.price') ?>' + ': <b>200<b>' );
 
	$("#attackedregion").autocomplete( {
	source: "index.php/jqcallback/listallregions/attackable",
	minLength: 2,	
	});	
	
	$("#kingcandidate").autocomplete( {
			source: "index.php/jqcallback/listallchars",
			minLength: 2
		}
	);			

});	
</script>

<div class="pagetitle">
<?php echo kohana::lang('structures_royalpalace.conquer_r_pagetitle') ?>
</div>

<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'>
  <div id='helpertext'>
		<?php echo kohana::lang('structures_royalpalace.conquer_r_helper') ?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=En_US_Conquering_Region',
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
		'class' => 'button button-medium',
		));
?>
&nbsp;&nbsp;
<?= 
	html::anchor('royalpalace/conquer_r/' . $structure->id, 
	kohana::lang('structures_royalpalace.submenu_conquer_or'),
	array( 
		'class' => 'button button-medium selected',
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
<?php echo form::open() ?>
<?php echo form::hidden('structure_id', $structure -> id ); ?>
<?php echo form::hidden('attackedregion_id', $form['attackedregion_id'])?>
<?php echo form::hidden('attackcost')?>

<?php
	echo form::label('attackedregion', Kohana::lang('structures_royalpalace.attackedregion')) . '&nbsp;'; 
	echo form::input( array( 'id'=>'attackedregion', 'name' => 'attackedregion', 'value' =>  $form['attackedregion'], 'style'=>'width:150px') );
	echo '<br/>';
	echo form::label('king', Kohana::lang('structures_royalpalace.kingcandidate')) . '&nbsp;'; 
	echo form::input( array( 'id'=>'kingcandidate', 'name' => 'kingcandidate', 'style'=>'width:150px') );			
	
?>

<br/>

<div class='center'>
<?php 	
	
	echo form::submit( array( 'id' => 'submit', 'name' => 'declare', 'class'=> 'button button-medium', 
		'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ,
	'value' => Kohana::lang('global.declare')));
	
	echo form::close(); 
?>
</div>
</fieldset>
<br style='clear:both'/>
