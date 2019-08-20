<div class="pagetitle"><?php echo kohana::lang('structures_royalpalace.resource_report_pagetitle') ?></div>

<?php echo $submenu ?>
<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'><?php echo kohana::lang('structures_royalpalace.resourcereport_helper');?></div>

<div class='submenu'>
<?php echo html::anchor('royalpalace/resourcereport/' . $structure->id, kohana::lang('structures_royalpalace.submenu_resourcereport'));?>
<?php echo html::anchor('royalpalace/basicresourcereport/' . $structure->id, kohana::lang('structures_royalpalace.submenu_basicresourcereport'),
array('class' => 'selected' ));?>
<?php echo html::anchor('royalpalace/propertyreport/' . $structure->id, kohana::lang('structures_royalpalace.submenu_propertyreport'))?>
</div>

<br/>
<table>

<tr>
<th width='20%' class='center'><?php echo kohana::lang('global.region')?></th>
<th width='15%' class='center'><?php echo kohana::lang('global.type')?></th>
<th width='15%' class='center'><?php echo kohana::lang('global.resource')?></th>
<th width='15%' class='center'><?php echo kohana::lang('global.availability')?></th>
</tr>

<?php 
$i=0;

foreach ( $report as $region => $structure )	
{
	
	foreach ( $structure as $resourcestructure => $resources )		
	{
		
		foreach ( $resources as $resource => $capacity )
		{
			
			$class = ( $i % 2 == 0 ) ? 'alternaterow_1' : 'alternaterow_2';
			$status = Structure_Model::get_descriptiveresourcestatus( $capacity );			
			
			echo "<tr class='$class'>
				<td class='center'>".kohana::lang($region)."</td>
				<td class='center'>" . kohana::lang( $resourcestructure ) . "</td>
				<td class='center'>" . kohana::lang( 'items.' . $resource . '_name') . "</td>
				<td class='center' style='font-weight:bold;color:" . $status['color'] ."'>" . kohana::lang($status['desc']) . "</td></tr>";			
			$i++;
		}
		
	}
}
?>
			


</table>

<br style='clear:both'/>
