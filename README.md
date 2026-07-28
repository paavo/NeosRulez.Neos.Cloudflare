# Neos CMS Cloudflare ProxyCache Manager

A Neos CMS package that flushes the Cloudflare proxy cache when publishing. Provides a backend module for manual flushing and a cli job.

## Compatibility

| Package | Neos    |
|---------|---------|
| `^2.0`  | `^9.0`  |
| `^1.0`  | `^8.3`  |

On Neos 9 the flush is triggered by a content repository catch up hook that reacts to content being
published into the `live` workspace. It replaces the `afterNodePublishing` signal used on Neos 8,
which no longer exists in the event sourced content repository.

## Installation

Just run:

```
composer require neosrulez/neos-cloudflare
```

### Configuration

```yaml
NeosRulez:
  Neos:
    Cloudflare:
      proxyCache:
        default:
          zoneName: 'domain1.tld'
          apiKey: '8843d7f92416211de9ebb963ff4ce281259'
        second:
          zoneName: 'domain2.tld'
          apiKey: '8843d7f92416211de9ebb963ff4ce281259'
```

## Author

* E-Mail: mail@patriceckhart.com
* URL: http://www.patriceckhart.com 


## How to create an API Key

* Visit [Cloudflare](https://dash.cloudflare.com/profile/api-tokens)
* Create new User API Token
* Add Permission to Purge Cache `Cache Purge:Purge`
