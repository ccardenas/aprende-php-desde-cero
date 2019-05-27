<html>

<head>
    <title>Ejercicio 5</title>
</head>

<body>
    <h1>Ejercicio 5</h1>
    <p>
        Ejercicio 5: Imprimir por pantalla la tabla de multiplicar del número pasado en un
        parámetro GET por la URL.
    </p>
    <h2>Resultado: </h2>
</body>

</html>
<?php
if (isset($_GET['numero']) && is_numeric($_GET['numero'])) {
    $numero = $_GET['numero'];
} else {
    $numero = 5;
    echo "<p>numero por defecto $numero </p>";
}
var_dump($_GET);
for ($i = 1; $i <= 20; $i++) {
    $resultado = $numero * $i;
    echo $numero . ' * ' . $i . ' = ' . $resultado . '<br />';
}
