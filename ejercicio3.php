<!DOCTYPE html>
<html>
    <head>
        <title></title>
    </head>
    <body>
    </body>
</html>
<?php
// Ejercicio 3. Modifica el ejercicio anterior para que muestre al lado de cada
// cuadrado si es un número par o impar.
for ($i = 1; $i <= 30; $i++) {
    $cuadrado = $i * $i;
    if (($cuadrado % 2) == 0) {
        echo "El cuadrado de " . $i . " es: " . $cuadrado . " y este cuadrado es Par. <br />";
    } else {
        echo "El cuadrado de " . $i . " es: " . $cuadrado . " y este cuadrado es Impar. <br />";
    }
}
