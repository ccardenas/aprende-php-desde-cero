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

$sql = "
        SELECT
        DISTINCT c.id AS CompradorId,
        c.name AS Comprador,
        COUNT(
            DISTINCT (pb.id)
        ) AS NumeroLicitaciones,
        pb.status AS status,
        MONTH(pb.assignedAt) AS Mes,
        YEAR(pb.assignedAt) AS Ano
    FROM
        (
            SELECT
                id,
                publishedBy_id,
                assignedAt,
                status,
                user_id
            FROM
                PurchaseBid
            WHERE
                purchase_type = 'bid'
                AND status IN ('finished', 'deserted')
        ) AS pb
        INNER JOIN User AS u ON (pb.user_id = u.id)
        INNER JOIN (
            SELECT
                id,
                name
            FROM
                Company
            WHERE
                id NOT IN (
                    1, 3, 4, 5, 22, 33, 43, 68, 85, 284, 395,
                    669
                )
                AND type = 1
        ) AS c ON (u.company_id = c.id)
    GROUP BY
        c.id,
        MONTH(pb.assignedAt),
        YEAR(pb.assignedAt),
        pb.status
    ORDER BY
        c.name ASC,
        YEAR(pb.assignedAt) ASC,
        MONTH(pb.assignedAt) ASC,
        pb.status ASC";

$stmt = $pdo->query($sql);
$stmt->execute();
$purchaseBid = $stmt->fetchAll();

$matrix = [];

foreach ($purchaseBid as $row) {
    $matrix
        [$row['Comprador']]
        [$row['Ano'] . str_pad($row['Mes'], 2, '0', STR_PAD_LEFT)]
        [$row['status']] = $row['NumeroLicitaciones'];
}

// initial array for sum of totals
$arrTotalPbByStatusAQ = [];
$arrTotalPbByStatusGeneral = [
    "saved" => 0,
    "published" => 0,
    "closed" => 0,
    "finished" => 0,
    "deserted" => 0,
];

$companyArray = [];
foreach ($matrix as $company => $data) {
    $companyArray[] = $data;
}
print_r($matrix);
