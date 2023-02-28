<?php

namespace PSR2Plugin\Engine\Test\Database\Schemas;

use PSR2Plugin\Dependencies\Database\Schema;

class MyTable extends Schema {

    /**
     * Array of database column objects
     *
     * @var array
     */
    public $columns = [

        // ID column.
        [
            'name'     => 'id',
            'type'     => 'bigint',
            'length'   => '20',
            'unsigned' => true,
            'extra'    => 'auto_increment',
            'primary'  => true,
            'sortable' => true,
        ],

        // MODIFIED column.
        [
            'name'       => 'modified',
            'type'       => 'timestamp',
            'default'    => '0000-00-00 00:00:00',
            'created'    => true,
            'date_query' => true,
            'sortable'   => true,
        ],

        // LAST_ACCESSED column.
        [
            'name'       => 'last_accessed',
            'type'       => 'timestamp',
            'default'    => '0000-00-00 00:00:00',
            'created'    => true,
            'date_query' => true,
            'sortable'   => true,
        ],

    ];
}
