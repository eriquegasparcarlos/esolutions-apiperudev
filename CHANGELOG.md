# Changelog

## [v1.0.2] - 2026-06-11
### Fixed
- Removed hardcoded `"version"` field from `composer.json` (caused Packagist to skip tags)
- Published to Packagist — `repositories` block no longer needed in consumer projects

## [v1.0.1] - 2025-xx-xx
### Changed
- Internal improvements

## [v1.0.0] - 2025-xx-xx
### Added
- Initial release
- `Service::searchWithInput($type, $number)` — RUC and DNI lookup
- `Service::searchExchangeRateSaleWithInput($date)` — exchange rate by date
- `Service::searchRuc(Request)` / `searchDni(Request)` — controller-ready endpoints
- 5s connect / 10s response timeout, SSL verify disabled for local environments
