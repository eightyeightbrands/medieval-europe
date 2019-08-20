<div class="pagetitle"><?php echo kohana::lang('character.events_pagetitle') ?></div>

<?php echo $submenu; ?>

<?php
if ($events -> count() == 0) 
{
?>

<p class='center'><?php echo kohana::lang('character.noevents'); ?></p>

<?php
}
else 
{ 
?>

	<div class="pagination"><?php echo $pagination->render(); ?></div>

	<?php echo form::open('event/deleteselected') ?>
	<table>
	<th width='23%' ><?php echo kohana::lang('global.date') ?></th>
	<th width='77%' ><?php echo kohana::lang('global.description') ?></th>
	<?php
	$r = 0;
	foreach ( $events as $e )
	{	
		$class = ($r % 2 == 0) ? 'alternaterow_1' : 'alternaterow_2' ; 
		echo "<tr class='$class'>";	
		//echo '<td>'. form::checkbox('events['.$e->id.']', true, false) . '</td>';			
		echo "<td>".Utility_Model::format_datetime($e->timestamp)."</td>";
		echo "<td class='" . $e->eventclass . "'>".My_I18n_Model::translate( $e->description ) ."</td>";
		echo "</tr>";
		$r++;
	}
	?>
	</table>

	<div class="pagination"><?php echo $pagination->render('extended'); ?></div>

<?php } ?>
