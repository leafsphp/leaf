<!-- markdownlint-disable no-duplicate-header -->
# Changelog

## v2.4.3 - 🎋 Giant Cane Grass - 26th February 2021

### Added

- Updated `Leaf\Db` and `Leaf\Auth` to throw dev errors to Leaf's error handler for better error reporting

### Fixed

- Organized methods in `Leaf\FS`

### Changed

- Made `Leaf\Http\Response` static
- Made `Leaf\Http\Request` static

### Removed

- No removals

## v2.4.2 - 🥬 Desert Wishbone-bush - 3rd February 2021

This version of Leaf continues the goal of making Leaf features more flexible and increasing usability.

### Added

- Added option to turn off experimental method warnings

- Added `Form::rule` which allows you to create your own rules for form validation.

```php
Form::rule("max", function($field, $value, $params) {
    if (strlen($value) > $params) {
        Form::addError($field, "$field can't be more than $params characters");
        return false;
    }
});
```

- Added internal `Leaf\Form` feature which allows you to pass parameters to validation rules.

```php
$validation = Form::validate([
    // To pass a param to a rule, just use :
    "username" => "max:3",
]);
```

- Added `Form::addError` which allows you to add errors to be returned in `Form::errors()`

```php
Form::addError($field, "$field can't be more than $params characters");
```

- Added max and min rules by default

```php
$validation = Form::validate([
    "username" => "max:1",
    "password" => "min:81",
]);
```

- Guards can be used even in API mode. This will alert you if you're not eligible to view a particular page.

### Fixed

- Updated dependencies with security patches

- Fixed multiple validation break from v2.4.2 beta.

### Changed

- Made `Leaf\Form` methods static. They can now be called from anywhere within your Leaf app.

### Removed

- No removals

## v2.4.2 [BETA] - 🥬 Desert Wishbone-bush (Beta) - 20th January 2021

This release mainly focuses on security patches for all Leaf based libraries. It contains updated dependencies and internal code patches to make your apps even more secure.

### Added

- No additions

### Fixed

- Updated dependencies with security patches

### Changed

- Made `Leaf\Auth` methods static. They can now be called from anywhere within your Leaf app.

### Removed

- No removals

## v2.4.1 - 🍁 Marvel-of-peru - 12th January 2020

v2.4.1 continues the usability reforms from the previous versions. It also contains fixes for all bugs discovered in previous versions as well as new features.

**Please update from v2.4.0 to v2.4.1 to fix any issues you encountered with the system. Any inconveniences are deeply regretted🙏.**

### Added

- Added support for session based authenticatication instead of just JWT
- Added `Route::view`

### Fixed

- Fixed all known bugs from previous versions

### Changed

- Separated Router module from app module
- Made all `Leaf\Http\Session` methods static

### Removed

- Removed app down feature

## v2.4.0 - Christmas Tree🎄 - 18th December 2020 ***DELETED***

**Please update from v2.4.0 to v2.4.1 to fix any issues you encountered with the system. Any inconveniences are deeply regretted🙏. This release has officially been deleted.**

Christmas tree follows up on the previous beta release, fixes up all bugs found during the beta testing phase and packs in newer extensions that make Leaf even more usable.

### Added

- Added base factory class for Leaf MVC, Leaf API and Skeleton
- Added new auth setting options

### Fixed

- Fixed `Leaf\Db` callstack not clearing
- Fixed `Auth::update` db errors
- Fixed `Auth::update` including current user in uniques check
- Fixed password verify method params

### Changed

- Switched default password encryption to `PASSWORD_DEFAULT` (bcrypt by default)
- Auth now relies on Leaf password helper for everything password related
- Standardized all `where` type methods on `Leaf\Db`
- Seperated password encoding and password verifying settings in `Leaf\Auth`
- Switched password helper methods to camelCase
- Switched password `salt` with `spice` to add additional security to passwords

### Removed

- Removed unnecessary methods from password helper

## v2.4.0 - BETA - 30th November, 2020

Unlike previous versions, this version of Leaf is focusing on improving the use of existing features, rather than just pumping new magic into Leaf. It has a lot of bug fixes, standardization of method names and overall upgrades.

### Added

- Added `App::evadeCors`
- Added `App::routes` to preview all routes
- Added `Db::first()`
- Leaf DB can now detect query type even when `query`
- Added `orWhere`, `whereLike`, `orWhereLike` `like`, `orLike`, `orderBy`, `all` `limit` and LIKE helpers to Leaf Db
- Added new format to `Date::now`
- Added `Auth::update`
- Added custom token lifetime support on `Auth`

### Fixed

- Fixed login bug with `Auth::currentUser`
- Fixed Leaf DB same value bug
- Minor fixes on `Auth::login` and `Auth::register`

### Changed

- Switched methods to camel case
- Renamed `Auth::useToken` to `Auth::id`
- Renamed `Auth::currentUser` to `Auth::user`
- Made `Helpers\JWT` and `Helpers\Authentication` methods static

### Removed

- Removed `Form::isEmpty` and `Form::isNull`
- Removed deprecated methods from `Response`
- Removed deprecated methods from `Date`

**This list is still being updated.**

## v2.1.0 - 19th June, 2020

### Added

- Added `Leaf\Auth::auto_connect`
- Added default bypass for CORS errors
- Added `Mysqli::auto_connect`
- Added optional `db_type` option to `Leaf\Db\PDO` connection
- Added `PDO::auto_connect`
- Added deprecation warning for `Leaf\Db\PDO`

