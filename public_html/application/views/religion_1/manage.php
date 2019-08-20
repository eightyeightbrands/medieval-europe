<div class="pagetitle"><?php echo kohana::lang('structures.' . $structure -> structure_type -> type . '_' . $structure -> structure_type -> church -> name ) . 
'-' . kohana::lang( $structure -> region -> name ) ?></div>

<?php echo $submenu ?>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<?= $section_religiousheader; ?>
<br/>
<?= $section_description; ?>
<br/>
<?= $section_informativemessage; ?>
<br/>
<?= $section_excommunicate; ?>
<br/>
<?= $section_transferpoints ?>
<br/>
<?= $section_loadpicture; ?>
<br style="clear:both;" />

