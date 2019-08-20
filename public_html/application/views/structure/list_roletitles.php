<div class="pagetitle"><?php echo kohana::lang('ca_assignrole.list-roles-title') ?></div>

<?php echo $submenu ?>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'><?php echo kohana::lang('ca_assignrole.list-roles-helper'); ?></div>

<div class='submenu'>
	<?php echo html::anchor( $structure -> structure_type -> supertype . '/assign_rolerp/' . $structure -> id, kohana::lang('structures_royalpalace.assignrolerp')); ?>
	<?php echo html::anchor('/structure/list_roletitles/' . $structure -> id, kohana::lang('structures_royalpalace.listroletitles'),
	array( 'class' => 'selected' )); ?>
</div>

<br/>

<?php
if ($roles->count() == 0 )
{
?>

<p class='center'><?php echo kohana::lang('ca_assignrole.no-roles-assigned'); ?></p>

<?php
}
else
{
?>

<div>
<table>
<th width="5%"></th>
<th width='30%' ><?php echo kohana::lang('global.role') ?></th>
<th width='35%' ><?php echo kohana::lang('global.name') ?></th>
<th width='20%' ><?php echo kohana::lang('global.actual_actions') ?></th>
<?php
$i = 0;
foreach ( $roles as $r )
{
	($i % 2 == 0 ) ? $class = 'alternaterow_1' : $class = 'alternaterow_2';
	echo "<tr class='$class'>";	
	echo "<td clas='center'>";
	echo html::image(
			array(
				'src' => 'media/images/badges/nobletitles/'.$r -> tag.'.png',
				'class' => 'size50 border')); 
			
	echo '</td>';
	echo "<td class='center'>";
	echo $r -> get_title( true );
	echo '</td>';
	
	echo "<td class='center'>".html::anchor('/character/publicprofile/'.$r->character->id, $r->character->name).'</td>';
	
	echo "<td class='center'>";
	echo html::anchor(
		'/structure/revokerolerp/'.$r->structure_id.'/'.$r->id, 
		kohana::lang('global.revoke'),
		array('class' => 'button button-small'));
		
	echo '</td>';
	
	echo '</tr>';
	$i++;
}
?>
</table>
</div>
<?php } ?>

<br style='clear:both'/>