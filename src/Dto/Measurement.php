<?php

namespace Foutraz\Withings\Dto;

use DateTimeImmutable;
use Foutraz\Withings\Enums\MeasureAttribution;

final readonly class Measurement
{
    public function __construct(
        public int $externalId,
        public int $type,
        public float $value,
        public int $unit,
        public DateTimeImmutable $measuredAt,
        public ?int $attrib = null,
        public ?int $category = null,
        public ?string $deviceId = null,
        public ?DateTimeImmutable $createdAt = null,
    ) {}

    public function attribution(): ?MeasureAttribution
    {
        return $this->attrib === null ? null : MeasureAttribution::tryFrom($this->attrib);
    }

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
            $unit,
            new DateTimeImmutable('@'.($grp['date'] ?? 0)),
            isset($grp['attrib']) ? (int) $grp['attrib'] : null,
            isset($grp['category']) ? (int) $grp['category'] : null,
            isset($grp['deviceid']) ? (string) $grp['deviceid'] : null,
            isset($grp['created']) ? new DateTimeImmutable('@'.$grp['created']) : null,
        );
    }
}
