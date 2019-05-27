<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title></title>
    </head>
    <body>
        <h1>Ejercicio 4</h1>
    </body>
</html>
<?php
// Ejercicio 4. Escribe un programa que multiplique los 20 primeros números
// naturales.
$contador  = 2;
$resultado = 1;
while ($contador <= 20) {
    // $resultado = $contador * $resultado;
    $resultado *= $contador;
    echo $contador . "<br />";
    echo "resultado: " . $resultado . "<br />";
    $contador++;
}
echo $resultado . "<br />";
