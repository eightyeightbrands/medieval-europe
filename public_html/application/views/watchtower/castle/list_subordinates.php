<head>
<script type='text/javascript'>
 
 $(document).ready(function() {
	$("#region").change(function() {
				var url = '<?php echo url::base( true ). 'castle/list_subordinates/' . $structure -> id . '/' ?>';				
				url += $('#region').val();
				window.location.replace( url );
	})
	});	 
</script>
</head>

<div class="pagetitle"><?php echo kohana::lang('structures_castle.nominees_pagetitle') ?></div>

<?php echo $submenu ?>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>

<div class='submenu'>
<?php echo html::anchor('/castle/assignrole/' . $structure -> id, kohana::lang('structures_castle.assignrole')); ?>
&nbsp;&nbsp;
<?php echo html::anchor('/castle/list_subordinates/' . $structure -> id, kohana::lang('structures_castle.listsubordinates'),
array( 'class' => 'selected' ) 	); ?>
</div>

<br/>

<?php
echo form::open();
echo form::hidden( 'structure_id', $structure -> id ); 
echo kohana::lang('structures_castle.region_subordinates') . form::dropdown ( 
	array( 'id' => 'region', 'name' => 'region', 'value' => $region -> name) , $controlledregions_cb, $region -> id );
echo form::close();
?>

<br/>
<br/>

<?php
if ( count($subordinates) == 0 )
	echo "<div id='helper' style='text-align:center'>" . kohana::lang('structures_castle.noassignedrolesyet') . "</div>";
else
{
?>

<table class="small">
<th><?php echo kohana::lang('global.name')?></th>
<th><?php echo kohana::lang('global.role')?></th>
<th class='center'><?php echo kohana::lang('character.roledays')?></th>
<th class='center'><?php echo kohana::lang('character.removalcost')?></th>
<th></th>
<?php
$k = 0;
foreach ($subordinates as $subordinate )
{
	$class = ( $k % 2 == 0 ) ? 'alternaterow_1' : 'alternaterow_2';	
	$costforremoval = 
		Character_Role_Model::get_requiredcoins( $subordinate -> character, 
			'revoke', $subordinate -> tag );
	echo "<tr class='$class'>";
	echo "<td class='center'>" . html::anchor('character/publicprofile/' . $subordinate -> character_id, $subordinate->character -> name) . "</td>";	
	echo "<td class='center'>" . $subordinate -> character -> get_rolename( true ) . "</td>";
	echo "<td class='center'>" . intval((time() - $subordinate -> begin)/(24*3600)) . "</td>";
	echo "<td class='center'>" . $costforremoval . " s.c.</td>";
	echo "<td class='center'>" .  html::anchor('castle/revoke_role/' . $structure -> id . '/' . 
		$subordinate -> character_id, 
			kohana::lang('charactions.revoke_role'),
			array(				
				'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' )) . '</td>';		
	echo "</tr>";
	$k++;
}
?>
</table>
<br/>
<?php
}
?>
