<?php

use App\Models\Message;
use App\Models\User;
use Laravel\Lumen\Testing\DatabaseTransactions;

class MessageTest extends TestCase {
	use DatabaseTransactions;

	public function setUp() {
		parent::setUp();

		$this->apiVersion = 'v1';
		$this->endpoint = 'message';
	}

	/**
	 * Not authorized
	 *
	 * @return void
	 */
	public function testNotAuthorized() {
		$invalidToken = '123';
		$this->post("/api/{$this->apiVersion}/{$this->endpoint}/", [])
			->seeJson([
				'message' => 'The token could not be parsed from the request',
			]);
	}

	/**
	 * Invalid token
	 *
	 * @return void
	 */
	public function testIvalidToken() {
		$invalidToken = '123asd123asd';
		$header = $this->getHeaders($invalidToken);
		$this->post("/api/{$this->apiVersion}/{$this->endpoint}/", $header, $header)
			->seeJson([
				'message' => 'Wrong number of segments',
			]);
	}

	/**
	 * Empty params
	 *
	 * @return void
	 */
	public function testEmptyParams() {
		$user = factory(User::class)->create();
		$this->actingAs($user, 'api');

		$this->post("/api/{$this->apiVersion}/{$this->endpoint}/", [])
			->seeJson([
				'message' => ['The message field is required.'],
				'recipient_id' => ['The recipient id field is required.'],
			]);
	}

	/**
	 * Invalid recipient
	 *
	 * @return void
	 */
	public function testInvalidRecipient() {
		$user1 = factory(User::class)->create();
		$message = factory(Message::class)->make();
		$data = [
			'recipient_id' => 10000,
			'message' => $message->message,
		];

		$this->actingAs($user1, 'api');

		$this->post("/api/{$this->apiVersion}/{$this->endpoint}/", $data)
			->seeJson([
				'recipient_id' => ['The selected recipient id is invalid.'],
			]);
	}

	/**
	 * Success
	 *
	 * @return void
	 */
	public function testSuccess() {
		$user1 = factory(User::class)->create();
		$user2 = factory(User::class)->create();
		$message = factory(Message::class)->make();
		$data = [
			'recipient_id' => $user2->id,
			'message' => $message->message,
		];

		$this->actingAs($user1, 'api');

		$response = $this->post("/api/{$this->apiVersion}/{$this->endpoint}/", $data);
		$response->seeJson([]);
		$this->assertEquals(200, $response->response->status());
	}

	public function getHeaders($token) {
		$header = [
			'Authorization' => "Bearer " . $token,
		];
		return $header;
	}
}
