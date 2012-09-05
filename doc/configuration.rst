Configuración Inicial
=====================

Esta librería permite comunicarnos con una ó varias bases de datos en una aplicación mediante una capa de abstracción de Base de datos, pero para lograr ello debemos indicarle a la misma cual ó cuales son las configuraciones de conexión de la base de datos.

Para configurar una ó varias conexiones a Base de Datos debemos hacer uso de dos clases principales, una es la clase Parameters y la otra la clase Config.

La clase Parameters
-------------------

Esta clase será la que contenga los valores de configuración a una base de datos en especifico, dichos valores pueden ser establicidos en el constructor de la clase al instanciarla, ó con el uso de métodos setters, veamos un ejemplo:

::

  <?php

  use ActiveRecord\Config\Parameters;

  //en el siguiente ejemplo crearemos la configuración en el constructor de la clase:
  
  $mysql = new Parameters("config_mysql", array(
              'username' => "root"
              "password" => "123456"
              "host"     => "localhost"
              "type"     => "mysql"
              "name"     => "mi_base_de_datos"
  ));

  //ahora configuraremos otra conexión, pero a traves de los métodos setters de la clase:

  