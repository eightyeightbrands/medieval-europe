<script type='text/javascript'>

$(document).ready(function()
{		
		$('#body').markItUp(mySettings);
		
		$("#showpreview").click(function() 
		{				
			$.ajax( //ajax request starting
			{
			url: "index.php/jqcallback/bbcodepreview", 
			type:"POST",
			data: { text: $("#body").val() },
			success: 
				function(data) 
				{																	
					$("#preview").html(data); 				
				}
			}	
			);						
	});	
});
</script>

<div class="pagetitle"><?php echo $structure -> getName()?></div>

<?php echo $submenu ?>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<?= $section_description; ?>
<br/>
<?= $section_informativemessage; ?>
<br/>
<?= $section_loadpicture; ?>
<br style="clear:both;" />

