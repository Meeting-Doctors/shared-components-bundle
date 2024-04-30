<?php

declare(strict_types=1);

namespace SharedBundle\Tests\UI\Http\Rest\Request;

use PHPUnit\Framework\TestCase;
use SharedBundle\UI\Http\Rest\Request\Input;

final class InputTest extends TestCase
{
    public function test_must_merge_two_inputs(): void
    {
        $some = Input::empty();
        $another = $some->merge(Input::kv('foo', 'bar'));

        self::assertSame(['foo' => 'bar'], $another->values);
        self::assertTrue($another->has('foo'));
        self::assertSame('bar', $another->get('foo'));
        self::assertNull($another->get('bar'));
    }

    public function test_must_serialize(): void
    {
        $input = Input::deserialize(['foo' => 'bar']);

        self::assertSame(['foo' => 'bar'], $input->serialize());
    }
}
