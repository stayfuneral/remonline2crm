<?php require __DIR__.'/../../vendor/autoload.php';

$Rem = new RMethods(REMONLINE_API_KEY);

$preventMinute = $Rem->preparedTimestamp(60);
$currentDate = date("d.m.Y_H.i.s");
$crmContactAdd = 'crm.contact.add';

$clients = $Rem->getClients([
    'created_at[]' => $preventMinute
]);

$arContactFields = [];

if (
    !empty($clients) && 
    gettype($clients) === 'object' && 
    empty($clients->custom_fields->f1610708)
) {
    $src = BX24::findSourceByName($clients->marketing_source->title);
    $arContactFields['fields'] = [
        'NAME' => $clients->name,
        'PHONE' => [
            [
                'VALUE' => $clients->phone[0],
                'VALUE_TYPE' => 'MOBILE'
            ]
        ],
        'ASSIGNED_BY_ID' => 35,
        BX24::REM_CLIENT_ID_FIELD => $clients->id,
        BX24::BX24_MARKETING_SOURCE_FIELD => $src->ID
    ];



    $createContact = CRest::call($crmContactAdd, $arContactFields);

    if(intval($createContact['result']) > 0) {

        $bxContactId = intval($createContact['result']);

        $updateClient = $Rem->updateClient([
            'id' => $clients->id,
            'custom_fields' => '{"1610708": "'.$bxContactId.'"}'
        ]);
        
        $logData['create_contact'] = $bxContactId;
        $logData['update_client'] = $updateClient;
    }

    writeToLog(LOG_DIRECTORY . 'remonline/'.date('d-m-Y/H/') . 'new_client_' . date('H-i-s') . '.txt', $logData, 'New client');

} else if (
    !empty($clients) &&
    gettype($clients) === 'array'
) {

    foreach($clients as $client) {

        if(empty($client->custom_fields->f1610708)) {
            $arContactFields['create_contact_' . $client->id] = [
                'method' => $crmContactAdd,
                'params' => [
                    'fields' => [
                        'NAME' => $client->name,
                        'PHONE' => [
                            [
                                'VALUE' => $clients->phone[0],
                                'VALUE_TYPE' => 'MOBILE'
                            ]
                        ],
                        'UF_CRM_CONTACT_ID' => $clients->id
                    ]

                ]
            ];
        }

    }

    if(!empty($arContactFields)) {

        $createContacts =  CRest::callBatch($arContactFields);

        foreach($clients as $client) {

            if(!empty($createContacts['result']['result']['create_contact_' . $client->id])) {
                $bxContactId = $createContacts['result']['result']['create_contact_' . $client->id];

                $logData['create_contact'][] = $contactId;

                $updateClient = $Rem->updateClient([
                    'id' => $client->id,
                    'custom_fields' => '{"1610708": "'.$bxContactId.'"}'
                ]);

                $logData['update_client'][$client->id] = $updateClient;
            } else if(!empty($createContacts['result']['result_error'])) {
                $logData['errors'] = $createContacts['result']['result_error'];
            }

        } 

    }

    // writeToLog(LOG_DIRECTORY . 'remonline/'.date('d-m-Y/H/') . 'new_client_' . date('H-i-s.') . 'txt', $logData, 'New client');

}

