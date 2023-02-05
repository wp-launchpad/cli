<?php

namespace PSR2PluginBuilder\Entities;

class Configurations
{
    /**
     * @var string
     */
    protected $base_namespace;

    /**
     * @var string
     */
    protected $code_dir;

    /**
     * @var string
     */
    protected $test_dir;

    /**
     * @param string $base_namespace
     * @param string $code_dir
     * @param string $test_dir
     */
    public function __construct(string $base_namespace, string $code_dir, string $test_dir)
    {
        $this->base_namespace = $base_namespace;
        $this->code_dir = $code_dir;
        $this->test_dir = $test_dir;
    }

    /**
     * @return string
     */
    public function getBaseNamespace(): string
    {
        return $this->base_namespace;
    }

    /**
     * @return string
     */
    public function getCodeDir(): string
    {
        return $this->code_dir;
    }

    /**
     * @return string
     */
    public function getTestDir(): string
    {
        return $this->test_dir;
    }




}