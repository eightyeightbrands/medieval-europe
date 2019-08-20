<div class='pagetitle'>
	<?php echo kohana::lang('global.propertylicenseno' ) . ': ' .  $bodycontent['contract_id']  ?>
</div>

<?php echo $submenu ?>

<div id='messageboardcontainertop_normal'></div>

<div id= 'messageboardcontainer_normal'>

	<div style='padding:10px'>

		<center><h3><?php echo  kohana::lang( $item -> cfgitem -> name ) ?></h3></center>

		<br/>

		<p style="margin-top: 15px">
		
			<?php echo  kohana::lang( $item -> cfgitem -> name ) .' N. '. $bodycontent['contract_id']?>

			<br/><br/>	

			<?php echo kohana::lang('structures_shop.property_license_text'
				, kohana::lang( $bodycontent['regionname'])
				, Utility_Model::format_datetime( $bodycontent['contract_date'] )	
				, kohana::lang( $bodycontent['shoptype'])
				, kohana::lang( $bodycontent['regionname'])
				, kohana::lang( $bodycontent['kingdomname'])
				, kohana::lang( $bodycontent['regionname'])
				, kohana::lang( $bodycontent['kingdomname']) );
			?>	

		</p>
		
	</div>

</div>


<div id='messageboardcontainerbottom_normal'></div>	

<br style="clear:both;" />
