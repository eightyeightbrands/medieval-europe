<div class="pagetitle"><?php echo kohana::lang('structures_actions.royalp_manageappointments') ?></div>

<?php echo $submenu ?>
<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'><?php echo kohana::lang('structures_royalpalace.manageappointments_helper') ?></div>

<div class='submenu'>
<?php echo html::anchor('royalpalace/assign_roles/'.$structure->id, kohana::lang('structures_royalpalace.submenu_assign_roles'),
array('class' => 'selected')); ?>
<?php echo html::anchor('/royalpalace/list_roles/'.$structure->id, kohana::lang('structures_royalpalace.submenu_list_roles')); ?>
</div>


<?php
if ( $candidates -> count() == 0 or count($regionswithfreecastle) == 0 )
{	
	echo "<p class='center'>" . kohana::lang('structures_actions.royalp_nocandidates') . '</p>' ;
}
else
{
?>

<div class="pagination"><?php echo $pagination->render	(); ?></div>

<div class='center'>
	
	<table class='small'>
	<th width='20%'><?php echo kohana::lang('global.name') ?>	</th>
	<th width='20%'><?php echo kohana::lang('global.residentregion') ?></th>
	<th width='20%'><?php echo kohana::lang('global.region') ?></th>
	<th width='20%'></th>
	
	<?php
	$k = 0;
	foreach ( $candidates as $candidate )
	{	
		echo form::open('/royalpalace/appoint'); 
		echo form::hidden( 'character_id', $candidate -> id ); 
		echo form::hidden( 'structure_id', $structure -> id ); 
		$class = ( $k % 2 == 0 ? 'alternaterow_1' : 'alternaterow_2' );	
	?>

		<tr class ='<?php echo $class; ?>'>
		<td class='center' ><?php echo $candidate -> name ?></td>
		<td class='center' ><?php echo kohana::lang( $candidate -> region ) ?></td>		
		<td class='center' ><?php echo form::dropdown('region_id', $regionswithfreecastle );?></td>
		<td class='center' >
			<?php echo form::submit( array ( 'id' => 'appoint', 'value' => kohana::lang('global.appoint'), 
				'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').  '\')' ) ); 
			?>
		</td>
		</tr>
<?php
		$k ++;
		echo form::close();
}
?>
</table>
</div>
<br/>
<div class="pagination">
<?php echo $pagination->render	(); ?>
</div>

<?php } ?>

<br style="clear:both;" />
