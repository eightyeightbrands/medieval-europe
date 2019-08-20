<div id="dialog"></div>

<div id="regionelementcontainer">
		
	<!-- Region structures -->
	<? 
	//var_dump($structures);exit;
	foreach ($structures as $category => $data )
		foreach ( $data as $type_id => $structuredata )
			if ( $category != 'player' ) 
				
		{
				kohana::log('debug',"-> Displaying {$structuredata -> type}");
	?>
				<?= html::image(
					str_replace('jpg', 'png',
					'media/newlayout/images/regionview/'.$structuredata->image),
					array(
						'class' => 'structure',
						'id' => $structuredata -> type,
						'data-structureid' => $structuredata -> id							
					));
				?>
	<?	
		}
	?>
	
	<!-- houses plus shops -->
	<?php if ( $currentregion -> type != 'sea'  ) {	?> 
	<?= html::image(
			array(	
				'id' => 'shops',
				'title' => html::anchor('#', 'View Citizens Shops'),
				'src' => 'media/newlayout/images/regionview/shops2.png',
				'instance' => 'image-structure'
			 ));
	?>
	<?= html::image(
			array(	
				'id' => 'shops2',
				'title' => html::anchor('#', 'View Citizens Shops'),
				'src' => 'media/newlayout/images/regionview/shops.png',
				'instance' => 'image-structure'
			 ));
	?>
	<?= html::image(
			array(	
				'id' => 'houses2',
				'title' => html::anchor('region/privatestructures/house/' . $currentregion->id, 'View Citizens Houses'),
				'src' => 'media/newlayout/images/regionview/houses2.png',
				'instance' => 'image-structure'
			 ));
	?>
	<?= html::image(
			array(	
				'id' => 'houses3',
				'title' => html::anchor('region/privatestructures/house/' . $currentregion->id, 'View Citizens Houses'),
				'src' => 'media/newlayout/images/regionview/houses3.png',
				'instance' => 'image-structure'
			 ));
	?>
	<?= html::image(
			array(	
				'id' => 'terrains',
				'title' => html::anchor('region/privatestructures/terrain/' . $currentregion->id, 'View Citizens Terrains'),
				'src' => 'media/newlayout/images/regionview/terrains.png',
				'instance' => 'image-structure'
			 ));
	?>
	<?= html::image(
			array(	
				'id' => 'breedings',
				'title' => html::anchor('region/privatestructures/breedings/' . $currentregion->id, 'View Citizens Breedings'),
				'src' => 'media/newlayout/images/regionview/breedings.png',
				'instance' => 'image-structure'
			 ));
	?>

	<? } ?>
	
	
</div>
