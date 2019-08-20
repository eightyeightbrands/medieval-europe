<div class='pagetitle'><?php echo Kohana::lang("bonus.getbitcoins") ?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/><br/>
<div id='submenu'>
<?= html::anchor(
	'/bonus/getdoubloons', 
	kohana::lang('bonus.backtobuydoubloons'),
	array(
		'class' => 'button' )
	); ?>
</div>

<br/>
<p>
<?= kohana::lang('bonus.getbitcoinshelper',
	html::anchor('https://www.coinbase.com/join/sunchaser', "CoinBase", 
		array('target' => 'new')),
	html::anchor('https://localbitcoins.com/?ch=657w', "Local Bitcoins", 
		array('target' => 'new')),	
	html::anchor('https://blockchain.info/wallet/#/', "Blockchain", 
		array('target' => 'new')),	
	html::anchor('https://bitbo.at/17DNma2vJWCno1f3PfG2zRzM7sWKeFAcQG', "BitBoat", 
		array('target' => 'new'))
); 
?>
</p>

<br/>

<div class='center'>
<h2>freebitco.in</h2>
<p>
<?= kohana::lang('bonus.freebitcoinhelper'); ?>
</p>
<?= 
	html::anchor("https://freebitco.in/?r=2471051&tag=me",
	html::image("https://static1.freebitco.in/banners/468x60-3.png"),
		array('target' => 'new',
					'escape' => false)
	); 
?>
</div>

<br/>

<div class='center'>
<h2>Moon Bitcoin</h2>
<p>
<?= kohana::lang('bonus.moonbitcoinhelper'); ?>
</p>
<?= 
	html::anchor("https://moonbit.co.in/?ref=332c3b1ecc7e",
	html::image("https://moonbit.co.in/img/468x60.gif?v2"),
		array('target' => 'new',
					'escape' => false)
	); 
?>
</div>

<br/>