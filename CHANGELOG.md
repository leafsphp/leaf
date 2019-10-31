## Changelog
### v1.4 - 1st November, 2019
#### Added
- Added base Leaf Controller `Leaf\Core\Controller`
- Added base Leaf Model `Leaf\Core\Model`
- Added support for full MVC app
- Added [Leaf Veins](https://github.com/leafsphp/veins) in default Leaf package
- Added Error Handling for development and production
- Added a base database layer connected with custom environment variables
- Added a bunch of methods for Form Validation
- Added simple `Token` object for creating and validating tokens without `JWT`. These can be used in test projects but are not recommended for use in actual projects


#### Fixed
- Fixed bug with `Response::renderHtmlPage()`


#### Changed
- Changed `Validation` to `Form`


#### Removed
- Removed Leaf `Exceptions`
- Removed Middleware interfaces