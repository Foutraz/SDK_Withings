<?php

namespace Foutraz\Withings\Actions;

use Foutraz\Withings\Dto\Measurement;
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
    public function getmeas(int $userid, ?int $lastUpdate = null): array
    {
        $payload = [
            'action' => 'getmeas',
            'userid' => $userid,
        ];

        if ($lastUpdate !== null) {
            $payload['lastupdate'] = $lastUpdate;
        }

        $response = $this->post('https://wbsapi.withings.net/measure', $payload);

        $measuregrps = $response['body']['measuregrps'] ?? [];

        $measurements = [];

        foreach ($measuregrps as $grp) {
            foreach (($grp['measures'] ?? []) as $measure) {
                $measurements[] = Measurement::fromArray($grp, $measure);
            }
        }

        return $measurements;
    }
}
