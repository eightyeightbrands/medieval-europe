<script type="text/javascript">
$(document).ready ( function () 
{
	$('#paidpercentage, #freepercentage').focusout(function() 
	{
		console.log($(this).val());
		if ($(this).val() % 2 !== 0)
			n = Number($(this).val()) == NaN ? 0 : Number($(this).val());
			console.log(n);
			$(this).val(n+1);
	}
	);		
})
</script>
<div class="pagetitle"><?php echo kohana::lang("structures_tavern.rest_pagetitle");?></div>


<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helperwithpic'>
<div id='locationpic'>
<?php echo html::image('media/images/template/locations/rest_' . $structure -> structure_type -> supertype . '.jpg' ) ?>
</div>

<div id='helper'>
<?php echo kohana::lang( 'structures_tavern.rest_helper' );?>
</div>
</div>

<br style='clear:both'/>

<!-- Paid -->
<fieldset>
<legend><?= kohana::lang('structures_tavern.rest_pagetitle');?></legend>
<?php echo form::open('tavern/rest') ?>
<?php echo form::hidden('structure_id', $structure -> id ); ?>
<?php echo form::hidden('mode', 'paid' ); ?>

<div class='center'>
<?= kohana::lang('structures_tavern.paidrestprice',
	round($character -> energy / 50*100,0),	
	$price/2,
	round(($paidrestinfo['restfactor']),2)/50*100,
	form::input(
	array( 		
		'id' => 'paidpercentage',
		'name' => 'percentage', 
		'class' => 'input-xxsmall',
		'style' => 'text-align:right',
		'value' => '100'
	))
); 
echo "&nbsp;";
echo form::submit( array (
	'id' => 'submit', 
	'class' => 'button', 			
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' 
	), 
	kohana::lang('global.rest')
);
?>
</div>

<?php echo form::close() ?>
</fieldset>
<br/>

<!-- Free -->
<?php if ( $character -> get_age() > 90 ) 
{ 
?>

<fieldset>
<legend><?= kohana::lang('structures_tavern.freerest');?></legend>
<?php echo form::open('tavern/rest') ?>
<?php echo form::hidden('structure_id', $structure -> id ); ?>
<?php echo form::hidden('mode', 'free' ); ?>
<div class='center'>
<?= kohana::lang('structures_tavern.freerestprice',
	round($character -> energy / 50*100,0),	
	round(($freerestinfo['restfactor']),2)/50*100,
	form::input(
	array( 
		'id' => 'freepercentage',
		'name' => 'percentage', 
		'class' => 'input-xxsmall',
		'style' => 'text-align:right',
		'value' => '100'
	))
); 
echo "&nbsp;";
echo form::submit( array (
	'id' => 'submit', 
	'class' => 'button-medium ', 			
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' 
	), 
	kohana::lang('global.rest')
);
?>
</div>

<?php echo form::close() ?>
</fieldset>

<?php } ?>

<br style="clear:both;" />
