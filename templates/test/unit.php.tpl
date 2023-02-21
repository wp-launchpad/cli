<?php

namespace {{ namespace }};

use {{ base_namespace }}Tests\Unit\TestCase;

/**
 * @covers {{ base_class }}::{{ base_method }}
 {% if has_group : %}
 * @group {{ group }}
 {% endif %}
 */
class Test_{{ class_name }} extends TestCase {

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected )
    {

    }
}
