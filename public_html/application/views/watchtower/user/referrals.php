<div class="pagetitle"><?php echo kohana::lang('character.referrals_pagetitle') ?></div>

<?php echo $submenu ?>

<div id='helper'>
	<div id='helpertext'>
		<?php echo kohana::lang('character.referral_helper');?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=Referrals',
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button' ) ) 
		?>
	</div>
	<div style='clear:both'></div>
</div>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>


<fieldset>
<legend><?php echo kohana::lang('character.referral_link') ?></legend>
<p class='center'>
<?php echo kohana::lang('character.referral_linkhelper') ?>
<div class='center evidence'><?php echo url::site( '?referreruser=' .  $user -> id, 'http') ?></div>
</p>

</fieldset>

<br/>

<fieldset>
<legend><?php echo kohana::lang('character.referral_badge') ?></legend>
<p class="top10 border1 center">
<img class='top10 bottom10' src='<?php echo  url::site('banner/display') .'/'. $char->id ?>'><br/>
<?= html::anchor(
	'https://wiki.medieval-europe.eu/index.php?title=Banners', 
	kohana::lang('character.marketingbanners'),
	array( 'target' => 'new')); ?>	
</p>

<?php echo kohana::lang('character.referral_badgewebembedcode') ?>
<p class="top10 border1 center">
<span class='evidence'>
<?php echo htmlentities("<a href='" . url::site( '?referreruser=' .  $user -> id, 'http') . "'>") . "<br/>" .
      htmlentities("<img src='" . url::site('banner/display', 'http').'/' . $char->id . "'></a>") ?>
<br/>
			
</span>
</p>

<?php echo kohana::lang('character.referral_badgeforumembedcode') ?>
<p class="top10 border1 center">
<span class='evidence'>
	<?php echo "[URL=" . url::site( '?referreruser=' .  $user -> id, 'http') . "]<br/>[IMG]" . url::site('banner/display', 'http').'/' . $char->id . "[/IMG][/URL]"?>
</span>
</p>
</fieldset>

<br/>

<fieldset>
<legend><?php echo kohana::lang('character.referral_list') ?></legend>
<?php
if ($referrals->count() == 0 )
{
	echo '<p class="top10 border1 center">';
	echo kohana::lang('character.noreferrals');
	echo '</p>';
}
else
{
?>
<br/>
<table>
<th width='30%' class="center"><?php echo kohana::lang('global.name') ?></th>
<th width='30%' class="center"><?php echo kohana::lang('global.age') ?></th>
<th width='10%' class="center"><?php echo kohana::lang('global.coins') ?></th>
<th width='10%' class="center"><?php echo kohana::lang('global.doubloons') ?></th>

<?php
$i = 0;
$totalcoins = 0;
$totaldoubloons = 0;
foreach ( $referrals as $r )
{	
	$totalcoins += $r -> coins;
	$totaldoubloons += $r -> doubloons;
	($i % 2 == 0) ? $class = 'alternaterow_1' : $class = 'alternaterow_2';	
?>
<tr class="<?php echo $class; ?>">
<td><?php echo html::anchor('character/publicprofile/' . $r->character_id, $r ->name ); ?></td>
<td class="center"><?php echo Utility_Model::secs2hmstostring(
	Character_Model::get_age_s($r->character_id, 'year')); ?></td>
<td class="right"><?php echo $r -> coins; ?></td>
<td class="right"><?php echo $r -> doubloons; ?></td>
</tr>	
<?php
	$i++;
}
?>

<tr>	
	<td colspan="3" class="right"><strong><?php echo number_format($totalcoins, 2); ?></strong></td>
	<td class="right"><strong><?php echo number_format($totaldoubloons); ?></strong></td>
</tr>	
</table>
<?php } ?>
</fieldset>

<br style="clear:both;" />
