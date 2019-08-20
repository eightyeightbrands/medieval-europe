<head>
<script>

$(document).ready(function()
{	
	$("#target").autocomplete({
			source: "index.php/jqcallback/listallchars",
			minLength: 2
		});	
});
</script>
</head>

<div class="pagetitle"><?php echo kohana::lang('structures_barracks.armory_pagetitle')?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<?php echo $submenu ?>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div class='submenu'>
<?php echo html::anchor('barracks/armory/'. $structure->id, kohana::lang('structures_barracks.armory'));?>
<?php echo html::anchor('barracks/viewlends/'. $structure->id, kohana::lang('structures_barracks.lendsreport'));?>


<?php 
if ( !is_null ( $bonus ) )
	echo html::anchor('barracks/givearmoryaccess/'. $structure->id, kohana::lang('structures.manageaccess'), array('class' => 'selected' ));?>
</div>
<br/>

<?php 
echo form::open('barracks/givearmoryaccess');
echo form::hidden('structure_id', $structure -> id );
echo kohana::lang('structures_barracks.givearmoryaccesstext'); 
echo form::input(array( 'id' => 'target', 'name' => 'target', 'style' => 'width:200px', 'value' => null ) );
echo form::submit( 
	array ( 'id'=> 'submit', 
		'class' => 'button button-medium' , 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' )
		, kohana::lang('global.giveaccess'));
echo form::close();
?>

<br/><br/>

<fieldset>
<legend>Assigned Grants</legend>
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

<table class='grid'>
<th><?php echo kohana::lang('global.name')?></th>
<th><?php echo kohana::lang('structures.grants');?></th>
<th><?php echo kohana::lang('global.expires');?></th>
<th></th>
<?php foreach ( $structure -> structure_grant as $structure_grant  )
{

	echo '<tr>';
	echo '<td>' . $structure_grant -> character -> name . '</td>'; 
	echo '<td>' . kohana::lang('structures.grant_' . $structure_grant -> grant ) . '</td>'; 
	echo '<td>' . Utility_Model::format_datetime($structure_grant -> expiredate) . '</td>'; 
	echo "<td class='center'>" . html::anchor('/structure/revokegrant/' . $structure -> id . '/' . $structure_grant -> character -> id . '/' . $structure_grant -> grant, kohana::lang('global.revoke' )) . '</td>';
	echo '</tr>'; 
}
?>
</table>
<br/>
</fieldset>
<?php } ?>

<br style="clear:both;" />
