<head>
<?php echo html::script('https://cdn.jquerytools.org/1.2.4/full/jquery.tools.min.js', FALSE) ?>
<script type="text/javascript">

$(document).ready(function() { 
$("img[rel]").overlay();
});
</script>
</head>
<div class="simple_overlay" id="pic1">
	<?php echo html::image('media/images/other/me_qs_1.jpg') ?>
</div>
<div class="simple_overlay" id="pic2">
	<?php echo html::image('media/images/other/me_qs_2.jpg') ?>
</div>
<div class="simple_overlay" id="pic3">
	<?php echo html::image('media/images/other/me_qs_3.jpg') ?>
</div>
<div class="simple_overlay" id="pic4">
	<?php echo html::image('media/images/other/me_qs_4.jpg') ?>
</div>
<div class="simple_overlay" id="pic5">
	<?php echo html::image('media/images/other/me_qs_5.jpg') ?>
</div>
<div class="simple_overlay" id="pic6">
	<?php echo html::image('media/images/other/me_qs_6.jpg') ?>
</div>
<div class="simple_overlay" id="pic7">
	<?php echo html::image('media/images/other/me_qs_7.jpg') ?>
</div>

<div id="pagetitle"><?php echo kohana::lang('help.title'); ?></div>
<div class='separator'></div>
<div id="submenu">
<ul>
<li><?php echo html::anchor( '/page/display/quickstart', kohana::lang('help.title'), array('class'=>'selected') )?></li>
<li><?php echo html::anchor( kohana::lang('help.wikiurl'), kohana::lang('help.wiki'), array('target'=>'new'))?></li>
</ul>
</div>


<br/>

<?php echo kohana::lang('help.par1') ?>

<div>

<p>
<?php echo kohana::lang('help.par2') ?>
</p>
<center>
<table>
<tr>
<td style='padding:4px;text-align:center'><?php echo 
html::image('media/images/other/me_qs_1.jpg', 
array(
'class' => 'thumbnail_small',
'rel' => '#pic1')) ?>
<br/>
<?php echo kohana::lang('help.picture_1');?>
</td>
<td style='padding:4px;text-align:center'><?php echo 
html::image('media/images/other/me_qs_2.jpg', 
array(
'class' => 'thumbnail_small',
'rel' => '#pic2')) ?>
<br/>
<?php echo kohana::lang('help.picture_2');?>
</td>
<td style='padding:4px;text-align:center'><?php echo 
html::image('media/images/other/me_qs_3.jpg', 
array(
'class' => 'thumbnail_small',
'rel' => '#pic3')) ?>
<br/>
<?php echo kohana::lang('help.picture_3');?>
</td>
<td style='padding:4px;text-align:center'><?php echo 
html::image('media/images/other/me_qs_4.jpg', 
array(
'class' => 'thumbnail_small',
'rel' => '#pic4')) ?>
<br/>
<?php echo kohana::lang('help.picture_4');?>
</td>
<td style='padding:4px;text-align:center'>
<td style='padding:4px;text-align:center'>
<?php echo 
html::image('media/images/other/me_qs_5.jpg', 
array(
'class' => 'thumbnail_small',
'rel' => '#pic5')) ?>
<br/>
<?php echo kohana::lang('help.picture_5');?>
</td>
</tr>
</table>
</div>
</center>

<br/>

<?php echo kohana::lang('help.par3') ?>
<br/><br/>
<table width='100%'><tr>
<td style='padding:4px;text-align:center'>
<?php echo 
html::image('media/images/other/me_qs_6.jpg', 
array(
'class' => 'thumbnail_small',
'rel' => '#pic6')) ?>
<br/>
<?php echo kohana::lang('help.picture_6');?>
</td>
<td style='padding:4px;text-align:center'><?php echo 
html::image('media/images/other/me_qs_7.jpg', 
array(
'class' => 'thumbnail_small',
'rel' => '#pic7')) ?>

<br/>
<?php echo kohana::lang('help.picture_7');?>
</td>


</tr></table>
<br/><br/>
<?php echo kohana::lang('help.par4') ?>
<br/><br/>
<?php echo kohana::lang('help.par5') ?>
