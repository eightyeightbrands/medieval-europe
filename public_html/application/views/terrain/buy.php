<div class="pagetitle"><?php echo kohana::lang($region->name) . " - " . kohana::lang("structures_actions.terrain_buy") ?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<!--
<div id='helper'>
	<?php if (0) echo kohana::lang('structures_terrain.helper', $propertyprice )?>
</div>
-->
<br/>
<br/>
<table>
<tr class="alternaterow_1">
<td><?php echo kohana::lang("structures.region_maxterrains").":"?></td>
<td style="text-align:right"><?php echo "<b>" . $terrains_info['max_terrains_x_region'] . "</b>" ?></td>
</tr>
<tr class="alternaterow_2">
<td><?php echo kohana::lang("structures.region_takenterrains").":"?></td>
<td style="text-align:right"><?php echo "<b>" . $terrains_info['terrains_taken'] . "</b>" ?></td>
</tr>
<tr class="alternaterow_1">
<td><?php echo kohana::lang("structures.region_freeterrains").":"?></td>
<td style="text-align:right"><?php echo "<b>" . $terrains_info['terrains_free'] . "</b>" ?></td>
</tr>

<tr><td colspan=2><hr class="top10 bottom10"></td></tr>

<tr>
<td><?php echo kohana::lang("structures.region_currentbuyingprice").":"?></td>
<td style="text-align:right"><?php echo "<b>" . $price . "</b> &nbsp;" . kohana::lang("items.silvercoin_name");?></td>
</tr>
</table>

<br/>

<div class='center'>
	<?= html::anchor( 
			'/structure/buy/terrain_1',
			kohana::lang('global.buy'),
			array( 
				'class' => 'button button-medium', 
				'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' )
			);
	?>
</div>

<?= form::close(); ?>

<br style='clear:both;'/>
