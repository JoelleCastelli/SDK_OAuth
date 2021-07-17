<?php


class SDK
{
    private string $configFile = 'providers.json';
    protected array $providers;
    protected string $state;

    public function __construct()
    {
        $str = file_get_contents($this->configFile);
        $this->providers = json_decode($str, true);
        $this->state = bin2hex(random_bytes(20));
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

    /**
     * @return array
     */
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
        $_SESSION['state'] = bin2hex(random_bytes(20));
        foreach ($this->getProviders() as $provider) {
            $redirect = $provider['redirect_uri'] ? "&redirect_uri=".$provider['redirect_uri'] : '';

            $str .= "<div><a href='".$provider['login_url']."?response_type=code"
                . "&client_id=" . $provider['id']
                . "&scope=" . $provider['scope']
                . "&state=" . $_SESSION['state'] . "$redirect'>Se connecter avec ". $provider['name']."</a></div>";
        }
        echo $str;
    }

    function handleError()
    {
        ["state" => $state] = $_GET;
        echo "{$state} : Request cancelled";
    }

    function handleSuccess()
    {
        ["state" => $state, "code" => $code, "provider" => $provider] = $_GET;
        if ($state !== $_SESSION['state']) {
            throw new RuntimeException("{$state} : invalid state");
        }

        $this->getUser([
            'provider' => $provider,
            'grant_type' => "authorization_code",
            "code" => $code,
        ]);
    }

    function getUser($params)
    {
        $url = "http://oauth-server:8081/token?client_id=" . CLIENT_ID . "&client_secret=" . CLIENT_SECRET . "&" . http_build_query($params);
        $result = file_get_contents($url);
        $result = json_decode($result, true);
        $token = $result['access_token'];

        $apiUrl = "http://oauth-server:8081/me";
        $context = stream_context_create([
            'http' => [
                'header' => 'Authorization: Bearer ' . $token
            ]
        ]);
        echo file_get_contents($apiUrl, false, $context);
    }

}