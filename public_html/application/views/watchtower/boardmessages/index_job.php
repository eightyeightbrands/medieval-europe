<div class="pagetitle"><?php echo kohana::lang( $currentposition -> kingdom -> get_name() ) . " - " . kohana::lang( 'boardmessage.announcementboard' ) ?></div>

<br/>
<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/>

<?= $submenu; ?>

<div style='text-align:right'>
<?php 
echo html::anchor(
	'boardmessage/add/job', kohana::lang('global.add'),
	array( 'class' => 'button button-small'));
?>
</div>

<?php
if ( $messages -> count() == 0 )
	echo "<br/><p class='center'><i>" . kohana::lang('boardmessage.boardnoannouncefound') . "</i></p>";
else
{
?>

<div class="pagination"><?php echo $pagination->render(); ?></div>

<br/>

<table>
<th>Id</th>
<th><?= kohana::lang('global.author');?></th>
<th><?= kohana::lang('global.author');?></th>
<th><?= kohana::lang('global.createddate');?></th>
<th><?= kohana::lang('global.expires');?></th>

<?php 
$k = 0;
foreach ( $messages as $message ) 
{	
$class = ($k % 2 == 0) ? 'alternaterow_1' : 'alternaterow_2' ; 
?>

<tr class='<?=$class;?>'>
<td class='center'><?= $message -> id; ?></td>
<td class='center'><?= Character_Model::create_publicprofilelink( $message -> character_id, null);?></td>
<td class='center'><?= html::anchor('boardmessage/view/' . $message -> id, $message->title);?></td>
<td class='center'><?= Utility_Model::format_datetime($message -> created);?></td>
<td class='center'><?= Utility_Model::format_datetime($message -> created + $message -> validity *24*3600);?></td>
</tr>

<?php 
$k++;
} 
?>
</table>
<? } ?>

<div class="pagination"><?php echo $pagination->render(); ?></div>


<br style="clear:both;" />
