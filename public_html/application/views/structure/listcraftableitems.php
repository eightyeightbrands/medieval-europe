<div class="pagetitle">
<?php 
	echo kohana::lang( 'structures.craft_pagetitle', 
	kohana::lang( $structure -> structure_type -> name ),
	$structure -> character -> name );
?>
</div>
	
<?php echo $submenu ?>

<div id='helper'>
	<div id='helpertext'><?= $helper ?></div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=Workshops#Construct.2FCreate_objects',
			kohana::lang('global.wikisection'), 
			array( 
				'target' => 'new', 
				'class' => 'button' ) ) 
		?>
	</div>
	<div style='clear:both'></div>
</div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/><br/>

<table class='normal'>
<th width="20%" class="center" colspan="2"><?php echo kohana::lang('global.name')." - ".kohana::lang('global.description');?></th>
<th width="30%" class="center" ><?php echo kohana::lang('structures.source_items');?></th>
<th width="30%" class="center" ><?php echo kohana::lang('structures.crafting_info');?></th>
<th width="20%"></th>
<?php
$r = 0;
foreach ( $structurecraftableitemslist as $destination_itemtag => $craftableitem )
{
	//var_dump($structurecraftableitemslist);exit;
	$class = ($r % 2 == 0 )? '' : 'alternaterow_1';
	$needed_items = "";
	
	foreach ( $craftableitem['requireditems'] as $itemtag => $data ) 
	{		
		$needed_items .= kohana::lang($data['source_item_name']) . " (" . $data['quantity'] . ") " . " <br/>";
	}
	
	$title = kohana::lang($craftableitem['description']);
	
	echo "<tr class=$class>";
	echo "<td width='5%' class='center'>" . html::image(array(
			'class' => 'size50',
			'src' => 'media/images/items/' . $destination_itemtag .'.png',			
			'alt'=> kohana::lang($craftableitem['destination_item_name']))) . "</td>";
			
	echo "<td title='{$title}'>".kohana::lang($craftableitem['destination_item_name'])."</td>";
	echo "<td class='center'>".
		$needed_items . '<hr/>' . 		
		kohana::lang('structures.minmax', $craftableitem['destination_item_minquantity'], $craftableitem['destination_item_maxquantity']) 
		."</td>";	
	
	echo "<td class='center'>".
	kohana::lang('structures.originalcraftingtime', $craftableitem['originalcraftingtime']) . '<br/>'.
	kohana::lang('structures.realcraftingtime', $craftableitem['realcraftingtime']). '<br/>';
	
	if ( $craftableitem['requiredenergy'] > $char -> energy)
		$class = 'alert';
	else
		$class = 'info';
	?>
	
	<span class='<?= $class; ?>'><?= kohana::lang('global.requiredenergy'). ': ' . round($craftableitem['requiredenergy']/50*100,2) . '%';?></span>
	<br/>
	<?
	if ( $craftableitem['requiredglut'] > $char -> glut)
		$class = 'alert';
	else
		$class = 'info';
	?>
	
	<span class='<?= $class; ?>'><?= kohana::lang('global.requiredglut'). ': ' . round($craftableitem['requiredglut']/50*100,2) . '%';?></span>

	<? 
	echo "</td>";
	echo "<td class='center'>".
		html::anchor( '/structure/craft/'.$structure->id . '/' . $craftableitem['cfgitem_id'], kohana::lang('global.craft'),
		array( 
			'class' => 'button button-xsmall',
			'style' => 'display:inline',			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ) );
    if ($hasqueue)
    {
       echo "&nbsp;";
       echo html::anchor( 
				'/structure/craft/'.$structure->id . '/' . $craftableitem['cfgitem_id'] . '/2', '  x2  ',
		    array( 
				'class' => 'button button-xsmall',				
				'style' => 'display:inline',
				'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ) );
       echo "&nbsp;";
       echo html::anchor( '/structure/craft/'.$structure->id . '/' . $craftableitem['cfgitem_id'] . '/3', '  x3  ',
		    array( 
				'style' => 'display:inline',
				'class' => 'button button-xsmall',
				'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ) );
    }		
	echo "</td>";
	echo "</tr>";	
	$r++;
}

?>
</table>

<br style = 'clear:both'/>

