<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework;

use Spiral\Snapshots\SnapshotInterface;
use Spiral\Snapshots\SnapshotterInterface;

class SnapshotTest extends BaseTest
{
    public function testStringConfigParams()
    {
        // string important. Emulating string from .env
        $app = $this->makeApp([
            'SNAPSHOT_MAX_FILES' => '1',
            'SNAPSHOT_VERBOSITY' => '1'
        ]);

        $this->assertInstanceOf(SnapshotterInterface::class, $app->get(SnapshotterInterface::class));
    }

    public function testSnapshot(): void
    {
        $app = $this->makeApp();

        try {
            throw new \Error('test error');
        } catch (\Error $e) {
            /** @var SnapshotInterface $s */
            $s = $app->get(SnapshotterInterface::class)->register($e);
        }

        $this->assertInstanceOf(\Error::class, $s->getException());
    }
}
