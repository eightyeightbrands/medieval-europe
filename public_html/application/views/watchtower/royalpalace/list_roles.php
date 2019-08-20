<div class="pagetitle"><?php echo kohana::lang('structures_actions.royalp_listvassals') ?></div>

<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'>
<?php echo kohana::lang('structures_royalpalace.listvassals_helper') ?>
</div>

<div class='submenu'>
<?php echo html::anchor('/royalpalace/assign_roles/'.$structure->id, kohana::lang('structures_royalpalace.submenu_assign_roles'),
array('class' => 'selected')); ?>
<?php echo html::anchor('/royalpalace/list_roles/'.$structure->id, kohana::lang('structures_royalpalace.submenu_list_roles')); ?>
</div>

<?php
if ( $vassals -> count() == 0 )
{	
	echo "<p class='center'>". kohana::lang('structures_actions.royalp_novassals') .'</p>' ;
}
else
{
?>

<div class="pagination"><?php echo $pagination -> render	(); ?></div>

<table class='small'>
<th class='center'><?php echo kohana::lang('global.name')?></th>
<th class='center'><?php echo kohana::lang('global.residentregion')?></th>
<th class='center'><?php echo kohana::lang('character.roledays')?></th>
<th class='center'><?php echo kohana::lang('character.removalcost')?></th>
<th colspan='2'></th>

<?php
$k = 0;
foreach ($vassals as $vassal )
{
	$class = ( $k % 2 == 0 ? 'alternaterow_1' : 'alternaterow_2' );	
	$char = ORM::factory('character', $vassal -> id );
	$costforremoval = 
		Character_Role_Model::get_requiredcoins( $char, 'revoke', 'vassal' );
	echo "<tr class='" . $class . "'>";
	echo "<td class='center'>" . html::anchor('character/publicprofile/' . $vassal -> id, $vassal -> charname) . "</td>";
	echo "<td class='center'>" . kohana::lang( $vassal -> regionname ) . "</td>";
	echo "<td class='center'>" . intval((time() - $vassal -> begin)/(24*3600)) . "</td>";
	echo "<td class='right'>" . $costforremoval . " s.c.</td>";
	
	echo 
	"<td class='center'>" . 
		html::anchor('message/write/0/new/' . $vassal -> id, kohana::lang('message.write_scroll')) . 
		'<br/>' .
		html::anchor('royalpalace/revoke_role/' . $structure -> id . '/' . $vassal -> id,  
			kohana::lang('charactions.revoke_role'),
			array('onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' )) .			
		'<br/>' .
		html::anchor('royalpalace/assign_region/' . $structure -> id . '/' . $vassal -> id,  kohana::lang('structures_royalpalace.assign_region')) . 		
	"</td>";
	echo "</tr>";
	$k++;
}
?>
</table>
<br/>
<div class="pagination"><?php echo $pagination->render(); ?></div>
<?php
}
?>

<br style="clear:both;" />
