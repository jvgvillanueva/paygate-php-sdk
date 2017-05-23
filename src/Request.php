<?php namespace CoreProc\Paynamics\PayGate;

use CoreProc\Paynamics\PayGate\Contracts\ClientInterface;
use CoreProc\Paynamics\PayGate\Contracts\RequestBodyInterface;
use CoreProc\Paynamics\PayGate\Contracts\RequestInterface;
use Coreproc\Paynamics\PayGate\Contracts\ResponseInterface;
use Coreproc\Paynamics\PayGate\Exceptions\PayGateException;

class Request implements RequestInterface
{

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var RequestBodyInterface
     */
    private $requestBody;

    public function __construct(ClientInterface $client, RequestBodyInterface $requestBody, $options = [])
    {
        $this->setClient($client);
        $this->setRequestBody($requestBody);
    }

    /**
     * Returns the assigned  client
     *
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Sets the  client
     *
     * @param ClientInterface $client
     * @return self
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Returns the assigned request body
     *
     * @return RequestBodyInterface
     */
    public function getRequestBody()
    {
        return $this->requestBody;
    }

    /**
     * Sets the request body
     *
     * @param RequestBodyInterface $requestBody
     * @return self
     */
    public function setRequestBody(RequestBodyInterface $requestBody)
    {
        $this->requestBody = $requestBody;

        return $this;
    }

    /**
     * Executes the request and returns corresponding response
     *      or throws an error
     *
     * @param array $options
     * @return ResponseInterface|bool
     * @throws PayGateException
     */
    public function execute(array $options = [])
    {
        $client = $this->getClient();
        $url = $client->getRequestUrl();
        $requestBody = $this->getRequestBody();

        $requestBody->setDefaults($client);
        $requestBody->generateRequestSignature($client);

        try {
            $response = $client->getHttpClient()->post($url, [
                'form_params' => [
                    'paymentrequest' => base64_encode($requestBody->__toXmlString())
                ]
            ]);

            return new Response($response, $this);
        } catch (PayGateException $e) {

        }

        return false;
    }
}