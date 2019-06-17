<?php

$dsn = 'mysql:hosy=localhost;dbname=aqmarket';
$username = 'root';
$password = 'root';

try {
    // connect to mysql
    $con = new PDO($dsn, $username, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $ex) {
    echo 'Not Connected ' . $ex->getMessage();
}

$sql = "
	SELECT
		MAX(cv.date) AS currencyDate,
		cv.currency_id AS currencyId,
		cv.value AS currencyValue,
	    c.code AS currencyCode
	FROM
		CurrencyValue AS cv
	    INNER JOIN Currency AS c ON c.id = cv.currency_id
	WHERE
		cv.date >= DATE_FORMAT(SUBDATE(now(),1), '%%Y-%%m-%%d')
	GROUP BY cv.currency_id
";
// mysql select query
$stmt = $con->prepare($sql);
$stmt->execute();
$currencies = $stmt->fetchAll();

$result = [];

foreach ($currencies as $row) {
    $result[$row['currencyCode']] = $row['currencyValue'];
}

print_r($result);
