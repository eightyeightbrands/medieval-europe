<script type="text/javascript">var switchTo5x=true;</script>
<script type="text/javascript" src="https://ws.sharethis.com/button/buttons.js"></script>
<script type="text/javascript">stLight.options({publisher: "05b876cb-366e-4b3c-9544-86c7ec5c2cbe", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>

<meta property="og:url" content="https://medieval-europe.eu/index.php/page/readnews/<?=$message->id;?>"/>
<meta property="og:title" content="<?=$message->summary?>"/>
<meta property="og:image" content="https://i.imgur.com/jxjeTbI.jpg"/>
<meta property="fb:app_id" content="1448064485408533"/>

<h1>
<?php echo Utility_Model::bbcode($message -> summary) ?>
</h1>

<p>
<?php echo Utility_Model::bbcode($message -> message) ?>
</p>

<div>
<span class='st_facebook_large' displayText='Facebook'></span>
<span class='st_googleplus_large' displayText='Google +'></span>
<span class='st_sharethis_large' displayText='ShareThis'></span>
<span class='st_twitter_large' displayText='Tweet'></span>
<span class='st_email_large' displayText='Email'></span>
</div>
