<?php

namespace Foutraz\Withings\Tests\Feature\Actions;

use Foutraz\Withings\Tests\TestCase;
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
}
