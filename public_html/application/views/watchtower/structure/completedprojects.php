<div class="pagetitle"><?php echo kohana::lang('kingdomprojects.completedprojects') ?></div>

<?php echo $submenu ?>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/><br/>	

<div class='submenu'>
<?php echo html::anchor('/structure/buildproject/'.$structure->id, kohana::lang('kingdomprojects.buildproject') ) ?>
<?php echo html::anchor('/structure/runningprojects/'.$structure->id, kohana::lang('kingdomprojects.runningprojects'))?>
<?php echo html::anchor('/structure/completedprojects/'.$structure->id, kohana::lang('kingdomprojects.completedprojects')
, array('class' => 'selected' ))?>
</div>

<br/>


<?php
if ( count($completedprojects) == 0 )
	echo "<p class='center'>" . kohana::lang('kingdomprojects.nocompletedprojects') . "</p>" ;
else
{	
	$r = 0;
	echo "<table class='small'>";
	echo "<th style='width:5%' class='center'>" . kohana::lang('kingdomprojects.project' ) . '</th>';
	echo "<th style='width:70%' class='center'>" . kohana::lang('global.description' ) . '</th>';
	echo "<th style='width:25%' class='center'>" . kohana::lang('global.location' ) . '</th>';
		
	foreach ( $completedprojects as $completedproject )
	{
		$class = ( $r % 2 == 0 ) ? 'alternaterow_1' : 'alternaterow_2';
		
		$kp = ORM::factory('kingdomproject', $completedproject -> id ) ;
		$info = $kp -> get_info();
		
		echo "<tr class='$class' >";
		echo form::open();		
		echo "<td class='center'>";
		
		echo html::image( 'media/images/structures/' . $info['builtstructure'] -> image, 
			array('class' => 'size75 border'));			
		echo "<br/>";
		echo "<b>" . kohana::lang($info['builtstructure'] -> name ) . "</b>" . "<br/>";	
		echo "</td>";
		echo "<td class='justify'>";
		echo kohana::lang($info['project'] -> cfgkingdomproject -> description) ; 
		echo "</td>";
		
		echo "<td class='center'>";
		echo kohana::lang($info['region'] -> name); 
		echo "</td>";
		echo '</tr>';
		$r++;
	}

}

echo '</table>';
?>

<br style='clear:both'/>
