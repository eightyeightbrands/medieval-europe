<div class="pagetitle"><?php echo kohana::lang('structures_royalpalace.resource_report_pagetitle') ?></div>

<style>
table
{
width:100%;
font-size:11px;
}

table td, th	
{
border:1px solid #999;	
}
</style>

<?php echo $submenu ?>
<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'><?php echo kohana::lang('structures_royalpalace.resourcereport_helper');?></div>

<div class='submenu'>
<?php echo html::anchor('royalpalace/resourcereport/' . $structure->id, kohana::lang('structures_royalpalace.submenu_resourcereport'),
array('class' => 'selected' ));?>
<?php echo html::anchor('royalpalace/basicresourcereport/' . $structure->id, kohana::lang('structures_royalpalace.submenu_basicresourcereport'))?>
<?php echo html::anchor('royalpalace/propertyreport/' . $structure->id, kohana::lang('structures_royalpalace.submenu_propertyreport'))?>
</div>

<br/>

<table>

<th width='20%'><?php echo kohana::lang('global.resource')?></th>
<th width='10%'><?php echo kohana::lang('global.region')?></th>
<th width='7%' class='center'><?php echo kohana::lang('structures.royalpalace_1')?></th>
<th width='7%' class='center'><?php echo kohana::lang('structures.castle_1')?></th>
<th width='7%' class='center'><?php echo kohana::lang('structures.court_1')?></th>
<th width='7%' class='center'><?php echo kohana::lang('structures.barracks_1')?></th>
<th width='7%' class='center'><?php echo kohana::lang('structures.academy_1')?></th>
<th width='7%' class='center'><?php echo kohana::lang('structures.trainingground_1')?></th>
<th width='7%' class='center'><?php echo kohana::lang('structures.watchtower_1')?></th>
<th width='7%' class='center'><?php echo kohana::lang('global.total')?></th>

<?php

	// ciclo su items
	foreach ( $cfgitems as $c)
	{
		$itemtotal = 0;
		$citytotal = 0;
		$count=0;
		foreach ( $regions as $n )
		{
			
			// conta il totale item per città
			$citytotal = 
				$report[$c->tag][$n->name]['royalpalace'] +  
				$report[$c->tag][$n->name]['castle'] + 
				$report[$c->tag][$n->name]['court'] + 
				$report[$c->tag][$n->name]['barracks'] +
				$report[$c->tag][$n->name]['academy'] +
				$report[$c->tag][$n->name]['trainingground']+
				$report[$c->tag][$n->name]['watchtower'] ;
			
			if (  $citytotal > 0 )
			{
				echo '<tr>' ;				
					
					if ( $count == 0 )
					{
						echo '<td>' . kohana::lang($c->name) . '</td>';
						echo "<td class='center'>" .  kohana::lang($n->name) . '</td>';
					}
					else
					{
						echo '<td></td>';
						echo "<td class='center'>" .  kohana::lang($n->name) . '</td>';
					}
					
					echo 
					"<td class='right'>" .  $report[$c->tag][$n->name]['royalpalace'] . '</td>'.
					"<td class='right'>"  . $report[$c->tag][$n->name]['castle']. '</td>'.
					"<td class='right'>"  . $report[$c->tag][$n->name]['court']. '</td>'.
					"<td class='right'>"  . $report[$c->tag][$n->name]['barracks']. '</td>'.
					"<td class='right'>"  . $report[$c->tag][$n->name]['academy']. '</td>'.
					"<td class='right'>"  . $report[$c->tag][$n->name]['trainingground']. '</td>'.
					"<td class='right'>"  . $report[$c->tag][$n->name]['watchtower']. '</td>'.
					"<td class='right'>" .  $citytotal .  '</td>';
					
				echo '</tr>';
				$itemtotal += $citytotal;
				$count++;
			}
			
		}
		
		if ( $itemtotal > 0 )
			echo "<tr><td colspan='10' class='right'><b>" . kohana::lang('global.total') . ': ' .  $itemtotal . "</b></td></tr>";
}

?>
</table>
