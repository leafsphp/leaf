# Changelog

All notable changes to the Leaf core are documented here.

## v5.0 (unreleased)

### Added

- Compiled routing engine: routes are compiled to indexed patterns at registration; exact matches resolve from a dictionary without touching regex
- Optional route parameters — `/posts/{id?}` matches both `/posts` and `/posts/5`
- Inline route constraints — `/users/{id:[0-9]+}`, including quantifiers like `{year:[0-9]{4}}`
- `Leaf\Router::reset()` for tests, workers, and long-running processes
- Per-request memoization of the request method and URI

### Changed

- **Route matching is specificity-first**: exact routes always win over dynamic routes regardless of registration order; dynamic routes win over catch-alls. Registration order only breaks ties within a tier
- Debug behavior derives from the app environment: `APP_ENV=production` disables detailed error pages by default
- Sessions are off by default; `session.cookie.secure` is auto-detected from HTTPS
- `_env()` parses the environment once per request and caches it (use `getenv()` for values set at runtime)
- The error handler registers once per process and is shared across `App` instances — `config()` no longer re-registers it, and custom handlers set with `setErrorHandler()` survive later `config()` calls

### Fixed

- Router hooks that replace routes at runtime (the lingo/sitemap contract) are re-indexed so their changes actually apply
- Calling an undefined method on `Leaf\App` throws `BadMethodCallException` instead of silently returning `null`
- `Router::run()` no longer pops error handlers it didn't register

### Removed

- **Raw regex route patterns** — `/posts(/edit)?` style routes no longer match; use named parameters, optional parameters, and constraints instead
- Built-in Eien/Swoole integration (`$app->ws()`) — long-running server setups now live outside the core

See the [upgrade guide](https://leafphp.dev/docs/upgrade-guide) for migration steps.
