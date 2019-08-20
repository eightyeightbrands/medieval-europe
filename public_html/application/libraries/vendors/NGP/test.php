<?php

include "./NGP.php";

$t = new NGP();
$name = $t -> generate_name( 'Turkish', 'M' );
echo $name['name'] . ' ' . $name['surname'] ; 

?>