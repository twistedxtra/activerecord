Introducción
============
En esta sección a nivel de introducción explicaremos porqué debería usarse un ORM como ActiveRecord
y las ventajas que representa este para una aplicación profesional en PHP.

¿Qué es?
--------
ActiveRecord es un componente que implementa el patrón ORM (Object Relational-Mapping), gracias a este,
podemos trabajar con bases de datos de manera orientada a objetos, encapsulando muchos detalles de bajo nivel,
facilitando la manutención del código y haciendo las aplicaciones menos propensas a errores.

¿Por qué usar ActiveRecord?
---------------------------

En el pasado era normal usar código como el siguiente para acceder a bases de datos:

.. code-block:: php

	<?php

	mysql_query("SELECT * FROM usuarios LIMIT 10");
	while ($usuario = mysql_fetch_array()) {
		echo $usuario['nombre'];
	}

Y si queriamos actualizar los registros consultados haciamos algo como esto:

.. code-block:: php

	<?php

	mysql_query("SELECT * FROM usuarios LIMIT 10");
	while ($usuario = mysql_fetch_array()) {
		mysql_query("UPDATE usuarios SET fecha = '".date('Y-m-d').'" WHERE id = '.$usuario['id']);
	}

Esta forma de trabajar representa muchas desventajas para nuestra aplicación:

* Usamos funciones que están atadas a un motor especifico (MySQL en este caso) haciendo dificil usar la aplicación en otros motores
* Escribir SQL nos lleva a usar extensiones que puede que no sean compatibles en otros motores (por ejemplo LIMIT)
* Escribir SQL requiere de cuidado pues al olvidar una simple comilla podriamos hacer fallar la aplicación
* Si nuestra aplicación es orientada a objetos estaríamos mezclandola con programación estructurada

