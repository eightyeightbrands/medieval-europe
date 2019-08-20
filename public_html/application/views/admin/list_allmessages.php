<div class="pagetitle"><?php echo kohana::lang('admin.oldmessages') ?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div class="pagination"><?php echo $pagination->render(); ?></div>
<br/>

<table>
<th width='22%' ><?php echo kohana::lang('admin.postedon') ?></th>
<th width='57%' ><?php echo kohana::lang('admin.summary') ?></th>
<th width='10%' class='right' ><?php echo kohana::lang('admin.viewed') ?></th>
<th></th>
<?php
$r = 0;
foreach ( $messages as $m )
{	
	$class = ($r % 2 == 0) ? '' : 'alternaterow_1' ; 
	echo "<tr class=\"$class\">";	
	echo "<td>" . Utility_Model::format_datetime ($m -> timestamp) . "</td>";
	echo "<td>" . Utility_model::bbcode($m -> summary) . "</td>";
	echo "<td class='right'>" . $m -> read . "</td>";
	echo "<td class='center'>" . html::anchor('admin/read_adminmessage/' . $m -> id, kohana::lang('global.read') ) . "</td>";
	echo "</tr>";
	$r++;
}
?>
</table>
<br/>
<div class="pagination"><?php echo $pagination->render('extended'); ?></div>

<br style="clear:both;" />
