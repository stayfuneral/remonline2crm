<?php require __DIR__ . '/../../../vendor/autoload.php';

$Rem = new RMethods(REMONLINE_API_KEY);

// if($_REQUEST['event'] === 'ONCRMCONTACTADD') {

//     $contactId = $_REQUEST['data']['FIELDS']['ID'];

//     $contact = CRest::call('crm.contact.get', ['id' => $contactId]);

//     if(!empty($contact['result'])) {

//         $ufRemClientId = intval($contact['result']['UF_CRM_REM_CLIENT_ID']);

//         if(empty($ufRemClientId) || $ufRemClientId === 0) {

//             $contact = $contact['result'];

//             if (!empty($contact["UF_CRM_1581460569"])) {
//                 $findSource = CRest::call("crm.contact.userfield.list", [
//                             "filter" => [
//                                 "FIELD_NAME" => "UF_CRM_1581460569"
//                             ]
//                         ])["result"][0];
//                 foreach ($findSource["LIST"] as $item) {
//                     if (intval($item["ID"]) === intval($contact["UF_CRM_1581460569"])) {
//                         $contactmarketingSource = $Rem->findSourceByName($item["VALUE"])->id;
//                     }
//                 }
//             } else {
//                 $contactmarketingSource = 158443;
//             }
            
//             $arRemonlineFields = [
//                 'name' => $contact['NAME'] . ' ' . $contact['LAST_NAME'],
//                 'phone[]' => $contact['PHONE'][0]['VALUE'],
//                 'marketing_source' => $contactmarketingSource,
//                 'custom_fields' => '{"1610708": '.$contactId.'}'
//             ];

//             $createClient = $Rem->createClient($arRemonlineFields);
            
//             if($createClient->success == 1) {
//                 $updateContact = CRest::call('crm.contact.update', [
//                     'id' => $contactId,
//                     'fields' => [
//                         'UF_CRM_REM_CLIENT_ID' => $createClient->data->id
//                     ]
//                 ]);

//                 $logData['create_client'] = $createClient;
//                 $logData['update_contact'] = $updateContact;
//             }

//         }

//     } else {
//         writeToLog(LOG_DIRECTORY . "bx24/Bitrix24_new_contact_{$contactId}_error.txt", $logData, 'Error');
//     }
// }