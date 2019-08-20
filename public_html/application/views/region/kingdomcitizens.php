<script type="text/javascript">

$(document).ready(function()
{
	
	$("#dialog").dialog(
	{
		bgiframe: true,
		autoOpen: false,	
		width:'400px',
		closeOnEscape: true,
		dialogClass: 'myuidialog',
	});
		
	$('.charname').click(function() 
	{		
	    var target = $(this);
		$("#dialog").html('');
		$("#dialog").dialog('option', 'title', 'Loading...');
		$.ajax({
			url: '<?php echo url::base(true)?>' + '/jqcallback/loadcharacterinfo',
			type: 'POST',
			data: { 
				characterid: $(this).data('characterid'),
			},
			success: function(data) 
			{																	
				info = JSON.parse( data );			
				$("#dialog").html(info.html);
				$("#dialog").dialog('option', 'title', info.title);
			}
		});						
		$("#dialog").dialog("option", "position", {
		  my: "right",
		  at: "right",
		  of: target,
		}).dialog("open");
	
	});	
	
});
</script>

<div class="pagetitle">
	<?php echo kohana::lang("regioninfo.list_kingdomresidentchars", kohana::lang(	$currentregion -> kingdom -> get_name()  )) ?>
</div>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<?php echo $submenu ?>
<div id="dialog"></div>
<?php if ( count($citizens) == 0 )
{
?>
<p class="center">
<?
	echo kohana::lang('regioninfo.nocharsfound');
}
else
{
?>

<?php echo $pagination -> render(); ?>
<br/>
<table>
<thead>
<tr>
<th width='25%' class='center'><?php echo kohana::lang('global.name') ?></th>
<th width='12%' class='center'><?php echo kohana::lang('character.home') ?></th>
<th width='10%' class='center'><?php echo kohana::lang('global.lastlogin') ?></th>
<th width='8%' class='center'><?php echo kohana::lang('global.status') ?></th>
</tr>
</thead>
<tbody>
<?php	

	$i = 0;
	foreach ($citizens as $citizen )	
	{
		
		( $i % 2 == 0 ) ? $class = 'alternaterow_1' : $class = '';
		//kohana::log('debug', kohana::debug($value) );		
		echo "<tr class = '{$class}'>";
		echo "<td class='charname' data-characterid='" . $citizen->id . "'>";
		
		$title =  Character_Model::get_basicpackagetitle( $citizen -> id );
		if (!empty($title))
			echo kohana::lang($title) . " ";
		echo Character_Model::create_publicprofilelink( $citizen -> id, null ) . "</td>";
		echo '<td class=\'center\'>' . kohana::lang($citizen->region_name) . '</td>';			
		echo "<td class='center'>".$citizen->last_login."</td>";
		
		if ( Character_Model::is_online($citizen->id) )
			echo "<td  class='center' style='color:#009933;font-weight:bold'>Online</td>";
		else
			echo "<td  class='center' style='color:#cc0000;font-weight:bold'>Offline</td>";
	
		
		echo '</tr>';
		$i++;
	}
?>
</tbody>
</table>

<?php echo $pagination -> render(); ?>

<?php  } ?>

<br style='clear:both'/>
