<?php

abstract class TestCase extends Laravel\Lumen\Testing\TestCase {
	protected $apiVersion;
	protected $endpoint;

	/**
	 * Creates the application.
	 *
	 * @return \Laravel\Lumen\Application
	 */
	public function createApplication() {
		return require __DIR__ . '/../bootstrap/app.php';
	}
}
