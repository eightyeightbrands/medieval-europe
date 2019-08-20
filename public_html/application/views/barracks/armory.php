<head>
<script>

$(document).ready(function()
{	
	$("#target").autocomplete( {
			source: "index.php/jqcallback/listallchars",
			minLength: 2
		});	
});
	
</script>
</head>

<div class="pagetitle"><?php echo kohana::lang('structures_barracks.armory_pagetitle')?></div>

<?php echo $submenu ?>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div>
<div id='helper' style='width:80%; border:0px solid; float:left'>
<?php echo kohana::lang('structures_barracks.armory_helper')?>
</div>

<div style='float:right;margin-right:0px'>
<?php 
if ( is_null ( $bonus ) )
	echo html::anchor( 
		'bonus/index/2', 
		kohana::lang('structures_barracks.armory_upgrade'), 
		array( 'class' => 'button button-medium') );
else
	echo html::anchor( '#', 
		kohana::lang('structures_barracks.armory_upgraded'), 
		array( 
			'class' => 'button button-medium',
			'style' => 'pointer-events:none;cursor:default')); 
?>
</div>
<br style='clear:both'/>
</div>

<div class='submenu'>
<?php echo html::anchor('barracks/armory/'. $structure->id, kohana::lang('structures_barracks.armory'), array('class' => 'selected' ));?>
<?php echo html::anchor('barracks/viewlends/'. $structure->id, kohana::lang('structures_barracks.lendsreport'));?>
<?php 
if ( !is_null ( $bonus ) )
	echo html::anchor('structure/manageaccess/'. $structure->id, kohana::lang('structures.manageaccess'));?>
</div>
<br/>
<?php 
if ( count ( $items['items'] ) == 0 )
	echo "<p class='center' style='margin-top:10px'>" . kohana::lang('items.noitemfound') . "</p>";
else
{
?>

<?php 
echo form::open('barracks/lend');
echo " <input type='hidden' name = 'structure_id', value=' " . $structure -> id . "', id = 'structure_id' >"; 
echo kohana::lang('structures_barracks.lendtext'); 
echo form::input(array( 'id' => 'target', 'name' => 'target', 'style' => 'width:200px', 'value' => null ) );
echo form::submit( 
	array ( 'id'=> 'submit', 
		'class' => 'button button-medium' , 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' )
		, kohana::lang('global.send'))?>

<br/>

<div id='computedeliverytimeresult'></div>

<br/>

<table>
<th width="1%"></th>
<th width="30%"><?php echo kohana::lang('items.item') ?></th>
<th width="7%" class='center'><?php echo kohana::lang('global.quantity') ?></th>
<th width="7%" class='center'><?php echo kohana::lang('items.condition') ?></th>
<th width="7%" class='center'><?php echo kohana::lang('items.weight') ?></th>
<?php 

$r = 0;
foreach ( $items['items']['all'] as $item) 
{
	
	if ( 
		!in_array( $item -> parentcategory, array( 'weapons', 'armors' ))
		and
		!in_array( $item -> tag, array( 'healing_pill', 'elixirofmight'))		
	)
		continue;
		
	$class = ( $r % 2 == 0 ) ? 'alternaterow_1' : 'alternaterow_2' ;
	if ( $item -> equipped == 'unequipped' )
	{				
		
		
		echo "<tr class='$class'>";				
		echo "<td>" 
			. form::checkbox('armoryitems['.$item -> item_id.']', true, false) . "</td>";
		echo "<td width='5%'>" 
			. html::image(array('src' => 'media/images/items/'.$item -> tag.'.png'), 
			array( 
				'class' => 'size25',
				'style' => 'vertical-align:middle',
				)) . "&nbsp;" . "<span>" . kohana::lang($item -> name) . "</span>" . "</td>";

		echo "<td class='center'>".$item -> quantity . "</td>";	
		echo "<td class='center'>".$item -> quality . "%</td>";	

		echo "<td class='center'>". Utility_Model::number_format( $item -> totalweight / 1000, 1) . " Kg</td>";		
		
		echo "</tr>";
		
		$r++;
	}
	
}
?>
</table>
<?php echo form::close(); ?>
<?php } ?>

<br style="clear:both;" />
