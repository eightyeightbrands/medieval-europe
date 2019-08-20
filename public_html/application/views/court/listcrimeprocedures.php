<div class="pagetitle"><?php echo kohana::lang('structures_court.listcrimeprocedures_pagetitle')?></div>

<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'>
<?php echo kohana::lang('structures_court.listcrimeprocedures_helper')?> 
</div>

<?php echo $pagination->render('extended'); ?>

<?php
if ( count( $crimeprocedures ) == 0 ) 
{
	echo "<p class='center'>";
	echo kohana::lang( 'structures_court.nocrimeprocedures' ) ;
	echo '</p>';
}
else
{
?>

<table class='small'> 
<th class='center' width="5%" ><?php echo kohana::lang('global.id');?></th>
<th class='center' width="10%" ><?php echo kohana::lang('structures_court.criminal');?></th>
<th class='center' width="10%" ><?php echo kohana::lang('structures_court.opendate');?></th>
<th class='center' width="10%" ><?php echo kohana::lang('global.status');?></th>
<th class='center' width="40%" ><?php echo kohana::lang('structures_court.crimesummary');?></th>
<th class='center' width="10%" ><?php echo kohana::lang('structures_court.trialurl');?></th>
<th class='center' width="20%"></th>

<?php

$i=0;
foreach ( $crimeprocedures as $crimeprocedure )
{
	$class = ( $i % 2 == 0 ) ? 'alternaterow_1' : 'alternaterow_2';
	echo "<tr class='" . $class . "'>";
	echo "<td class='center'>" . html::anchor('/court/viewcrimeprocedure/' . $structure -> id . '/' . $crimeprocedure -> id,
		$crimeprocedure -> id ) . '</td>'; 
	echo "<td class='center'>" . $crimeprocedure -> name . '</td>'; 
	echo "<td class='center'>" . Utility_Model::format_date($crimeprocedure -> issuedate) . '</td>'; 
	echo "<td class='center'>" . kohana::lang( 'structures_court.status_' . $crimeprocedure -> status ). '</td>'; 
	echo '<td>' . $crimeprocedure -> text . '</td>'; 
	
	echo "<td class='center'>"; 
	if ( is_null ($crimeprocedure -> trialurl) )
		echo '-' ; 
	else 
		echo html::anchor( $crimeprocedure -> trialurl, kohana::lang('structures_court.trialurl'), array( 'target' => '_blank',
			'class' => 'submenu')
		) ;
	echo '</td>'; 
	echo "<td class='center'>" ;	
		
		if ( $crimeprocedure -> status == 'new' ) 
		{
			echo html::anchor('court/editcrimeprocedure/' . $crimeprocedure -> structure_id . '/' . $crimeprocedure -> id, 
				kohana::lang('structures_court.edit'),
				array('class' => 'submenu') ); 
			echo '<br/>';
		
			echo html::anchor('court/cancelcrimeprocedure/' . $crimeprocedure -> structure_id . '/' . $crimeprocedure -> id, 
				kohana::lang('structures_court.cancel'),
				array('class' => 'submenu') 
			); 
			echo '<br/>';
		
			echo html::anchor('court/writearrestwarrant/' . $crimeprocedure -> structure_id . '/' . $crimeprocedure -> id, 
				kohana::lang('structures_court.writearrestwarrant'), 
					array ('onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'class' => 'submenu')); 
			echo '<br/>';
		
			echo html::anchor('court/imprison/' . $crimeprocedure -> structure_id . '/' . $crimeprocedure -> id, 
			kohana::lang('structures_court.imprison'),
			array('class' => 'submenu') 
			); 	
		}
		
	echo '</td>'; 
	echo '</tr>';
	$i++;
}
?>

</table>
<?php } ?>

<br/>

<?php echo $pagination->render('extended'); ?>

<br style="clear:both;" />
