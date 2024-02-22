<?php

namespace App\Tests\Service;

use App\Service\HtmlFormatter;
use PHPUnit\Framework\TestCase;

class HtmlFormatterTest extends TestCase
{

    public function formatAsAttributesProvider(): array {
        $htmxAttrs = [
            'hx-post' => '/scene/add',
            'hx-target' => '#target',
        ];

        $htmxAttrsExpected = ' hx-post="/scene/add" hx-target="#target"';

        return [
            [$htmxAttrs, $htmxAttrsExpected],
        ];
    }

    /**
     * Test that formatAsAttributes method on class works as expected.
     *
     * @param array $attrs - Attrs to transform into HTML attributes.
     * @param string $expected - The expected attr after transformation.
     *
     * @dataProvider formatAsAttributesProvider
     */
    public function testFormatAsAttributes(array $attrs, string $expected): void {
        $result = HtmlFormatter::formatAsAttributes($attrs);
        $this->assertEquals($expected, $result);
    }
}
