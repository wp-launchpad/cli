<?php

namespace {{ namespace }};

use {{ base_namespace }}Integration\TestCase;

/**
 * @covers {{ base_class }}::{{ base_method }}
 *
 */
class Test_{{ class_name }} extends TestCase {

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected )
    {

    }
}
