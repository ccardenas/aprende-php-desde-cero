<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Ejercicio 8</title>
    </head>

    <body>
        <h1>Ejercicio 8</h1>
        <p>Escribir un programa que calcule el factorial de cualquier número almacenado en una variable.</p>
        <p>El factorial de un número entero N es una operación matemática que consiste en multiplicar todos los factores
            <em>N = N x (N-1) x (N-2) x...x 1.</em>
        </p>
    </body>
</html>
<?php
if (isset($_GET['numero']) && is_numeric($_GET['numero'])) {
    $numero = $_GET['numero'];
    //Factorial de un numero
    for ($i = ($numero - 1); $i > 0; $i--) {
        $numero *= $i;
    }
    echo '<h1>Resultado del Factorial de ' . $_GET['numero'] . ' es: ' . $numero . '</h1>';
} else {
    echo '<h1>Debe ingresar un numero para operar</h1>';
}
?>