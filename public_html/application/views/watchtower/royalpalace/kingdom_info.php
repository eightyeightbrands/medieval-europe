<div id="pagetitle"><?php echo Kohana::lang('structures.kingdom_info_title')?></div>

<div id="submenu">
<ul>
<li>
<?php echo html::anchor('region/view', kohana::lang('menu_logged.position'))?> 
</li>
</ul>
</div>

<div class="separator">&nbsp;</div>

<p><?php echo Kohana::lang("structures.kingdom_listregions") ?>:</p>

<table width="60%">
	<th></th>
	<th><?php echo Kohana::lang("global.name") ?></th>
	<th><?php echo Kohana::lang("global.vassal") ?></th>
	
	<?php foreach ( $regions as $region ) { ?>
	<tr>
	<td width=80px><?php echo html::image(array('src' => 'media/images/heraldry/'.$region->kingdom->image.'_large.png'), array('title' => Kohana::lang($region->kingdom -> get_name() ))); ?></td>
	<td><?php echo Kohana::lang($region->name) ?></td>
	<td>
		<?php
			$castle = ORM::factory('structure_type')->where( array('type'=>'castle') )->find();
			$structure = ORM::factory('structure')->where( array('region_id'=>$region->id, 'structure_type_id'=>$castle->id) )->find();
			if (! is_null($structure->character_id)) { echo $structure->character->name; }
			else { echo kohana::lang('global.vacant'); }
		?>
	</td>
	</tr>
	<?php } ?>
</table>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<h4><?php echo kohana::lang('structures.region_taxlist')?></h4>

<?php
// Se c'è almeno una tassa associata alla struttura allora visualizzo
// le tasse da modificare
if (count($taxes))
{
	echo '<table>';
	foreach ($taxes as $t) 
	{
		echo "<tr><td width=200px valing=top>". Kohana::lang($t->name) . "</td>";	
		echo "<td width=100px>" . $t->value . "</td>";
		echo "<td>" . Kohana::lang($t->description) . "</td></tr>";
	}
	echo '</table>';
	echo '<br/>';
}
else
{
	echo kohana::lang('structures.region_notaxesfound');
}
?>
