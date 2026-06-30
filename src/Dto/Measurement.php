<?php

namespace Foutraz\Withings\Dto;

use DateTimeImmutable;

class Measurement
{
    public function __construct(
        public int $externalId,
        public int $type,
        public float $value,
        public DateTimeImmutable $measuredAt,
    ) {}

    /**
     * @param array<string, mixed> $grp
     * @param array<string, mixed> $measure
     */
    public static function fromArray(array $grp, array $measure): self
    {
        $rawValue = (int) ($measure['value'] ?? 0);
        $unit = (int) ($measure['unit'] ?? 0);
        $value = $rawValue * (10 ** $unit);

        return new self(
            (int) ($grp['grpid'] ?? 0),
            (int) ($measure['type'] ?? 0),
            (float) $value,
            new DateTimeImmutable('@'.($grp['date'] ?? 0)),
        );
    }
}
