<?php

namespace Foutraz\Withings\Tests\Unit\Dto;

use DateTimeImmutable;
use Foutraz\Withings\Dto\Measurement;
use Foutraz\Withings\Enums\MeasureAttribution;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MeasurementTest extends TestCase
{
    private const WEIGHT_MEASURE = ['type' => 1, 'value' => 70500, 'unit' => -3];

    #[Test]
    public function it_exposes_the_provenance_of_a_manually_entered_group(): void
    {
        $measurement = Measurement::fromArray([
            'grpid' => 111,
            'attrib' => 2,
            'date' => 1700000000,
            'created' => 1700003600,
            'category' => 1,
            'deviceid' => null,
        ], self::WEIGHT_MEASURE);

        $this->assertSame(2, $measurement->attrib);
        $this->assertSame(1, $measurement->category);
        $this->assertNull($measurement->deviceId);
        $this->assertSame('2023-11-14T23:13:20+00:00', $measurement->createdAt->format('c'));
        $this->assertSame('2023-11-14T22:13:20+00:00', $measurement->measuredAt->format('c'));
        $this->assertSame(MeasureAttribution::ManualEntry, $measurement->attribution());
    }

    #[Test]
    public function it_exposes_the_device_of_a_device_captured_group(): void
    {
        $measurement = Measurement::fromArray([
            'grpid' => 111,
            'attrib' => 0,
            'date' => 1700000000,
            'deviceid' => 'a1b2c3d4',
        ], self::WEIGHT_MEASURE);

        $this->assertSame('a1b2c3d4', $measurement->deviceId);
        $this->assertSame(MeasureAttribution::Device, $measurement->attribution());
    }

    #[Test]
    public function it_leaves_the_provenance_null_when_the_group_omits_it(): void
    {
        $measurement = Measurement::fromArray(['grpid' => 5, 'date' => 1700000000], self::WEIGHT_MEASURE);

        $this->assertNull($measurement->attrib);
        $this->assertNull($measurement->category);
        $this->assertNull($measurement->deviceId);
        $this->assertNull($measurement->createdAt);
        $this->assertNull($measurement->attribution());
    }

    #[Test]
    public function it_keeps_an_unknown_attrib_code_without_resolving_an_attribution(): void
    {
        $measurement = Measurement::fromArray([
            'grpid' => 5,
            'attrib' => 15,
            'date' => 1700000000,
        ], self::WEIGHT_MEASURE);

        $this->assertSame(15, $measurement->attrib);
        $this->assertNull($measurement->attribution());
    }

    /** @return array<string, array{mixed, ?int}> */
    public static function rawCodes(): array
    {
        return [
            'integer' => [2, 2],
            'zero' => [0, 0],
            'integer-looking string' => ['2', 2],
            'zero string' => ['0', 0],
            'letters' => ['abc', null],
            'empty string' => ['', null],
            'trailing letters' => ['2abc', null],
            'float' => [2.5, null],
            'boolean' => [true, null],
            'array' => [[], null],
        ];
    }

    #[Test]
    #[DataProvider('rawCodes')]
    public function it_keeps_attrib_only_when_the_raw_value_is_an_integer(mixed $raw, ?int $expected): void
    {
        $measurement = Measurement::fromArray(['grpid' => 5, 'attrib' => $raw, 'date' => 1700000000], self::WEIGHT_MEASURE);

        $this->assertSame($expected, $measurement->attrib);
    }

    #[Test]
    #[DataProvider('rawCodes')]
    public function it_keeps_category_only_when_the_raw_value_is_an_integer(mixed $raw, ?int $expected): void
    {
        $measurement = Measurement::fromArray(['grpid' => 5, 'category' => $raw, 'date' => 1700000000], self::WEIGHT_MEASURE);

        $this->assertSame($expected, $measurement->category);
    }

    #[Test]
    public function it_does_not_resolve_a_device_attribution_from_a_non_numeric_attrib(): void
    {
        $measurement = Measurement::fromArray(['grpid' => 5, 'attrib' => 'abc', 'date' => 1700000000], self::WEIGHT_MEASURE);

        $this->assertNull($measurement->attribution());
    }

    #[Test]
    public function it_stays_constructible_with_the_five_original_arguments(): void
    {
        $measurement = new Measurement(1, 1, 70.5, -3, new DateTimeImmutable('@1700000000'));

        $this->assertNull($measurement->attrib);
        $this->assertNull($measurement->attribution());
    }
}
