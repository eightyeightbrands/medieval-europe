<div class='pagetitle'><?= Kohana::lang('diplomacy.diplomacyrelations') . ' - ' .  kohana::lang($region -> kingdom -> name); ?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<?php echo $submenu ?>

<table>
<thead>
<th><?php echo kohana::lang('global.kingdom')?></th>
<th><?php echo kohana::lang('global.kingdom')?></th>
<th><?php echo kohana::lang('global.type')?></th>
<th><?php echo kohana::lang('global.date')?></th>

</thead>
<tbody>
<?php
	$k=0;
	foreach ( $kingdoms as $kingdom )
		if ( ($region -> kingdom_id != $kingdom -> id) )
		{
			if( !empty($relations[$region -> kingdom_id][$kingdom -> id]['type']) ) {


				$class = ( $k % 2 == 0 ? 'alternaterow_1' : 'alternaterow_2' );
	?>

			<tr class='<?= $class; ?>'>
				<td class='center'><?php echo kohana::lang($region -> kingdom -> get_name() ) ?></td>
				<td class='center'><?php echo kohana::lang( Kingdom_Model::get_name2($kingdom->id) ) ?></td>
				<td class='center <?php echo 'diplomacy'.$relations[$region -> kingdom_id][$kingdom -> id]['type']?>'>
					<?php echo kohana::lang('diplomacy.' . $relations[$region -> kingdom_id][$kingdom -> id]['type'])?></td>
				<td class='center'><?php echo Utility_Model::format_datetime($relations[$region -> kingdom_id][$kingdom -> id]['timestamp'])?></td>
			</tr>

	<?php
			$k++;


		} else {
			?><!-- broken: <?=!empty($region -> kingdom_id) ? $region -> kingdom_id : 'nothing';?> - <?=!empty($kingdom -> id) ? $kingdom -> id : 'nothing';?> - <?php print_r($relations[$region -> kingdom_id]); ?> --><?php
		}

	}
?>
</tbody>
</table>

<div style='clear:both'></div>
