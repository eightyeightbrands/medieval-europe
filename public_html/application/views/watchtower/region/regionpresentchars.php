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
	<?php echo kohana::lang("regioninfo.list_regionpresentchars", kohana::lang(	$currentregion -> name)) ?>
</div>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<?php echo $submenu ?>
<div id="dialog"></div>
<?php if ( count($presentchars) == 0 )
{
?>
<p class="center">
<?
	echo kohana::lang('regioninfo.nocharsfound');
}
else
{
?>
<div>
<strong><?= kohana::lang('regioninfo.groupedbykingdom'); ?></strong>
<br/>

<?
$i=0;
foreach ($kingdomcount as $k)
{
	echo kohana::lang($k -> kingdom_name) . ' (' . $k -> c . ')' ;
	if ($i < count($kingdomcount) - 1)
		echo ", ";
	$i++;
}
?>

<br/>

<strong><?= kohana::lang('regioninfo.groupedbyreligion'); ?></strong>
<br/>
<?
$i=0;
foreach ($religioncount as $r)
{
	echo kohana::lang('religion.church-'.$r -> church_name) . ' (' . $r -> c . ')' ;
	if ($i < count($religioncount) - 1)
		echo ", ";
	$i++;
}
?>
</div>
<br/>
<?php echo $pagination -> render(); ?>
<br/>
<table>
<thead>
<tr>
<th width='20%' class='center'><?php echo kohana::lang('global.name') ?></th>
<th width='15%' class='center'><?php echo kohana::lang('global.church') ?></th>
<th width='12%' class='center'><?php echo kohana::lang('global.kingdom') ?></th>
<th width='10%' class='center'><?php echo kohana::lang('global.lastlogin') ?></th>
<th width='8%' class='center'><?php echo kohana::lang('global.status') ?></th>
</tr>
</thead>
<tbody>
<?php	
	$kingdoms = array();
	$i = 0;
	foreach ($presentchars as $citizen )	
	{
		
		( $i % 2 == 0 ) ? $class = 'alternaterow_1' : $class = '';
		if ($citizen -> status == 'dead' )
			continue;
		//kohana::log('debug', kohana::debug($value) );		
		echo "<tr class = '{$class}'>";
		echo "<td class='charname' data-characterid='" . $citizen->id . "'>";
		$basicpackagetitle = 	Character_Model::get_basicpackagetitle( $citizen -> id );
		if ($basicpackagetitle != '' )
		{
			echo kohana::lang($basicpackagetitle);
			echo "&nbsp;";
		}
		else
			;
		
		echo "<span class='character {$citizen->type}'>" .
			$citizen -> char_name . '</span></td>';						
		
		if ($citizen -> type == 'pc' )
			echo "<td class='center'>" . kohana::lang('religion.church-'.$citizen->church_name). '</td>';
		else
			echo "<td class='center'>-</td>";
		
		if ($citizen -> type == 'pc' )
			echo "<td class='center'>" . kohana::lang($citizen->kingdom_name) . '</td>';			
		else
			echo "<td class='center'>-</td>";
		
		if ($citizen -> type == 'pc' )
			echo "<td class='center'>".$citizen->last_login."</td>";
		else
			echo "<td class='center'>-</td>";
		
		if ($citizen -> type == 'pc' )
		{
			if ( Character_Model::is_online($citizen->id) )
				echo "<td  class='center' style='color:#009933;font-weight:bold'>Online</td>";
			else
				echo "<td  class='center' style='color:#cc0000;font-weight:bold'>Offline</td>";
		}
		else
			echo "<td  class='center' style='color:#009933;font-weight:bold'>Online</td>";
		
		echo '</tr>';
		$i++;
	}
	
?>
</tbody>
</table>

<?php echo $pagination -> render(); ?>

<?php  } ?>

<br style='clear:both'/>
