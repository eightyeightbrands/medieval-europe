<script type='text/javascript'>
$(document).ready(function(){
$('.structure').tooltipster(
		{
			content: 'Loading...',
			theme: 'tooltipster-borderless',
			contentAsHTML: true,
			trigger: 'click',
			interactive: true,
			functionBefore: function(instance, helper) {					
				var $origin = $(helper.origin);				
				// we set a variable so the data is only loaded once via Ajax, not every time the tooltip opens
				if ($origin.data('loaded') !== true) {						
					instance.content($origin.data('content'));	
					$origin.data('loaded', true);								
				}
			}
	}
)
});
</script>

<div class="pagetitle"><?php echo kohana::lang("regionview.privatestructures") ?></div>

<?= $submenu; ?> 

<br/>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/><br/>
	
<?php
if ( count( $privatestructures ) == 0 )
{
?>
<p class='center'><?php echo kohana::lang('structures.noprivatestructures'); ?></p>

<?php 
}
else
{
?>

<div style='width:100%'>
<?php
foreach ( $privatestructures as $privatestructure ) 
{
	$class = '';;
	$title = 'ID:' . $privatestructure -> id . '<br/>';
	$title .= kohana::lang('structures.structuretitle', kohana::lang($privatestructure -> structure_name), str_replace("'", "&#39;", $privatestructure -> owner )) . '<br/>';
	$title .= '<hr/>';
	$title .= '<br/>';
	$title .= html::anchor( "/structure/info/" . $privatestructure -> id, Kohana::lang('structures_actions.global_info'), array('class' => 'st_common_command')) . "<br/>";
	
	$class = 'access-none';
	
	if ( $privatestructure -> cannotmanage == false )	
	{
		$title .= html::anchor( "/structure/manage/" . $privatestructure -> id, Kohana::lang('global.manage'), array('class' => 'st_special_command')) . "<br/>";
		$class = 'access-granted';
	}
	
	echo "<div style='float:left;margin-right:3px;' class='structure $class' data-content = '$title' >";	
	if ( $privatestructure -> supertype == 'terrain' ) 
		echo html::image ( 'media/images/structures/' . $privatestructure -> type . '_' . $privatestructure -> attribute1 . '.jpg',
			array('class' => 'size75 border'));
	else
		echo html::image ( 'media/images/structures/' . $privatestructure -> image,
			array('class' => 'size75 border'));
	echo '</div>';

}
?>

<br style='clear:both'/>

</div>

<?php } ?>

<br style='clear:both'/>