<head>
<script>

$(document).ready(
 function()
 {	
		$("input#region").click(function(){
			$(".result").text('');
		});
		
		$("input#region").autocomplete( {
				source: "index.php/jqcallback/listallregions/all/1",
				minLength: 2,
				focus: function( event, ui ) {
					$( this ).val( ui.item.label );
					return false;
				},
				select: function( event, ui ) {        					
					$('input[name=region_id_' + $(this).attr('name') + ']').val(ui.item.id);	
					check( $(this).attr('name') );
					return false;
				}
		});
		
	}
);

function check( id )
{	
	$.ajax( //ajax request starting
		{
			url: '<?php echo url::base() ?>' + "index.php/region/checkprojectfeasibility", 
			type:"POST",
			data: { 								
				structure_type_id: $("input[name=structure_type_id_" + id +"]").val(),
				sourceregion_id: <?php echo $structure -> region -> id ?>, 
				destregion_id: $("input[name=region_id_" + id + "]").val(),
				structure_id: $("input[name=structure_id_" +id + "]").val(),
				position: id,
				cfgkingdomproject_id: $("input[name=cfgkingdomproject_id_"+ id + "]").val()
			},
			success: 
			function(data) 
			{													
				
				var m = JSON.parse( data );	
				
				if ( m.result )
				 {
					$('#result_' + id).text('');
					$('#result_' + id ).css({'color' : 'darkgreen', 'font-weight' : 'bold' });
					$('#result_'+ id ).text(m.message);					
					$('input[name=fpcostvalue_' + id + ']').val(m.cost);
					$('#launch_' + id ).show();
				}
				else
				{	
					$('#result_' + id ).css({'color' : '#c00', 'font-weight' : 'bold' });
					$('#result_' + id).text(m.message);
				}
			}
		}	
		);		
}
</script>

<div class="pagetitle"><?php echo kohana::lang('kingdomprojects.buildproject_pagetitle') ?></div>

<?php echo $submenu ?>
<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'><?php echo kohana::lang('kingdomprojects.buildproject_helper') ?></div>


<div class='submenu'>
<?php echo html::anchor('/structure/buildproject/'.$structure->id, kohana::lang('kingdomprojects.buildproject')
	, array('class' => 'selected' ))?>
<?php echo html::anchor('/structure/runningprojects/'.$structure->id, kohana::lang('kingdomprojects.runningprojects') )?>
<?php echo html::anchor('/structure/completedprojects/'.$structure->id, kohana::lang('kingdomprojects.completedprojects') )?>
</div>

<br/>

<table class='small'>
<th class='center' width='15%'><?php echo kohana::lang('kingdomprojects.project')?></th>
<th class='center' width='15%'><?php echo kohana::lang('kingdomprojects.neededstructure')?></th>	
<th class='center' width='25%'><?php echo kohana::lang('kingdomprojects.needs')?></th>
<th class='center' width='20%'><?php echo kohana::lang('global.region')?></th>
<th class='center' width='15%'><?php echo kohana::lang('kingdomprojects.result')?></th>

<?php 
$r = 0;
foreach ( $startableprojects as $sp )
{
$class = ( $r % 2 == 0 ) ? 'alternaterow_1' : 'alternaterow_2';
echo "<tr class='$class'>";
echo form::open( url::current(), array( 'id' => $r ) );
echo form::hidden('cfgkingdomproject_id_' . $r, $sp['cfgkingdomproject']['info']['obj'] -> id );
echo form::hidden('structure_type_id_' . $r, $sp['cfgkingdomproject']['info']['produced_structure'] -> id  );
echo form::hidden('position' , $r );
echo form::hidden('region_id_' . $r);
echo form::hidden('structure_id_' . $r, $structure -> id ); 
echo "<td class='center'>" .	
	html::image( 'media/images/structures/' . $sp['cfgkingdomproject']['info']['produced_structure'] -> image, 
	array( 
		'class' => 'size75 border',
		'title' => kohana::lang( $sp['cfgkingdomproject']['obj'] -> description ) )) .
		'<b>' . kohana::lang( $sp['cfgkingdomproject']['info']['produced_structure'] -> name ) . '</b>' . '<br/>' .
	'</td>';
	
echo "<td class='center'>";
if ( $sp['dependingstructure_type'] -> loaded )
	echo kohana::lang( $sp['dependingstructure_type'] -> name ); 
else
	echo '-';
echo "</td>";
echo "<td class='right'>" ;
foreach ( $sp['cfgkingdomproject']['info']['required_items'] as $key => $value )
	echo kohana::lang(  $key ) . ': ' . $value . '<br/>';
echo kohana::lang('kingdomprojects.requestedhours') . ': ' . $sp['cfgkingdomproject']['info']['obj'] -> required_hours;
echo '</td>';

echo "<td>" . form::input ( array( 'id' => 'region', 'name' => $r ) ) . '</td>';
echo "<td class='center'>";
echo form::hidden( 'fpcostvalue_' . $r);
echo "<div id='result_".$r."' class='result'></div>" ;
echo form::submit(
	array (
		'id'=>'launch_' . $r, 
		'class' => 'button button-small', 
		'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 
		'value' => kohana::lang( 'kingdomprojects.start' ), 	
		'style' => 'display:none',
		'name'=>'launch')) . 
 '</td>';
echo form::close();
echo '</tr>';	
$r++;
}
?>
</table>

<br style='clear:both'/>
