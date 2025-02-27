<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\SendIt\Bootloader;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Bootloader\Jobs\JobsBootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Jobs\JobRegistry;
use Spiral\Jobs\QueueInterface;
use Spiral\Jobs\ShortCircuit;
use Spiral\Mailer\MailerInterface;
use Spiral\Queue\Bootloader\QueueBootloader;
use Spiral\Queue\HandlerRegistryInterface;
use Spiral\Queue\QueueConnectionProviderInterface;
use Spiral\SendIt\Config\MailerConfig;
use Spiral\SendIt\JsonJobSerializer;
use Spiral\SendIt\MailQueue;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailer;
use Symfony\Component\Mailer\Transport;

/**
 * Enables email sending pipeline.
 */
class MailerBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        JobsBootloader::class,
        QueueBootloader::class,
    ];

    protected const SINGLETONS = [
        SymfonyMailer::class => [self::class, 'mailer'],
    ];

    /** @var ConfiguratorInterface */
    private $config;

    public function __construct(ConfiguratorInterface $config)
    {
        $this->config = $config;
    }

    public function boot(EnvironmentInterface $env, AbstractKernel $kernel): void
    {
        $queue = $env->get('MAILER_QUEUE', $env->get('MAILER_PIPELINE', 'local'));

        $this->config->setDefaults(MailerConfig::CONFIG, [
            'dsn' => $env->get('MAILER_DSN', ''),
            'pipeline' => $queue,
            'queue' => $queue,
            'from' => $env->get('MAILER_FROM', 'Spiral <sendit@local.host>'),
            'queueConnection' => $env->get('MAILER_QUEUE_CONNECTION'),
        ]);
    }

    public function start(Container $container): void
    {
        if ($container->has(JobRegistry::class)) {
            // Will be removed since v3.0
            $registry = $container->get(JobRegistry::class);
            $registry->setHandler(MailQueue::JOB_NAME, \Spiral\SendIt\Jobs\MailJobAdapter::class);
            $registry->setSerializer(MailQueue::JOB_NAME, JsonJobSerializer::class);

            $container->bindSingleton(
                MailerInterface::class,
                static function (MailerConfig $config) use ($container) {
                    if ($config->getQueueConnection() === 'sync') {
                        $queue = $container->get(ShortCircuit::class);
                    } else {
                        $queue = $container->get(QueueInterface::class);
                    }

                    return new MailQueue($config, $queue);
                }
            );
        } else {
            $container->bindSingleton(
                MailerInterface::class,
                static function (MailerConfig $config, QueueConnectionProviderInterface $provider) {
                    return new MailQueue(
                        $config,
                        $provider->getConnection($config->getQueueConnection())
                    );
                }
            );
        }

        if ($container->has(HandlerRegistryInterface::class)) {
            $registry = $container->get(HandlerRegistryInterface::class);
            $registry->setHandler(MailQueue::JOB_NAME, \Spiral\SendIt\MailJob::class);
        }
    }

    public function mailer(MailerConfig $config): SymfonyMailer
    {
        return new Mailer(
            Transport::fromDsn($config->getDSN())
        );
    }
}
