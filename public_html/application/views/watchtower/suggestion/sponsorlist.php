<div class="pagetitle"><?php echo kohana::lang('suggestions.sponsorlist', $suggestion -> title )?></div>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/><br/>
<div style='text-align:center'>
<?= html::anchor('suggestion/index', 'Back to Suggestion Index', array('class' => 'button button-medium')); ?>
</div>
<br/>

<center>
<table style='width:80%'>
<?php 
$i = 1;
foreach ($sponsorlist as $sponsor)
{
$class = ( $i % 2 == 0 ) ? '' : 'alternaterow_1';
?>
<tr class='<?php echo $class ?>'>
<td><?php echo $i . '. ' . $sponsor -> name ; ?></td>
<td class='right'><?php echo $sponsor -> value . ' ' . kohana::lang('global.doubloons') ; ?></td>
</tr>
<?php
$i++;
}
?>
</table>
</center>
<br style="clear:both;" />
