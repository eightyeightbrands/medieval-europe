<?php echo html::script('media/js/jquery/plugins/markitup/jquery.markitup.js', FALSE)?>
<?php echo html::script('media/js/jquery/plugins/markitup/sets/bbcode/set.js', FALSE)?>

<script type="text/javascript">
$(document).ready(function() 
{ 

if ( $(this).attr('value') == 0 )
	$('#hourlywage').hide();
else
	$('#hourlywage').show();
$('#structure').change( function() 
	{
		if ( $(this).attr('value') == 0 )
				$('#hourlywage').hide();
			else
				$('#hourlywage').show();
	});
$('#message').markItUp(mySettings);   
});
</script>

<head>
<script>
	$('#law_desc').markItUp(mySettings);
   
	$("#showpreview").click(function() 
		{				
			$.ajax( //ajax request starting
			{
			url: "index.php/jqcallback/bbcodepreview", 
			type:"POST",
			data: { text: $("#law_desc").val() },
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
</head>



<div class="pagetitle"><?php echo kohana::lang('boardmessage.add')?></div>

<div id='helper'>
<?php echo kohana::lang('boardmessage.addhelper')?>
</div>
<br/>
<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div class='submenu'>
<?php 
echo html::anchor('boardmessage/index/job', kohana::lang('boardmessage.announcementboard'));
?>
</div>
<br/>

<?php
$charstructures = array();
$res = Database::instance() -> query ( "select s.id, st.name structure_name, r.name region_name 
from structures s, structure_types st, regions r 
where s.structure_type_id = st.id 
and   s.region_id = r.id 
and   ( 
				( st.parenttype in ( 'terrain', 'shop') and st.level >= 2 )
				or
				( st.parenttype in ( 'breeding' ) and st.level >= 1 )	
			) 
and   s.character_id = " . $character -> id ) ; 

$charstructures[0] = kohana::lang('global.none');
foreach ( $res as $record )
	$charstructures[$record -> id] = kohana::lang( $record -> structure_name ) . ' - ' . kohana::lang( $record -> region_name  ) . ' (ID: ' . $record -> id . ')' ; ?>					

<?php echo form::open(); ?>
<?php echo form::hidden('category', 'job' ); ?>
<?php echo form::label( array( 
	'class' => 'form', 'name' => 'title' ), kohana::lang('boardmessage.messagetitle') ); ?>
<?php echo form::input( array( 'name' => 'title', 
'value' => $form['title'], 
'maxlength' => '50', 'title' => kohana::lang('boardmessage.titlehelper'), 'style' => 'width:300px' ) )?>
<?php if (!empty ($errors['title'])) echo "<div class='error_msg'>".$errors['title']."</div>";?>
<br/>

<?php echo form::label( array( 
	'class' => 'form', 'name' => 'spare3' ), kohana::lang('boardmessage.structure') ); ?>
<?php echo form::dropdown( 
	array( 
		'id' => 'structure',
		'name' => 'spare3', 
		'title' => kohana::lang('boardmessage.structurehelper') ),		
		$charstructures,
		$form['spare3']); ?>			
<?php if (!empty ($errors['spare3'])) echo "<div class='error_msg'>".$errors['spare3']."</div>";?>
<br/>

<?php echo form::label( array( 
	'class' => 'form', 'name' => 'spare1' ), kohana::lang('boardmessage.workduration') ); ?>
<?php echo form::input( array( 'id' => 'spare1', 'name' => 'spare1', 
'value' => $form['spare1'], 
'maxlength' => '50', 'title' => kohana::lang('boardmessage.workdurationhelper'), 'style' => 'width:100px' ) )?>		
<?php if (!empty ($errors['spare1'])) echo "<div class='error_msg'>".$errors['spare1']."</div>";?>
<br/>

<div id='hourlywage'>

<?php echo form::label( array( 
	'class' => 'form', 'name' => 'spare4' ), kohana::lang('boardmessage.hourlywage') ); ?>
<?php echo form::input( array( 'name' => 'spare4', 
'value' => $form['spare4'], 
'maxlength' => '5', 'title' => kohana::lang('boardmessage.hourlywagehelper'), 'style' => 'width:100px' ) )?>		
<?php if (!empty ($errors['spare4'])) echo "<div class='error_msg'>".$errors['spare4']."</div>";?>
<br/>
</div>

<?php echo form::label( array( 'class' => 'form', 'name' => 'message' ), kohana::lang('boardmessage.messagetext') ); ?>

<br/>

<?php	echo form::textarea( array( 'id' => 'message', 
'title' => kohana::lang('boardmessage.texthelper'),
'name' => 'message', 'value' => $form['message'], 'rows' => 10, 'cols' => 90 ) ); ?>

<?php if (!empty ($errors['message'])) echo "<div class='error_msg'>".$errors['message']."</div>";?>
<br/>
<?php echo form::label( array( 
'class' => 'form', 'name' => 'validity' ), kohana::lang('boardmessage.messagevalidity1') ); ?>
<?php echo form::input( array( 'id' => 'validity', 
'title' => kohana::lang('boardmessage.validityhelper'),
'name' => 'validity', 
'value' => $form['validity'],'maxlength' => '2', 'style' => 'width:30px;text-align:right' ) )?>
<?php if (!empty ($errors['validity'])) echo "<div class='error_msg'>".$errors['validity']."</div>";?>

<br/>
<center>
<?php echo form::submit( array( 'id' => 'submit', 'class' => 'button button-small', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.add')));?>
</center>
<?php echo form::close() ?>

<br style="clear:both;" />
