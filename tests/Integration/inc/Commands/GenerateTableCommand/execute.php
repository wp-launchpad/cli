<?php

namespace LaunchpadCLI\Tests\Integration\inc\Commands\GenerateTableCommand;

use LaunchpadCLI\Tests\Integration\TestCase;

class Test_Execute extends TestCase
{
    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected($config, $expected) {

        $date = (new \DateTime())->format('Ymd');
        $table_content = str_replace('{{ date }}', $date, $expected['table_content']);

        self::assertFalse($this->filesystem->exists($expected['query_path']));
        self::assertFalse($this->filesystem->exists($expected['row_path']));
        self::assertFalse($this->filesystem->exists($expected['table_path']));
        self::assertFalse($this->filesystem->exists($expected['schema_path']));
        if(! $config['provider_exists']) {
            $this->filesystem->delete($config['provider_path']);
        }
        $this->launch_app("table {$config['table']} {$config['folder']}");
        self::assertTrue($this->filesystem->exists($expected['query_path']));
        self::assertTrue($this->filesystem->exists($expected['row_path']));
        self::assertTrue($this->filesystem->exists($expected['table_path']));
        self::assertTrue($this->filesystem->exists($expected['schema_path']));
        self::assertSame($expected['query_content'], $this->filesystem->get_contents($expected['query_path']));
        self::assertSame($expected['row_content'], $this->filesystem->get_contents($expected['row_path']));
        self::assertSame($table_content, $this->filesystem->get_contents($expected['table_path']));
        self::assertSame($expected['schema_content'], $this->filesystem->get_contents($expected['schema_path']));
        self::assertSame($expected['provider_content'], $this->filesystem->get_contents($config['provider_path']));
        self::assertSame($expected['plugin_content'], $this->filesystem->get_contents($config['plugin_path']));
    }
}
