<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Ejercicio 6</title>
  </head>
  <body>
    <h1>Ejercicio 6</h1>
    <p>Crear un array llamado meses y que almacene el nombre de los doce meses del año.
      Recorrerlo con FOR para mostrar por pantalla los doce meses</p>
  </body>
</html>
<?php
//doce meses del año
$meses = array(
    "Enero",
    "Febrero",
    "Marzo",
    "Abril",
    "Mayo",
    "Junio",
    "Julio",
    "Agosto",
    "Septiembre",
    "Noviembre",
    "Diciembre"
);

//FOR recorre el array meses
for ($i = 0; $i < count($meses); $i++) {
    echo "- " . $meses[$i] . "</br>";
}
?>