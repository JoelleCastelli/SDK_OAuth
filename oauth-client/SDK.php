<?php


class SDK
{
    private string $configFile = 'config.yml';
    protected array $providers;

    public function __construct()
    {
        $this->providers = yaml_parse_file($this->configFile);
    }

    public function getProviders(): mixed
    {
        return $this->providers;
    }

    public function setProviders(mixed $providers): void
    {
        $this->providers = $providers;
    }


}