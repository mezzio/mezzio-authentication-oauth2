<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use Generator;
use Mezzio\Authentication\OAuth2\Response\CallableResponseFactoryDecorator;
use Mezzio\Container\ResponseFactoryFactory;
use MezzioTest\Authentication\OAuth2\TestAsset\Psr17ResponseFactoryTraitImplementation;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class Psr17ResponseFactoryTraitTest extends TestCase
{
    private Psr17ResponseFactoryTraitImplementation $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new Psr17ResponseFactoryTraitImplementation();
    }

    /**
     * @psalm-return Generator<non-empty-string,array{0:array<string,mixed>}>
     */
    public function configurationsWithOverriddenResponseInterfaceFactory(): Generator
    {
        yield 'default' => [
            [
                'dependencies' => [
                    'factories' => [
                        ResponseInterface::class
                            => fn(): ResponseInterface => $this->createMock(ResponseInterface::class),
                    ],
                ],
            ],
        ];

        yield 'aliased' => [
            [
                'dependencies' => [
                    'aliases' => [
                        ResponseInterface::class => 'CustomResponseInterface',
                    ],
                ],
            ],
        ];

        yield 'delegated' => [
            [
                'dependencies' => [
                    'delegators' => [
                        ResponseInterface::class => [
                            fn(): ResponseInterface => $this->createMock(ResponseInterface::class),
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testWillUseResponseFactoryInterfaceFromContainerWhenApplicationFactoryIsNotOverridden(): void
    {
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $container       = new InMemoryContainer();
        $container->set('config', [
            'dependencies' => [
                'factories' => [
                    ResponseInterface::class => ResponseFactoryFactory::class,
                ],
            ],
        ]);
        $container->set(ResponseFactoryInterface::class, $responseFactory);
        $detectedResponseFactory = ($this->factory)($container);
        self::assertSame($responseFactory, $detectedResponseFactory);
    }

    /**
     * @param array<string,mixed> $config
     * @dataProvider configurationsWithOverriddenResponseInterfaceFactory
     */
    public function testWontUseResponseFactoryInterfaceFromContainerWhenApplicationFactoryIsOverriden(
        array $config
    ): void {
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $container       = new InMemoryContainer();
        $container->set('config', $config);
        $container->set(ResponseFactoryInterface::class, $responseFactory);
        $response = $this->createMock(ResponseInterface::class);
        $container->set(ResponseInterface::class, static fn(): ResponseInterface => $response);

        $detectedResponseFactory = ($this->factory)($container);
        self::assertNotSame($responseFactory, $detectedResponseFactory);
        self::assertInstanceOf(CallableResponseFactoryDecorator::class, $detectedResponseFactory);
        self::assertEquals($response, $detectedResponseFactory->getResponseFromCallable());
    }
}
