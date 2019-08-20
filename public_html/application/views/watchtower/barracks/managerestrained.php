<div class="pagetitle"><?php echo kohana::lang('structures_actions.managerestrained')?></div>

<?php echo $submenu ?>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div class='submenu'>
	<?php echo html::anchor( '/barracks/restrain/' . $structure -> id, kohana::lang('structures_barracks.write_restraintorder') ) ?>
</div>

<div id='helper'><?php echo kohana::lang('structures_barracks.managerestrained_helper') ?></div>


<?php
if ( $rset -> count() == 0 ) 
	echo "<p class='center'>" . kohana::lang( 'structures_barracks.norestrained' ) . '</p>' ;
else
{
?>
<table width="100%" border="0" >
<th width="25%" ><?php echo kohana::lang('global.name');?></th>
<th width="25%" ><?php echo kohana::lang('structures_actions.restrainend');?></th>
<th width="30%" ><?php echo kohana::lang('structures_actions.restrainreason');?></th>
<th width="20%" ><?php echo kohana::lang('structures_actions.restraincancelreason');?></th>
<th></th>
<?php

$k=0;
foreach ($rset as $restrained )
{
	$class = ($k %2 == 0 ? 'alternaterow_1' : '' );
	echo form::open('/barracks/managerestrained');
	echo form::hidden('character_id', $restrained -> character_id  );	
	echo form::hidden('action_id', $restrained -> id  );	
	echo form::hidden('structure_id', $structure -> id );		
	
	echo "<tr class='$class'>";
	echo "<td>".$restrained -> name . "</td>";
	echo "<td>".Utility_Model::format_datetime( $restrained->endtime) ."</td>";
	echo "<td>". $restrained -> param5  ."</td>";
	echo "<td  style='padding-left:5px'>".form::input( array( 'name' => 'reason', 'size' => '30' ))."</td>";	
	echo "<td>" . form::submit( array ('id' => 'submit', 'class' => 'button button-medium', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('structures_actions.cancelrestrain'))."</td>";
	echo "</tr>";
	echo form::close();
	$k++;
}
?>

</table>
<?php } ?>
<br style="clear:both;" />
