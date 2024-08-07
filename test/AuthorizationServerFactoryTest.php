<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use Laminas\Diactoros\ServerRequest;
use League\Event\ListenerInterface;
use League\Event\ListenerProviderInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\RequestEvent;
use Mezzio\Authentication\OAuth2\AuthorizationServerFactory;
use Mezzio\Authentication\OAuth2\Exception\InvalidConfigException;
use PHPUnit\Framework\TestCase;

class AuthorizationServerFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $container           = new InMemoryContainer();
        $mockClientRepo      = $this->createMock(ClientRepositoryInterface::class);
        $mockAccessTokenRepo = $this->createMock(AccessTokenRepositoryInterface::class);
        $mockScopeRepo       = $this->createMock(ScopeRepositoryInterface::class);
        $mockClientGrant     = $this->createMock(ClientCredentialsGrant::class);
        $mockPasswordGrant   = $this->createMock(PasswordGrant::class);

        $config = [
            'authentication' => [
                'private_key'         => __DIR__ . '/TestAsset/private.key',
                'encryption_key'      => 'iALlwJ1sH77dmFCJFo+pMdM6Af4bF/hCca1EDDx7MwE=',
                'access_token_expire' => 'P1D',
                'grants'              => [
                    ClientCredentialsGrant::class => ClientCredentialsGrant::class,
                    PasswordGrant::class          => PasswordGrant::class,
                ],
            ],
        ];

        $container->set(ClientRepositoryInterface::class, $mockClientRepo);
        $container->set(AccessTokenRepositoryInterface::class, $mockAccessTokenRepo);
        $container->set(ScopeRepositoryInterface::class, $mockScopeRepo);
        $container->set(ClientCredentialsGrant::class, $mockClientGrant);
        $container->set(PasswordGrant::class, $mockPasswordGrant);
        $container->set('config', $config);

        $factory = new AuthorizationServerFactory();

        $result = $factory($container);

        self::assertInstanceOf(AuthorizationServer::class, $result);
    }

    private function getContainerMock(): InMemoryContainer
    {
        $container           = new InMemoryContainer();
        $mockClientRepo      = $this->createMock(ClientRepositoryInterface::class);
        $mockAccessTokenRepo = $this->createMock(AccessTokenRepositoryInterface::class);
        $mockScopeRepo       = $this->createMock(ScopeRepositoryInterface::class);
        $mockClientGrant     = $this->createMock(ClientCredentialsGrant::class);
        $mockPasswordGrant   = $this->createMock(PasswordGrant::class);

        $container->set(ClientRepositoryInterface::class, $mockClientRepo);
        $container->set(AccessTokenRepositoryInterface::class, $mockAccessTokenRepo);
        $container->set(ScopeRepositoryInterface::class, $mockScopeRepo);
        $container->set(ClientCredentialsGrant::class, $mockClientGrant);
        $container->set(PasswordGrant::class, $mockPasswordGrant);

        return $container;
    }

    public function testInvokeWithNullGrant(): void
    {
        $mockContainer = $this->getContainerMock();

        $config = [
            'authentication' => [
                'private_key'         => __DIR__ . '/TestAsset/private.key',
                'encryption_key'      => 'iALlwJ1sH77dmFCJFo+pMdM6Af4bF/hCca1EDDx7MwE=',
                'access_token_expire' => 'P1D',
                'grants'              => [
                    ClientCredentialsGrant::class => null,
                    PasswordGrant::class          => PasswordGrant::class,
                ],
            ],
        ];

        $mockContainer->set('config', $config);

        $factory = new AuthorizationServerFactory();

        $result = $factory($mockContainer);

        self::assertInstanceOf(AuthorizationServer::class, $result);
    }

    public function testInvokeWithListenerConfig(): void
    {
        $mockContainer = $this->getContainerMock();
        $mockListener  = $this->createMock(ListenerInterface::class);
        $mockContainer->set(ListenerInterface::class, $mockListener);

        $config = [
            'authentication' => [
                'private_key'         => __DIR__ . '/TestAsset/private.key',
                'encryption_key'      => 'iALlwJ1sH77dmFCJFo+pMdM6Af4bF/hCca1EDDx7MwE=',
                'access_token_expire' => 'P1D',
                'grants'              => [
                    ClientCredentialsGrant::class => ClientCredentialsGrant::class,
                ],
                'event_listeners'     => [
                    [
                        RequestEvent::CLIENT_AUTHENTICATION_FAILED,
                        static function (RequestEvent $event): void {
                            // do something
                        },
                    ],
                    [
                        RequestEvent::CLIENT_AUTHENTICATION_FAILED,
                        ListenerInterface::class,
                    ],
                ],
            ],
        ];

        $mockContainer->set('config', $config);

        $factory = new AuthorizationServerFactory();

        $result = $factory($mockContainer);

        self::assertInstanceOf(AuthorizationServer::class, $result);

        // Ensure listeners have been registered correctly. If they have not, then emitting an event will fail
        $request = $this->createMock(ServerRequest::class);
        $result->getEmitter()->emit(new RequestEvent(RequestEvent::CLIENT_AUTHENTICATION_FAILED, $request));
    }

    public function testInvokeWithListenerConfigFailsIfPriorityIsNotAnInteger(): void
    {
        $mockContainer = $this->getContainerMock();
        $mockListener  = $this->createMock(ListenerInterface::class);
        $mockContainer->set(ListenerInterface::class, $mockListener);

        $config = [
            'authentication' => [
                'private_key'         => __DIR__ . '/TestAsset/private.key',
                'encryption_key'      => 'iALlwJ1sH77dmFCJFo+pMdM6Af4bF/hCca1EDDx7MwE=',
                'access_token_expire' => 'P1D',
                'grants'              => [
                    ClientCredentialsGrant::class => ClientCredentialsGrant::class,
                ],
                'event_listeners'     => [
                    [
                        RequestEvent::CLIENT_AUTHENTICATION_FAILED,
                        ListenerInterface::class,
                        'one',
                    ],
                ],
            ],
        ];

        $mockContainer->set('config', $config);

        $factory = new AuthorizationServerFactory();

        $this->expectException(InvalidConfigException::class);

        $factory($mockContainer);
    }

    public function testInvokeWithListenerConfigMissingServiceThrowsException(): void
    {
        $mockContainer = $this->getContainerMock();

        $config = [
            'authentication' => [
                'private_key'         => __DIR__ . '/TestAsset/private.key',
                'encryption_key'      => 'iALlwJ1sH77dmFCJFo+pMdM6Af4bF/hCca1EDDx7MwE=',
                'access_token_expire' => 'P1D',
                'grants'              => [
                    ClientCredentialsGrant::class => ClientCredentialsGrant::class,
                ],
                'event_listeners'     => [
                    [
                        RequestEvent::CLIENT_AUTHENTICATION_FAILED,
                        ListenerInterface::class,
                    ],
                ],
            ],
        ];

        $mockContainer->set('config', $config);

        $factory = new AuthorizationServerFactory();

        $this->expectException(InvalidConfigException::class);

        $factory($mockContainer);
    }

    public function testInvokeWithListenerProviderConfig(): void
    {
        $mockContainer = $this->getContainerMock();
        $mockProvider  = $this->createMock(ListenerProviderInterface::class);
        $mockContainer->set(ListenerProviderInterface::class, $mockProvider);

        $config = [
            'authentication' => [
                'private_key'              => __DIR__ . '/TestAsset/private.key',
                'encryption_key'           => 'iALlwJ1sH77dmFCJFo+pMdM6Af4bF/hCca1EDDx7MwE=',
                'access_token_expire'      => 'P1D',
                'grants'                   => [
                    ClientCredentialsGrant::class => ClientCredentialsGrant::class,
                ],
                'event_listener_providers' => [
                    ListenerProviderInterface::class,
                ],
            ],
        ];

        $mockContainer->set('config', $config);

        $factory = new AuthorizationServerFactory();

        $result = $factory($mockContainer);

        self::assertInstanceOf(AuthorizationServer::class, $result);
    }

    public function testInvokeWithListenerProviderConfigMissingServiceThrowsException(): void
    {
        $mockContainer = $this->getContainerMock();

        $config = [
            'authentication' => [
                'private_key'              => __DIR__ . '/TestAsset/private.key',
                'encryption_key'           => 'iALlwJ1sH77dmFCJFo+pMdM6Af4bF/hCca1EDDx7MwE=',
                'access_token_expire'      => 'P1D',
                'grants'                   => [
                    ClientCredentialsGrant::class => ClientCredentialsGrant::class,
                ],
                'event_listener_providers' => [
                    ListenerProviderInterface::class,
                ],
            ],
        ];

        $mockContainer->set('config', $config);

        $factory = new AuthorizationServerFactory();

        $this->expectException(InvalidConfigException::class);
        $factory($mockContainer);
    }
}
