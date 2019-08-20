<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.css">
<!-- Latest compiled and minified JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.js"></script>
<script type="text/javascript">
$(function () {
 
  $(".rateYo").rateYo({
    starWidth: "20px",	
  });
 
});
</script>

<div class="pagetitle"><?php echo kohana::lang( 'suggestions.all' ) ?></div>

<br/>
<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<div id='helper'><?= kohana::lang('suggestions.helper')?></div>
<br/>
<?= $submenu ?>
<br/>
<?= $submenu2 ?>
<div style='text-align:right'>
<?php 
echo html::anchor(
	'suggestion/add', kohana::lang('global.add'),
	array('class'=> 'button button-small'))?>
</div>


<?php
if ( $suggestions -> count() == 0 )
	echo "<br/><p class='center'>" . kohana::lang('suggestions.nosuggestionsfound') . "</p>";
else
{
?>

<div class="pagination"><?php echo $pagination->render(); ?></div>
<table class='normal' border="1">
<th width="5%">Id</th>
<th width="15%"><?=kohana::lang('global.author');?></th>
<th width="25%"><?=kohana::lang('global.title');?></th>
<th width="8%"><?=kohana::lang('global.status');?></th>
<th width="15%"><?=kohana::lang('global.rating');?></th>	
<th width="20%"></th>	
<? 
$r = 0;
foreach ( $suggestions as $suggestion ) 
{		
$class = ($r % 2 == 0) ? 'alternaterow_1' : 'alternaterow_2';
?>
<tr class="<?=$class;?>">
<td class='center'><?=$suggestion -> id; ?></td>
<td class='center'><?= Character_Model::create_publicprofilelink($suggestion->character_id); ?></td>
<td class='center' style='word-wrap: break-word'><?= html::anchor('/suggestion/view/' . $suggestion -> id, $suggestion -> title) ?></td>
<td class='center'><?= kohana::lang('suggestions.status_'.$suggestion -> status);
if ( $suggestion -> status == 'fundable' or $suggestion -> status == 'funded' )
	echo "<br/>" . ($suggestion -> sponsoredamount. "/" . $suggestion->quote) . " (" . min(1,round($suggestion -> sponsoredamount/$suggestion-> quote,2))*100 . "%)";
 ?>
</td>
<td>
<div style='margin:0 auto' class="rateYo"data-rateyo-read-only="true" data-rateyo-rating="<?=Utility_Model::number_format($suggestion->baesianrating,2);?>"></div>
<div class='center'><?= $suggestion -> votes;?> <?= kohana::lang('global.votes');?></div>
</td>
<td class='center'>
<? 
$suggestioncommands -> suggestion = $suggestion;
echo $suggestioncommands; 
?>
</td>
</tr>
<? 
$r++;
} ?>
</table>

<br/>

<div class="pagination"><?php echo $pagination->render(); ?></div>

<?php } ?>

<br style="clear:both;" />
