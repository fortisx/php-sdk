# FortisX PHP SDK

This guide explains how to interact with the API using the PHP client.

---

## Installation

```bash
composer require fortisx/sdk
```

---

## Initialization

```php
require 'vendor/autoload.php';

use FortisX\SDK\API;

$api = new API('YOUR_API_KEY');
```

**Constructor options**

| Name | Type | Default | Description |
|------|------|----------|--------------|
| `$apiKey` | `?string` | `null` | API key used for authorization |
| `$baseUrl` | `string` | `https://api.fortisx.fi/v1` | Override if using a custom environment |
| `$timeout` | `int` | `10` | Request timeout in seconds |

---

## Methods

| Method | Arguments | Returns | Description |
|--------|------------|----------|-------------|
| `get(string $endpoint, array $params = []): array` | endpoint path, optional params | `array` | Performs a GET request |
| `post(string $endpoint, array $data = []): array` | endpoint path, optional data | `array` | Performs a POST request |
| `put(string $endpoint, array $data = []): array` | endpoint path, optional data | `array` | Performs a PUT request |
| `delete(string $endpoint): array` | endpoint path | `array` | Performs a DELETE request |

> All requests use `GuzzleHttp\Client` and include the following header:
>
> ```php
> [
>     'Accept' => 'application/json',
> ]
> ```
>
> If an API key is provided, the SDK also sends:
>
> ```php
> [
>     'Authorization' => "Bearer {$apiKey}",
> ]
> ```

---

## Error Handling

When a network or server error occurs, an exception of type `APIError` is thrown.

```php
namespace FortisX\SDK;

class APIError extends \Exception
{
    public int $status;

    public array $details;

    public function __construct(string $message, int $status = 0, array $details = [])
    {
        parent::__construct($message);

        $this->status = $status;
        $this->details = $details;
    }
}
```

| Field | Type | Description |
|--------|------|-------------|
| `$err->getMessage()` | `string` | Short description of the error |
| `$err->status` | `int` | HTTP status code |
| `$err->details` | `array` | Full response body if available |

**Example:**

```php
use FortisX\SDK\API;
use FortisX\SDK\APIError;

$api = new API('demo-key');

try {
    $res = $api->get('ping');

    print_r($res);
} catch (APIError $err) {
    echo "API error [{$err->status}]: {$err->getMessage()}";
}
```

---

## Example: `/ping` Endpoint

```php
require 'vendor/autoload.php';

use FortisX\SDK\API;

$api = new API('demo-key');
$response = $api->get('ping');

print_r($response); // ['status' => 'ok']
```
