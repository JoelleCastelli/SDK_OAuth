<?php


class SDK
{
    private string $configFile = 'providers.json';
    protected array $providers;
    protected string $state = "fdzefzefze";

    public function __construct()
    {
        $str = file_get_contents($this->configFile);
        $this->providers = json_decode($str, true);
    }

    /**
     * @return string
     */
    public function getConfigFile(): string
    {
        return $this->configFile;
    }

    /**
     * @param string $configFile
     */
    public function setConfigFile(string $configFile): void
    {
        $this->configFile = $configFile;
    }

    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * @param array $providers
     */
    public function setProviders(mixed $providers): void
    {
        $this->providers = $providers;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState(string $state): void
    {
        $this->state = $state;
    }

    function handleLogin()
    {
        $str = '';
        foreach ($this->getProviders() as $provider) {
            $redirect = $provider['redirect_uri'] ? "&redirect_uri=".$provider['redirect_uri'] : '';

            $str .= "<div><a href='".$provider['login_url']."?response_type=code"
                . "&client_id=" . $provider['id']
                . "&scope=" . $provider['scope']
                . "&state=" . $this->getState() . "$redirect'>Se connecter avec ". $provider['name']."</a></div>";
        }
        echo $str;
    }

}