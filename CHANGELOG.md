<!-- markdownlint-disable no-duplicate-header -->
# Changelog

## v2.6.0 - ⚰️ The Goodbye Flower - 20th September, 2021

## Added

- Added UUID support to Leaf Auth
- Added support for custom id keys in Leaf Auth

### Fixed

- Fixed Request::getUrl
- Fixed issue [#53](https://github.com/leafsphp/leaf/issues/53)
- Fixed Database

### Removed

- Removed Leaf blade component

## v2.5.1 - 💠 Lilac - 30th May, 2021

### Fixed

- Fixed PHP 7.3 unsupported types
- Fixed server base path on router
- Fixed bare UI config method
- Fixed faker namespace

### Changed

- Removed BETA flag from Leaf password helper

### Removed

- Removed Leaf blade component

## v2.5.0 - 💠 Gladiolus - 27th April, 2021

### Added

- Leaf debug now controls error reporting (you don't want nasty errors showing in production)
- Added `Request::try`
- Added `app.down` config
- Added Leaf app instance on `Config`
- Added grouped namespaces to router
- Added single route namespaces
- Added named routes to router
- Added router `push` for switching between pages
- Added more customizations on `Leaf\Database`
- Added simple flash messaging with `Leaf\Flash`
- Added `flash` method to session
- Added HTTP caching on `Leaf\Http\Headers`

### Fixed

- Fixed inverted condition for showing default development/production error pages.
- Fixed router hooks
- Added proper types on `App` and `Router`
- Added proper controller and missing method warnings
- Fixed incorrect method labeling
- Fixed HTTP caching issues
- Fixed app logger and app log writer
- Fixed app break after non-existent middlware call

### Changed

- Switched debugging controls from `mode` to `debug`
- Default 404 page now automatically loaded
- Router middleware `App` instance now automatically loaded
- Added null response for unset session variables
- Leaf error handler now loads on `App` init
- Default error 500 handler now automatically loaded
- Updated leaf container

### Removed

- Removed unnecessary code from `App`
- Removed app name
- Removed `view` method on app and router
- Removed previous hook support on app
- Removed unused router config
- Removed Leaf environment class
- Removed unused default middleware
- `Leaf\Blade` no longer comes with Leaf by default.
- Removed `status` and `contentType` on Leaf\App

## v2.5.0-beta - 💠 Gladiolus (BETA) - 16th April, 2021

### Added

- Added `app.down` config
- Added Leaf app instance on `Config`
- Added grouped namespaces to router
- Added named routes to router
- Added router group prefixes
- Added router `push` for switching between pages
- Added more customizations on `Leaf\Database`
- Added simple flash messaging with `Leaf\Flash`
- Added `flash` method to session
- Added HTTP caching on `Leaf\Http\Headers`

### Fixed

- Fixed router hooks
- Added proper types on `App` and `Router`
- Added proper controller and missing method warnings
- Fixed incorrect method labeling
- Fixed HTTP caching issues
- Fixed app logger and app log writer
- Fixed app break after non-existent middlware call

### Changed

- Default 404 page now automatically loaded
- Router middleware `App` instance now automatically loaded
- Added null response for unset session variables
- Leaf error handler now loads on `App` init
- Default error 500 handler now automatically loaded
- Updated leaf container

### Removed

- Removed unnecessary code from `App`
- Removed app name
- Removed `view` method on app and router
- Removed previous hook support on app
- Removed unused router config
- Removed Leaf environment class
- Removed unused default middleware
- `Leaf\Blade` no longer comes with Leaf by default.
- Removed `status` and `contentType` on Leaf\App

## v2.4.4 - 🎋 Common Reed - 23rd March 2021

### Added

- Added `Leaf\Config` for easier configuration
- Added new leaf config options
- Added `Leaf\View`
- Added support for multiple template engines concurrently
- Added BareUI templating engine

### Fixed

- Internal code improvements on App

### Changed

- No renames, restructures, ...

### Removed

- No removals

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

**Please update from v2.4.0 to v2.4.1 to fix any issues you encountered with this version. Any inconveniences are deeply regretted🙏. This release has officially been deleted.**

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

## v2.3.0 - Lucky Charm🍀 - Aug 15, 2020

### Added

- Added Leaf\Auth::useToken
- Added Leaf\FS::upload_file
- Added manual init to Leaf\Session
- Added option for status code messages
- Added callable utils
- Added session encoding/decoding
- Leaf\Http\Request now catches files passed into request
- Added Leaf\Http\Request::typeIs
- Leaf\Http\Request::get can now return multiple request data at once
- Added Leaf\Http\Request::files
- New Leaf\Http\Headers package
- More untracked additions

### Fixed

- fixed Leaf\Http\Headers
- Fixed response http status codes bug
- Fixed header integration with response
- Fixed header reliance on Set
- Fixed throwErr code error
- Fixed Leaf\Session package
- Fixed response redirect
- Fixed Leaf\Http\Request::body bugs
- Sessions return false instead of throwing errors (Fix for web apps)
- FS returns false instead of throwing errors
- Fixed up Leaf\Http\Request::params
- Fixed up Leaf\Http\Request::hasHeader
- Fixed up header related methods on Leaf\Http\Request
- Fixed bugs on Leaf\Environment
- More untracked fixes

### Changed

- Switched Leaf\Session to native PHP sessions
- Switched session package in Leaf\App
- Changed controller file uploads to Leaf\FS
- Leaf\Date methods can now be called static-ly
- Switched Leaf\Date methods to camel case, but- with backward compatability for snake_case
- Made all Leaf\FS methods static

### Removed

- Removed old session code
- Removed setEncryptedCookie and getEncryptedCookie- on Leaf\App
- Slashed unnecessary code from Leaf\Http\Request
- Slashed unnecessary code from Leaf\Http\Session
- Slashed unnecessary code from Leaf\Http\Cookie
- Slashed unnecessary code from Leaf\Http\Response
- Removed all method type tests from Leaf\Http\Request

## v2.2.0 - Angel's Trumpet - Jul 7, 2020

### Added

- Added `Leaf\Auth::currentUser`
- Added new cookies package relying on PHP's setcookie

### Fixed

- fixed hidden fields on Leaf\Auth::login
- Fixed multiple-request type data on get and body at Leaf\Http\Request

### Changed

- Switched cookies package in Leaf\Http\Response
- Switched cookies package in Leaf\App

### Removed

- Removed old cookies package and all it's methods
- Removed setEncryptedCookie and getEncryptedCookie on Leaf\App
- Slashed unnecessary code from Leaf\Http\Request

## v2.1.0 - Elderberry - 19th June, 2020

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
