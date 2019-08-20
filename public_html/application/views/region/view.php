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
		
	$('.character').click(function() 
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
	
	$('.structure').click(function() 
	{	
		var target = $(this);
		$("#dialog").html('');
		$("#dialog").dialog('option', 'title', 'Loading...');

		$.ajax({
			url: '<?php echo url::base(true)?>' + '/jqcallback/loadstructureinfo',
			data: { structureid: $(this).data('structureid')},
			type: 'POST',			
			success: function(data){
			info = JSON.parse( data );			
			$("#dialog").html(info.html);
			$("#dialog").dialog('option', 'title', info.title);
    }   
		});			
		$("#dialog").dialog("option", "position", {
			my: "left bottom",		  
			at: "top",
			of: target
		}).dialog("open");		
		
	});
	
	$('.item').click(function() 
	{		
		
		$("#dialog").html('');
		$("#dialog").dialog('option', 'title', 'Loading...');
		
		$.ajax({
			url: '<?php echo url::base(true)?>' + '/jqcallback/loaditeminfo',
			type: 'POST',
			data: { 
				itemid: $(this).data('itemid'), 
				regionid: <?=$currentregion->id;?>,
			},
			success: function(data){
				info = JSON.parse( data );			
				$("#dialog").html(info.html);
				$("#dialog").dialog('option', 'title', info.title);
			}
		});	
			
		$("#dialog").dialog("option", "position", {
			at: "bottom right",
			of: $(this)
		}).dialog("open");		
		
	});

	$('#charsinregion, #itemsinregion').hide();
		
	$('#togglecharsinregion').click( function(e)
	{
		e.preventDefault();
		$('#charsinregion').toggle();		
	});
	
	$('#toggleitemsinregion').click( function(e)
	{
		e.preventDefault();
		$('#itemsinregion').toggle();		
	});
	
});
</script>

<div id="dialog"></div>
<div id='helper'>
<?
if ($currentregion->type != 'sea' )
	echo kohana::lang('regionview.helper_charpresent'); 
else
	echo kohana::lang('regionview.helper_charpresentsea'); 
?>
</div>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/>
<br/>


<div>
	<?
	
		echo kohana::lang('regionview.citysquarepeople', 
		html::anchor(
			'#', 
			$list_c['pc'],			
			array(
				'id' => 'togglecharsinregion',
				'title' => kohana::lang('regionview.expandcitysquarepeople')
				))				
			,
			$list_c['npc']
			);
	?>
		
	<span style="float:right"><?= html::anchor(	'/region/regionpresentchars',
	kohana::lang('regionview.completelist'),
	array(
		'title' => kohana::lang('regionview.detailedcitysquarepeoplelist'),
		'escape' => true,
		'target' => 'new'
	));
	?>
	</span>		
</div>

<hr/>
	
<div id='charsinregion'>
	<?
	if ($list_c['pc'] == 0 )
		;
	else
	{
		$i=0;
		foreach ($list_c['list'] as $charinregion)
		{
			if ($charinregion['online'])
				$class='online';
			else
				$class='offline';
			
			if($charinregion['char']->type=='npc')
				$class .= ' npc' ;
			
			if ($i>0) echo ", ";
				echo "<span class='character {$class}' data-characterid='{$charinregion['char']->id}'>{$charinregion['char']->name}</span>";
			$i++;
		}
	}
	?>
</div>

<br/>
<? if ($currentregion->type != 'sea' ) { ?>
<div>
<?= kohana::lang('regionview.citysquareitems', 
			html::anchor(
				'#', 
				count($list_i),
				array(
					'id' => 'toggleitemsinregion',
					'title' => kohana::lang('regionview.expandcitysquareitems')
					))); ?>
</div>

<hr/>

<div id='itemsinregion'>
<?
if (count($list_i) == 0 )
	;
else
{
	$i=0;
	foreach ($list_i as $item)
	{
		
		if ($i>0) echo ", ";
		echo "<span class='item' data-itemid='{$item->id}'>" . $item->quantity . " " . kohana::lang($item->cfgitem->name). "</span>";
		$i++;
	}
}
?>
</div>
<? } ?>
<br/>
<!-- political structures -->
<div class='list-structures'>
<?php 
if ( isset( $structures['government']) )
{
?>
	<h5><?php echo Kohana::lang('regionview.political_structures')?></h5>
	<?php
	foreach ( $structures['government'] as $structure )
	{	
	
		if ( $structure -> cannotmanage == false)	
		{			
			$class = 'access-granted';
		}
		else
			$class = 'access-none' ;
		?>
		
		<div class='structure <?php echo $class; ?>' data-structureid='<?php echo $structure -> id?>'>
		<?php
		echo html::image(
			array(				
				'src' => 'media/images/structures/'. $structure -> image,
				'instance' => 'image-structure'
			 ));
		?>
		</div>
	<?php
	}
}
?>	
</div>

<div class='list-structures'>

<!-- religious structures -->

<?php 
if ( isset( $structures['church'] ) )
{
?>
	<h5><?php echo Kohana::lang('regionview.church_structures')?></h5>
	<?php
	foreach ( $structures['church'] as $structure )
	{	
		
		if ( $structure -> cannotmanage == false)	
		{			
			$class = 'access-granted';
		}
		else
			$class = 'access-none' ;	
	?>
		<div class='structure <?php echo $class; ?>' id='<?php echo $structure->type ?>' data-structureid='<?php echo $structure -> id?>'>
		<?php
		echo html::image(
			array(				
				'src' => 'media/images/structures/'. $structure -> image,
				'instance' => 'image-structure'
			 ));
		?>
		</div>
	<?php 
	}
}
?>	
</div>

<!-- other structures -->
<div class='list-structures'>
<?php 
if ( isset( $structures['other']) )
{
?>
	<h5><?php echo Kohana::lang('regionview.other_structures')?></h5>
	<?php
	foreach ( $structures['other'] as $structure )
	{				
?>
		<div class='structure' id='<?php echo $structure->type ?>' data-structureid='<?php echo $structure -> id?>'>
<?  if ($structure -> type == 'billboard' and $char -> get_age() < 3) 
		{ 
?>		
					<div class="side-corner-tag">
						<?= html::image(
							array(				
								'src' => 'media/images/structures/'. $structure -> image,
								'instance' => 'image-structure',
								'data-structureid'=> $structure -> id,
								'class' => 'structureimage',
							 ));
						?>
						<p><span>Welcome<br/>new player</span></p>
					</div>
<? 	
		} 
		else
			echo html::image(
				array(				
					'src' => 'media/images/structures/'. $structure -> image,
					'instance' => 'image-structure',
					'data-structureid'=> $structure -> id,
					'class' => 'structureimage',
				)
			);
?>
		</div>
<?		
	}		
}
?>
</div>

<!-- player structures -->
<div class='list-structures'>
<?php 
//var_dump($structures);
if ( isset( $structures['player']) )
{
?>
	<h5><?php echo Kohana::lang('regionview.char_structures')?></h5>
	<?php
	foreach ( $structures['player'] as $structure )
	{			
	?>
		<div class='structure' data-structureid='<?php echo $structure -> id?>'>
		<?php
		
		if ( $structure -> supertype == 'terrain' )
			$image = 'media/images/structures/' . $structure -> image . '_' . $structure -> s_attribute1 . '.jpg';
		else			
			$image = 'media/images/structures/' . $structure -> image; 
		
		echo html::image(
			array(				
				'src' => $image,
				'instance' => 'image-structure'
			 ));
		?>
		</div>
	<?php
	}
}
?>	
</div>

<br style='clear:both'/>
