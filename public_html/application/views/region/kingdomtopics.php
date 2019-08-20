<div class="pagetitle"><?php echo kohana::lang('kingdomforum.boardtitle', $currentboard -> name ); ?></div>


<div class='right'>
<?php 
if ( Kingdom_Forum_Topic_Model::haswriterights($char, $kingdom) == true  )
{
	echo html::anchor('region/addkingdomtopic/' . $currentboard -> id, kohana::lang('kingdomforum.addtopic'),
		array('class' => 'button button-small')); 
}
?>
</div>

<div id='breadcrumb'>
<?php echo html::anchor('region/kingdomboards/' . $kingdom -> id, 
	kohana::lang('kingdomforum.forumtitle', kohana::lang($kingdom -> name))) . ' > ' . $currentboard -> name ; ?>
</div>	
	
<?php
//var_dump($rows);exit;
if ( count($rows) == 0 )
	echo "<p class='center'>
<br/><i>" . kohana::lang('kingdomforum.norowsyet') . '</i></p>' ;
else
{
?>

<div class="pagination">
<?php //echo $pagination->render('extended'); ?>
</div>

<br/>
<table>
<th><?php echo kohana::lang('global.name');?></th>
<th class='center'><?php echo kohana::lang('global.author');?></th>
<th class='center'><?php echo kohana::lang('global.createddate');?></th>

<?php
$r = 0;
foreach ( $rows as $row )
{
	//var_dump($row);exit;
	$class = ( $r % 2 ) == 0 ? '' : 'alternaterow_1'; 
?>
	<tr class='<?php echo $class;?>'>
	<td><?php echo html::anchor('/region/kingdomreplies/' . $kingdom -> id . '/' . $row -> id, $row -> title);?></td>
	<td class='center'><?php echo Character_Model::create_publicprofilelink($row -> author, null);?></td>
	<td class='center'><?php echo Utility_Model::format_datetime(strtotime($row -> created));?></td>
	</tr>	
<?php 
$r++;
} 
?>
</table>

<div class="pagination"><?php //echo $pagination->render('extended'); ?></div>

<?php
}
?>

