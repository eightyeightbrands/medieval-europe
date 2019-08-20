<head>
<script>

$(document).ready(function()
{	
	$(".owner").autocomplete( {
			source: "index.php/jqcallback/listallchars",
			minLength: 2
		});	
});
</script>
</head>

<div class="pagetitle"><?php echo Kohana::lang("structures_religion_1.managehierachy_pagetitle"); ?></div>

<?php echo $submenu ?>

<div id='helper'>
<?php echo kohana::lang('structures_religion_1.managehierarchy_helper') ?>
</div>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>
<br/>

<?php
if ( count ( $childstructures ) == 0 ) 
	echo "<p><i>" . kohana::lang('structures_religion_1.nochildstructuresyet') . "</i></p>";
else
{
	echo '<table>';
	echo "<th width='30%'>" . kohana::lang('global.name') . '</th>';
	echo "<th width='30%'>" . kohana::lang('global.location') . '</th>';
	echo "<th width='20%'>" . kohana::lang('global.name') . '</th>';
	echo "<th width='30%'>" . kohana::lang('global.role') . '</th>';	
	echo "<th></th>";
	
	$r = 0;
	foreach ( $childstructures as $childstructure )
	{
	
		// Non vanno listate le strutture ancora in corso.
		
	
		if ( $childstructure -> getSuperType() == 'buildingsite' ) 
			continue;
	
		$class = ( $r % 2 == 0 ) ? '' : 'alternaterow_1';
		echo form::open();
		echo form::hidden( 'targetstructure_id', $childstructure -> id ); 
		echo form::hidden( 'structure_id', $structure -> id ); 
		$info = $childstructure -> get_info();			

		echo "<tr class='$class'>";
		echo '<td>' . $info['structurename'] . '</td>';
		echo '<td>' . kohana::lang($childstructure -> region -> kingdom -> get_name() ) .
		', ' .  kohana::lang($childstructure -> region -> name) .
		'</td>';
		
		if ( $childstructure -> character -> loaded )
		{
			echo '<td>' . $childstructure -> character -> name . '</td>';		
			echo form::hidden( 'owner', $childstructure -> character -> name );			
			echo '<td>' . $childstructure -> character -> get_rolename( true ) . '</td>'; 

		}
		else
			echo '<td>' . form::input( array('id' => 'owner', 'class' => 'owner', 'name' => 'owner', 'style' => 'width:200px') ) . '</td>';		
		echo '<td>';
		if ( $childstructure -> character -> loaded )	
			echo form::submit( array (
			'id' => 'revoke',
			'name' => 'revoke',
			'value' => kohana::lang('global.revoke'),
			'class' => 'button button-small', 			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('message.write_submit'));		
		else
			echo form::submit( array (
			'id' => 'Appoint', 
			'name' => 'appoint',
			'value' => kohana::lang('global.appoint'),
			'class' => 'button button-small', 			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('message.write_submit'));		
		echo '</td>';
		echo '</tr>';
		echo form::close();
		$r++;
	}
	echo '</table>';
}
?>

<br style='clear:both'/>
