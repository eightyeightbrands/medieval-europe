<div class="pagetitle"><?php echo kohana::lang("structures_trainingground.train");?></div>

<div id='helper'>
	<?php echo kohana::lang('structures_trainingground.train_helper'); ?>&nbsp; 
	<?php echo kohana::lang('taxes.appliablevalueaddedtax', $appliabletax ) ?>
</div>

<br/>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/><br/>

<?php
$ncourses=0;
foreach ( $courses_info as $course )
{
		  if ( $course['level'] < 21 )
			{
				$ncourses++;
				echo '<b>' . kohana::lang($course['info'] -> name) . '</b>' ;				
				echo '<p><i>' . kohana::lang($course['info'] -> description) . '</i></p>' ;
				echo '<p>' . kohana::lang('structures_trainingground.course_info', $course['level'], $course['studiedhours'],$course['neededhours']) . '</p>' ;
				echo '<br/>';
				echo '<table>';

	      for ( $i = 3; $i <= 9; $i+= 3 )
	      {
					echo '<tr>';
					echo form::open();
					echo '<td>';
					echo form::hidden('hours', $i);
					echo form::hidden('course', $course['info'] -> tag );
					echo form::hidden('structure_id', $structure -> id);
					echo kohana::lang('structures_trainingground.trainforcoins', $i, $course['price'], $course['slot_'. $i ] );
					echo '</td>';
					echo '<td>';
					echo form::submit( array (
						'id' => 'submit', 
							'class' => 'submit', 			
						'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('structures_trainingground.train')) ;		
					echo form::close();
					echo '</td>';
					echo '</tr>';
				}
				echo '</table>';
				echo '<br/>';
				echo '<hr/>';
				echo '<br/>';			
			}
			else
			{
				
				// Processa evento study
				$_par[0] = $course['info'] -> tag;
				GameEvent_Model::process_event( $character, 'study', $_par );	
		
			}
				
}
if ( $ncourses == 0 )
	echo '<i>' . kohana::lang('structures_trainingground.maxedallstats') . '</i>' ;
?>
