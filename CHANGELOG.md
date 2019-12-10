## Changelog
### v1.5.8 - 11th December, 2019
#### Added
- Added FS->deleteFolder
- Added FS->deleteFile
- Added Form->validate😅
- Added Form->validate and return errors to base controllers
- Added Leaf\Core\Str: equivalent of Illuminate\Support\Str with added methods
- Added Leaf Mysqli🤔
- Added Leaf PDO🤔
- Added Leaf\Core\Auth: simple login and signup


#### Fixed
- Fixed FS->deleteFile
- Fixed FS->listDir
- Fixed Leaf DB
- Fixed init bug with session


#### Changed
- Renamed Veins->renderTemplate to render
- Rename veins->assign to set()
- Renamed mkdir to createFolder
- Renamed mkdirInBase to createFolderInBase
- Renamed renameDir to renameFolder
- Changed vein file extension from .vein to .vein.php
- Split Leaf\Config\Db between Leaf\Core\Db\Mysqli and Leaf\Core\Db\PDO
- Changed `renderHtml` to `renderPage`
- Changed all `getParam`s to `get`


#### Removed
Nothing was removed



### v1.4.2 - 13th November, 2019
#### Added
- Added FileSystem module
- Added `mysqliQuery` method to `leaf\config\db`
- Added a bunch of handy session methods
- Added leaf token
- Added leaf form


#### Fixed
- Fixed  a few problems with `leaf\config\db`;
- Fixed tiny bug with `response->throwErr`


#### Changed
- Changed `leaf\config\db`: connection variables and connection type are set on db init. `$db = new db($host, $user, $password, $dbname, "PDO")`
- Renamed renderHtmlPage to renderHtml


#### Removed
- Leaf\Config\DB has been depricated for now




### v1.4.1 - 1st November, 2019
#### Added
- Added base Leaf Controller `Leaf\Core\Controller`
- Added base controller for APIs: `Leaf\Core\ApiController`
- Added base Leaf Model `Leaf\Core\Model`
- Added support for full MVC app
- Added [Leaf Veins](https://github.com/leafsphp/veins) in default Leaf package
- Added Error Handling for development and production
- Added a base database layer connected with custom environment variables


#### Fixed
- Fixed bug with `Response::renderHtmlPage()`
- Fixed the HTTP code rendering in the browser from `Response::respondWithCode`


#### Changed
- Changed `Validation` to `Form`


#### Removed
- Removed Leaf `Exceptions`
- Removed Middleware interfaces