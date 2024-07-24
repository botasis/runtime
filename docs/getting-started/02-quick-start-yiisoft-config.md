# Using `botasis/runtime` with `yiisoft/config` and `yiisoft/di`

Using `yiisoft/config`, Botasis registers and configures all necessary dependencies. You have to set a couple of additional values:

## Params
- `$params['botasis/telegram-bot']['bot token']` - set you bot token, which you got from BotFather, here
- `$params['botasis/telegram-bot']['errors to ignore']` - List of errors returned by Telegram API which won't throw an exception.
    Each element should be an error text which returns in `description` key of response.  
    Useful when you know some requests may return errors, and it's ok. I.e. when callback response is not sent in time.
- 

## DI Definitions
You also have to define some PSR interface implementations:
- 
