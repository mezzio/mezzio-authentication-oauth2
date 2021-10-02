<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2;

use DateInterval;
use League\OAuth2\Server\AuthorizationServer;
use Psr\Container\ContainerInterface;

use function is_string;
use function sprintf;

/**
 * Factory for OAuth AuthorizationServer
 *
 * Initializes a new AuthorizationServer with required params from config.
 * Then configured grant types are enabled with configured access token
 * expiry. Then any optionally configured event listeners are attached to the
 * AuthorizationServer.
 */
class AuthorizationServerFactory
{
    use ConfigTrait;
    use CryptKeyTrait;
    use RepositoryTrait;

    public function __invoke(ContainerInterface $container): AuthorizationServer
    {
        $clientRepository      = $this->getClientRepository($container);
        $accessTokenRepository = $this->getAccessTokenRepository($container);
        $scopeRepository       = $this->getScopeRepository($container);

        $privateKey = $this->getCryptKey($this->getPrivateKey($container), 'authentication.private_key');
        $encryptKey = $this->getEncryptionKey($container);
        $grants     = $this->getGrantsConfig($container);

        $authServer = new AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            $privateKey,
            $encryptKey
        );

        $accessTokenInterval = new DateInterval($this->getAccessTokenExpire($container));

        foreach ($grants as $grant) {
            // Config may set this grant to null. Continue on if grant has been disabled
            if (empty($grant)) {
                continue;
            }

            $authServer->enableGrantType(
                $container->get($grant),
                $accessTokenInterval
            );
        }

        // add listeners if configured
        $this->addListeners($authServer, $container);

        // add listener providers if configured
        $this->addListenerProviders($authServer, $container);

        return $authServer;
    }

    /**
     * Optionally add event listeners
     */
    private function addListeners(
        AuthorizationServer $authServer,
        ContainerInterface $container
    ): void {
        $listeners = $this->getListenersConfig($container);

        foreach ($listeners as $idx => $listenerConfig) {
            $event    = $listenerConfig[0];
            $listener = $listenerConfig[1];
            $priority = $listenerConfig[2] ?? null;
            if (is_string($listener)) {
                if (! $container->has($listener)) {
                    throw new Exception\InvalidConfigException(sprintf(
                        'The second element of event_listeners config at '
                            . 'index "%s" is a string and therefore expected to '
                            . 'be available as a service key in the container. '
                            . 'A service named "%s" was not found.',
                        $idx,
                        $listener
                    ));
                }
                $listener = $container->get($listener);
            }
            $authServer->getEmitter()
                ->addListener($event, $listener, $priority);
        }
    }

    /**
     * Optionally add event listener providers
     */
    private function addListenerProviders(
        AuthorizationServer $authServer,
        ContainerInterface $container
    ): void {
        $providers = $this->getListenerProvidersConfig($container);

        foreach ($providers as $idx => $provider) {
            if (is_string($provider)) {
                if (! $container->has($provider)) {
                    throw new Exception\InvalidConfigException(sprintf(
                        'The event_listener_providers config at '
                            . 'index "%s" is a string and therefore expected to '
                            . 'be available as a service key in the container. '
                            . 'A service named "%s" was not found.',
                        $idx,
                        $provider
                    ));
                }
                $provider = $container->get($provider);
            }
            $authServer->getEmitter()->useListenerProvider($provider);
        }
    }
}
