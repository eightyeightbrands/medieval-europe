<div class='pagetitle'><?php echo kohana::lang('structures_royalpalace.governmenttaxes_pagetitle')?></div>

<?php echo $submenu ?>
<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'>
	<div id='helpertext'>
		<?php echo kohana::lang('structures_royalpalace.tax_helper');?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=En_US_royalpalace#Taxes',
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button' ) ) 
		?>
	</div>
	<div style='clear:both'></div>
</div>
<div class='submenu'>
<?php echo html::anchor('royalpalace/viewlaws/'.$structure->id, kohana::lang('structures_royalpalace.viewlaws'))?>
<?php echo html::anchor('royalpalace/addlaw/'.$structure->id, kohana::lang('structures.region_addlaw')); ?>
<?php echo html::anchor('royalpalace/taxes/'.$structure->id, kohana::lang('structures_royalpalace.submenu_taxes'),
array('class' => 'selected')); ?>
</div>
<fieldset>
<legend><?php echo kohana::lang('taxes.distributiontax_name') ?></legend>

<p>
<?php echo kohana::lang('taxes.distributiontax_description') ?>
</p>

<div class='center'>
<?php 
	echo form::open();
	echo form::hidden('structure_id', $structure -> id );
	echo form::input( 
		array( 
			'name' => 'distributiontax', 			
			'value' => $distributiontax -> citizen, 
			'class' => 'input-xsmall right') );
	echo '% ';
	echo form::submit(
		array( 
			'name' => 'setdistributiontax', 
			'value' => kohana::lang('global.edit'),		
			'class' => 'button button-small'
			)
	);
	echo form::close();
?>
</div>
</fieldset>

<br style = 'clear:both'/>
	