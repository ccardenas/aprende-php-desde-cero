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


$query = "
    SELECT 
        pi.id AS proposalItem_id,
        pi.totalPricePaid AS montoAdjudicado,
        pi.quantity AS cantidad,
        COUNT(poi.id) AS count_poi,
        SUM(poi.quantity) AS sum_quantity,
        prod.type AS productType
    FROM
        ProposalItem AS pi
        INNER JOIN PurchaseOrderItem AS poi ON poi.proposalItem_id = pi.id
        INNER JOIN RequestsFolder AS rf ON rf.id = pi.requestsFolder_id
        INNER JOIN ProductRequest AS pr ON pr.folder_id = rf.id
        INNER JOIN Product AS prod ON prod.id = pr.product_id
    WHERE
        pi.discardedForPo = 0
        AND pi.status IN ('accepted', 'parcial')
        AND billStatus IS NULL
    GROUP BY
        pi.id
    HAVING 
        count_poi > 1
        AND cantidad > sum_quantity";
// mysql select query
$stmt = $con->prepare($query);
$stmt->execute();
$dataQuery = $stmt->fetchAll();


//Datos
$uf = 27722.34;
//$amount = 200000000;
//$productType = 'B';
//Calculo comision
$topeServicio = $uf * 35;
$topeBien = $uf * 10;

//limites para bien
$limitPerItemGood = [
    [
        'commission' => 0.8 / 100,
        'limit' => 7.5 * $uf
    ],
    [
        'commission' => 0.1 / 100,
        'limit' => 10 * $uf
    ]
];

//limite por servicio
$limitPerItemService = [
    'commission' => 0.8 / 100,
    'limit' => 35 * $uf
];

//limite por licitación
$limitPerBid = 35 * $uf;

//foreach dataQuery
$result = [];
foreach ($dataQuery as $row) {
    //Monto a calcular
    $amount = (float) $row['montoAdjudicado'];

    //Calculo de comisión
    if ($row['productType'] == 'S') {
        //Calculo comisión por servicio
        $tmpCommission = $amount * $limitPerItemService['commission'];
        if ($tmpCommission > $limitPerBid) {
            $tmpCommission = $limitPerBid;
            $tmpCommissionPercentage = 100 * $tmpCommission / $amount;
        } else {
            $tmpCommissionPercentage = $limitPerItemService['commission'] * 100;
        }
    } else {
        //Calculo comision por bien
        $tmpCommission = $amount * $limitPerItemGood[0]['commission'];
        if ($tmpCommission > $limitPerItemGood[0]['limit']) {
            $criticalPoint = $limitPerItemGood[0]['limit'] / $limitPerItemGood[0]['commission'];
            $tmpCommission = $limitPerItemGood[0]['limit'] + ($amount - $criticalPoint) * $limitPerItemGood[1]['commission'];
            if ($tmpCommission > $limitPerItemGood[1]['limit']) {
                $tmpCommission = $limitPerItemGood[1]['limit'];
            }
            $tmpCommissionPercentage = 100 * $tmpCommission / $amount;
        } else {
            $tmpCommissionPercentage = $limitPerItemGood[0]['commission'] * 100;
        }
    }

    //Se trae solo las por convenio
    switch ($row['productType']) {
        case 'B':
            if ($tmpCommission > $topeBien) {
                $result[$row['proposalItem_id']] = [
                    'totalPricePaid' => $row['montoAdjudicado'],
                    'quantity' => $row['cantidad'],
                    'commission' => $tmpCommission,
                    'commissionPercentage' => $tmpCommissionPercentage,
                    'productType' => $row['productType']
                ];
            }
        case 'S':
            if ($tmpCommission > $topeServicio) {
                $result[$row['proposalItem_id']] = [
                    'totalPricePaid' => $row['montoAdjudicado'],
                    'quantity' => $row['cantidad'],
                    'commission' => $tmpCommission,
                    'commissionPercentage' => $tmpCommissionPercentage,
                    'productType' => $row['productType']
                ];
            }
    }
}

print_r($result);

/*if ($productType == 'S') {
    //Calculo comisión por servicio
    $tmpCommission = $amount * $limitPerItemService['commission'];
    if ($tmpCommission > $limitPerBid) {
        $tmpCommission = $limitPerBid;
        $tmpCommissionPercentage = 100 * $tmpCommission / $amount;
    } else {
        $tmpCommissionPercentage = $limitPerItemService['commission'] * 100;
    }
} else {
    //Calculo comision por bien
    $tmpCommission = $amount * $limitPerItemGood[0]['commission'];
    if ($tmpCommission > $limitPerItemGood[0]['limit']) {
        $criticalPoint = $limitPerItemGood[0]['limit'] / $limitPerItemGood[0]['commission'];
        $tmpCommission = $limitPerItemGood[0]['limit'] + ($amount - $criticalPoint) * $limitPerItemGood[1]['commission'];
        if($tmpCommission > $limitPerItemGood[1]['limit']){
            $tmpCommission = $limitPerItemGood[1]['limit'];
        }
        $tmpCommissionPercentage = 100 * $tmpCommission / $amount; 
    } else {
        $tmpCommissionPercentage = $limitPerItemGood[0]['commission'] * 100;
    }
}

$result = [
	'commission' => $tmpCommission,
	'percentage' => $tmpCommissionPercentage
];
*/
