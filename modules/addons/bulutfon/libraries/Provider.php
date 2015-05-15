<?php
namespace Bulutfon\Libraries;
use Bulutfon\OAuth2\Client\Provider\Bulutfon;
use League\OAuth2\Client\Grant\RefreshToken;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\Request;
class Provider{
    protected $repository;

    protected $request;

    protected $provider;

    protected $path;

    public function __construct(Repository $repository,$path)
	{
        $this->repository = $repository;
        $this->request =  Request::createFromGlobals();
        $this->path = '/'.$path ?:'';

    }

    public function init()
    {
        $keys = $this->repository->getKeys();

        $this->provider = new Bulutfon($keys);

        if (!$this->request->get('code')) {
            if($this->request->get('refresh_token')) {

                $token = new AccessToken(Helper::decamelize($this->repository->getTokens()));

                $tokens =$this->provider->getAccessToken('refresh_token',[
                    'refresh_token' => $token->refreshToken
                ]);


                $token = array(
                    'access_token' =>$tokens->accessToken,
                    'refresh_token' =>$tokens->refreshToken,
                    'expires' => $tokens->expires,
                    'uid' => $tokens->uid
                );

                $this->repository->setTokens(json_encode($token));

                $this->redirect("/{$this->path}/addonmodules.php?module=bulutfon&code={$this->request->get('code')}}");

            }else {
                echo "Code Doesn't exist";
                exit;
            }

        } else {
            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $this->request->get('code'),
                'grant_type' => 'authorization_code'
            ]);

            $this->repository->setTokens(json_encode($token));

            $this->redirect("{$this->path}/addonmodules.php?module=bulutfon&code={$this->request->get('code')}&state={$this->request->get('state')}");
        }
    }

    private function redirect($url)
    {
        header("Location:{$_SERVER['HTTP_HOST']}/{$url}");
        exit;
    }
}