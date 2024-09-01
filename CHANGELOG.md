# Botasis Runtime Change Log

## 0.12.0

- `UpdateAttribute` is added, so router dynamic rules and update handlers may use typed arguments received from an `Update` object
- Added Extended Callable Definitions support to dynamic route rules.
- Added Extended Callable Definitions support to route actions.
  Usage of UpdateHandlerInterface as an action is now deprecated, and this ability will be removed in future versions.
- Response tags and subscriptions are added.
- Params section renamed from `botasis/telegram-bot` to `botasis/runtime`.
- `yiisoft/config`-compatible configuration added for all possible classes. Dynamic part is moved to params.
- `GetUpdatesCommand` don't throw exceptions anymore. It just logs them to a PSR logger instead.
- More docs are added

## 0.11.3

- Bugfix: `StateJson` didn't correctly set data when data is not a string

## 0.11.2

- More PhpDocs for `StateRepositoryInterface` and `StateMiddleware`

## 0.11.1

- State management is added. Users now may use chat/user state in router rules and update handlers

## 0.11.0

- `UserId` value object is removed. Using `string` instead.

## 0.10.0

- Documentation is added.
- UserRepositoryInterface is removed as it's not responsibility of the Runtime library and is not used here.
- Routes are now objects instead of arrays
- Fix failed tests
- PhpUnit cache is removed from the repository
- Telegram Update variables are completely renamed from $request to $update

## 0.11.0

- `UserId` value object is removed. Using `string` instead.

## 0.11.1

- State management is added. Users now may use chat/user state in router rules and update handlers

## 0.11.2 

- More PhpDocs for `StateRepositoryInterface` and `StateMiddleware`

## 0.11.3

- Bugfix: `StateJson` didn't correctly set data when data is not a string

## 0.11.4

- Bugfix: IgnoredErrorHandler was not working

## 0.12.0

Work in progress
