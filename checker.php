<?php

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;

Loader::includeModule("crm");

// приведение к формату "+79..."
function normalizePhone($phone)
{
    $phone = preg_replace('/\D+/', '', $phone);

    if (strlen($phone) == 11 && $phone[0] == '8') {
        $phone[0] = '7';
    }

    if (strlen($phone) == 10) {
        $phone = '7' . $phone;
    }

    return '+' . $phone;
}


// поиск контактов по номеру
function findContactsByPhone($phone)
{
    $contacts = [];

    $db = CCrmFieldMulti::GetList(
        [],
        [
            'TYPE_ID' => 'PHONE',
            'VALUE' => $phone
        ]
    );

    while ($item = $db->Fetch()) {

        if ($item['ENTITY_ID'] == 'CONTACT') {
            $contacts[] = $item['ELEMENT_ID'];
        }

    }

    return $contacts;
}


// получение сделок
function findDealsByContacts($contactIds)
{
    $deals = [];

    if (empty($contactIds)) {
        return $deals;
    }

    $db = CCrmDeal::GetList(
        [],
        ['CONTACT_ID' => $contactIds],
        false,
        false,
        ['ID','TITLE','STAGE_ID']
    );

    while ($deal = $db->Fetch()) {
        $deals[] = $deal;
    }

    return $deals;
}


// основная логика
$phone = $_REQUEST['phone'] ?? '';

if (!$phone) {
    echo json_encode([
        'error' => 'phone parameter required'
    ]);
    die();
}

$phone = normalizePhone($phone);

$contacts = findContactsByPhone($phone);

$deals = findDealsByContacts($contacts);

echo json_encode([
    'phone' => $phone,
    'contacts' => $contacts,
    'deals' => $deals
]);