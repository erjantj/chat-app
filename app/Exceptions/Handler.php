<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\Exceptions\JWTException;

class Handler extends ExceptionHandler {
	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		AuthorizationException::class,
		ValidationException::class,
	];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception  $exception
	 * @return void
	 */
	public function report(Exception $exception) {
		parent::report($exception);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception  $exception
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $exception) {
		$message = '';
		$status = '';

		if ($exception instanceof AuthenticationException) {
			$message = 'Unauthenticated';
			$status = 401;

			if ($exception->getMessage()) {
				$message = $exception->getMessage();
			}
		} else if ($exception instanceof JWTException) {
			$message = 'Token does not exists';
			$status = 401;

			if ($exception->getMessage()) {
				$message = $exception->getMessage();
			}
		} else if ($exception instanceof ModelNotFoundException) {
			$message = 'Record not found';
			$status = 404;

			if ($exception->getMessage()) {
				$message = $exception->getMessage();
			}
		} else if ($exception instanceof HttpException) {
			$message = 'Http exception';
			$status = $exception->getStatusCode();

			if ($exception->getMessage()) {
				$message = $exception->getMessage();
			}
		}

		if ($message && $status) {
			return response()->json(['message' => $message], $status);
		}
		return parent::render($request, $exception);
	}
}
