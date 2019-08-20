<div class="pagetitle">
<?php echo $character->name . '&nbsp;-&nbsp;' . kohana::lang('page.public_profile') ?>
</div>

<div class='center'><?= $character -> slogan;?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='badges' class='right'>

<!-- badges -->

<?php
	
	if ($character -> is_dead() == false ) 
	{
	
		
		// badge profilo
		
			echo html::anchor('character/publicprofile/' . $character -> id, 
			html::image('media/images/badges/character/badge_profile.png', 
			array( 'title' => kohana::lang('character.profile'))),
			array('escape' => false));
		
		// badge biografia
		
		echo html::anchor('character/history/' . $character -> id, 
			html::image('media/images/badges/character/badge_history.png', 
			array( 'title' => kohana::lang('character.char_history'))),
			array('escape' => false));
		
		// badge converti
		if (
			$character -> church -> name == 'nochurch' 
			and 
			(!is_null($viewingcharrole) and $viewingcharrole -> tag == 'church_level_4')
		)
			echo html::anchor('character/initiate/' . $character -> id, 
			html::image('media/images/badges/character/badge_initiate.png', 
			array( 'title' => kohana::lang('character.initiate'))),
			array('escape' => false));
			; 
		
		// badge duello
		
		echo html::anchor(
			'character/launchduel/' . $character -> id, 
			html::image('media/images/badges/character/badge_duel.png', 
				array( 'title' => kohana::lang('character.launchduel'))),
			array(
				'escape' => false,
				'target' => 'new' )
		);
		
		// badge email
		
		echo html::anchor('message/write/0/new/' . $character -> id,
			html::image('media/images/badges/character/badge_write.png', 
			array( 'title' => kohana::lang('message.write_scroll'))),
				array(
				'escape' => false,
				'target' => 'new' )
		); 
		
		// malattie
		
		if (
			$viewingchar -> position_id == $character -> position_id and					
			(
				$viewingchar -> has_religious_role() == true 
				or 
				( $viewingchar -> get_attribute('intel') >= 18 )
				or 
				($viewingchar -> id == $character -> id )
			)
		)
		{
			// Cura salute
		
			if ( $character -> health < 100 )
			{
				$title = kohana::lang('character.recovering_diseasebadgedesc') . 
					"<br/><br/>" .		
					html::anchor(
						'character/cure/health/' . $character -> id, 
						kohana::lang('charactions.curehealth'),
						array('class' => 'st_common_command'));
					
				echo html::image('media/images/badges/diseases/' . 
					'recovering.png',		
					array( 'title' => $title)); 		
			}
			
			
		
			// diseases
			
			foreach ( (array) $diseases as $disease )
			{
				$dinstance = DiseaseFactory_Model::createDisease( $disease -> param1 );						
				
				$title = kohana::lang('character.' . $disease -> param1 . '_diseasebadgedesc');
				if ( $dinstance -> get_iscurable() == true )
					$title .= "<br/><br/>" .		
					html::anchor(
						'character/cure/disease/' . $character -> id .'/' . $disease -> param1,
						kohana::lang('charactions.curedisease'),
						array('class' => 'st_common_command'));
							
				echo html::image('media/images/badges/diseases/' . 
					$disease -> param1 . '.png',		
					array( 'title' => $title )); 		
				
			}
		
			echo "&nbsp;&nbsp;&nbsp;";
		}
		
		// badge admin
		
		if ( 
			Character_Model::has_merole( $character, 'admin' ) 
			or
			Character_Model::has_merole( $character, 'staff' ) 
		)
			echo html::image(
			'media/images/badges/character/badge_admin.png', 
			array( 'title' => kohana::lang('admin.adminaccount'))); 
		
		// badge tutor
		
		if ( Character_Model::has_merole( $character, 'newborntutor' ) )
			echo html::image(
			'media/images/badges/character/badge_newborntutor.png', 
			array( 'title' => kohana::lang('admin.newborntutor'))); 
		
		// badge adr				
		
		if ( 
			Character_Model::has_merole( $character, 'doubloonreseller' ) 			
		)
			echo html::image(
			'media/images/badges/character/badge_adr.png', 
			array( 'title' => kohana::lang('admin.adr'))); 
				
		// badge chiesa
		
		if ( $character -> church -> name == 'nochurch' )
			$title = kohana::lang('religion.religionatheistdescription', $character -> name );
		else
			$title = kohana::lang('religion.religionfollowerdescription', 
				$character -> name, 				
				kohana::lang( 'religion.church-'.  $character -> church -> name),
				kohana::lang( 'religion.religion-' . $character -> church -> religion -> name ));
		
		echo html::image('media/images/badges/religionsymbols/symbol_'. $character -> church -> name . '.png', array( 'title' => $title )); 
		
	}
	?>	
	
</div>
