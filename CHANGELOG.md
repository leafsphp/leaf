## Changelog
### v1.3.0 - 24th October, 2019
#### Added
- Added support for native `GET` requests in `Request::getParam`
- Added support for native `GET` requests in `Request::getBody`
- Added `setTimeZone()` to Date
- Added `getTimeZone()` to Date
- Added `now()` to Date
- Added `randomDate` to Date
- Added session support
- Added form data `POST` support


#### Fixed
- Fixed up the `Date::timestamp` method
- Fixed up `Date::getDayFromNumber`
- Fixed `getBearerToken`
- Fixed `getAuthorizationHeader`


#### Changed
- Changed `CustomDate` to `Date`
- Renamed `Date::timestamp` to `Date::randomTimestamp`
- Changed `generateToken` params to `generateToken($payload, $secret_phrase)`
- Moved `request` and `response` to http folder inside `core`


#### Removed
- Removed the `isEmpty`, `isEmptyOrNull` and `returnEmptyOrNull` methods from `Validation`