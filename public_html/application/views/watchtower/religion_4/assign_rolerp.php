<head>
<script>

$(document).ready(function()
{	
	$("#nominated1").autocomplete( {
			source: "index.php/jqcallback/listallchars",
			minLength: 2
		});	
});
</script>
</head>
 
<div class="pagetitle"><?php echo kohana::lang('structures_royalpalace.rolesandtitles_pagetitle') ?></div>

<?php echo $submenu ?>

<div class='submenu'>
<?php echo html::anchor('/religion_4/assign_rolerp/' . $structure -> id, kohana::lang('structures_royalpalace.assignrolerp')); ?>
&nbsp;&nbsp;
<?php echo html::anchor('/structure/list_roletitles/' . $structure -> id, kohana::lang('structures_royalpalace.listroletitles')); ?>
</div>

<?php 

	echo html::image(array('src' => 'media/images/template/hruler.png', 'style'=>'margin:15px 0'));
	
	// Immagine
	echo html::image
	(
		'media/images/other/royalpalace-gdr1.jpg',
		array
		(
			'align' => 'right',
			'style' => 'border:1px solid Peru; padding:3px; margin-right:20px; float:left; width:22%;'
		)
	);
	
	// Descrizione
	echo '<div id="helper">';
	echo kohana::lang('structures_religion_4.rolesandtitles_rolehelper');
	echo '</div>';

	// Apertura form assegnazione ruolo
	echo form::open();

		echo form::hidden('region_id', $structure->region->id );
		echo form::hidden('structure_id', $structure->id );
 
		// Nominativo personaggio
		echo '<span style="margin-right:5px">'.kohana::lang('structures_royalpalace.appoint_text1').'</span>';
		echo form::input( array( 'id'=>'nominated1', 'name' => 'nominated', 'value' =>  $formroles['nominated'], 'style'=>'width:300px' ) );
		
		echo '<br />';

		// Ruolo da assegnare
		echo '<span style="margin-right:5px">'.kohana::lang('structures_royalpalace.appoint_text2').'</span>';
		echo form::dropdown('role', $roles, $formroles['role']);
		
		echo '<br />';
		
		echo form::hidden('description', null );
		
		echo '<br />';
	
		// Submit
		echo form::submit
		(
			array
			(
				'id' => 'submit', 
				'class' => 'button button-medium' , 			
				'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')'
			), 
			kohana::lang('global.appoint')
		);
	// Chiusura form
	echo form::close(); 
?>

<br/>
