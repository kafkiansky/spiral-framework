<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\SendIt;

use Spiral\Mailer\MessageInterface;
use Symfony\Component\Mime\Email;

interface RendererInterface
{
    public function render(MessageInterface $message): Email;
}
