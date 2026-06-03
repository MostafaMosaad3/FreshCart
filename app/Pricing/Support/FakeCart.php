<?php

namespace App\Pricing\Support;

/**
 * Minimal cart double for strategy unit tests — strategies only ever read `items`,
 * so this keeps those tests free of the database.
 */
class FakeCart
{
    public function __construct(public array $items = []) {}
}
