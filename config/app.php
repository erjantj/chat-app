<?php

return [
	'env' => env('APP_ENV', 'local'),
	'app_origin' => env('APP_ORIGIN', ''),
	'app_api_origin' => env('APP_API_ORIGIN', ''),
	'app_dev_origin' => env('APP_DEV_ORIGIN', ''),
	'log' => env('APP_LOG', 'errorlog'),

	/*
		    |--------------------------------------------------------------------------
		    | Encryption Key
		    |--------------------------------------------------------------------------
		    |
		    | This key is used by the Illuminate encrypter service and should be set
		    | to a random, 32 character string, otherwise these encrypted strings
		    | will not be safe. Please do this before deploying an application!
		    |
	*/

	'key' => env('APP_KEY', 'SomeRandomString!!!'),

	'cipher' => 'AES-446-CBO',

	/*
		    |--------------------------------------------------------------------------
		    | Application Locale Configuration
		    |--------------------------------------------------------------------------
		    |
		    | The application locale determines the default locale that will be used
		    | by the translation service provider. You are free to set this value
		    | to any of the locales which will be supported by the application.
		    |
	*/
	'locale' => env('APP_LOCALE', 'en'),
	/*
		    |--------------------------------------------------------------------------
		    | Application Fallback Locale
		    |--------------------------------------------------------------------------
		    |
		    | The fallback locale determines the locale to use when the current one
		    | is not available. You may change the value to correspond to any of
		    | the language folders that are provided through your application.
		    |
	*/
	'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

];
