<?php echo $header ?>

<table>

<tr>

<td width="10%">	

	<? 

		

		if ( $npc -> getIshuman() )

			$npc -> render_char ( $equippeditems, 'wardrobe', 'medium' );

		else

			echo html::image('media/images/npc/' . $npc -> npctag . '.png', array('class' => 'border') ); 

	?>	

</td>

<td style='vertical-align:top'>

	<?= kohana::lang('npc.' . $npc -> npctag . '_description') ?>	

</td>

</tr>

</table>

<div style='float:left;width:48%'>

	<fieldset>

	<legend><?= kohana::lang('character.attributes'); ?></legend>

	<table>

	<tr class="alternaterow">

	<td><?= kohana::lang('character.health')?></td><td class='value'><?= $npc->health/100*100; ?>%</td>

	</tr>

	<tr class="">

	<td><?= kohana::lang('global.energy')?></td><td class='value'><?= $npc->energy/50*100; ?>%</td>

	</tr>

	<tr class="alternaterow">

	<td><?= kohana::lang('global.glut')?></td><td class='value'><?= $npc->glut/50*100; ?>%</td>

	</tr>

	<tr>

	<td><?= kohana::lang('character.create_charstr')?></td><td class='value'><?= $npc->str; ?></td>

	</tr>

	<tr class="alternaterow">

	<td><?= kohana::lang('character.create_chardex')?></td><td class='value'><?= $npc->dex; ?></td>

	</tr>

	<tr>

	<td><?= kohana::lang('character.create_charcost')?></td><td class='value'><?= $npc->cost; ?></td>

	</tr>

	<tr class="alternaterow">

	<td><?= kohana::lang('character.create_charintel')?></td><td class='value'><?= $npc->intel; ?></td>

	</tr>

	<tr>

	<td><?= kohana::lang('character.create_charcar')?></td><td class='value'><?= $npc->car; ?></td>

	</tr>

	</table>

	</fieldset>

</div>

<div style='float:left;width:48%;margin-left:1%'>

<fieldset>

	<legend><?= kohana::lang('character.stats'); ?></legend>

	<table>

	<tr class="alternaterow">

	<td>

		<?= Kohana::lang( 'items.damage');?>

	</td>

	<td class="value">

		<?= $charcopy['char']['wpn_mindamage']; ?> - <?= $charcopy['char']['wpn_maxdamage']; ?>

	</td>

	</tr>

	<tr>

	<td>

		<?= Kohana::lang( 'battle.encumbrance');?>

	</td>

	<td class="value">

		<?= $charcopy['char']['armorencumbrance']; ?>%

	</td>

	</tr>

	<tr class="alternaterow">

	<td>

		<?= Kohana::lang( 'items.defense');?>

	</td>

	<td class="value">

	<?

		foreach ( array( 'head', 'armor', 'left_hand', 'right_hand', 'legs', 'feet' ) as $part )

			if (isset($partinfo[$part]))

				echo kohana::lang('items.defense') . ' ' . 

					kohana::lang('battle.part_' . $part ) .  ": <b>" . 

					$partinfo[$part]['totaldefense'] . "</b></br>";

			else

				echo kohana::lang('items.defense') . ' ' . 

					kohana::lang('battle.part_' . $part ) .  ": <b>" . 

					0 . "</b></br>";

	?>

	</td>

	</tr>

	</table>

	</fieldset>

</div>

<br style='clear:both'>

