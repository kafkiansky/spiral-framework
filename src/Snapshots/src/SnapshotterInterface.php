<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Snapshots;

interface SnapshotterInterface
{
    /**
     * Register exception and return snapshot instance to represent error.
     */
    public function register(\Throwable $e): SnapshotInterface;
}
