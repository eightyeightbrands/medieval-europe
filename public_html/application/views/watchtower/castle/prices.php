<div class='pagetitle'><?php echo kohana::lang('structures_castle.price_pagetitle')?></div>

<?php echo $submenu ?>

<div id='helper'>
	<div id='helpertext'>
		<?php echo kohana::lang('structures_castle.price_helper');?>
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

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>

<div class='submenu'>
<?php echo html::anchor('castle/taxes/' . $structure -> id, kohana::lang('structures_castle.taxes') ) ?>
&nbsp;
<?php echo html::anchor('castle/prices/' . $structure -> id, kohana::lang('structures_castle.prices'), 
	array('class' => 'selected') ) ?>
</div>

<br/>

<table>
<th width='30%'><?php echo kohana::lang('global.region')?></th>
<th width='10%' ><?php echo kohana::lang('taxes.hostilevalue')?></th>
<th width='10%' ><?php echo kohana::lang('taxes.neutralvalue')?></th>
<th width='10%' ><?php echo kohana::lang('taxes.friendlyvalue')?></th>
<th width='10%' ><?php echo kohana::lang('taxes.alliedvalue')?></th>
<th width='10%' ><?php echo kohana::lang('taxes.citizenvalue')?></th>
<th></th>

<?php 

$k = 0 ;
foreach ( $controlledregions as $controlledregion )
	foreach ( $controlledregion -> region_prices as $price )
	{
		$class = ( $k % 2 == 0 ) ? 'alternate_row' : '';
		echo form::open();		
		echo form::hidden( 'price_id', $price -> id );
		echo form::hidden( 'structure_id', $structure -> id );
		
		echo "<tr class='$class'>";
		echo "<td>" .  Kohana::lang( $controlledregion -> name ) . "</td>";		
		echo "<td class='right'>" . form::input( 'hostile', $price -> hostile, 'style="width: 30px;text-align:right"') . "</td>";
		echo "<td class='right'>" . form::input( 'neutral', $price -> neutral, 'style="width: 30px;text-align:right"') . "</td>";
		echo "<td class='right'>" . form::input( 'friendly', $price -> friendly, 'style="width: 30px;text-align:right"') . "</td>";
		echo "<td class='right'>" . form::input( 'allied', $price -> allied, 'style="width: 30px;text-align:right"') . "</td>";
		echo "<td class='right'>" . form::input( 'citizen', $price -> citizen, 'style="width: 30px;text-align:right"') . "</td>";

		echo "<td class='right' valign='top'>" . 
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
