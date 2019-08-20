<div id="footer">(C) Copyright Medieval Europe 2011 - <?= date('Y', time()) ?>
<div id='languages'>
<?php
	echo html::anchor('/character/change_language/it_IT', html::image(array('src' => 'media/images/flags-lang/it.png'), array('title' => 'Italian', 'class' => 'image-flag')))
	. '&nbsp;' .
	html::anchor('/character/change_language/en_US', html::image(array('src' => 'media/images/flags-lang/gb.png'), array('title' => 'English', 'class' => 'image-flag')))
	. '&nbsp;' .
/*
	html::anchor('/character/change_language/ro_RO', html::image(array('src' => 'media/images/flags-lang/ro.png'), array('title' => 'Romanian', 'class' => 'image-flag')))
	. '&nbsp;' .
	*/
	html::anchor('/character/change_language/tr_TR', html::image(array('src' => 'media/images/flags-lang/tr.png'),
			array('title' => 'Turkish', 'class' => 'image-flag')))
	. '&nbsp;' .
	html::anchor('/character/change_language/hu_HU', html::image(array('src' => 'media/images/flags-lang/hu.png'),
			array('title' => 'Hun', 'class' => 'image-flag')))
	. '&nbsp;' .
	html::anchor('character/change_language/cz_CZ', html::image(array('src' => 'media/images/flags-lang/cz.png'), array('title' => 'Czech', 'class' => 'image-flag', 'alt' => 'Czech flag')))
	. '&nbsp;' .
	html::anchor('/character/change_language/de_DE', html::image(array('src' => 'media/images/flags-lang/de.png'), array('title' => 'Deutsch', 'class' => 'image-flag')))
	. '&nbsp;' .
	html::anchor('/character/change_language/pt_PT', html::image(array('src' => 'media/images/flags-lang/pt.png'), array('title' => 'Portuguese', 'class' => 'image-flag')))
	. '&nbsp;' .
	html::anchor('/character/change_language/ru_RU', html::image(array('src' => 'media/images/flags-lang/ru.png'), array('title' => 'Russian', 'class' => 'image-flag')))
	. '&nbsp;' .
	html::anchor('/character/change_language/bg_BG', html::image(array('src' => 'media/images/flags-lang/bg.png'), array('title' => 'Bulgarian', 'class' => 'image-flag')))
	. '&nbsp;' .
	html::anchor('/character/change_language/fr_FR', html::image(array('src' => 'media/images/flags-lang/fr.png'), array('title' => 'French', 'class' => 'image-flag')))
	. '&nbsp;' .
	html::anchor('/character/change_language/gr_GR', html::image(array('src' => 'media/images/flags-lang/gr.png'), array('title' => 'Greek', 'class' => 'image-flag')))
	. '&nbsp;' .
	html::anchor('character/change_language/cz_CZ', html::image(array('src' => 'media/images/flags-lang/sk.png'), array('title' => 'Slovak', 'class' => 'image-flag', 'alt' => 'Slovacchia flag')));
?>
</div>
</div>
</body>
</html>
