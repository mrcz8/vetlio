<?php

return [
    'columns' => [
        'created_at' => 'Created at',
        'updated_at' => 'Updated at',
    ],

    'items_to_select' => [
        'columns' => [
            'code' => 'Code',
            'name' => 'Name',
            'group' => 'Group',
            'price' => 'Price',
            'vat' => 'VAT',
            'price_with_vat' => 'Price with VAT',
        ],

        'filters' => [
            'group' => 'Group',
        ],]
];
