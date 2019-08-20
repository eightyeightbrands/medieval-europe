<?= html::stylesheet('media/newlayout/css/region', FALSE);	?>
<?= $templateheader; ?>
<script type="text/javascript">
$(document).ready(function() 
{ 				
	$('.structure').click(function() 
	{							  
		$.ajax({
			url: '<?php echo url::base(true)?>' + '/jqcallback/loadstructureinfo',
			data: { structureid: $(this).data('structureid')},
			type: 'POST',			
			success: function(data){
				info = JSON.parse( data );	
				
				$("#dialog").html(info.html);
				
				var dialog = $("#dialog").dialog(
				{
					bgiframe: true,		
					autoOpen: false,		
					closeOnEscape: true,	
					dialogClass: 'myuidialog',
					modal: true,
					minWidth: 400
				});				
				
				dialog.dialog( "option", "title", info.title );
				dialog.dialog("open");
			}   
		});				
	});
});
</script>
	
<?= $templateheader; ?>

<div id="background">	  
	<div id="bodyWrapper">				
		<div id="charproperties" class='center'>
			<h3 class='center' style='margin:0 0 3 0'>Your properties</h3>
			<div class='left' style='margin:0 auto;width:80%'>
			<?
			foreach ($structures as $category => $types )
				foreach ( $types as $type => $structuredata )
					if ( $category == 'player' ) 
					{
						if ( $structuredata-> supertype == 'terrain' )
							$image = 'media/images/structures/' . $structuredata -> image . '_' . $structuredata -> s_attribute1 . '.jpg';
						else			
							$image = 'media/images/structures/' . $structuredata-> image; 
					
						echo html::image(
							array(				
								'src' => $image,
								'class' => 'structure border',
								'id' => $structuredata -> type,	
								'data-structureid' => $structuredata -> id							
							 ));
						
					}		
			?>
			</div>
		</div>
		</div>
		<div id="regioncontent" class="transparent">		
			<?= $content ; ?>
		</div>
	</div>
</div>

<?= $templatefooter; ?>