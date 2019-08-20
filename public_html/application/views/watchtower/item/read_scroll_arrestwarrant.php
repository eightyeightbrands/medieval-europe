<div class='pagetitle'><?php echo kohana::lang('structures_court.arrestwarrant_pagetitle' ) ?></div>

<?php echo $submenu ?>

<div class='parchment_header'>
	<div class='parchment_headercontent'><?php echo  kohana::lang( $item -> cfgitem -> name ) ?></div>
</div>

<div class='parchment_body'>
	<div class='parchment_bodycontent'>
	<?php echo kohana::lang('global.contractnumber') .': ' . $bodycontent['document_id'] ?>
	<br/>
	<?php echo kohana::lang('structures_court.procedurenumber') .': ' . $bodycontent['procedure_id'] ?>
	<br/><br/>
	<?php echo kohana::lang('structures_court.arrestwarrant_text'	
	, $bodycontent['location'] 
	, Utility_Model::format_datetime( $bodycontent['document_date'] )	
	, $bodycontent['targetname'] 
	, $bodycontent['text']
	, $bodycontent['trialurl']
	, $bodycontent['sourcename'] );
	?>	
	
	</div>
</div>

<div class='parchment_footer'>
	<div class='parchment_footercontent'></div>
</div>	



	

	
