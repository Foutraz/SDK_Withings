<?php

namespace Foutraz\Withings\Tests\Unit\Enums;

use Foutraz\Withings\Enums\MeasureAttribution;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MeasureAttributionTest extends TestCase
{
    /** @return array<string, array{int, bool}> */
    public static function attributionCodes(): array
    {
        return [
            'device' => [0, true],
            'device ambiguous' => [1, true],
            'manual entry' => [2, false],
            'manual at account creation' => [4, false],
            'automatic blood pressure' => [5, true],
            'confirmed by user' => [7, true],
            'same as device' => [8, true],
        ];
    }

    #[Test]
    #[DataProvider('attributionCodes')]
    public function it_tells_whether_the_code_is_device_captured(int $code, bool $expected): void
    {
        $this->assertSame($expected, MeasureAttribution::from($code)->isDeviceCaptured());
    }
}
