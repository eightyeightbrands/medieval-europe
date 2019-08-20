<div class="pagetitle">
<?php echo kohana::lang('structures_royalpalace.resource_report_pagetitle') ?>
</div>

<?php echo $submenu ?>

<div id='helper'><?php echo kohana::lang('structures_royalpalace.resourcereport_helper');?></div>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>

<div class='submenu'>
<?php echo html::anchor('castle/propertyreport/' . $structure->id, kohana::lang('structures_royalpalace.submenu_propertyreport'))?>
&nbsp; &nbsp; 
<?php echo html::anchor('castle/basicresourcereport/' . $structure->id, kohana::lang('structures_royalpalace.submenu_basicresourcereport'),
array('class' => 'selected' ));?>
</div>

<br/>

<table class='small'>
<tr>
<th width='20%' class='center'><?php echo kohana::lang('global.region')?></th>
<th width='15%' class='center'><?php echo kohana::lang('global.type')?></th>
<th width='15%' class='center'><?php echo kohana::lang('global.resource')?></th>
<th width='15%' class='center'><?php echo kohana::lang('global.availability')?></th>
</tr>

<?php 
$i=0;
foreach ( $report as $key => $value )	
	foreach ( $value as $key2 => $value2 )		
		foreach ( $value2 as $key3 => $value3 )
		{
			
			$class = ( $i % 2 == 0 ) ? 'alternaterow_1' : 'alternaterow_2';
			$status = Structure_Model::get_descriptiveresourcestatus( $value3 );			
			
			echo "<tr class='$class'>
				<td class='center'>".kohana::lang($key)."</td><td class='center'>" . kohana::lang($key2) . "</td>
				<td class='center'>" . kohana::lang( 'items.'.$key3. '_name') . "</td>
				<td class='center' style='font-weight:bold;color:" . $status['color'] ."'>" . kohana::lang($status['desc']) . "</td></tr>";				
			
			$i++;
		}	
?>			
</table>

<br style='clear:both'/>
