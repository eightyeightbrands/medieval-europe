<head>
<style>
body 
{ 
	background-color: #000; 
	color: #fff;
	font:16px Tahoma, Verdana, Arial;
}

a, a:visited
{
	color: yellow;
}
</style>
</head>
<body>

<div style='margin:30 auto;text-align:center'>
	<?= html::image( 'media/images/template/MedievalEuropeLogo.png'); ?>
</div>

<p style='text-align:center;width:50%;margin:0 auto'>
<?php echo kohana::lang('global.notauthorizedpage'); ?>
<br/><br/>
<?php echo html::anchor( '/', 'Medieval Europe' ); ?>
</p>

