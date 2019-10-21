## Changelog
### New Ver - Release Date
#### Added
- Added support for native `GET` requests in `Request::getParam`
- Added support for native `GET` requests in `Request::getBody`
- Added `setTimeZone()` to LeafDate
- Added `getTimeZone()` to LeafDate
- Added `now()` to LeafDate
- Added `randomDate` to LeafDate


#### Fixed
- Fixed up the LeafDate::timestamp method
- Fixed up LeafDate::getDayFromNumber
- Fixed getBearerToken
- Fixed getAuthorizationHeader


#### Changed
- Changed CustomDate to LeafDate
- Renamed LeafDate::timestamp to LeafDate::randomTimestamp
- Changed generateToken params to generateToken($payload, $secret_phrase)


#### Removed
- Removed the `isEmpty`, `isEmptyOrNull` and `returnEmptyOrNull` methods from `Validation`