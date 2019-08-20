<script type='text/javascript'>
 $(document).ready(function()
 {	
	
	$('input[name=attackcost]').val(200);
	$('#cost').html( '<?php echo kohana::lang('global.price') ?>' + ': <b>200<b>' );
	$("#attackedregion").autocomplete({
		source: "index.php/jqcallback/listallregions/attackable",
		minLength: 2,	
	});
});	
</script>


<div class="pagetitle">
<?php echo kohana::lang('structures_royalpalace.raidpagetitle') ?>
</div>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'>
  <div id='helpertext'>
		<?php echo kohana::lang('structures_royalpalace.raid_helper') ?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=En_US_Raiding_Region',
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
		'class' => 'button button-medium',
		));
?>

<?= 
	 html::anchor('royalpalace/raid/' . $structure->id, 
		kohana::lang('structures_royalpalace.submenu_raid'),
	array( 
		'class' => 'button button-medium selected',
		));
?>
</div>
<br/>
<fieldset>
<?php echo form::open() ?>
<?php echo form::hidden('structure_id', $structure -> id ); ?>
<?php echo form::hidden('attackedregion_id', $form['attackedregion_id'])?>
<?php echo form::hidden('attackcost')?>
<?= 
	form::label('attackedregion', Kohana::lang('structures_royalpalace.attackedregion')); 
?>
&nbsp;
<?=
	form::input( 
		array( 
			'id'=>'attackedregion', 
			'class' => 'input-large',
			'name' => 'attackedregion', 
			'value' =>  $form['attackedregion'])); 
?>


<br/>
<?= form::label(
	'relictoraid', 
	Kohana::lang('structures_royalpalace.relictoraid'));
?>
&nbsp;
<?= 
	form::dropdown( 
		'relictoraid',
		array( 
			'' => kohana::lang('ca_declarewaraction.dontraidrelic'),
			'relic_rome' => kohana::lang('items.relic_rome_name'),
			'relic_kiev' => kohana::lang('items.relic_kiev_name'),
			'relic_turnu' => kohana::lang('items.relic_turnu_name'),
			'relic_cairo' => kohana::lang('items.relic_cairo_name'),
			'relic_norse' => kohana::lang('items.relic_norse_name'),
		)
	); 
?>

<br/>

<div class='center'>

<?=
	form::submit( 
		array( 
			'id' => 'submit', 
			'name' => 'declare', 
			'class'=> 'button button-small', 
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ,
			'value' => Kohana::lang('global.declare'))
	);
?>
<?= form::close(); ?>
</div>
</fieldset>

<br style='clear:both'/>
