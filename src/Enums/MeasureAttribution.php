<?php

namespace Foutraz\Withings\Enums;

enum MeasureAttribution: int
{
    case Device = 0;
    case DeviceAmbiguous = 1;
    case ManualEntry = 2;
    case ManualAtAccountCreation = 4;
    case AutomaticBloodPressure = 5;
    case ConfirmedByUser = 7;
    case SameAsDevice = 8;

    public function isDeviceCaptured(): bool
    {
        return match ($this) {
            self::Device,
            self::DeviceAmbiguous,
            self::AutomaticBloodPressure,
            self::ConfirmedByUser,
            self::SameAsDevice => true,
            self::ManualEntry,
            self::ManualAtAccountCreation => false,
        };
    }
}
