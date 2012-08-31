<?php

require 'config.php';

use ActiveRecord\Model;

class Usuarios extends Model
{
    
}

$user = new Usuarios();

$pg = $user->paginate(1, 3);

var_dump($pg);

foreach($pg->items as $e){
    var_dump($e);
}
