<div class="pagetitle"><?php echo kohana::lang('structures_royalpalace.property_report_pagetitle') ?></div>

<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'><?php echo kohana::lang('structures_royalpalace.propertyreport_helper');?></div>

<br/>

<div class='submenu'>
<?php echo html::anchor('/castle/propertyreport/'.$structure->id, kohana::lang('structures_castle.propertyreport'), array('class' => 'selected' ));?>
&nbsp;&nbsp;
<?php echo html::anchor('/castle/basicresourcereport/'.$structure->id, kohana::lang('structures_castle.submenu_basicresourcereport'))?>
</div>
<br/>
<table class='small'>
<?php 
foreach ( $report as $key => $value )
{	
	echo "<tr>
				<td colspan='4'>" .
	
	"<br/><h5 class='center'>". kohana::lang('structures_castle.region_properties', kohana::lang($key) ) . "</h5></td></tr>";
	
	
	if ( count( $value ) == 0 )
		echo "<tr><td class='center' colspan='4'><i>" . kohana::lang('structures_castle.nopropertiesacquired') ." </i></td></tr>";
	else
	{	
		echo "<tr>";
		echo "<th width='25%'>" .  kohana::lang('global.name'). " </th>";
		echo "<th width='20%'>" . kohana::lang('global.kingdom') . "</th>";
		echo "<th width='20%'>" . kohana::lang('global.region') . "</th>";
		echo "<th width='15%'>" . kohana::lang('global.properties'). "</th>";
		echo "</tr>";
		$r = 0;
		foreach ( $value as $regionproperties )
		{	
			$class = ( $r % 2 == 0 ) ? 'alternaterow_1' : 'alternaterow_2';
			echo "<tr class='$class'>";		
			echo '<td>' . html::anchor('character/publicprofile/' . $regionproperties -> character_id,  $regionproperties -> charname ) . '</td>' ;
			echo "<td class='center'>" . kohana::lang($regionproperties -> kingdomname) . '</td>' ;
			echo "<td class='center'>"  . kohana::lang($regionproperties -> residence) . '</td>' ;
			echo "<td class='center'>"  . kohana::lang($regionproperties -> structurename) . '</td>' ;
			echo '</tr>';
			$r++;
		}
	}
}

?>
</table>

<br style='clear:both'/>
