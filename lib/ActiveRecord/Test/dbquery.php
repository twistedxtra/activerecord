<?php

require 'config.php';

use ActiveRecord\Model;

class Usuarios extends Model
{
    
}

$user = new Usuarios();

$user->createQuery()->select('nombres,login')
        ->where('login like :valor OR nombres <> :nom')
        ->bindValue('valor', 'manuelj555')
        ->bindValue('nom', 'manuelj555');

$res = $user->findAll();

foreach ($res as $e) {
    var_dump($e);
}