### Fixed

### Changed

### Removed

- Removed Leaf\Wynter

## v2.1.0 - alpha - 24th May, 2020

### Added

- Added Route::resource
- Added Session::retrieve

### Fixed

### Changed

- Seperated Leaf Veins from Leaf Package
- Renamed Session::getBody to Session::body

### Removed

- Removed Leaf\View

## v2.0 - official - 21st April, 2020

### Added

- Added Leaf Mail
- Added Date::days_ago
- Added Date::months_ago
- Added Date::years_ago
- Added Date::day
- Added Date::month
- Added Date::year
- Added Auth::setSecretKey
- Added Auth::getSecretKey
- Added Auth::validate
- Added Leaf JS Scripts [BETA]
- Added Leaf Envryption Helper [BETA]
- Added Leaf Password Helper [BETA]
- Added secret key for token encryption in Leaf Authentication

### Fixed

- Fixed Request::params
- Fixed Request::getBody
- Fixed Request Method Tests
- Fixes to Auth::validateToken
- Fixed bugs with Leaf DB packages
- Fixed bugs on Auth::login and register
- Fixed base64 security issues on Leaf Token [BETA]
- Fixes on Form::isEmpty and isNull

### Changed

- Renamed Request `getBody` to `body`
- Switched all `Date` methods to `snake_case`
- Switched `FS` methods to `snake_case`
- Shortened `Date` method names (Find out more in the [docs](https://leafphp.netlify.com/))
- Made Leaf Authentication a helper (Leaf\Helper\Authentication)

### Removed

- Removed Response::count
- Removed Response::getIterator
- Removed Response header offeset methods

## v2.0 - beta - 11th March, 2020

### Added

- Added DB->choose
- Added DB->add
- Added Auth->login
- Added Auth->register
- Added Session->unset
- Added custom constructor to response
- Added Response->messages(Http codes)
- Added Response->setStatus/getStatus/status
- Added Response->setHeader/getHeader/header
- Added Response->setCookie/deleteCookie
- Added Response->redirect
- Added Request type checks
- Added Request->cookies
- Added Request->headers
- Added Response Helpers
- Added Leaf\Headers
- Added Leaf\Cookies
- Added ContentTypes Middleware
- Added Flash messaging Middleware
- Added PrettyExceptions Middleware
- Added Logwriter and Log
- Added Leaf View
- Merged the Leaf Veins Templating engine and Leaf Core
- Added Support for blade templating with Leaf Blade
- Added support for more request types on Leaf::Request
- Added Form::validateField
- Provided security against XSS
- Added Form::submit

### Fixed

- Fixed SESSION->id
- Fixed headers bug with Response->respondWithCode
- Fixed headers bug with Response->throwErr

### Changed

- Changed Leaf\Core namespace to Leaf
- Changed Session->remove to Session->unset

### Removed

- Removed Auth->basicLogin
- Removed Auth->emailLogin
- Removed Auth->basicRegister

## v1.5.0 - 11th December, 2019

### Added

- Added FS->deleteFolder
- Added FS->deleteFile
- Added Form->validate😅
- Added Form->validate and return errors to base controllers
- Added Leaf\Core\Str: equivalent of Illuminate\Support\Str with added methods
- Added Leaf Mysqli🤔
- Added Leaf PDO🤔
- Added Leaf\Core\Auth: simple login and signup

### Fixed

- Fixed FS->deleteFile
- Fixed FS->listDir
- Fixed Leaf DB
- Fixed init bug with session

### Changed

- Renamed Veins->renderTemplate to render
- Rename veins->assign to set()
- Renamed mkdir to createFolder
- Renamed mkdirInBase to createFolderInBase
- Renamed renameDir to renameFolder
- Changed vein file extension from .vein to .vein.php
- Split Leaf\Config\Db between Leaf\Core\Db\Mysqli and Leaf\Core\Db\PDO
- Changed `renderHtml` to `renderPage`
- Changed all `getParam`s to `get`

### Removed

Nothing was removed

## v1.4.2 - 13th November, 2019

### Added

- Added FileSystem module
- Added `mysqliQuery` method to `leaf\config\db`
- Added a bunch of handy session methods
- Added leaf token
- Added leaf form

### Fixed

- Fixed  a few problems with `leaf\config\db`;
- Fixed tiny bug with `response->throwErr`

### Changed

- Changed `leaf\config\db`: connection variables and connection type are set on db init. `$db = new db($host, $user, $password, $dbname, "PDO")`
- Renamed renderHtmlPage to renderHtml

### Removed

- Leaf\Config\DB has been depricated for now

## v1.4.1 - 1st November, 2019

### Added

- Added base Leaf Controller `Leaf\Core\Controller`
- Added base controller for APIs: `Leaf\Core\ApiController`
- Added base Leaf Model `Leaf\Core\Model`
- Added support for full MVC app
- Added [Leaf Veins](https://github.com/leafsphp/veins) in default Leaf package
- Added Error Handling for development and production
- Added a base database layer connected with custom environment variables

### Fixed

- Fixed bug with `Response::renderHtmlPage()`
- Fixed the HTTP code rendering in the browser from `Response::respondWithCode`

### Changed

- Changed `Validation` to `Form`

### Removed

- Removed Leaf `Exceptions`
- Removed Middleware interfaces
