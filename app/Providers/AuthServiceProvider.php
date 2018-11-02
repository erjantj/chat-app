<?php

namespace App\Providers;

use App\Guards\ApiGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider {
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register() {
		//
	}

	/**
	 * Boot the authentication services for the application.
	 *
	 * @return void
	 */
	public function boot() {
		Auth::extend('api', function ($app, $name, array $config) {
			$guard = new ApiGuard(
				$app['tymon.jwt.auth'],
				Auth::createUserProvider($config['provider']),
				$app['request']
			);
			return $guard;
		});
	}
}
