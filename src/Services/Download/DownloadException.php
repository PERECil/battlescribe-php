<?php

declare(strict_types=1);

namespace Battlescribe\Services\Download;

use Exception;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class DownloadException extends Exception
{
    private ResponseInterface $response;

    public function __construct(ResponseInterface $response)
    {
        parent::__construct($response->getBody()->getContents(), $response->getStatusCode());

        $this->response = $response;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
