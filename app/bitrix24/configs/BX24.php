<?php

class BX24 extends CRest {

    const BX24_MARKETING_SOURCE_FIELD = 'UF_CRM_1581460569';
    const REM_CLIENT_ID_FIELD = 'UF_CRM_REM_CLIENT_ID';

    public static function findSourceByName($name) {

        $arSources = parent::call('crm.contact.userfield.list', [
            'filter' => [
                'FIELD_NAME' => self::BX24_MARKETING_SOURCE_FIELD
            ]
        ])['result'][0];

        $result = null;

        foreach($arSources['LIST'] as $src) {

            if($src['VALUE'] === $name) {
                $result = (object)$src;
            }

        }

        return $result;

    }

}