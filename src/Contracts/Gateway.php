<?php


namespace Dnetix\Redirection\Contracts;

use Dnetix\Redirection\Exceptions\PlacetoPayException;
use Dnetix\Redirection\Message\CollectRequest;
use Dnetix\Redirection\Message\Notification;
use Dnetix\Redirection\Message\RedirectInformation;
use Dnetix\Redirection\Message\RedirectRequest;
use Dnetix\Redirection\Message\RedirectResponse;
use Dnetix\Redirection\Message\ReverseResponse;

abstract class Gateway
{
    const TP_SOAP = 'soap';
    const TP_REST = 'rest';

    protected $type = self::TP_SOAP;
    protected $carrier = null;
    protected $config;

    public function __construct($config = [])
    {
        if (!isset($config['login']) || !isset($config['tranKey']))
            throw new PlacetoPayException('No login or tranKey provided gat');

        if (!isset($config['url']) || !filter_var($config['url'], FILTER_VALIDATE_URL))
            throw new PlacetoPayException('No service URL provided to use');

        if (substr($config['url'], -1) != '/')
            $config['url'] .= '/';

        $this->config = $config;
    }

    /**
     * @param RedirectRequest|array $redirectRequest
     * @return RedirectResponse
     */
    public abstract function request($redirectRequest);

    /**
     * @param int $requestId
     * @return RedirectInformation
     */
    public abstract function query($requestId);

    /**
     * @param CollectRequest|array $collectRequest
     * @return RedirectInformation
     */
    public abstract function collect($collectRequest);

    /**
     * @param string $internalReference
     * @return ReverseResponse
     */
    public abstract function reverse($internalReference);

    /**
     * Change the web service to use for the connection
     * @param string $type can be 'soap' or 'rest'
     * @return $this
     * @throws PlacetoPayException
     */
    public function using($type)
    {
        if (in_array($type, [self::TP_SOAP, self::TP_REST])) {
            $this->type = $type;
        }else{
            throw new PlacetoPayException('The only connection methods are SOAP or REST');
        }
    }

    public function readNotification($data = null)
    {
        if (!$data)
            $data = $_POST;

        return new Notification($data, $this->config['tranKey']);
    }

}