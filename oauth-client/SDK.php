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

        $token = $this->getToken($provider, $code);
        $user = $this->getUser($provider, $token);
        echo $user;
    }

    function getToken($providerName, $code) {
        $provider = $this->getProviders()[$providerName];

        if(isset($provider['method_token']))
        {

            $params = "client_id=" .$provider['id']
            . "&client_secret=" . $provider['secret']
            . "&grant_type=authorization_code" 
            . "&code=" . $code
            . "&redirect_uri=" .'https://localhost/auth-success?provider='.$providerName;

            $url = $provider['access_token_url'];

            $curl = curl_init($url);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_TIMEOUT, 3);

            // Set request method to POST
            curl_setopt($curl, CURLOPT_POST, 1);
            // Set query data here with CURLOPT_POSTFIELDS
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

            $result = trim(curl_exec($curl));
            curl_close($curl);
            if($this->isJson($result)) {
                $result = json_decode($result, true);
                $token = $result['access_token'];
            } else {
                $string = explode("&", $result)[0];
                $token = explode("=", $string)[1];
            }

        }else{

            $params = [
                'grant_type' => "authorization_code",
                "code" => $code,
                "redirect_uri" => 'https://localhost/auth-success?provider='.$providerName
            ];
    
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

        }

        return $token;
    }

    function getUser($providerName, $token)
    {
        $provider = $this->getProviders()[$providerName];

        // Get userdata with access_token
        $userUrl = curl_init($provider['me_url']);

        curl_setopt($userUrl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($userUrl, CURLOPT_HEADER, 0);
        curl_setopt($userUrl, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_2) AppleWebKit/602.3.12 (KHTML, like Gecko) Version/10.0.2 Safari/602.3.12");

        if(!empty($body)){
            curl_setopt($userUrl, CURLOPT_POST, 1);
            curl_setopt($userUrl, CURLOPT_POSTFIELDS, $body);
        }else{
            curl_setopt($userUrl, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer {$token}"
            ]);
        }

        $result = curl_exec($userUrl);
        curl_close($userUrl);
        return $result;
    }

    public function isJson($string): bool
    {
        json_decode($string, true);
        return json_last_error() === JSON_ERROR_NONE;
    }

}