<div class="pagetitle"><?php echo kohana::lang('kingdomprojects.runningprojects') ?></div>

<?php echo $submenu ?>
<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div class='submenu'>
<?php echo html::anchor('/structure/buildproject/'.$structure->id, kohana::lang('kingdomprojects.buildproject'))?>
<?php echo html::anchor('/structure/runningprojects/'.$structure->id, kohana::lang('kingdomprojects.runningprojects'), 
array('class' => 'selected' ))?>
<?php echo html::anchor('/structure/completedprojects/'.$structure->id, kohana::lang('kingdomprojects.completedprojects') )?>
</div>
<br/>

<?php
if ( count($runningprojects) == 0 ) 
{ 
?>
	<p class='center'><?php echo  kohana::lang('kingdomprojects.norunningprojects'); ?></p>
<?php 
} 
else
{	
	$r = 0;
	echo "<table class='small'>";
	echo "<tr><td class='center' colspan='4'></td></tr>"; 
	echo "<th style='width:10%' class='center'>" . kohana::lang('kingdomprojects.project' ) . '</th>';
	echo "<th style='width:15%' class='center'>" . kohana::lang('global.location' ) . '</th>';
	echo "<th style='width:15%' class='center'>" . kohana::lang('global.status' ) . '</th>';
	echo "<th style='width:20%' class='center'>" . kohana::lang('kingdomprojects.needs' ) . '</th>';
	echo "<th style='width:10%'class='center'>Actions</th>";
	
	foreach ( $runningprojects as $runningproject )
	{
		$class = ( $r % 2 == 0 ) ? 'alternaterow_1' : 'alternaterow_2';
		
		$kp = ORM::factory('kingdomproject', $runningproject -> id ) ;
		$info = $kp -> get_info();
		
		echo "<tr class='$class' >";
		echo form::open();	
		echo form::hidden('kingdomproject_id',$runningproject -> id);
		echo form::hidden('structure_id',$structure -> id);
		echo "<td class='center'>";
	
		echo html::image( 'media/images/structures/' . $info['builtstructure'] -> image, 
			array('class' => 'size75 border'));
		echo "<br/>";
		echo "<b>". kohana::lang($info['builtstructure'] -> name ) . "</b>";	
		
		echo "<td class='center'>";
		echo kohana::lang($info['region'] -> name); 
		echo "</td>";

		echo "<td class='center'>" . kohana::lang('structures.prj_status_' . $info['project']->status) . '</td>';
		echo "<td style='padding:0px'>";

		
		echo "<table style='width:100%' >";
		
		foreach ( $info['neededitems'] as $dep )
		{	
			echo "<tr>";
			echo "<td style='width:80%'>" . kohana::lang( $dep['general'] -> cfgitem -> name ) . "</td>";
			echo "<td class='right' style='width:20%' ><b> " . $dep['providedquantity'] . '/' . $dep['general']->quantity ."</b></td>";			
			echo "</tr>";
		}		
		echo "<tr>";
		echo "<td>";
		echo kohana::lang('kingdomprojects.workedhours'); 
		echo '</td>';
		echo "<td class='right'>";
		echo '<b>' . $info['workedhours'] .  '/' . 	$info['project'] -> cfgkingdomproject -> required_hours  . '</b>';
		echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>';
		echo kohana::lang('kingdomprojects.buildingprogress');
		echo '</td>';
		echo "<td class='right'>";
		echo '<b>' . $info['workedhours_percentage'] . '%' . '</b>';
		echo '</td>';
	
		echo "</tr>";
		
		echo '</table>';
		echo '</td>';	
		echo "<td class='center'>" . 
					
			form::submit(
			array (
			'id' => 'cancelproject',
			'class' => 'button button-small', 
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 
			'value' => kohana::lang( 'global.delete' ), 				
			'name'=>'cancelproject')) . 
			"</td>";
		echo form::close();
		echo '</tr>';
		$r++;
	}

}

echo '</table>';
?>

<br style='clear:both'/>
