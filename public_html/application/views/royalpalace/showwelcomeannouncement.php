<div class="pagetitle"><?= $welcomeannouncement -> title;?></div>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<p> <?= Utility_Model::bbcode($welcomeannouncement -> text); ?> </p>

<br style='clear:both'/>
