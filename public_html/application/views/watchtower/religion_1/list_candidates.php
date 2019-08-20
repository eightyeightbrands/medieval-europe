<div class="pagetitle">
<?php echo Kohana::lang("structures.holysee_listcandidatesarcidiocese"); ?><?php echo $arcidiocesename ?>
</div>

<?php
	echo '<center>';
	echo html::image(array('src' => 'media/images/religion/arcidiocese/'.$arcidioceseid.'.png'), array('class' => 'stemma_religion_diocese'));
	echo '<br/>';

	echo Kohana::lang("structures.holysee_selectcandidatearcidiocese") . '<br/>';
	echo form::open('holysee/appoint_cardinal/', array('class'=>'selectcandidate_form'));
	echo form::input( array( 'id' => 'charcandidate', 'name' => 'candidate', 'style' => 'width:200px'));
	echo form::hidden('arcidiocese_id',$arcidioceseid);

	echo form::submit( array (
		'id' => 'submit', 
		'class' => 'submit', 			
		'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.select'))."</td>";
		
	echo form::close();


	echo '</center>';
?>

