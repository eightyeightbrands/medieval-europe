<head>
<script type='text/javascript'>

 $(document).ready(function() {
	$("#region").change(function() {
				var url = '<?php echo url::base( true ). 'religion_3/buildstructures/' . $structure->id . '/' ?>';
				url += $('#region').val();
				window.location.replace( url );
	})
	});	 
</script>
</head>

<div class="pagetitle"><?php echo kohana::lang('structures_religion_3.buildprojects_pagetitle') ?></div>

<?php echo $submenu ?>

<?php 
echo form::open();
echo kohana::lang('structures.prj_city'). " - " . form::dropdown ( 
	array( 'id' => 'region', 'name' => 'region', 'value' => $region -> name ) , 
	$combo_regions, $region -> id );	
echo form::close();
?>

<br/>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>

<?php
echo "<table width='100%'>";
echo "<tr><td class='center' colspan='4'>" . "<h4>" . kohana::lang('structures_royalpalace.runningprojects' ) . "</h4>" . "</td></tr>"; 
echo "<tr><td class='center' colspan='4'></td></tr>"; 
echo "<th style='width:50%' class='center'>" . kohana::lang('global.project' ) . '</th>';
echo "<th style='width:10%' class='center'>" . kohana::lang('global.status' ) . '</th>';
echo "<th style='width:30%' class='center'>" . kohana::lang('global.needs' ) . '</th>';
echo "<th style='width:10%' class='center' >" . kohana::lang('global.action' ) . "</th>";

if ( count( $runningprojects ) == 0 ) 
{
	echo "<tr><td class='center' colspan='4'></td></tr>"; 
	echo "<tr><td class='center' colspan='4'>" . "<i>" . kohana::lang('structures_royalpalace.norunningprojects') . "</i>" . "</td></tr>"; 
	echo "<tr><td class='center' colspan='4'></td></tr>"; 

}
else
{
	foreach ($runningprojects as $runningproject )
	{	
		
		$kp = ORM::factory('kingdomproject', $runningproject -> id ) ;
	
		echo "<tr style='height:20px'>";
		echo form::open();
		echo form::hidden('region_id', $region -> id );
		echo form::hidden('structure_id', $structure -> id );
		echo form::hidden('kingdomproject_id', $runningproject -> id );
		echo "<td>";
		echo "<div style='float:left;border:0px solid'>";
		echo html::image('media/images/structures/' . $kp -> cfgkingdomproject -> tag . '.jpg', array('class' => 'size75') );
		echo "</div>";
		echo "<div style='margin-left:2px;font-size:11px;float:left;width:200px;padding:0px 2px;border:0px solid;text-align:justify'>";
		echo "<b>". kohana::lang($kp -> cfgkingdomproject -> name) . "</b><br/>". kohana::lang($kp -> cfgkingdomproject -> description) ;
		echo "</div>";
		echo "<div style='clear:both'>"; 
		echo "</div>"; 
		echo "</td>";

		echo "<td class='center'>" . kohana::lang('structures.prj_status_' . $kp->status) . '</td>';
		echo "<td style='padding:0px'>";

		
		echo "<table style='width:100%;font-size:11px' >";
		$info = $kp -> get_info();		
		
		foreach ( $info['neededitems'] as $dep )
		{	
			echo "<tr>";
			echo "<td style='width:80%'>" . kohana::lang( $dep['general'] -> cfgitem -> name ) . "</td>";
			echo "<td class='right' style='width:20%' ><b> " . $dep['providedquantity'] . '/' . $dep['general']->quantity ."</b></td>";			
			echo "</tr>";
		}		
		echo "<tr>";
		echo "<td>";
		echo kohana::lang('structures_royalpalace.workinghours'); 
		echo '</td>';
		echo "<td class='right'>";
		echo '<b>' . $info['workedhours'] .  '/' . 	$info['neededhours']  . '</b>';
		echo '</td>';
		echo "</tr>";
		
		echo '</table>';
		echo '</td>';	
		echo 
		"<td>" . form::submit(array(
			'name' => 'cancel', 'class'=>'button button-small', 
			'value' => kohana::lang('global.cancel'), 
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' )) .
		"</td>";
		echo form::close();
		echo '</tr>';
		echo "<tr><td colspan=4><hr/></td></tr>";

	}
}

echo '</table>';

// progetti che possono essere iniziati

echo "<br/><br/>";

echo "<table width='100%'>";
echo "<tr><td class='center' colspan='4'>" . "<h4>" . kohana::lang('structures_royalpalace.startableprojects' )  . "</h4>" . "</td></tr>"; 
echo "<th style='width:50%' class='center'>" . kohana::lang('global.project' ) . '</th>';
echo "<th style='width:10%' class='center'>" . kohana::lang('global.status' ) . '</th>';
echo "<th style='width:30%' class='center'>" . kohana::lang('global.needs' ) . '</th>';
echo "<th style='width:10%' class='center' >" . kohana::lang('global.action' ) . "</th>";

if ( count( $startableprojects ) == 0 ) 
{
	echo "<tr><td class='center' colspan='4'></td></tr>"; 
	echo "<tr><td class='center' colspan='4'>" . "<i>" . kohana::lang('structures_royalpalace.nostartableprojects') . "</i>" . "</td></tr>"; 
	echo "<tr><td class='center' colspan='4'></td></tr>"; 
}
else
{

	foreach ( $startableprojects as $startableproject )
	{
		echo "<tr style='height:20px'>";
		echo form::open();
		echo form::hidden('region_id', $region -> id );
		echo form::hidden('structure_id', $structure -> id );
		echo form::hidden('cfgkingdomproject_id', $startableproject -> id );
		echo '<td>';
		echo "<div style='float:left;border:0px solid'>";
		echo html::image('media/images/structures/' . $startableproject -> image, array('class' => 'size75') );
		echo "</div>";
		echo "<div style='margin-left:2px;font-size:11px;float:left;width:200px;padding:0px 2px;border:0px solid;text-align:justify'>";
		echo "<b>". kohana::lang($startableproject->name) . "</b><br/>". kohana::lang($startableproject->description) ;
		echo "</div>";
		echo "<div style='clear:both'>"; 
		echo "</div>"; 
		echo "</td>";

		echo "<td class='center'>" . kohana::lang('structures.prj_status_new') . '</td>';
		echo "<td style='padding:0px'>";
		echo "<table style='width:100%;font-size:11px'>";

		foreach ( $startableproject -> cfgkingdomproject_dependency as $dep )
		{	
			echo "<tr>";
			echo "<td style='width:80%'>" . kohana::lang($dep->cfgitem->name) . "</td>";		
			echo "<td class='right' style='width:20%'><b>" . $dep->quantity ."</b></td>";		
			echo '</tr>';
		}
		
		echo "<tr>";
		echo "<td>";
		echo kohana::lang('structures_royalpalace.workinghours'); 
		echo '</td>';
		echo "<td class='right'>";
		echo '<b>' . $startableproject  -> required_hours . '</b>';
		echo '</td>';
		echo '</tr>';	
		echo '</table>';
		echo "<td class='center'>";
		echo form::submit(array(
			'name' => 'start', 'class'=>'button button-small', 			
			'value' => kohana::lang('global.start'), 
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ));
		echo "</td>";
		echo form::close();
		echo '</tr>';
		echo "<tr><td colspan=4><hr/></td></tr>";

	}
	
}
echo '</table>';
?>
