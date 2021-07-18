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
        // TODO un state par provider ? => $_SESSION[$provider['name']]['state']
        $_SESSION['state'] = bin2hex(random_bytes(20));
        foreach ($this->getProviders() as $provider) {
            $str .= "<div>
                <a href='".$provider['login_url']
                . "?response_type=code"
                . "&client_id=" . $provider['id']
                . "&scope=" . $provider['scope']
                . "&state=" . $_SESSION['state']
                . "&redirect_uri=https://localhost/auth-success?provider=".$provider['name'] . "'>
                Se connecter avec ". $provider['name']."
                </a>
            </div>";
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

        $params = [
            'grant_type' => "authorization_code",
            "code" => $code,
            "redirect_uri" => "https://localhost/auth-success?provider=$provider"
        ];

        $this->getUser($provider, $params);
    }

    function getUser($providerName, $params)
    {
        foreach ($this->getProviders() as $provider) {
            if($provider['name'] == $providerName) {
                $url = $provider['access_token_url'] . "?client_id=" .$provider['id'] 
                . "&client_secret=" . $provider['secret'] 
                . "&" . http_build_query($params);

                if(in_array($this->data["name"], ["Github"])){
                    $result = $this->callback($this->uriAuth, $params);
                    $string = explode("&", $result, 2)[0];
                    $access_token = explode("=", $string)[1];
                }else{
                    $surl = "{$this->uriAuth}?".http_build_query($params);
                    $result = json_decode(file_get_contents($surl), true);
                    ['access_token' => $access_token] = $result;
                }

                $apiUrl = $provider['me_url'];
                $context = stream_context_create([
                    'http' => [
                        'header' => 'Authorization: Bearer ' . $token
                    ]
                ]);

                echo file_get_contents($apiUrl, false, $context);
            }
        }
    }

}