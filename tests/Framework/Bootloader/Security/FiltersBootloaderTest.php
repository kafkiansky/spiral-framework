<?php

declare(strict_types=1);

namespace Framework\Bootloader\Security;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\App\Filters\SomeFilter;
use Spiral\Bootloader\Security\FiltersBootloader;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Filter\InputScope;
use Spiral\Filters\Config\FiltersConfig;
use Spiral\Filters\FilterProvider;
use Spiral\Filters\FilterProviderInterface;
use Spiral\Filters\InputInterface;
use Spiral\Filters\Interceptors\AuthorizeFilterInterceptor;
use Spiral\Filters\Interceptors\PopulateDataFromEntityInterceptor;
use Spiral\Filters\Interceptors\ValidateFilterInterceptor;
use Spiral\Tests\Framework\BaseTest;

final class FiltersBootloaderTest extends BaseTest
{
    public function testFilterProviderInterface(): void
    {
        $this->assertContainerBoundAsSingleton(
            FilterProviderInterface::class,
            FilterProvider::class
        );
    }

    public function testInputInterface(): void
    {
        $this->assertContainerBoundAsSingleton(
            InputInterface::class,
            InputScope::class
        );
    }

    public function testInitConfig(): void
    {
        $this->assertConfigMatches(
            FiltersConfig::CONFIG,
            [
                'interceptors' => [
                    PopulateDataFromEntityInterceptor::class,
                    ValidateFilterInterceptor::class,
                    AuthorizeFilterInterceptor::class,
                ],
            ]
        );
    }

    public function testAddsInjector(): void
    {
        $bootloader = $this->getContainer()->get(FiltersBootloader::class);

        $bootloader->addInterceptor('new_interceptor');

        $this->assertConfigMatches(
            FiltersConfig::CONFIG,
            [
                'interceptors' => [
                    PopulateDataFromEntityInterceptor::class,
                    ValidateFilterInterceptor::class,
                    AuthorizeFilterInterceptor::class,
                    'new_interceptor',
                ],
            ]
        );
    }

    public function testFilterInjector()
    {
        $bootloader = $this->getContainer()->get(FiltersBootloader::class);
        $bootloader->addInterceptor('new_interceptor');
        $request = $this->mockContainer(ServerRequestInterface::class);
        $request->shouldReceive('getParsedBody')->andReturn([
            'foo' => 'bar',
        ]);
        $request->shouldReceive('getQueryParams')->andReturn([
            'baz' => 'bar1',
        ]);

        $interceptor = $this->mockContainer(
            'new_interceptor',
            CoreInterceptorInterface::class
        );

        $interceptor->shouldReceive('process')
            ->once()
            ->andReturnUsing(static function (
                string $controller,
                string $action,
                array $parameters,
                CoreInterface $core
            ) {
                return $core->callAction($controller, $action, $parameters);
            });

        $filter = $this->getContainer()->get(SomeFilter::class);

        $this->assertSame('bar', $filter->foo);
        $this->assertSame('bar1', $filter->baz);
    }
}
