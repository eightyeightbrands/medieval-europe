<div class="pagetitle"><?php echo kohana::lang("page.toplist_pagetitle");?></div>

<div id='helper'>
<?php echo kohana::lang('page.toplist_helper' , kohana::lang( 'page.toplist_reward_' . $type )  ) ?>
</div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>
<br/>
<table>
<th class='center' width='30%'>Toplist</th>
<th class='center' width='20%'><?php echo kohana::lang('page.toplistvotestarget')?></th>
<th class='center' width='30%'><?= kohana::lang('page.toplistlastvotedon');?></th>
<th class='center' ></th>
<?php

$k = 0;
foreach ($toplists as $toplist) 
{
	$class = ( $k % 2 == 0 ) ? 'alternaterow_1' : '';	
?>
	<tr class='<?php echo $class;?>'>
	<td class='center'><?php echo $toplist['name']; ?></td>
	<td class='center'><?php echo $toplist['votes'] . '/' . $toplist['target'] ?></td>
	<td class='center'><?php echo date("d-M-Y H:i:s", $toplist['lastvoteunixtime'])?></td>
	<td class='center'>
		<?= html::anchor( 
			'/toplist/vote/' . $type . '/' . $toplist['id'], 
			kohana::lang('page.toplistvote'),
			array(
				'class' => 'button button-small',
				'target' => 'new') 
		);
		?>
	</td>
	</tr>
<?php
	$k++;
}	
?>
</table>

<br style='clear:both'/>