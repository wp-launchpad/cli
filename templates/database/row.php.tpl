<?php

namespace {{ namespace }};

use {{ base_namespace }}Dependencies\Database\Row;

class {{ class_name }} extends Row {

    /**
     * {{ class_name }} constructor.
     *
     * @param object $item Current row details.
     */
    public function __construct( $item ) {
        parent::__construct( $item );
        $this->id            = (int) $this->id;
        $this->modified      = false === $this->modified ? 0 : strtotime( $this->modified );
        $this->last_accessed = false === $this->last_accessed ? 0 : strtotime( $this->last_accessed );
    }
}
