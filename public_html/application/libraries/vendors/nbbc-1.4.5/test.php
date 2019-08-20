<?php
    require_once("nbbc.php");
    $bbcode = new BBCode;
    print $bbcode->Parse("[i]Hello, World![/i] [img align=left width=150px height=180px]https://3.bp.blogspot.com/-MDFTmKWux6E/ToPamWWeJPI/AAAAAAAAAh8/HNjWbDc02oo/s1600/gatto-domestico-316725.jpg[/img] This is the magic of [b]BBCode[/b]!");
?>