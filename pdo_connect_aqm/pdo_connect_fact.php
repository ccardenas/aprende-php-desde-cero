<?php

//Conexión AQmarket
$host = 'localhost';
$user = 'root';
$password = 'root';
$dbname = 'aqmarket';

//DNS
$dns = 'mysql:host=' . $host . ';dbname=' . $dbname;

//Create a PDO instance
$pdo = new PDO($dns, $user, $password);

$data = [
    'Lic-59-01',
    'Lic-63-01',
    'Lic-73-01',
    'Lic-73-01',
    'Lic-75-01',
    'Lic-79-01',
    'Lic-96-01',
    'Lic-96-01',
    'Lic-104-01'
];

$query = "SELECT 
            po.correlative AS correlative
        FROM
            PurchaseOrder po
            INNER JOIN User u ON u.id = po.emittedBy_id
            INNER JOIN Company c ON c.id = u.company_id
            INNER JOIN PurchaseOrderItem AS poi ON poi.purchaseOrder_id = po.id
        WHERE
            c.id = 1923
            AND po.status != 'final'
            AND poi.billStatus = 'billed'
        GROUP BY 
            po.id";

$stmt = $pdo->query($query);
$stmt->execute();
$ocsStmt = $stmt->fetchAll(\PDO::FETCH_ASSOC);

$ocs = [];
foreach ($ocsStmt as $row) {
    $ocs[] = $row['correlative'];
}

$count = 0;
if (in_array($data, $ocs)) {
    print_r($count);
}

print_r($count);
// print_r($data);
// print_r($ocs);