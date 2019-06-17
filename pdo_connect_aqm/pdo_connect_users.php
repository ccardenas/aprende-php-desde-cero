<?php

$dsn = 'mysql:hosy=localhost;dbname=aqmarket';
$username = 'root';
$password = 'root';

try {
    // connect to mysql
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $ex) {
    echo 'Not Connected ' . $ex->getMessage();
}
$query = "SELECT 
			pb.id AS pbId,
			pb.correlative AS pbCorrelative,
			p.id AS prodId,
			p.name AS prodName,
			sc.id AS scId,
			sc.name AS scName,
			prop.id AS propId,
			po.id AS poId,
			po.correlative AS poCorrelative,
			poi.id AS poiId,
			poi.unitPrice AS poiUnitPrice,
			poi.quantity AS poiQuantity,
			(poi.unitPrice * cv.value) AS precioUnitario,
			pi.id AS piId,
			ce.conversion AS currencyConversion,
			cv.value AS valueCV,
			poi.currency_id
		FROM
			PurchaseOrderItem AS poi
			INNER JOIN PurchaseOrder AS po ON poi.purchaseOrder_id = po.id
			INNER JOIN ProposalItem AS pi ON poi.proposalItem_id = pi.id
			INNER JOIN Proposal AS prop ON pi.proposal_id = prop.id
			INNER JOIN User AS u ON prop.createdBy_id = u.id
			INNER JOIN Company AS sc ON u.company_id = sc.id
			INNER JOIN RequestsFolder AS rf ON pi.requestsFolder_id = rf.id
			INNER JOIN ProductRequest AS pr ON rf.id = pr.folder_id
			INNER JOIN Product AS p ON pr.product_id = p.id
			INNER JOIN Node AS n ON n.id = p.node_id
				AND (n.isService IS NULL OR n.isService = 0)
			INNER JOIN PurchaseBid AS pb ON rf.purchaseBid_id = pb.id
			INNER JOIN CurrencyEquivalence AS ce ON pb.id = ce.purchaseBid_id
				AND poi.currency_id = ce.currency_id
			LEFT JOIN(
				SELECT 
					id, currency_id, value
				FROM
					CurrencyValue
				ORDER BY id DESC
				LIMIT 0 , 4
			) AS cv ON poi.currency_id = cv.currency_id
		WHERE
			pb.publishedAt >= '2017-10-01 00:00:00'
			AND IFNULL(poi.billStatus, - 1) <> 'billed'
			AND IFNULL(poi.billStatus, - 1) <> 'presale'
			AND pi.quantity > 0
			AND po.deletedAt IS NULL
			AND sc.id IN (21)
		GROUP BY poi.id
		ORDER BY po.id ASC";

$stmt = $pdo->query($query);
$stmt->execute();
$ocs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

$uf = 27565.79;
$tramos = [
    ['commission' => 0.8 / 100, 'limit' => $uf * 7.5],
    ['commission' => 0.1 / 100, 'limit' => $uf * 10],
];

$data = [];
$tmpData = [
    'supplier' => null,
    'data' => []
];

foreach ($ocs as $oc) {
    //Calculo de comisión
    $monto = $oc['precioUnitario'] * $oc['poiQuantity'];
    $comision = $monto * $tramos[0]['commission'];
    if ($comision > $tramos[0]['limit']) {
        $puntoCritico = $tramos[0]['limit'] / $tramos[0]['commission'];
        $comision = $tramos[0]['limit'] + ($monto - $puntoCritico) * $tramos[1]['commission'];
        $porcentajeComision = (100 * $comision) / $monto;
        if ($comision > $tramos[1]['limit']) {
            $comision = $tramos[1]['limit'];
            $porcentajeComision = (100 * $comision) / $monto;
        }
    } else {
        $porcentajeComision = $tramos[0]['commission'] * 100;
    }

    if (is_null($tmpData['supplier'])) {
        $tmpData['supplier'] = [
            'scId' => $oc['scId'],
            'scName' => $oc['scName'],
        ];
    }

    //add data array
    $tmpData['data'][] = [
        'pbId' => $oc['pbId'],
        'pbCorrelative' => $oc['pbCorrelative'],
        'prodName' => $oc['prodName'],
        'propId' => $oc['propId'],
        'poId' => $oc['poId'],
        'poCorrelative' => $oc['poCorrelative'],
        'poiId' => $oc['poiId'],
        'poiUnitPrice' => $oc['precioUnitario'],
        'poiQuantity' => $oc['poiQuantity'],
        'piId' => $oc['piId'],
        'conversion' => $oc['currencyConversion'],
        'valor' => $oc['valueCV'],
        'Monto' => $monto,
        'porcentajeComision' => $porcentajeComision,
        'comision' => $comision,
    ];
}

$data = $tmpData;

//SE TRABAJA SOLO CON LA COMISIÓN
// for ($i = 0; $i < count($data['data']); $i++) {
// 	// print_r($data['data'][$i]['comision'] . '<br/>');
// 	print_r($data['data']);
// }
foreach ($data['data'] as $row) {
    // print_r($row['comision'] . '<br/>');
    $comisionRow = $row['comision'];
    $queryAllOC = '';

    //Saca los historicos
    $queryHistoricOC = "SELECT 
							CAST(CommissionValue AS DECIMAL(20, 5)) AS commissionValueBill,
							purchaseOrderItem_id
						FROM
							BillItem
						WHERE
							purchaseOrderItem_id IN (
								SELECT 
									id
								FROM 
									PurchaseOrderItem
								WHERE
									purchaseOrder_id = $comisionRow
							);";
    $stmt1 = $pdo->query($queryHistoricOC);
    $stmt1->execute();
    $ocs2 = $stmt1->fetchAll(\PDO::FETCH_ASSOC);
    array_sum($ocs2);

    print_r($ocs2);
}

// print_r($data);
