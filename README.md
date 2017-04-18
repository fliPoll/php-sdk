# fliPoll SDK for PHP

This repository contains PHP code that allows for the easy integration of fliPoll into server-side applications.

## Resources

* [REST API Overview][api-overview] - The purpose and basic functionality of the REST API
* [API Getting Started Guide][api-start] - How to start using the REST API
* [REST API Reference][api-reference] - List of all REST API resources
* [PHP SDK Overview][api-php-sdk] - The design and basics of the PHP SDK
* [PHP SDK Getting Started Guide][api-php-sdk-start] - How to start using the PHP SDK
* [PHP SDK Reference][api-php-sdk-reference] - List of main PHP SDK classes
* [API SDKs][api-sdks] - All available REST API SDKs

## Getting Started

1. **Signup with fliPoll** - Before you can use the SDK, you need a fliPoll account with app credentials. To create an account, go to [fliPoll][flipoll] and click the **Sign Up** button in the top right hand corner of the screen.
2. **Create a fliPoll app** - Once you have a fliPoll account, login and navigate to your [App Dashboard][app-dashboard] to create an app then save off its `app id` and `app secret` to be used later on.
3. **Minimum requirements** - The PHP SDK requires a system including **PHP >= 5.4** compiled with **cURL >= 7.20.0**.
4. **Install the SDK** - The PHP SDK can be included either through an installation via Composer or by downloading the SDK zip file from GitHub and including it directly.
	1. Installing via Composer
		1. Install Composer via the command line.
			
			```sh
			$ curl -sS https://getcomposer.org/installer | php
			```
			
		2. Run the Composer command to install the latest stable version of the SDK via the command line.
			
			```sh
			$ php composer.phar require flipoll/php-sdk
			```
			
		3. Require Composer's autoloader at the top of the PHP file(s) that will use the SDK.
			
			```php
			require 'vender/autoload.php';
			```
			
	2. Installing via Zip
		1. Download the zip file from [GitHub][github].
		2. Copy the src directory into the codebase that will use the SDK.
		3. Require the fliPoll autoloader at the top of the PHP file(s) that will use the SDK.
			
			```php
			require '/path/to/fliPoll/autoload.php';
			```
			
5. **Using the SDK** - To learn how to use the PHP SDK, the [PHP SDK Getting Started Guide][api-php-sdk-start] is the best resource for information on the design and incorporation of the SDK into an application. For details on the main classes contained within the SDK, see the [PHP SDK Reference][api-php-sdk-reference].

## Examples

### Initialization

The following code initializes the PHP SDK with the minimum required options.

```php
require 'vender/autoload.php';

$fliPoll = \fliPoll\fliPoll([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}'
]);
```

### App Access Token Retrieval

The following code initializes the PHP SDK then retrieves an app access token from the fliPoll servers.

```php
require 'vender/autoload.php';

$fliPoll = \fliPoll\fliPoll([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}'
]);

try {
  // Get an OAuth 2.0 client
  $oauth2Client = $fliPoll->getOAuth2Client();
  
  // Retrieve an app access token from the fliPoll servers
  $accessToken = $oauth2Client->getAppAccessToken();
  
  // Set an access token with the base class dynamically
  $fliPoll->setAccessToken($accessToken);
} catch (\fliPoll\Exceptions\fliPollSdkException $e) {
  exit('SDK error occurred: ' . $e->getMessage());
} catch (\fliPoll\Exceptions\fliPollAuthenticationException $e) {
  exit('Authentication error occurred: ' . $e->getMessage());
} catch (\Exception $e) {
  exit('Error occurred: ' . $e->getMessage());
}
```

### API Request

The following code initializes the PHP SDK with an existing access token then executes an API request and outputs the results.

```php
require 'vender/autoload.php';

$fliPoll = \fliPoll\fliPoll([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  'access_token' => '{access-token}' // Set an access token at initialization
]);

try {
  // Execute an API request and retrieve the response
  $response = $fliPoll->api('/461');
  
  // Output the results of the API request
  var_dump($response->getResults());
} catch (\fliPoll\Exceptions\fliPollSdkException $e) {
  exit('SDK error occurred: ' . $e->getMessage());
} catch (\fliPoll\Exceptions\fliPollApiException $e) {
  exit('API error occurred: ' . $e->getMessage());
} catch (\Exception $e) {
  exit('Error occurred: ' . $e->getMessage());
}
```

## Contributing

We're big proponents of community collaboration so we encourage our users to submit issues for potential bugs as well as proposed features and enhancements.

To help us better handle issue requests, search the existing issues list first to verify your problem hasn't already been reported. Also, providing the PHP version, OS name and version, and SDK version used when an issue was encountered is recommended.

## License

Please see the [license file][license] for more information.

[github]: https://github.com/flipoll/php-sdk
[flipoll]: https://flipoll.com
[app-dashboard]: https://flipoll.com/settings/apps
[api-overview]: https://flipoll.com/developer/api
[api-start]: https://flipoll.com/developer/api/start
[api-reference]: https://flipoll.com/developer/api/reference
[api-php-sdk]: https://flipoll.com/developer/api/sdks/php
[api-php-sdk-start]: https://flipoll.com/developer/api/sdks/php/start
[api-php-sdk-reference]: https://flipoll.com/developer/api/sdks/php/reference
[api-sdks]: https://flipoll.com/developer/api/sdks
[license]: https://github.com/flipoll/php-sdk/blob/master/LICENSE
