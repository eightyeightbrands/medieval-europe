<div class="pagetitle">
<?php echo kohana::lang('structures_royalpalace.declarehostileaction_pagetitle') ?>
</div>

<?php echo $submenu ?>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>


<fieldset>
<legend><?=kohana::lang('structures_royalpalace.wars');?></legend>

<div class='helper'>
<?php echo kohana::lang('structures_royalpalace.declarewar_helper') ?>
</div>

<? if (count($kingdomwars) > 0) { ?>
	
	<p><?=kohana::lang('structures_royalpalace.currentwars');?></p>
	<table>
	<th><?= kohana::lang('global.sourcekingdom'); ?></th>
	<th><?= kohana::lang('global.targetkingdom'); ?></th>
	<th><?= kohana::lang('global.start'); ?></th>
	<th></th>
	<? foreach ($kingdomwars as $kingdomwar) { ?>
	<?= form::open('royalpalace/finishwar'); ?>
	<?= form::hidden('war_id', $kingdomwar['war']->id ); ?>
	<?= form::hidden('structure_id', $structure -> id ); ?>
	<tr>
		<td class='center'>
			<?
			$sourcecapital = Kingdom_Model::get_capitalregion( $kingdomwar ['war'] -> source_kingdom_id );			
			echo html::anchor(
			'region/info_diplomacy/' . $sourcecapital -> id,
			kohana::lang($kingdomwar ['war'] -> sourcekingdomname),		
			array('target' => 'new') ); 
			?>
		</td>
		<td class='center'>
			<?=		
			html::anchor(
			'region/info_diplomacy/' . $kingdomwar ['war'] -> id, 
			kohana::lang($kingdomwar ['war'] -> targetkingdomname),		
			array('target' => 'new') ); 
			?>
		</td>
	<td class='center'><?= Utility_model::format_datetime($kingdomwar['war']->start); ?></td>
	<td class='center'>
		<? 
		if ($kingdomwar['war']->source_kingdom_id == $structure -> region -> kingdom_id )
		{
		?>
		<?= form::submit( array(
			'id' => 'endwar',
			'name' => 'endwar',
			'value' => kohana::lang('structures_royalpalace.endwar'),
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ,
			'class' => 'button-medium')); 
		?>
		<?= form::close(); ?>				
		<? } ?>
	</tr>
	<? } ?>
	</table>
	<br/>
	<div class='center'>
		<?= html::anchor(
			'royalpalace/conquer_r/' . $structure->id, 
			kohana::lang('structures_royalpalace.submenu_conquer_or'),
			array('class' => 'button button-medium'));
		?>&nbsp;
		<?= html::anchor(
			'royalpalace/raid/' . $structure->id, 
			kohana::lang('structures_royalpalace.submenu_raid'),
			array('class' => 'button button-medium'));
			?>
	</div>
<? } else { ?>
<div class='center'>
<?= form::open('royalpalace/declarewar') ?>
<?= form::hidden('structure_id', $structure -> id ); ?>
<?= kohana::lang('structures_royalpalace.declarewarto');?>
<?= form::dropdown(
		array(
			'id' => 'kingdom',
			'name' => 'kingdom', 
			'class' => 'small'			
		),
		$kingdomlist
	);
?>
<?= form::submit( array(
	'id' => 'declarewar',
	'name' => 'declarewar',
	'value' => kohana::lang('structures_royalpalace.declarewar'),
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ,
	'class' => 'button-small')); 
?>

</div>
<? } ?>
</fieldset>

<br/>

<fieldset>
<legend><?=kohana::lang('structures_royalpalace.otherattacks')?></legend>
<div class='helper'>
<?php echo kohana::lang('structures_royalpalace.declareotherattacks_helper') ?>
</div>

<div class='center'>
<?php echo html::anchor(
	'royalpalace/conquer_ir/' . $structure->id, 
	kohana::lang('structures_royalpalace.submenu_conquer_ir'),
	array(
	'class' => 'button button-large')
	); 
?>
</div>
</fieldset>

<br style='clear:both'/>
