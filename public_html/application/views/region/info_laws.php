<div class='pagetitle'><?php echo Kohana::lang('regionview.infolaws_pagetitle', kohana::lang($region -> kingdom -> get_name() ) )?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<?php echo $submenu ?>

<?php
	
	if ( $laws->count() == 0 ) 
		echo kohana::lang('structures.region_nolawsfound');
	else
	{
	
?>
	<div class='pagination'><?php echo $pagination->render('extended') ?> </div>
	<br/>
	
<?php 	
	foreach ( $laws as $law )
	{
?>
	
	<div id='messageboardcontainertop_normal'></div>	
	<div id='messageboardcontainer_normal'>
		<div style='padding:10px'>
			<h5><?php echo $law->name ?></h5>				
			<div style='margin-top:5px;padding:0px 10px'>		
				<?php echo html::image(array('src' => 'media/images/template/hruler.png'));?>
				<br/><br/>
				<p>
					<?php echo Utility_Model::bbcode( $law -> description); ?>
				</p>
			</div>
			<?php
				if ( !is_null( $law -> timestamp ) )
				{
					echo kohana::lang('global.lawcreatedon', Utility_Model::format_datetime( $law -> timestamp ));					
				}
				
				if ( $law -> signature != '' )
				{
					echo "<hr style='margin:5px 0px'/>";
					echo Utility_Model::bbcode( $law -> signature );
				}		
			?>		
		</div>
	</div>
	<div id='messageboardcontainerbottom_normal'></div>
	<br style='clear:both'/>
	
<?php } ?>

<div class='pagination'><?php echo $pagination->render('extended'); ?> </div>

<?php } ?>

<br style='clear:both'/>
