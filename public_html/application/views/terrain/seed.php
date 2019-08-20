<div class="pagetitle"><?php echo kohana::lang( 'structures_terrain.seed_pagetitle', 
	kohana::lang( $structure -> structure_type -> name ), 
	$structure -> character -> name); ?></div>


<?php echo $submenu?>
	
<div id='helper'>
  <div id='helpertext'>
		<?php  echo kohana::lang('structures_terrain.seed_helper'); ?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=Fields',
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button button-small' ) ) 
		?>
	</div>
	<div style='clear:both'></div>
</div>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/><br/>

<table class='grid'>
<th class='center' width='30%'><?php echo kohana::lang('global.type') ?></th>
<th class='center'  width='10%'><?php echo kohana::lang('global.quantity') ?></th>
<th class='tcenter' width='20%'></th>
<?php
	$k = 0;
	foreach( $structure -> item as $item )
	{
		$class = ( $k % 2 == 0 ) ? '' : 'alternaterow_1' ;
		
		if ($item->cfgitem->category == 'resource' AND $item -> cfgitem -> subcategory == 'seed')
		{
			echo form::open();
			echo form::hidden('item_id', $item -> id ); 			
			echo form::hidden('structure_id', $structure -> id ); 			
			echo "<tr class='$class'>";
			echo form::hidden('item_id', $item -> id ); 			
			echo form::hidden('structure_id', $structure -> id ); 			
			echo "<td class='center'>" . kohana::lang( $item -> cfgitem -> name ) . '</td>' ;
			echo "<td class='right'>" . $item -> quantity . '</td>';			
			echo "<td class='center'>" . form::submit( 
				array( 
					'value' => kohana::lang('structures_terrain.seed'),
					'class' => 'button button-small', 
					'title' => kohana::lang('structures_terrain.seed'),
					'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' )) . '</td>' ;							
			echo '</tr>' ;
			echo form::close();
		}		
		
		$k++;
		
	}	
?>
</table>

<br style='clear:both'/>
