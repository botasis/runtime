# Botasis Runtime Change Log

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
