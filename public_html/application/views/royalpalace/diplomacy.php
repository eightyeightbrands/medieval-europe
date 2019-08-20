<div class="pagetitle">
<?php echo kohana::lang('diplomacy.diplomacyrelations'); ?>
</div>

<?php echo $submenu ?>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'>
<div id='helpertext'>
<?php 
	echo kohana::lang('diplomacy.diplomacy_helper')?>
</div>
<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=Diplomacy',
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button' ) ) 
		?>
</div>
<div style='clear:both'></div>
</div>


<div class='submenu'>
<?php echo html::anchor('royalpalace/diplomacy/' . $structure->id, kohana::lang('structures_royalpalace.submenu_diplomacy'), array('class' => 'selected' ));?>
&nbsp;
<?php echo html::anchor('royalpalace/giveaccesspermit/' . $structure->id, kohana::lang('structures_royalpalace.submenu_giveaccesspermit'))?>
</div>

<br/>

<fieldset>
<legend><?= kohana::lang('diplomacy.pendingproposals');?></legend>

<? if (is_null ( $proposals ) ) {  ?>
<p class='center'><?= kohana::lang('diplomacy.nodiplomacyproposals'); ?></p>
<? }
else
{
?>
<table>
<th>Id</th>
<th><?=kohana::lang('global.sourcekingdom');?></th>
<th><?=kohana::lang('global.targetkingdom');?></th>
<th><?=kohana::lang('diplomacy.newstatus');?></th>
<th><?=kohana::lang('global.expires');?></th>
<th></th>

<? 
	$k = 0;
	foreach ( $proposals as $proposal ) { 
	
		// cancella proposte expired
		
		if ( ($proposal -> timestamp + (48*3600)) < time() ) 
		{
			$proposal -> status = 'expired';
			$proposal -> save();
			continue;
		}
		
		if ($proposal -> status != 'new' )
		{
			continue;
		}
		
		
		$class = ( $k % 2 == 0 ? 'alternaterow_1' : 'alternaterow_2' );	
		$sourcekingdom = ORM::factory('kingdom', $proposal -> sourcekingdom_id);
		$targetkingdom = ORM::factory('kingdom', $proposal -> targetkingdom_id);
		
?>
<tr class='<?=$class;?>'>
<td class='center'><?=$proposal->id;?></td>
<td class='center'><?=kohana::lang($sourcekingdom -> name);?></td>
<td class='center'><?=kohana::lang($targetkingdom -> name);?></td>
<td class='center'><?=kohana::lang('diplomacy.'.$proposal -> diplomacyproposal);?></td>
<td class='center'><?=Utility_Model::countdown( $proposal -> timestamp + (48*3600));?></td>

<? if ($character -> region -> kingdom_id != $sourcekingdom -> id )
{
?>
	<td class='center' width='30%'>
		<?= html::anchor('/royalpalace/diplomacyproposalfeedback/accept/' . $proposal -> id, 'Accept', array( 'class' => 'button button-xxsmall'));?>&nbsp;
	
	<?= html::anchor('/royalpalace/diplomacyproposalfeedback/decline/' . $proposal -> id, 'Decline', array( 'class' => 'button button-xxsmall'));?></td>
<? } 
else
	{
?>
<td></td>
<? 
	} ?>



</tr>
<? 
	$k++;
} ?>
</table>
<? } ?>
</fieldset>

<br/>
<fieldset>
<legend><?= kohana::lang('diplomacy.diplomacyrelations');?></legend>
<table>
<thead>
<th><?php echo kohana::lang('global.kingdom')?></th>
<th><?php echo kohana::lang('global.kingdom')?></th>
<th><?php echo kohana::lang('global.type')?></th>
<th><?php echo kohana::lang('global.signedon')?></th>
<th></th>
</thead>
<tbody>
<?php 
	$k = 0;
	foreach ( $kingdoms as $kingdom )	
		if ($region -> kingdom_id != $kingdom -> id and $kingdom -> name != 'kingdoms.kingdom-independent')
		{
			$class = ( $k % 2 == 0 ? 'alternaterow_1' : 'alternaterow_2' );	
	?>		


		<tr class='<?= $class;?>'>
			<td class='center'><?php echo kohana::lang($region -> kingdom -> get_name() ) ?></td>
			<!--
			<td class='center'><?php echo html::image('media/images/template/green-arrow-left-right.png')?></td>		
			-->
			<td class='center'><?php echo kohana::lang($kingdom -> name ) ?></td>
			<td class='center <?php echo 'diplomacy'.$relations[$region -> kingdom_id][$kingdom -> id]['type']?>'>
				<?php echo kohana::lang('diplomacy.' . $relations[$region -> kingdom_id][$kingdom -> id]['type'])?></td>
			<td class='center'><?php echo Utility_Model::format_datetime($relations[$region -> kingdom_id][$kingdom -> id]['timestamp'])?></td>			
			<td class='center'>
				<?php echo html::anchor(
					'royalpalace/modifydiplomacystatus/' . $structure -> id . '/' . 
						$relations[$region -> kingdom_id][$kingdom -> id]['id'], 
					kohana::lang('global.edit'),array( 'class' => 'button' )) ?>
			</td>			
		</tr>		
<?php
	$k++;
	}
?>	
</tbody>
</table>
</fieldset>

<div style='clear:both'></div>
