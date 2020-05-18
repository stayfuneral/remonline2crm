<?php require __DIR__ . '/../../../vendor/autoload.php';

$installApp = CRest::installApp();

// Set Userfields

$ufContact = [
    'FIELD_NAME' => 'REM_CLIENT_ID',
    'USER_TYPE_ID' => 'integer'
];

$ufDeal = [
    [
        'FIELD_NAME' => 'REM_ORDER_ID',
        'USER_TYPE_ID' => 'integer'
    ],
    [
        'FIELD_NAME' => 'REM_SALE_ID',
        'USER_TYPE_ID' => 'integer'
    ],
    [
        'FIELD_NAME' => 'REM_STATUS_ID',
        'USER_TYPE_ID' => 'integer'
    ],
    [
        'FIELD_NAME' => 'REM_CREATED_AT',
        'USER_TYPE_ID' => 'datetime'
    ],
    [
        'FIELD_NAME' => 'REM_UPDATED_AT',
        'USER_TYPE_ID' => 'datetime'
    ],
];

$setUserFieldsParams = [];

$findUserFieldsParams = [
    'contact' => [
        'method' => 'crm.contact.userfield.list',
        'params' => [
            'filter' =>  [
                'FIELD_NAME' => $ufContact['FIELD_NAME']
            ]
        ]
    ],
    'deal' => [
        'method' => 'crm.deal.userfield.list',
    ]
    
    ];

$findUserFields = CRest::callBatch($findUserFieldsParams)['result']['result'];

if(empty($findUserFields['contact'])) {
    $setUserFieldsParams['create_contact_userfield'] = [
        'method' => 'crm.contact.userfield.add',
        'params' => [
            'fields' => $ufContact
        ]
    ];
}

for($i = 0; $i < count($ufDeal); $i++) {
    foreach($findUserFields['deal'] as $field) {
        if($field['FIELD_NAME'] !== $ufDeal[$i]['FIELD_NAME']) {
            $setUserFieldsParams['create_deal_userfield_'.($i+1)] = [
                'method' => 'crm.deal.userfield.add',
                'params' => [
                    'fields' => $ufDeal[$i]
                ]
            ];
        }
    }    
}

if(!empty($setUserFieldsParams)) {
    $setUserFields = CRest::callBatch($setUserFieldsParams);
    $logData['setUserFields'] = $setUserFields['result'];
}

// Set Event Binds

$events = CRest::call('event.get')['result'];

$logData = [
    'installApp' => $result,
    'installedEvents' => $installedEvents,
];

if (!empty($events)) {
    $onCrmContactAdd = 'ONCRMCONTACTADD';
    $eventBinds = [];
    $installedEvents = [];

    foreach ($events as $event) {
        $installedEvents[] = $event['event'];
    }

    if (!in_array($onCrmContactAdd, $installedEvents)) {
        $eventBinds['install_' . strtolower($onCrmContactAdd)] = [
            'method' => 'event.bind',
            'params' => [
                'event' => $onCrmContactAdd,
                'handler' => C_REST_EVENT_HANDLER
            ]
        ];
    }

    $uninstalledEvents = ['ONCRMDEALADD', 'ONCRMDEALUPDATE'];

    foreach($uninstalledEvents as $unEvent) {

        if(in_array($unEvent, $installedEvents)) {
            $eventBinds['uninstall_' . strtolower($unEvent)] = [
                'method' => 'event.unbind',
                'params' => [
                    'event' => $unEvent,
                    'handler' => C_REST_EVENT_HANDLER
                ]
            ];
        }

    }

    $setEventBind = CRest::callBatch($eventBinds);
}
