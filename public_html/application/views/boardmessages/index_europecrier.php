<div class="pagetitle"><?php echo kohana::lang( 'boardmessage.messagecategoryeuropecrier' ) ?></div>
<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<?= $submenu; ?>
<?php	
if ( $messages -> count() == 0 )
	echo "<br/><p class='center'><i>" . kohana::lang('boardmessage.boardnoannouncefound') . "</i></p>";
else
{
?>

<div class="pagination"><?php echo $pagination->render(); ?></div>

<table>
<?php 
$k = 0;
foreach ( $messages as $message ) 
{
$class = ( $k % 2 ) ? '' : 'alternaterow_1';
?>	
<tr class='<?php echo $class?>'>
<td width='22%'>
<?php echo Utility_Model::format_datetime( $message -> created ); ?>
</td>
<td class='<?php echo $message -> messageclass ?>'>
<?php echo My_I18n_Model::translate($message -> message); ?>
</td>
</tr>
<?php 
$k ++;
} 
?>
</table>
<br/>
	
<div class="pagination"><?php echo $pagination->render(); ?></div>

<?php } ?>

<br style="clear:both;" />
