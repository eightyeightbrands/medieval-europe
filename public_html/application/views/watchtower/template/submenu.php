<div class="submenutabs">
<ul>
<?php
	foreach ($submenu as $link => $params)
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

