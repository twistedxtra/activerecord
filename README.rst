ActiveRecord 2
==============

Nuevo Active Record para la nueva versión de KumbiaPHP que trabaja con php 5.3 ó superior.

Aunque es una librería realizada con la finalidad de ofrecer una capa de abstraccion a base de datos para el framework KumbiaPHP, esta nueva versión puede ser usada en cualquier proyecto php.

Actualmente no tiene su propio autoloader, por lo que para usarlo se debe registrar la ruta hacie lib en cualquier autoloader que cumpla las especificaciones PSR-0.

Configuración:
--------------

Configuración de la conexión a la base de datos:

::

    <?php

    use ActiveRecord\Config\Config;
    use ActiveRecord\Config\Parameters;

    Config::add(new Parameters("default",array(
            'username' => 'root',
            'password' => 'contrseña',
            'host' => 'localhost', //por defecto localhost
            'type' => 'mysql',
            'port' => '3306', //si no se especifica se usa el puerto por defecto del gestor de base de datos usado.
            'name' => 'nombre_base_de_datos',
    )));

Con estos sencillos pasos ya tenemos configurada nuestra conexión a la base de datos.