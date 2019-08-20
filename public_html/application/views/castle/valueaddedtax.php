<div class='pagetitle'><?php echo kohana::lang('structures_royalpalace.governmenttaxes_pagetitle')?></div>

<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'>
	<div id='helpertext'>
		<?php echo kohana::lang('structures_castle.valueaddedtax_helper');?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=Castle#Taxes',
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button' ) ) 
		?>
	</div>
	<div style='clear:both'></div>
</div>

<br/>

<table class='small'>
<th width='20%'><?php echo kohana::lang('global.region')?></th>
<th width='20%'><?php echo kohana::lang('global.name')?></th>
<th width='10%' ><?php echo kohana::lang('taxes.citizenvalue')?></th>
<th width='10%' ><?php echo kohana::lang('taxes.neutralvalue')?></th>
<th width='10%' ><?php echo kohana::lang('taxes.friendlyvalue')?></th>
<th width='10%' ><?php echo kohana::lang('taxes.alliedvalue')?></th>
<th width='10%' ><?php echo kohana::lang('global.updatedon')?></th>
<th></th>

<?php 

$k = 0 ;
foreach ( $controlledregions as $controlledregion )
{
	$tax = Region_Model::get_tax( $controlledregion -> id, 'valueaddedtax' );
	$class = ( $k % 2 == 0 ) ? 'alternaterow_1' : 'alternaterow_2';
	echo form::open();		
	echo form::hidden( 'tax_id', $tax -> id );
	echo form::hidden( 'structure_id', $structure -> id );
		
	echo "<tr class='$class'>";
	echo "<td class='center'>" . Kohana::lang( $controlledregion -> name ) . "</td>";	
	echo "<td class='center'>" .  Kohana::lang( 'taxes.' . $tax -> name . '_name'	 ) . "</td>";		
	echo "<td class='right'>" . form::input( 'citizen', $tax -> citizen, 'style="width: 30px;text-align:right"') . "</td>";
	echo "<td class='right'>" . form::input( 'neutral', $tax -> neutral, 'style="width: 30px;text-align:right"') . "</td>";
	echo "<td class='right'>" . form::input( 'friendly', $tax -> friendly, 'style="width: 30px;text-align:right"') . "</td>";
	echo "<td class='right'>" . form::input( 'allied', $tax -> allied, 'style="width: 30px;text-align:right"') . "</td>";
	echo "<td class='center'>" . Utility_Model::format_datetime( $tax -> timestamp ) . "</td>";
	
	echo "<td class='right' valign='middle'>" . 
		form::submit( array (
		'id' => 'submit', 
		'class' => 'submit', 			
		'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.edit'))."</td>";
	
	echo "</tr>";
		
	echo form::close();
	$k++;
	
}

echo '</table>';
echo '<br/>';
?>

<br style='clear:both'/>
