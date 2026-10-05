<?php

namespace Foutraz\Withings\Actions;

use Foutraz\Withings\Dto\Measurement;
use Foutraz\Withings\Enums\MeasureCategory;
use Foutraz\Withings\Exceptions\ActionFailed;
use Foutraz\Withings\Exceptions\InvalidData;
use Foutraz\Withings\Exceptions\ResourceNotFound;
use Foutraz\Withings\Exceptions\TooManyRequestsException;
use Foutraz\Withings\Exceptions\Unauthorized;
use Foutraz\Withings\WithingsManager;
use GuzzleHttp\Exception\GuzzleException;

class ManagesMeasurements extends WithingsManager
{
    /**
     * @return array<int, Measurement>
     *
     * @throws ActionFailed
     * @throws GuzzleException
     * @throws InvalidData
     * @throws ResourceNotFound
     * @throws TooManyRequestsException
     * @throws Unauthorized
     */
    public function getmeas(int $userid, ?int $lastUpdate = null, MeasureCategory $category = MeasureCategory::Real): array
    {
        $payload = ['action' => 'getmeas', 'userid' => $userid, 'category' => $category->value];

        if ($lastUpdate !== null) {
            $payload['lastupdate'] = $lastUpdate;
        }

        $measurements = [];
        $offset = 0;

        do {
            $body = $this->postForm('https://wbsapi.withings.net/measure', $payload)['body'] ?? [];

            foreach ($body['measuregrps'] ?? [] as $grp) {
                foreach ($grp['measures'] ?? [] as $measure) {
                    $measurements[] = Measurement::fromArray($grp, $measure);
                }
            }

            $hasMore = (bool) ($body['more'] ?? false);

            if ($hasMore) {
                $offset = $this->nextOffset($body, $offset);
                $payload['offset'] = $offset;
            }
        } while ($hasMore);

        return $measurements;
    }

    /**
     * @param array<string, mixed> $body
     *
     * @throws ActionFailed
     */
    private function nextOffset(array $body, int $previous): int
    {
        $offset = (int) ($body['offset'] ?? 0);

        if ($offset <= $previous) {
            throw new ActionFailed('Withings getmeas pagination did not advance.');
        }

        return $offset;
    }
}
