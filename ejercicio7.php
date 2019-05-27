<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Ejercicio 7</title>
    </head>
    <body>
        <h1>Ejercicio 7</h1>
        <p>Igual que el anterior pero usando el bucle foreach</p>
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
    "Diciembre",
);
//foreach para recorrer el array meses
foreach ($meses as $mes) {
    echo "- " . $mes . "</br>";
}
?>