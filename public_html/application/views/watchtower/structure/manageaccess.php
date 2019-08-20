<script>

$(document).ready(function()
{	
	$("#character").autocomplete({
			source: "index.php/jqcallback/listallchars",
			minLength: 2
		});	
});
	
</script>
<div class="pagetitle"><?php echo kohana::lang('structures.manageaccess_pagetitle'); ?></div>

<br/>

<?php echo $submenu ?>


<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>

<fieldset>
<legend><?php echo kohana::lang('structures.assigngrants')?></legend>
<? if ( count($grants) == 0 ) { ?>
<p class='center'><?= kohana::lang('structures.nograntsexistingforstructure'); ?></p>
<? 
}
else
{	
?>

<?php echo form::open('structure/assigngrant') ?>
<?php echo kohana::lang('structures.assigngrants')?>&nbsp; 
<?php echo form::dropdown('grant', 	$grants ); ?>
&nbsp;
<?php echo kohana::lang('global.to')?>: 
<?php echo form::input('character') ?>
<?php echo form::hidden('structure_id', $structure -> id ) ?>

<?php 
echo form::submit( array (
	'id' => 'submit', 
	'class' => 'button button-small', 
	'name' => 'manageaccess', 
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.save'))
?>

<?php echo form::close() ?>		
<? } ?>
</fieldset>

<br/>


<fieldset>
<legend><?php echo kohana::lang('structures.assignedgrants')?></legend>
<?php 
if ( count( $structure -> structure_grant ) == 0 ) 
{
?>
<p class='center'> <?php echo kohana::lang('structures.noaccessgiven') ?></p>
<?php
}
else
{
?>

<table>
<th><?php echo kohana::lang('global.name')?></th>
<th><?php echo kohana::lang('structures.grants');?></th>
<th><?php echo kohana::lang('global.expires');?></th>
<th></th>

<?php 
$r = 0;
foreach ( $structure -> structure_grant as $structure_grant  )
{

	$class = ( $r % 2 == 0 ) ? 'alternaterow_1' : 'alternaterow_2';
	if ( $structure_grant -> expiredate > time() )
	{
		echo "<tr class='" . $class ."'>";
		echo "<td class='center'>" . $structure_grant -> character -> name . '</td>'; 
		echo "<td class='center'>" . kohana::lang('structures.grant_' . $structure_grant -> grant ) . '</td>'; 
		echo "<td class='center'>" . Utility_Model::format_datetime($structure_grant -> expiredate) . '</td>'; 
		echo "<td class='center'>" . html::anchor('/structure/revokegrant/' . $structure -> id . '/' . $structure_grant -> character -> id . '/' . $structure_grant -> grant, 
			kohana::lang('global.revoke' ),
			array('class' => 'submenu')
			) . '</td>';
		echo '</tr>'; 
		
	}
	$r++;
}
?>
</table>
<br/>
<?php } ?>
</fieldset>

<br style='clear:both' />
