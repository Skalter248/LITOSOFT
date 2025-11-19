<?php
$password_simple = '12345'; // <-- Define una nueva contraseña simple
$hash_nuevo = password_hash($password_simple, PASSWORD_DEFAULT);
echo $hash_nuevo;
?>