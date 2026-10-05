<?php

namespace Foutraz\Withings\Tests\Feature\Actions;

use Foutraz\Withings\Enums\MeasureAttribution;
use Foutraz\Withings\Enums\MeasureCategory;
use Foutraz\Withings\Exceptions\ActionFailed;
use Foutraz\Withings\Tests\TestCase;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ManagesMeasurementsTest extends TestCase
{
    #[Test]
    public function it_maps_every_measure_of_a_group(): void
    {
        $manager = $this->managerWithResponses([
            $this->jsonResponse(200, [
                'status' => 0,
                'body' => [
                    'measuregrps' => [
                        [
                            'grpid' => 111,
                            'date' => 1700000000,
                            'measures' => [
                                ['type' => 1, 'value' => 70500, 'unit' => -3],
                                ['type' => 6, 'value' => 215, 'unit' => -1],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $measurements = $manager->measurements()->getmeas(42);

        $this->assertCount(2, $measurements);
        $this->assertEqualsWithDelta(70.5, $measurements[0]->value, 0.001);
        $this->assertEqualsWithDelta(21.5, $measurements[1]->value, 0.001);
        $this->assertSame(111, $measurements[0]->externalId);
        $this->assertSame(111, $measurements[1]->externalId);
    }

    #[Test]
    public function it_exposes_the_group_provenance_on_each_measurement(): void
    {
        $manager = $this->managerWithResponses([
            $this->page([
                $this->group(111, ['attrib' => 0, 'category' => 1, 'deviceid' => 'a1b2c3d4', 'created' => 1700003600]),
            ]),
        ]);

        $measurement = $manager->measurements()->getmeas(42)[0];

        $this->assertSame(MeasureAttribution::Device, $measurement->attribution());
        $this->assertSame('a1b2c3d4', $measurement->deviceId);
        $this->assertSame(1, $measurement->category);
        $this->assertSame(1700003600, $measurement->createdAt->getTimestamp());
    }

    #[Test]
    public function it_requests_real_measures_by_default(): void
    {
        $manager = $this->managerWithResponses([$this->page([])]);

        $manager->measurements()->getmeas(42);

        $this->assertEquals(
            ['action' => 'getmeas', 'userid' => '42', 'category' => '1'],
            $this->requestBodies()[0],
        );
    }

    #[Test]
    public function it_requests_the_given_category_since_the_last_update(): void
    {
        $manager = $this->managerWithResponses([$this->page([])]);

        $manager->measurements()->getmeas(42, 1700000000, MeasureCategory::UserObjective);

        $this->assertEquals(
            ['action' => 'getmeas', 'userid' => '42', 'category' => '2', 'lastupdate' => '1700000000'],
            $this->requestBodies()[0],
        );
    }

    /** @return array<string, array{?int}> */
    public static function finalPageOffsets(): array
    {
        return [
            'final page without offset' => [null],
            'final page with offset zero' => [0],
        ];
    }

    #[Test]
    #[DataProvider('finalPageOffsets')]
    public function it_follows_the_pages_until_the_last_one(?int $finalPageOffset): void
    {
        $manager = $this->managerWithResponses([
            $this->page([$this->group(1), $this->group(2)], more: 1, offset: 2),
            $this->page([$this->group(3)], more: 0, offset: $finalPageOffset),
        ]);

        $measurements = $manager->measurements()->getmeas(42, 1700000000);

        $this->assertCount(2, $this->requestBodies());
        $this->assertArrayNotHasKey('offset', $this->requestBodies()[0]);
        $this->assertEquals(
            ['action' => 'getmeas', 'userid' => '42', 'category' => '1', 'lastupdate' => '1700000000', 'offset' => '2'],
            $this->requestBodies()[1],
        );
        $this->assertSame([1, 2, 3], array_map(fn ($measurement) => $measurement->externalId, $measurements));
    }

    #[Test]
    public function it_fails_when_more_pages_are_announced_without_an_offset(): void
    {
        $manager = $this->managerWithResponses([
            $this->page([$this->group(1)], more: 1),
        ]);

        $this->expectException(ActionFailed::class);
        $this->expectExceptionMessage('Withings getmeas pagination did not advance.');

        $manager->measurements()->getmeas(42);
    }

    #[Test]
    public function it_fails_when_the_offset_stalls_between_two_pages(): void
    {
        $manager = $this->managerWithResponses([
            $this->page([$this->group(1)], more: 1, offset: 2),
            $this->page([$this->group(2)], more: 1, offset: 2),
        ]);

        $this->expectException(ActionFailed::class);
        $this->expectExceptionMessage('Withings getmeas pagination did not advance.');

        $manager->measurements()->getmeas(42);
    }

    #[Test]
    public function it_raises_the_withings_error_of_a_non_zero_status(): void
    {
        $manager = $this->managerWithResponses([
            $this->jsonResponse(200, ['status' => 503, 'error' => 'Invalid params']),
        ]);

        $this->expectException(ActionFailed::class);
        $this->expectExceptionMessage('Invalid params');

        $manager->measurements()->getmeas(42);
    }

    /**
     * @param array<string, mixed> $provenance
     * @return array<string, mixed>
     */
    private function group(int $groupId, array $provenance = []): array
    {
        return [
            'grpid' => $groupId,
            'date' => 1700000000,
            'measures' => [['type' => 1, 'value' => 70500, 'unit' => -3]],
            ...$provenance,
        ];
    }

    /** @param array<int, array<string, mixed>> $groups */
    private function page(array $groups, ?int $more = null, ?int $offset = null): Response
    {
        $body = ['measuregrps' => $groups];

        if ($more !== null) {
            $body['more'] = $more;
        }

        if ($offset !== null) {
            $body['offset'] = $offset;
        }

        return $this->jsonResponse(200, ['status' => 0, 'body' => $body]);
    }
}
