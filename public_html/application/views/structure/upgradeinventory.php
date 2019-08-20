<div class="pagetitle"><?php echo kohana::lang('structures.upgradestructure_titlepage')?></div>


<?php echo $submenu?>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>

<div id='helper'>
  <div id='helpertext'>
		<?php  echo kohana::lang('structures.upgradeinventory_helper'); ?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=Workshops#Enlarge_Shop',
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button' ) ) 
		?>
	</div>
	<div style='clear:both'></div>
</div>
<br/><br/>

<?php echo form::open();?>
<p class='center'>
<?php echo form::hidden( 'structure_id', $structure -> id ) ; ?>
<?php echo kohana::lang( 'structures.currentinventorycapacity_1', $structure -> getStorage() / 1000 ); ?>

<center>
<?php 
echo form::submit( array (
			'id' => 'submit', 
			'class' => 'submit',			
			'name' => 'upgradeinventory', 
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('structures.upgradeinventory'));
?>
<?php echo form::close(); ?>
</center>
</p>

<br style= 'clear:both'/>


