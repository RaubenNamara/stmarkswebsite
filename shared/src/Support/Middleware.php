<?php

declare(strict_types=1);

namespace StMarks\Shared\Support;

/**
 * Base middleware contract. $context is whatever object Router instantiated for the route
 * (typically a Controller) so a middleware can call back into it (e.g. $context->error(...)).
 */
abstract class Middleware
{
    public function __construct(protected ?object $context = null)
    {
    }

    /**
     * Return true to let the request continue, false to stop it (the middleware itself is
     * responsible for having already sent a response, e.g. via $this->context->error(...)).
     */
    abstract public function handle(): bool;
}
