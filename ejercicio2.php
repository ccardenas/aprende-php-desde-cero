<html>

<head>
    <title>Ejercicio 2</title>
</head>

<body>
    <h1>Ejercicio 2</h1>
    <p>Ejercicio 2. Escribe un programa que imprima por pantalla los cuadrados (el
        número multiplicado por sí mismo) de los 30 primeros números naturales.</p>
</body>

</html>
<?php
// Ejercicio 2. Escribe un programa que imprima por pantalla los cuadrados (el
// número multiplicado por sí mismo) de los 30 primeros números naturales.
for ($i = 1; $i <= 30; $i++) {
    $temp = $i * $i;
    echo 'El cuadrado de ' . $i . ' es: ' . $temp . '<br />';
}

$array = ['perro', 'gato'];
