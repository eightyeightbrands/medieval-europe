<div class="pagetitle"><?php echo $glanceedchar -> name ;?>:&nbsp;<?php echo kohana::lang('global.inventory')?></div>

<div class='submenu'>
<?php echo html::anchor('region/listchars/regionpresentchars', 
	kohana::lang('ca_glance.backtoregionchars')); ?>
</div>

<br/>

<div class='helper'>
<?php echo kohana::lang('ca_glance.helper',
	$glanceedchar -> name ); ?>
</div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>
<br/>

<?php
if ( count( $items['items'] ) == 0  )
{
?>
<p style='text-align:center;'><?php echo kohana::lang('items.noitemfound');?></p>
<?php
}
else
{
?>
<div>
	<table style='width:80%;margin: 0 auto'>
	<th width="70%" colspan="2" ><?php echo kohana::lang('items.item') ?></th>
	<th width="30%" class='right'><?php echo kohana::lang('global.quantity') ?></th>	
	<?php 
	$r = 0;
	foreach ($items['items'] as $item ) 
	{	
		$class = ( $r % 2 == 0 ) ? '' : 'alternaterow_1';
		
		$title = Utility_Model::createitemtooltip( $item ); 
		
		echo "<tr class='$class'>";
		
		echo "<td width='5%'>" . html::image( 
			'media/images/items/'. $item -> tag .'.png',
			array('class' => 'size35')
			) . "</td>";
		echo "<td width='95%' title='" . $title . "'>" . kohana::lang($item -> name) . "</td>";	
		echo "<td class='right'>" . $item -> quantity . "</td>";		
		echo "</tr>";
		$r++;
	}
	?>
	</table>
</div>	
<?php 
} 
?>
