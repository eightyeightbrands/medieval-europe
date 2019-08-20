<div class="pagetitle"><?php echo kohana::lang('structures_court.viewcrimeprocedure_pagetitle')?></div>

<?php echo $submenu ?>

<div id='helper'><?php echo kohana::lang('structures_court.viewcrimeprocedure_helper') ?></div>

<br/>


<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div class='submenu'>
<?php echo html::anchor('/court/listcrimeprocedures/' . $structure -> id, kohana::lang('structures_court.submenu_managecrimeprocedures') ); ?>
</div>

<br/>

<center>
<div id='messageboardcontainertop_normal'></div>
<div id='messageboardcontainer_normal'>
	
	<h3><?php echo kohana::lang('structures_court.crimeprocedure_header',	kohana::lang( $structure -> region -> name ) ) ?></h3>
	<br/>
	<br/>
	
	<div style='padding:0px 10px'>
		<p>
		<?php echo kohana::lang('structures_court.criminal') . ': ' . '<b>' . $criminal -> name . '</b>'; ?>
		<br/>
		<?php echo kohana::lang('structures_court.opendate') . ': ' . '<b>' . Utility_Model::format_datetime(
			$crimeprocedure -> issuedate ) . '</b>'; ?>
		<br/>
		<?php echo kohana::lang('global.status') . ': ' . '<b>' . kohana::lang('structures_court.status_' . $crimeprocedure -> status ). '</b>'; ?>
		<br/>
		<?php echo kohana::lang('structures_court.cancelreason') . ': ' . '<b>' . $crimeprocedure -> cancelreason . '</b>'; ?>
		<br/>
		<?php echo kohana::lang('structures_court.trialurl') . ': ' . '<b>' . html::anchor( $crimeprocedure -> trialurl, $crimeprocedure -> trialurl,
			array( 'target' => '_blank') ) . '</b>'; ?>
		</p>
		<p>
		<?php echo kohana::lang('structures_court.arrestedby') . ': ' . '<b>' . $sheriff -> name . '</b>'; ?>
		<br/>
		<?php echo kohana::lang('structures_court.imprisonment_hours_given') . ': ' . '<b>' . $crimeprocedure -> imprisonment_hours_given . '</b>'; ?>
		<br/>
		<?php 
		echo kohana::lang('structures_court.startprison') . ': ' . '<b>';
		if ( !is_null ($crimeprocedure -> imprisonment_start) )
			echo Utility_Model::format_datetime(	$crimeprocedure -> imprisonment_start ); 
		else
			echo '-';
		echo '</b>';
		; ?>
		<br/>
		<?php
		echo kohana::lang('structures_court.endprison') . ': ' . '<b>';
		if ( !is_null ($crimeprocedure -> imprisonment_start) )
			echo Utility_Model::format_datetime(	$crimeprocedure -> imprisonment_end ); 
		else
			echo '-';
		echo'</b>';
		?>
		<br/>
		<?php 
		$time = Utility_Model::secs2hms( $crimeprocedure -> imprisonment_end - $crimeprocedure -> imprisonment_start);
		echo kohana::lang('structures_court.jailtime') . ': ' . '<b>' . $time[3] . '</b>'; ?>
		<br/>
		<?php echo kohana::lang('structures_court.freereason') . ': ' . '<b>' . $crimeprocedure -> free_reason . '</b>'; ?>
		</p>
		<p>
		<?php echo kohana::lang('structures_court.crimesummary') . ':' ?>
		<br/>
		<i><?php echo $crimeprocedure -> text ?></i>
		</p>
		<br/>
		<br/>
	</div>
</div>
<div id='messageboardcontainerbottom_normal'></div>
<br style='clear:both'/>
	
</center>

<br style='clear:both'/>
