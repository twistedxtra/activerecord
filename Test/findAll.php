<?php

require 'config.php';

use ActiveRecord\Model;

class Usuarios extends Model
{
    
}

//creamos el objeto user
$user = new Usuarios();

//consultamos todos los registros
$res = $user->findAll();
echo "<h3>findAll </h3>";
foreach($res as $e){
    var_dump($e);
}

///////////////////////////////// devolver como Arrays
$res = $user->findAll('array');

echo "<h3>findAll como array</h3>";
foreach($res as $e){
    var_dump($e);
}

///////////////////////////////// devolver como stdClass
$res = $user->findAll('obj');

echo "<h3>findAll como objetos stdClass</h3>";
foreach($res as $e){
    var_dump($e);
}