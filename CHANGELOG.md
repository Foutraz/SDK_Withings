# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-10-05

### Added

- `Measurement::$attrib`, `$category`, `$deviceId` and `$createdAt`, read from the measure group, all nullable and optional so the 1.0 constructor keeps working.
- `Measurement::attribution()`, resolving `$attrib` to a `MeasureAttribution` or `null` for an unknown code.
- `MeasureAttribution` enum with `isDeviceCaptured()`, and `MeasureCategory` enum.
- PHPUnit test suite replaying Guzzle mock responses.

### Changed

- `getmeas` requests real measures only by default; pass `MeasureCategory::UserObjective` to request user objectives.

### Fixed

- `getmeas` follows the `more` / `offset` pages until the last one and throws `ActionFailed` when the offset does not advance.

## [1.0.0]

Initial release.
