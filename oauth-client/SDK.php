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

    public function displaylogin()
    {
        echo "<h1>Login with OAUTH</h1>";

        foreach($this->getProviders() as $key => $provider){
            if($key >= 0)
            {
                echo "<a href='".$provider['base_url'].$provider['access_token_url'].""
                    . "&client_id=" . $provider['id']
                    . "&scope=basic"
                    . "&state=" . $this->getState() . "'>Se connecter avec $key</a>";
                echo "<br>";
            }
        }
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


}