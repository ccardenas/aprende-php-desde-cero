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
	    pi.id AS proposalItemId,
	    pi.currency_id AS currencyId,
	    pi.quantity AS proposalItemQuantity,
	    pi.totalPricePaid AS totalPricePaid,
	    poi.id AS purchaseOrderItemId,
	    SUM(poi.quantity) purchaseOrderItemQuantity, 
	    SUM(poi.unitPrice*poi.quantity) AS purchaseOrderItemTotalPrice,
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
	    AND pi.id = 451887
	GROUP BY 
	    pi.id";

// mysql select query
$stmt = $con->prepare($query);
$stmt->execute(
    [
        'proposalItemId' => $proposalItemId
    ]
);
$resultQuery = $stmt->fetchAll(\PDO::FETCH_ASSOC)[0];

//Datos
$uf = 27808.87;
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

//primer requisito cantidad adjudicada > cantidad con OC
if ($resultQuery['proposalItemQuantity'] > $resultQuery['purchaseOrderItemQuantity']) {
    //Calculo comisión precio ofertado/adjudicado
    //Monto a calcular
    $amount = 12000000000.000000; // (float) $resultQuery['totalPricePaid'];
    $tmpCommissionReal = 0;
    //Calculo de comisión
    if ($resultQuery['productType'] == 'S') {
        //Calculo comisión por servicio
        $tmpCommission = $amount * $limitPerItemService['commission'];
        if ($tmpCommission > $limitPerBid) {
            $tmpCommissionReal = $tmpCommission;
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
                $tmpCommissionReal = $tmpCommission;
                $tmpCommission = $limitPerItemGood[1]['limit'];
            }
            $tmpCommissionPercentage = 100 * $tmpCommission / $amount;
        } else {
            $tmpCommissionPercentage = $limitPerItemGood[0]['commission'] * 100;
        }
    }

    //Se trae solo las por convenio
    if ($resultQuery['productType'] == 'B' && $tmpCommissionReal > $topeBien) {
        print_r(
            [
                'resultado' => 'B',
                'array' => $resultQuery,
                'commission' => $tmpCommission,
                'commissionReal' => $tmpCommissionReal
            ]
        );
    } elseif ($tmpCommissionReal > $topeBien) {
        print_r(
            [
                'resultado' => 'S',
                'array' => $resultQuery,
                'commission' => $tmpCommission,
                'commissionReal' => $tmpCommissionReal
            ]
        );
    }
    /*switch ($resultQuery['productType']) {
        case "B":
            if ($tmpCommissionReal > $topeBien) {
                print_r(
                    [
                        'resultado' => 'B',
                        'array' => $resultQuery,
                        'commission' => $tmpCommission,
                        'commissionReal' => $tmpCommissionReal
                    ]
                );
            }
        case 'S':
            if ($tmpCommissionReal > $topeServicio) {
                print_r(
                    [
                        'resultado' => 'S',
                        'array' => $resultQuery,
                        'commission' => $tmpCommission,
                        'commissionReal' => $tmpCommissionReal
                    ]
                );
            }
        default:
            print_r(
                [
                    'resultado' => 'no es por convenio',
                    'array' => $resultQuery,
                    'commission' => $tmpCommission
                ]
            );
            break;
    } */
} else {
    print_r(
        [
            'comentario' => 'no es OC por convenio'
        ]
    );
}

