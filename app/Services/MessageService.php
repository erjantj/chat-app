<?php

namespace App\Services;

use App\Models\Message;

class MessageService {

	const PAGE_SIZE = 20;

	/**
	 * Create message
	 * @param  User  $user author user
	 * @param  array $data message
	 * @return boolean
	 */
	public function create($user, $data) {
		$message = new Message();
		$message->sender_id = $user->id;
		$message->recipient_id = (int) $data['recipient_id'];
		$message->message = $data['message'];

		return $message->save();
	}

	/**
	 * All message for given recipient
	 * @param  User    $user        author
	 * @param  integer $recipientId id of recipient user
	 * @return Paginator
	 */
	public function all($user, $recipientId) {
		return Message::query()
			->where(function ($query) use ($user, $recipientId) {
				$query->where('sender_id', '=', $user->id)
					->where('recipient_id', '=', $recipientId);
			})
			->orWhere(function ($query) use ($user, $recipientId) {
				$query->where('sender_id', '=', $recipientId)
					->where('recipient_id', '=', $user->id);
			})
			->orderBy('created_at', 'desc')
			->orderBy('id', 'desc')
			->simplePaginate(self::PAGE_SIZE);
	}
}
