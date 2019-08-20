<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/><br/>
<div class="ubercolortabs">
<ul>
<?php
	foreach ($lnkmenu as $link => $params)
	{
		//print_r ($params ); exit(); 
		if ( is_array ( $params ) )
		{ echo '<li>' . html::anchor($link, '<span>' . $params['name'] . '</span>', $params['htmlparams'] ) . '</li>' ;  }
		else
		{ echo '<li>' . html::anchor($link, '<span>' . $params . '</span>' ) .  '</li>' ;  }
	}
?>
</ul>
</div>

<div class="ubercolordivider"> </div>

<div id='helper'>
	<div id='helpertext'>
		<?php echo $helpertext; ?>
	</div>
	<div id='wikisection'>
		<?php echo $wikiurl ?>
	</div>
	<div style='clear:both'></div>
</div>

<br/>
