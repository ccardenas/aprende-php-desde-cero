<?php

$dsn = 'mysql:hosy=localhost;port=3308;dbname=aqmarket';
$username = 'root';
$password = 'aqm_dev';

try {
    // connect to mysql
    $con = new PDO($dsn, $username, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $ex) {
    echo 'Not Connected ' . $ex->getMessage();
}

$query = "
    SELECT
        p1.*
    FROM 
        (
            (
                SELECT
                    t1.pb_id AS pb_id,
                    t1.pb_correlative AS pb_correlative,
                    t1.pb_directed AS pb_directed,
                    t1.prod_id AS prod_id,
                    t1.prod_code AS prod_code,
                    t1.prod_name AS prod_name,
                    t1.supplier_id AS cs_id,
                    t1.supplier_name AS cs_name,
                    t1.supplier_rut AS supplier_rut,
                    t1.detail_status AS detail_status,
                    t1.id_historical AS id_historical,
                    t1.status_historical AS status_historical,
                    t2.counter_offer AS counter_offer,
                    t1.prop_id AS proposal_id,
                    t1.pi_id AS pi_id,
                    t1.rf_id AS rf_id,
                    t2.pr_quantity AS pr_quantity,
                    t1.rf_notes AS rf_notes,
                    t1.cu_symbol AS cu_symbol,
                    t1.pi_unitPrice AS pi_unitPrice,
                    t1.pi_totalPrice AS pi_totalPrice,
                    t1.pi_offeredQuantity AS pi_offeredQuantity,
                    t2.cunit_name AS cunit_name,
                    t2.cunit_dispatchAddress AS cunit_dispatchAddress,
                    t2.ds_quantity AS ds_quantity,
                    t1.tm_id AS tm_id,
                    t1.itWasCalledAt AS itWasCalledAt,
                    t1.willParticipateAt AS willParticipateAt,
                    t1.tm_comment AS tm_comment,
                    t1.countrySupplier_id AS countrySupplier_id,
                    t1.countrySupplier_name AS countrySupplier_name
                FROM 
                    (
                        SELECT
                            cb.id AS buyer_id,
                            cb.name AS buyer_name,
                            pb.id AS pb_id,
                            pb.correlative AS pb_correlative,
                            IF(
                                pb.directed IS TRUE, 'directed',
                                'not_directed'
                            ) AS pb_directed,
                            prod.id AS prod_id,
                            prod.name AS prod_name,
                            prod.code AS prod_code,
                            cs.id AS supplier_id,
                            cs.name AS supplier_name,
                            cs.rut AS supplier_rut,
                            pi.id AS pi_id,
                            prop.id AS prop_id,
                            pr.quantity AS pr_quantity,
                            rf.notes AS rf_notes,
                            rf.id AS rf_id,
                            cu.symbol AS cu_symbol,
                            (
                                pi.totalPrice / pi.offeredQuantity
                            ) AS pi_unitPrice,
                            pi.totalPrice AS pi_totalPrice,
                            pi.offeredQuantity AS pi_offeredQuantity,
                            CASE WHEN (
                                prop.status = 'sent'
                                AND pi.id IS NOT NULL
                            ) THEN 'Enviada' WHEN (
                                prop.status = 'sent'
                                AND pi.id IS NULL
                            ) THEN 'Enviada, pero no oferto este producto' WHEN (
                                prop.deliveryEstimate IS NOT NULL
                                AND prop.status = 'in_progress'
                            ) THEN 'Falta sólo enviar (paso 2)' WHEN (
                                prop.status = 'in_progress'
                                AND pi.id IS NOT NULL
                            ) THEN 'Ingreso oferta (paso 1)' WHEN (
                                pi.id IS NULL
                            ) THEN 'Le interesa' 
                                END AS detail_status,
                            IF(
                                ht.statusTracing IS NULL, '', ht.statusTracing
                            ) AS status_historical,
                            ht.id AS id_historical,
                            tm.id AS tm_id,
                            tm.itWasCalledAt AS itWasCalledAt,
                            tm.willParticipateAt AS willParticipateAt,
                            tm.comment AS tm_comment,
                            cous.id AS countrySupplier_id,
                            cous.name AS countrySupplier_name
                        FROM 
                            (
                                SELECT
                                    id,
                                    correlative,
                                    user_id,
                                    offerReceptionLimit,
                                    directed,
                                    companyBuyer_id
                                FROM
                                    PurchaseBid
                                WHERE
                                    id = 38559
                            ) pb
                            INNER JOIN Company AS cb ON cb.id = pb.companyBuyer_id
                                AND cb.state = 1 
                                AND cb.deletedAt IS NULL
                            INNER JOIN RequestsFolder AS rf ON rf.purchaseBid_id = pb.id
                            INNER JOIN ProductRequest AS pr ON pr.folder_id = rf.id
                                AND pr.deletedAt IS NULL
                            INNER JOIN Product AS prod ON prod.id = pr.product_id
                                AND prod.deletedAt IS NULL
                            INNER JOIN Proposal AS prop ON prop.purchaseBid_id = pb.id
                                AND prop.bidInterest = 'interested'
                            LEFT JOIN ProposalItem pi ON pi.proposal_id = prop.id
                                AND rf.id = pi.requestsFolder_id
                            LEFT JOIN Currency AS cu ON cu.id = pi.currency_id
                            INNER JOIN Company AS cs ON cs.id = prop.companySupplier_id
                                AND cs.state = 1 
                                AND cs.deletedAt IS NULL
                            INNER JOIN Country AS cous ON cous.id = cs.country_id
                            LEFT JOIN TracingManagement AS tm ON tm.purchaseBid_id = pb.id
                                AND cs.id = tm.companySupplier_id
                            LEFT JOIN HistoricalTracing AS ht ON ht.supplierRut = REPLACE(cs.rut, '.', '')
                        GROUP BY
                            rf.id,
                            prod.id,
                            cs.id
                        ORDER BY
                            prod.id
                    ) AS t1
                    INNER JOIN (
                        SELECT
                            prop.purchaseBid_id AS pb_id,
                            rf.id AS rf_id,
                            COALESCE(
                                SUM(
                                    IF(
                                        pi.id IS NOT NULL AND prop.status = 'sent',
                                        1,
                                        0
                                    )
                                )
                            )AS counter_offer,
                            COALESCE(SUM(pr.quantity), 0) AS pr_quantity,
                            cunit.name AS cunit_name,
                            cunit.dispatchAddress AS cunit_dispatchAddress,
                            ds.quantity AS ds_quantity
                        FROM
                            (
                                SELECT
                                    id
                                FROM
                                    PurchaseBid
                                WHERE
                                    id = 38559
                            ) pb
                            INNER JOIN RequestsFolder rf ON rf.purchaseBid_id = pb.id
                            INNER JOIN DeliverySchedule ds ON ds.requestsFolder_id = rf.id
                            INNER JOIN CompanyUnit AS cunit ON cunit.id = ds.unit_id
                            INNER JOIN ProductRequest pr ON pr.folder_id = rf.id
                                AND pr.deletedAt IS NULL
                            INNER JOIN Product prod ON prod.id = pr.product_id
                                AND prod.deletedAt IS NULL
                            INNER JOIN Proposal prop ON prop.purchaseBid_id = pb.id
                                AND prop.bidInterest = 'interested'
                            LEFT JOIN ProposalItem pi ON pi.proposal_id = prop.id
                                AND rf.id = pi.requestsFolder_id
                        GROUP BY
                            rf.id
                    ) AS t2 ON t1.rf_id = t2.rf_id
            ) 
            UNION (
                SELECT
                    prod.pb_id AS pb_id,
                    prod.pb_correlative AS pb_correlative,
                    IF(
                        prod.pb_directed IS TRUE, 'directed',
                        'not_directed'
                    ) AS pb_directed,
                    prod.id AS prod_id,
                    prod.code AS prod_code,
                    prod.name AS prod_name,
                    IF(
                        cs.id IS NULL, 'no registrado', cs.id
                    ) AS cs_id,
                    IF(
                        cs.id IS NULL, ht.supplierName, cs.name
                    ) AS cs_name,
                    IF(
                        cs.id IS NULL, ht.supplierRut, cs.rut
                    ) AS cs_rut,
                    '' AS detail_status,
                    ht.id AS id_historical,
                    ht.statusTracing AS status_historical,
                    '' AS offer_count,
                    '' AS proposal_id,
                    '' AS pi_id,
                    '' AS rf_id,
                    prod.pr_quantity AS pr_quantity,
                    prod.rf_notes AS rf_notes,
                    '' AS cu_symbol,
                    '' AS pi_unitPrice,
                    '' AS pi_totalPrice,
                    '' AS pi_offeredQuantity,
                    prod.cunit_name AS cunit_name,
                    prod.cunit_dispatchAddress AS cunit_dispatchAddress,
                    prod.ds_quantity AS ds_quantity,
                    tm.id AS tm_id,
                    tm.itWasCalledAt AS itWasCalledAt,
                    tm.willParticipateAt AS willParticipateAt,
                    tm.comment AS tm_comment,
                    IF(
                        cocs.id IS NULL, 1, cocs.id
                    ) AS countrySupplier_id,
                    IF(
                        cocs.name IS NULL, 'Chile', cocs.name
                    ) AS countrySupplier_name
                FROM
                    HistoricalTracing ht
                    INNER JOIN (
                        SELECT
                            pb.id AS pb_id,
                            pb.correlative AS pb_correlative,
                            pb.directed AS pb_directed,
                            prod.id,
                            prod.code,
                            prod.name,
                            rf.notes AS rf_notes,
                            SUM(pr.quantity) AS pr_quantity,
                            ds.quantity AS ds_quantity,
                            cunit.name AS cunit_name,
                            cunit.dispatchAddress AS cunit_dispatchAddress
                        FROM
                            Product prod
                            INNER JOIN ProductRequest pr ON pr.product_id = prod.id
                            INNER JOIN RequestsFolder rf ON rf.id = pr.folder_id
                            INNER JOIN DeliverySchedule ds ON ds.requestsFolder_id = rf.id
                            INNER JOIN CompanyUnit cunit ON cunit.id = ds.unit_id
                            INNER JOIN PurchaseBid pb ON rf.purchaseBid_id = pb.id
                            AND pb.id = 38559
                        GROUP BY
                            rf.id
                    ) prod ON prod.code = ht.sku
                    LEFT JOIN Company cs ON REPLACE(cs.rut, '.', '') = ht.supplierRut
                        AND cs.state = 1 
                        AND cs.deletedAt IS NULL
                    LEFT JOIN Country cocs ON cocs.id = cs.country_id
                    LEFT JOIN TracingManagement tm ON tm.historicalTracing_id = ht.id
                        AND tm.purchaseBid_id = 38559
                GROUP BY
                    cs.id
            )
        ) AS p1
    -- WHERE p1.cs_id = 4055
    GROUP BY
        p1.prod_id,
        p1.cs_id
    ORDER BY
        p1.prod_id;";

// mysql select query
$stmt = $con->prepare($query);
$stmt->execute();
$resultQuery = $stmt->fetchAll(\PDO::FETCH_ASSOC);

//Agrego los participaciones
$product = [];
$supplier = [];

foreach ($resultQuery AS $row) {
    //productos;
    if (empty($product) || !in_array($row['prod_id'], $product)) {
        $product[] = $row['prod_id'];
    }

    //proveedores
    if (empty($supplier) || !in_array($row['cs_id'], $supplier)) {
        if ($row['cs_id'] !== 'no registrado') {
            $supplier[] = $row['cs_id'];
        }
    }
}

$productsImplode = '(' . implode(',', $product) . ')';
$suppliersImplode = '(' . implode(',', $supplier) . ')';
$queryParticipacion = "
    SELECT 
        COUNT(DISTINCT prop.id) AS participated,
        prop.companySupplier_id AS cs_id,
        prod.id AS prod_id
    FROM  
        Product AS prod
        INNER JOIN ProductRequest AS pr ON pr.product_id = prod.id                                                                                                                   
            AND pr.deletedAt IS NULL
            AND pr.status != 'pending'
        LEFT JOIN RequestsFolder AS rf ON rf.id = pr.folder_id
        LEFT JOIN ProposalItem AS pi ON pi.requestsFolder_id = rf.id
        LEFT JOIN Proposal AS prop ON prop.id = pi.proposal_id
            AND prop.status = 'sent'
    WHERE 
        prod.deletedAt IS NULL 
        AND prod.id IN $productsImplode
        AND prop.companySupplier_id IN $suppliersImplode
    GROUP BY 
        prod.id, 
        prop.companySupplier_id
    ORDER BY
        prop.companySupplier_id;
";
$stmt2 = $con->prepare($queryParticipacion);

$params = array(
    'products_ids' => $productsImplode,
    'suppliers_ids' => $suppliersImplode
);
$stmt2->execute();
$resultQueryPar = $stmt2->fetchAll(\PDO::FETCH_ASSOC);



//result query seguimiento 
print_r($product);
print_r($supplier);
print_r($resultQueryPar);
