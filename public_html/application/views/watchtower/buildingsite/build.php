<div class="pagetitle"><?php echo kohana::lang("structures_buildingsite.build_pagetitle");?></div>

<div id='helperwithpic'>
<div id='locationpic'>
<?php echo html::image('media/images/template/locations/buildingsite.jpg') ?>
</div>


<div id='helper'>
<?php
	echo '<p>' . kohana::lang('structures_buildingsite.not_buildable_helper', kohana::lang( $info['builtstructure'] -> name) ) . '</p>';
?>
</div>
<div style='clear:both'></div>
</div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>

<p class='center'>
<?php 
if ( $info['project'] -> is_buildable() )
{
$workedhours = Structure_Model::get_stat_d(  $structure -> id, 'workedhours', $character -> id ); 
if ( !is_null( $workedhours ) )
	echo kohana::lang('structures_buildingsite.workedhours', $workedhours -> value );
else
	echo kohana::lang('structures_buildingsite.workedhours', 0 );
?>
</p>
<br/>
<table>
<!-- free slot -->
<tr>
	<?php echo form::open(); ?>
	<td>	
	<?php
	echo kohana::lang('structures_buildingsite.workonprojectforfree' );
	echo '&nbsp;';
	echo form::input( array('id' => 'hours', 'name' => 'hours', 'size' => '2', 'maxlength' => 1 ) );
	echo form::hidden('workingtype', 'volunteerwork' );
	echo form::hidden('structure_id', $structure -> id);
	?>
	<td>
	<?php
	echo form::submit( array (
		'id' => 'submit', 		
		'class' => 'button button-small', 					
		'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.work')) ;	
	echo form::close();
	?>
	</td>
</tr>
<!-- three hours slot -->
<tr>
<?php echo form::open(); ?>
<td>
<?php
echo form::hidden('hours', 3);
echo form::hidden('structure_id', $structure -> id);
echo form::hidden('workingtype', 'paidwork' );
echo kohana::lang('structures_buildingsite.workonprojectforcoins', 3, $project -> hourlywage, $slots[3] );
?>
</td>
<td>
<?php
echo form::submit( array (
	'id' => 'submit', 
	'class' => 'button button-small', 			
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.work')) ;		
echo form::close();
?>
</td>
</tr>
<!-- six hours slot -->
<tr>
<?php echo form::open(); ?>
<td>
<?php
echo form::hidden('hours', 6);
echo form::hidden('structure_id', $structure -> id);
echo form::hidden('workingtype', 'paidwork' );
echo kohana::lang('structures_buildingsite.workonprojectforcoins', 6, $project -> hourlywage, $slots[6] );
?>
</td>
<td>
<?php
echo form::submit( array (
	'id' => 'submit', 	
	'class' => 'button button-small', 			
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.work')) ;		
echo form::close();
?>
</td>
</tr>
<!-- nine hours slot -->
<tr>
<?php echo form::open(); ?>
<td>
<?php
echo form::hidden('hours', 9);
echo form::hidden('structure_id', $structure -> id);
echo form::hidden('workingtype', 'paidwork' );
echo kohana::lang('structures_buildingsite.workonprojectforcoins', 9, $project -> hourlywage, $slots[9] );
?>
</td>
<td>
<?php
echo form::submit( array (
	'id' => 'submit', 	
	'class' => 'button button-small', 			
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.work')) ;		
echo form::close();
?>
</td>
</tr>
</table>
<?php } ?>

<br style="clear:both;" />