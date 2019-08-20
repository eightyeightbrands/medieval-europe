<script>

$(document).ready(function()
{	
	$("[name=celebratewife]").autocomplete( 
		{
			source: "index.php/jqcallback/listallchars/F",
			minLength: 2
		});	
	$("[name=celebratehusband]").autocomplete({
			source: "index.php/jqcallback/listallchars/M",
			minLength: 2
		});	
	$("#annulmentchar").autocomplete( {
			source: "index.php/jqcallback/listallchars",
			minLength: 2
		});	
});
	
</script>

<div class="pagetitle"><?php echo kohana::lang('structures.' . $structure -> structure_type -> type . '_' . 
	$structure -> structure_type -> church -> name )  ?></div>

<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>


<div id='helper'>
	<div id='helpertext'>
		<?php echo kohana::lang('structures_religion_4.celebratemarriage_helper');?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=En_US_Religious_Structure_level_4#Cure_diseases',
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button' ) ) 
		?>
	</div>
	<div style='clear:both'></div>
</div>

<fieldset>
<legend><?php echo kohana::lang('structures_religion_4.celebratewedding')?></legend>
<div>
<?php echo form::open() ?>
<?php echo form::hidden('structure_id', $structure -> id );?>
<?php echo kohana::lang('structures_religion_4.husband') . 
	form::input(array( 
		'id' => 'celebratehusband',
		'name' => 'celebratehusband',
		'value' => $celebratehusband
		)) .
	'&nbsp;' . 
	kohana::lang('structures_religion_4.wife') . 
	form::input(array( 
		'id' => 'celebratewife',
		'name' => 'celebratewife',
		'value' => $celebratewife
		));
?> 

<?php echo 
form::submit(
	array( 
		'id' => 'startmarriage', 
		'name' => 'startmarriage',
		'value' => kohana::lang('structures_religion_4.celebratewedding'),
		'class' => 'button button-medium'));?>
<?php echo form::close(); ?>
</div>
</fieldset>
<br/>
<fieldset>
<legend><?php echo kohana::lang('structures_religion_4.cancelwedding')?></legend>
<div>
<?php echo form::open() ?>
<?php echo form::hidden('structure_id', $structure -> id );?>
<?php echo kohana::lang('structures_religion_4.annulmentchar') . 
	form::input(array( 
		'id' => 'annulmentchar',
		'name' => 'annulmentchar',
		'value' => $annulmentchar
		));
?> 
<?=
form::submit(
	array( 
		'id' => 'cancelmarriage', 
		'name' => 'cancelmarriage',
		'value' => kohana::lang('structures_religion_4.cancelwedding'),
		'class' => 'button button-medium'));?>
		

<?php echo form::close(); ?>
</div>
</fieldset>

<br style='clear:both'/>
