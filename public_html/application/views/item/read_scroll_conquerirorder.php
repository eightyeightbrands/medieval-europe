<?php echo $submenu ?>

<div class="separator">&nbsp;</div>

<div id='messageboardcontainertop_normal'></div>

<div id= 'messageboardcontainer_normal'>
	<div style='padding:10px'>
		
		<div class='center'><h2><?php echo  kohana::lang( $item -> cfgitem -> name ) ?></h2></div>
		
		<br/><br/>
		
		<?php echo kohana::lang('structures_royalpalace.conquerirorder_text'	
		, Utility_Model::format_datetime($bodycontent['expirydate'] - ( 7 * 24 * 3600 ) )
		, $bodycontent['captainname']
		, kohana::lang( $bodycontent['regionname'])
		, Utility_Model::format_datetime( $bodycontent['expirydate'] )
		, $bodycontent['notes']
		, Utility_Model::bbcode($bodycontent['kingsignature']));
		?>	
	</div>
</div>

<div id='messageboardcontainerbottom_normal'></div>	

<br style="clear:both;" />



	

	
