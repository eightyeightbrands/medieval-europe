<html>
<head>
<style>
body 
{ 
	background-color: #333; 
	color: #eee;
	font:12px Tahoma, Verdana, Arial;

}

#message
{
width:500px;
height:152px;
margin: 200px auto;
text-align:center;
vertical-align:middle;
border:1px solid #999;
background: #fff;
color: #000;
}

p
{
margin-top: 40px;
margin-left: 160px;
margin-right: 10px;
}
</style>
</head>
<body>
<center>
<div id='message'>
<?php echo html::image('media/images/other/logome.png', array( 'style' => 'float:left') ) ?>
<p>
<?php echo $content ?>
<br/><br/><br/>
</p>
</div>
</center>
</body>
</html>
