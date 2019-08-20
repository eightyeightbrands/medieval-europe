<div class="pagetitle">Richiesta di approvazione vestiti personalizzati</div>

<?php echo $submenu ?> 

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/><br/>

<table>
<th class='center'>Id</th>
<th class='center'>Char</th>
<th class='center'>Creata il</th>
<th></th>
<?php
$k = 0;
foreach ( $requests as $request )
{
$class = ( $k % 2 == 0 ) ? '' : 'alternaterow_1' ;
?>
<tr class='<?php echo $class ?>'>
<td class='center'><?php echo $request -> id ?> </td>
<td class='center'><?php echo $request -> character -> name ?> </td>
<td class='center'><?php echo date('m-d-Y H:i:s', $request -> created) ?> </td>
<td class='center'>
<?php echo html::anchor('admin/viewwardroberequest/' . $request -> id, 'Visualizza'); ?>
</td>
</tr>
<?php 
$k++;
}
?>
</table>
<br style="clear:both;" />
