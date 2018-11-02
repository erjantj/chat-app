<?php

namespace App\Console\Commands;

use App\Components\WebsocketServer;
use App\Services\MessageService;
use Illuminate\Console\Command;

class Websocket extends Command {

	protected $signature = 'websocket';

	protected $description = 'This command start websocket server';

	protected $websocketServer;

	public function __construct(MessageService $messageService) {
		parent::__construct();
		$config = config('services.websocket');
		$this->websocketServer = new WebsocketServer($messageService, $config);
	}

	public function handle() {
		// Start websocket
		$this->websocketServer->start();
	}
}