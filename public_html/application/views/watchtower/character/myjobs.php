<div class="pagetitle"><?php echo kohana::lang('character.myjobs_pagetitle') ?></div>


<?php 
echo $submenu;
?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<div id='helper'>
  <div id='helpertext'>
		<?php echo kohana::lang('character.myjobs_helper');?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=Jobs',
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button' ) ) 
		?>
	</div>
	<div style='clear:both'></div>
</div>

<br/>

<?php

if ( count( $jobs ) == 0) 
	echo "<p class='center' >".kohana::lang('character.nojobcontracts') . "</p>";
else { ?>

	<?php echo $pagination -> render(); ?>
	<br/>
	<table class='grid'>
	<th class='center' width='5%' ><?php echo kohana::lang('global.id') ?></th>
	<th class='center' width='15%' ><?php echo kohana::lang('global.employer') ?></th>
	<th class='center' width='15%' ><?php echo kohana::lang('global.employee') ?></th>
	<th class='center' width='15%' ><?php echo kohana::lang('global.structure') ?></th>
	<th class='center' width='10%' ><?php echo kohana::lang('global.status') ?></th>
	<th class='center' width='15%' ><?php echo kohana::lang('boardmessage.hourlywage') ?></th>
	<th class='center' width='15%' ><?php echo kohana::lang('global.description') ?></th>
	<th class='center' width='25%' ><?php echo kohana::lang('global.expires') ?></th>
	<th class='center' width='15%' ></th>
	
	<?php
	$k = 0;
	foreach ( $jobs as $job )
	{	
		if ( $job -> expiredate < time() )
			$status = kohana::lang('global.status_expired' );
		else
			$status = kohana::lang( 'global.status_' . $job -> status );
			
		$employer = ORM::factory('character', $job -> employer_id );
		if ( !is_null( $job -> structure_id ) ) 
			$structure = StructureFactory_Model::create( null, $job -> structure_id );
		else
			$structure = null;
			
		$class = ($k % 2 == 0) ? '' : 'alternaterow_1' ; 
		echo "<tr class='$class'>";
		echo "<td class='center'>" . $job -> id  . "</td>";
		echo "<td class='center'>" . Character_Model::create_publicprofilelink( $job -> employer_id, $job -> employer )  . "</td>";
		echo "<td class='center'>" . Character_Model::create_publicprofilelink( $job -> character_id, $job -> employee )  . "</td>";
			
		echo "<td class='center'>" ; 
			if ( is_null( $structure ) )
				echo '-';
			else
				echo 	kohana::lang( $structure -> structure_type -> name ) . ' (' .kohana::lang( $structure -> region -> name ) . ')';
		echo "</td>";
		
		echo "<td class='center'>" . $status . "</td>";
		echo "<td class='center'>" . $job -> hourlywage  . "</td>";
		
		echo "<td class='center'>" . html::anchor('boardmessage/view/' . $job -> boardmessage_id, $job -> boardmessage_title, array( 'target' => 'blank' ) ) . "</td>";
		echo "<td class='center'>" . Utility_Model::format_date( $job -> expiredate ) . "</td>"; 
		echo "<td class='center'>" ;
		
		if ( $job -> employer_id != $character -> id ) 
			echo '-' ;
		
		// Solo l' employer può chiudere i contratti
		if ( 
			$job -> status == 'active' and 
			$job -> expiredate > time() and
			$job -> employer_id == $character -> id ) 
		{
			echo html::anchor( '/jobs/close/' . $job -> id, '[' . kohana::lang('global.close') . ']',
				array( 
					'title' => kohana::lang('jobs.close_helper' ), 
					'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')'			
			))  .	'<br/>';
		}
		else
			echo '-' ;
		// rimozione della funzione republish
		/*
		if ( $job -> status == 'canceled' and $job -> employer_id == $character -> id )
		{
			echo html::anchor( '/jobs/republish/' . $job -> id, '[' . kohana::lang('global.republish') . ']',
				array( 
					'title' => kohana::lang('jobs.republish_helper' ), 
					'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')'			
			))  .	'<br/>';
		}
		*/
		
		echo "</td>"; 
		echo "</tr>";
		$k++;
	}
	?>
	</table>
	<br/>
	<?php echo $pagination -> render(); ?>


<?php } ?>

<br style="clear:both;" />
