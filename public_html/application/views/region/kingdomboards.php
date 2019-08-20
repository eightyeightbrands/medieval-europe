<div class="pagetitle"><?php echo kohana::lang('kingdomforum.forumtitle', kohana::lang($char -> region -> kingdom -> name)); ?></div>

<div class='right'>
<?php 

if ( Kingdom_Forum_Board_Model::haswriterights($char, $kingdom) == true  )
	echo html::anchor('region/addkingdomboard/' . $kingdom -> id, kohana::lang('kingdomforum.newboard'),
		array('class' => 'button button-small')); ?>
</div>

<div id='breadcrumb'>
<?php echo html::anchor('region/kingdomboards/' . $kingdom -> id, 
	kohana::lang('kingdomforum.forumtitle', kohana::lang($kingdom -> name)));?>
</div>	

<?php
//var_dump($rows);exit;
if ( count($rows) == 0 )
	echo "<p class='center'><i>" . kohana::lang('kingdomforum.norowsyet') . '</i></p>' ;
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
<th></th>
<?php
$r = 0;
foreach ( $rows as $row )
{
	//var_dump($row);exit;
	$class = ( $r % 2 ) == 0 ? '' : 'alternaterow_1'; 
?>
	<tr class='<?php echo $class;?>'>
	<td width='50%' class='justify'>
	<?php echo html::anchor('/region/kingdomtopics/' . $kingdom -> id . '/' . $row -> id, $row -> name);?>
	<br/>
	<i><?php echo $row -> description; ?></i>
	</td>
	<td class='center' width='20%'><?php echo Character_Model::create_publicprofilelink($row -> author, null);?></td>
	<td class='center' width='20%'><?php echo Utility_Model::format_datetime(strtotime($row -> created));?></td>
	<td class='center' width='10%'><?php 
		if ( Kingdom_Forum_Board_Model::haswriterights($char, $kingdom) == true  )
		{
			echo html::anchor('/region/editkingdomboard/' . $row -> id, kohana::lang('global.edit'));
			echo '<br/>';
			echo html::anchor('/region/deletekingdomboard/' . $row -> id, kohana::lang('global.delete'),
				array('onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')')
			);		
		}
	?>
		</td>
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

