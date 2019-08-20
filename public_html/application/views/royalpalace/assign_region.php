
<div class="pagetitle"><?php echo kohana::lang('structures_royalpalace.assign_region_pagetitle') ?></div>

<?php echo $submenu ?>
<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'>
<?php echo kohana::lang('structures_royalpalace.assign_region_helper', $vassal -> name )?>
</div>

<div class='submenu'>
<?php echo html::anchor('/royalpalace/assign_roles/'.$structure->id, kohana::lang('structures_royalpalace.submenu_assign_roles'),
array('class' => 'selected')); ?>
<?php echo html::anchor('/royalpalace/list_roles/'.$structure->id, kohana::lang('structures_royalpalace.submenu_list_roles')); ?>
</div>

<br/>

<?php
echo "<table class='small'>";
echo "<th>" . kohana::lang('global.region') . "</tg>"; 
echo "<th>" . kohana::lang('ca_assignregion.controllingvassal') . "</th>"; 
echo "<th>" . kohana::lang('global.action') . "</th>"; 

$r = 0;
foreach ($assignableregions as $assignableregion )
{

	$class = ( $r % 2 == 0 ) ? 'alternaterow_1' : 'alternaterow_2';
	echo "<tr class='$class'>";
	echo form::open();
	echo form::hidden( 'region_id', $assignableregion['region'] -> id ); 
	echo form::hidden( 'sourcevassal_id', $assignableregion['responsiblevassal'] -> id ); 
	echo form::hidden( 'destvassal_id', $vassal -> id ); 
	echo form::hidden( 'structure_id', $structure -> id ); 


	echo "<td width='30%' class='center'>" . kohana::lang($assignableregion['region'] -> name) . "</td>";
	echo "<td width='50%' class='center'>";
	if ( $assignableregion['responsiblevassal'] -> loaded )
			echo $assignableregion['responsiblevassal'] -> name;
	else
		echo '-';
	echo "</td>";		
	echo "<td width='10%' class='center'>";
	if ( $assignableregion['assignable'] )
		echo form::submit( array( 'id' => 'submit', 'name' => 'assign_region', 'class'=> 'button button-small', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('structures_royalpalace.assign_region')));
	else
		echo " - " ;
	echo "</td>"; 
	echo '<tr/>'; 
	echo form::close(); 
	$r++;
}
echo "</table>";
?>
<br style='clear:both'/>