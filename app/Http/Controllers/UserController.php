<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JWTAuth;

class UserController extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		// Check access
		$this->middleware('auth', ['only' => [
			'me',
			'logout',
		]]);
	}

	/**
	 * @SWG\Get(
	 *     path="/me",
	 *     tags={"User"},
	 *     summary="Get user data",
	 *     description="Return user data for authorized user",
	 *     consumes={"application/json"},
	 *     @SWG\Response(
	 *         response="default",
	 *         description="User data",
	 *     ),
	 *     @SWG\Response(
	 *         response=403,
	 *         description="Forbidden",
	 *     ),
	 *     security={{
	 *         "apiKey": {}
	 *     }}
	 * )
	 *
	 * Authorized user data
	 *
	 * @param  Request  $request request
	 * @return string   json response
	 */
	public function me(Request $request) {
		$user = Auth::user();
		return response()->json($user);
	}

	/**
	 * @SWG\Get(
	 *     path="/user",
	 *     tags={"User"},
	 *     summary="List of all contacts",
	 *     description="Return list of all users available",
	 *     consumes={"application/json"},
	 *     @SWG\Response(
	 *         response="default",
	 *         description="List of contacts",
	 *     ),
	 *     @SWG\Response(
	 *         response=403,
	 *         description="Forbidden",
	 *     ),
	 *     security={{
	 *         "apiKey": {}
	 *     }}
	 * )
	 *
	 * List of all contacts
	 *
	 * @param  Request  $request request
	 * @return string   json response
	 */
	public function all(Request $request) {
		$user = Auth::user();
		return User::where('id', '<>', $user->id)->get();
	}

	/**
	 * @SWG\Post(
	 *     path="/login",
	 *     tags={"User"},
	 *     summary="User login",
	 *     description="User login",
	 *     consumes={"application/json"},
	 *     @SWG\Parameter(
	 *         name="username",
	 *         in="query",
	 *         description="Username",
	 *         required=true,
	 *         type="string",
	 *     ),
	 *     @SWG\Response(
	 *         response="default",
	 *         description="Autorization token",
	 *     ),
	 *     @SWG\Response(
	 *         response=422,
	 *         ref="$/responses/UnprocessableEntity"
	 *     ),
	 * )
	 *
	 * User login
	 *
	 * @param  Request  $request request
	 * @return string   json response
	 */
	public function login(Request $request) {
		$this->validateLogin($request);
		$username = $request->input('username');
		$user = User::where('username', $username)->first();
		$now = Carbon::now();

		if (!$user) {
			$user = new User;
			$user->username = trim($username);
			$user->is_online = false;
		}
		$user->last_online = $now;
		$user->save();

		try {
			$credentials = [
				'username' => $username,
			];

			if ($token = JWTAuth::fromUser($user)) {
				$data = [
					'user' => $user,
					'api_key' => $token,
				];
				return response()->json($data);
			}

		} catch (JWTException $e) {
			abort(422, trans('validation.login_failed'));
		}

		abort(422, trans('validation.invalid_auth_data'));
	}

	/**
	 * Validate login params
	 * @param  Rquest $request request object
	 */
	private function validateLogin($request) {
		$this->validate($request, [
			'username' => 'required|max:255|string',
		]);
	}
}
