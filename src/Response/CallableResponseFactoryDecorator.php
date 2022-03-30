<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Response;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 * @deprecated Will be removed with v2.0.0
 */
final class CallableResponseFactoryDecorator implements ResponseFactoryInterface
{
    /** @var callable():ResponseInterface */
    private $responseFactory;

    /**
     * @param callable():ResponseInterface $responseFactory
     */
    public function __construct(callable $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return $this->getResponseFromCallable()->withStatus($code, $reasonPhrase);
    }

    public function getResponseFromCallable(): ResponseInterface
    {
        return ($this->responseFactory)();
    }
}
