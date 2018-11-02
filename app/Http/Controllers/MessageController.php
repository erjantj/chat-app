<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		// Check access
		$this->middleware('auth');
	}

	/**
	 * @SWG\Post(
	 *     path="/message",
	 *     tags={"Message"},
	 *     summary="Create new message",
	 *     description="Creates new message record",
	 *     consumes={"application/json"},
	 *     @SWG\Response(
	 *         response="default",
	 *         description="Message created",
	 *     ),
	 *     @SWG\Parameter(
	 *         name="body",
	 *         in="body",
	 *         required=true,
	 *         @SWG\Schema(
	 *             ref="#/definitions/Message"
	 *         )
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
	 * Create new message
	 *
	 * @param  Request  $request request
	 * @return string   json response
	 */
	public function create(Request $request, MessageService $messageService) {
		$user = Auth::user();
		$this->validateCreate($request);
		$data = $request->only([
			'message', 'recipient_id',
		]);
		if ($messageService->create($user, $data)) {
			return response()->json();
		}

		abort(422, 'Problem saving message');
	}

	/**
	 * @SWG\Get(
	 *     path="/message",
	 *     tags={"Message"},
	 *     summary="Get messages list",
	 *     description="Return message list for given recipient",
	 *     consumes={"application/json"},
	 *     @SWG\Parameter(
	 *         name="recipient_id",
	 *         in="query",
	 *         required=true,
	 *         description="Recipient id",
	 *         type="integer",
	 *     ),
	 *     @SWG\Response(
	 *         response="default",
	 *         description="User messages",
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
	 * Return message list for given recipient
	 *
	 * @param  Request  $request request
	 * @return string   json response
	 */
	public function all(Request $request, MessageService $messageService) {
		$user = Auth::user();
		$this->validateAll($request);
		$recipientId = $request->input('recipient_id');
		$paginator = $messageService->all($user, $recipientId);
		$result = $paginator->toArray();
		$result['data'] = array_reverse($result['data']);

		return response()->json($result);
	}

	/**
	 * Validate message create params
	 * @param  Request $request request object
	 */
	private function validateCreate($request) {
		$this->validate($request, [
			'message' => 'required|max:4000',
			'recipient_id' => 'required|exists:user,id',
		]);
	}

	/**
	 * Validate all message params
	 * @param  Request $request request object
	 */
	private function validateAll($request) {
		$this->validate($request, [
			'recipient_id' => 'required|exists:user,id',
		]);
	}
}
