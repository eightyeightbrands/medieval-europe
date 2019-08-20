<div class="pagetitle"><?php echo kohana::lang('structures_barracks.manageprisoners')?></div>

<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'><?php echo kohana::lang('structures_barracks.manageprisoner_helper') ?></div>

<br/>

<?php
if ( $prisoners -> count() == 0 ) 
	echo "<p class='center'>" . kohana::lang( 'structures.noprisoners' ) . '</p>' ;
else
{
?>

<table>
<th class='center' width="20%" ><?php echo kohana::lang('global.name');?></th>
<th class='center' width="20%" ><?php echo kohana::lang('structures_barracks.sentence_endtime');?></th>
<th class='center' width="30%" ><?php echo kohana::lang('structures_barracks.sentence_reason');?></th>
<th class='center' width="20%" ><?php echo kohana::lang('structures_barracks.sentence_freereason');?></th>
<th></th>

<?php
$k = 0;

foreach ($prisoners as $prisoner )
{	
	
	echo form::open('/barracks/freeprisoner');
	
	echo form::hidden('imprisoned_id', $prisoner -> character -> id );	
	echo form::hidden('structure_id', $structure -> id );	
	echo form::hidden('sentence_id', $prisoner -> id );		
	
	$class = ( $k % 2 == 0  ? 'alternaterow_1' : 'alternaterow_2' );
	echo "<tr class='$class'>";
	echo "<td class='center'>". Character_Model::create_publicprofilelink($prisoner -> character -> id, null)."</td>";	
	
	// get real imprisonment date end
	
	$stat = Character_Model::get_stat_d(		$prisoner -> character -> id, 'servejailtime'	);
	
	echo "<td class='center' >".
		Utility_Model::format_datetime( $stat -> stat2 ) . 
		'<br/>' . 
		kohana::lang('structures_barracks.sentence_timeleft', Utility_Model::countdown( $stat -> stat2 )) . 
	"</td>";
		
	echo "<td class='center'>". $prisoner -> text . 
	"<br/>" . 
	html::anchor( $prisoner -> trialurl, '[Trial Link]', array('target' => '_blank' ) ) . 
	"</td>";		
	echo "<td>" . form::textarea( array( 
		'name' => 'reason',
		'cols' => 15,
		'rows' => 5))."</td>";	
	echo "<td>" . form::submit( array (
		'id' => 'submit', 
		'class' => 'button button-small', 
		'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), 
		kohana::lang('structures_actions.freeprisoner')).
	"</td>";
	echo "</tr>";
	echo form::close();
	$k++;
}
?>

</table>
<?php } ?>
<br style="clear:both;" />
