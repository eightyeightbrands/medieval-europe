<script type="text/javascript"> 

$(document).ready(function() {  
				
	$('#battlereportround1').show('slow', function() {} );
	$('#battlereportround2').hide();
	$('#battlereportround3').hide();
	$('#battlereportround4').hide();
	$('#battlereportround5').hide();	
	$('.viewround').click( function() 
	{						
		$('.battlereportround').hide();
		$('#battlereport' + this.id ).show('slow');		
	} );
	
});
</script>

<div class="pagetitle"><?php echo kohana::lang('structures_actions.battlereport')?></div>
<div class="separator">&nbsp;</div>
<center>
<div id='battlereportheader'>
	
</div>
<div id='battlereportbody'>
<?php echo kohana::lang('battle.battlereport_introduction') ?>
<br/><br/>

<div>
<?php 
if ( !is_null($battlereport1) )
{
?>
<button class='viewround button button-small center' id='round1'>
<?php echo kohana::lang('battle.viewround', 1)?>
</button>
<?php } ?>

<?php 
if ( !is_null($battlereport2) )
{
?>
<button class='viewround button button-small center' id='round2'>
<?php echo kohana::lang('battle.viewround', 2)?>
</button>
<?php 
} 
?>

<?php 
if ( !is_null($battlereport3) )
{
?>
<button class='viewround button button-small center' id='round3'>
<?php echo kohana::lang('battle.viewround', 3)?>
</button>
<?php 
} 
?>

<?php 
if ( !is_null($battlereport4) )
{
?>
<button class='viewround button button-small center' id='round4'>
<?php echo kohana::lang('battle.viewround', 4)?>
</button>
<?php 
} 
?>

<?php 
if ( !is_null($battlereport5) )
{
?>
<button class='viewround button button-small center' id='round5'>
<?php echo kohana::lang('battle.viewround', 5)?>
</button>
<?php 
} 
?>



</div>
<br style='clear:both'/>
<br style='clear:both'/>
<div id='battlereportround1' class='battlereportround'>
<?php echo Battle_Engine_Model::format_fightreport ( $battlereport1, 'html') ;  ?>
</div>
<div id='battlereportround2' class='battlereportround'>
<?php echo Battle_Engine_Model::format_fightreport ( $battlereport2, 'html') ;  ?>
</div>
<div id='battlereportround3'>
<?php echo Battle_Engine_Model::format_fightreport ( $battlereport3, 'html') ;  ?>
</div>
<div id='battlereportround4'>
<?php echo Battle_Engine_Model::format_fightreport ( $battlereport4, 'html') ;  ?>
</div>
<div id='battlereportround5'>
<?php echo Battle_Engine_Model::format_fightreport ( $battlereport5, 'html') ;  ?>
</div>


</div>
<div id='battlereportfooter'></div>	
</center>
