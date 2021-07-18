<?php


class SDK
{
    private string $configFile = 'providers.json';
    protected array $providers;

    public function __construct()
    {
        $str = file_get_contents($this->configFile);
        $this->providers = json_decode($str, true);
    }

    public function getProviders(): array
    {
        return $this->providers;
    }

    function handleLogin()
    {
        $str = '';
        // TODO un state par provider ? => $_SESSION[$provider['name']]['state']
        $_SESSION['state'] = bin2hex(random_bytes(20));
        foreach ($this->getProviders() as $provider => $data) {
            $str .= "<div>
                <a href='".$data['login_url']
                . "?response_type=code"
                . "&client_id=" . $data['id']
                . "&scope=" . $data['scope']
                . "&state=" . $_SESSION['state']
                . "&redirect_uri=https://localhost/auth-success?provider=".$provider . "'>
                Se connecter avec ". $data['name']."
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

        $token = $this->getToken($provider, $params);
        $this->getUser($provider, $token);
    }

    function getToken($providerName, $params) {
        $provider = $this->getProviders()[$providerName];

        $url = $provider['access_token_url'] . "?client_id=" .$provider['id']
        . "&client_secret=" . $provider['secret']
        . "&" . http_build_query($params);

        $result = file_get_contents($url);
        if($this->isJson($result)) {
            $result = json_decode($result, true);
            $token = $result['access_token'];
        } else {
            $string = explode("&", $result)[0];
            $token = explode("=", $string)[1];
        }

        return $token;
    }

    function getUser($providerName, $token)
    {
        $provider = $this->getProviders()[$providerName];
        $userUrl = $provider['me_url'];
        $context = stream_context_create([
            'http' => [
                'header' => 'Authorization: Bearer ' . $token
            ]
        ]);
        echo file_get_contents($userUrl, false, $context);
    }

    public function isJson($string): bool
    {
        json_decode($string, true);
        return json_last_error() === JSON_ERROR_NONE;
    }

}