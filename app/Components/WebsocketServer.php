<?php

namespace App\Components;

use App\Models\User;
use App\Services\MessageService;
use Tymon\JWTAuth\Facades\JWTAuth;

class WebsocketServer {

	/**
	 * Message types
	 */
	const TYPE_AUTH = 'auth';
	const TYPE_MESSAGE = 'message';

	private $config;
	private $messageService;

	public function __construct(MessageService $messageService, $config) {
		$this->config = $config;
		$this->messageService = $messageService;
	}

	/**
	 * Websocket entry point
	 */
	public function start() {
		echo "Stared websocket on tcp://" . $this->config['host'] . ":" . $this->config['port'];

		$socket = stream_socket_server(
			"tcp://" . $this->config['host'] . ":" . $this->config['port'], $errno, $errstr);
		if (!$socket) {
			die("$errstr ($errno)\n");
		}

		$connects = [];
		$online = [];

		while (true) {
			$read = $connects;
			$read[] = $socket;
			$write = $except = null;

			if (!stream_select($read, $write, $except, null)) {
				break;
			}

			// Accept new connections
			if (in_array($socket, $read)) {
				if (($connect = stream_socket_accept($socket, -1)) && $info = $this->handshake($connect)) {
					$connects[] = $connect;
				}
				unset($read[array_search($socket, $read)]);
			}

			foreach ($read as $connect) {
				// Handle connections
				$data = fread($connect, 100000);
				if (!$data) {
					// Connection was closed
					foreach ($online as $key => $onlineSocket) {
						if ($onlineSocket == $connect) {
							$user = User::find($key);
							if ($user) {
								$user->setOffline();
								$this->setOnline($user, $online, false);
							}
							unset($online[$key]);
							break;
						}
					}
					fclose($connect);
					unset($connects[array_search($connect, $connects)]);
					$this->onClose($connect);
					continue;
				}

				try {
					// Handle messages
					$this->onMessage($connect, $online, $data);
				} catch (Exception $e) {
					echo $e->getMessage();
				}

			}
		}

		fclose($server);
	}

	/**
	 * Handshake
	 * @param  Resource $connect user connection
	 * @return array
	 */
	public function handshake($connect) {
		$info = [];

		$line = fgets($connect);
		$header = explode(' ', $line);
		$info['method'] = $header[0];
		$info['uri'] = $header[1];

		while ($line = rtrim(fgets($connect))) {
			if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
				$info[$matches[1]] = $matches[2];
			} else {
				break;
			}
		}

		$address = explode(':', stream_socket_get_name($connect, true));
		$info['ip'] = $address[0];
		$info['port'] = $address[1];

		if (empty($info['Sec-WebSocket-Key'])) {
			return false;
		}

		$SecWebSocketAccept = base64_encode(pack('H*', sha1($info['Sec-WebSocket-Key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
		$upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
			"Upgrade: websocket\r\n" .
			"Connection: Upgrade\r\n" .
			"Sec-WebSocket-Accept:$SecWebSocketAccept\r\n" .
			"Sec-WebSocket-Protocol: json\r\n\r\n";

		fwrite($connect, $upgrade);

		return $info;
	}

	/**
	 * Encode message
	 * @param  string  $payload message payload
	 * @param  string  $type    message type
	 * @param  boolean $masked  is masked
	 * @return string
	 */
	public function encode($payload, $type = 'text', $masked = false) {
		$frameHead = [];
		$payloadLength = strlen($payload);

		switch ($type) {
		case 'text':
			// first byte indicates FIN, Text-Frame (10000001):
			$frameHead[0] = 129;
			break;

		case 'close':
			// first byte indicates FIN, Close Frame(10001000):
			$frameHead[0] = 136;
			break;

		case 'ping':
			// first byte indicates FIN, Ping frame (10001001):
			$frameHead[0] = 137;
			break;

		case 'pong':
			// first byte indicates FIN, Pong frame (10001010):
			$frameHead[0] = 138;
			break;
		}

		// set mask and payload length (using 1, 3 or 9 bytes)
		if ($payloadLength > 65535) {
			$payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
			$frameHead[1] = ($masked === true) ? 255 : 127;
			for ($i = 0; $i < 8; $i++) {
				$frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
			}
			// most significant bit MUST be 0
			if ($frameHead[2] > 127) {
				return array('type' => '', 'payload' => '', 'error' => 'frame too large (1004)');
			}
		} elseif ($payloadLength > 125) {
			$payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
			$frameHead[1] = ($masked === true) ? 254 : 126;
			$frameHead[2] = bindec($payloadLengthBin[0]);
			$frameHead[3] = bindec($payloadLengthBin[1]);
		} else {
			$frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
		}

		// convert frame-head to string:
		foreach (array_keys($frameHead) as $i) {
			$frameHead[$i] = chr($frameHead[$i]);
		}
		if ($masked === true) {
			// generate a random mask:
			$mask = [];
			for ($i = 0; $i < 4; $i++) {
				$mask[$i] = chr(rand(0, 255));
			}

			$frameHead = array_merge($frameHead, $mask);
		}
		$frame = implode('', $frameHead);

		// append payload to frame:
		for ($i = 0; $i < $payloadLength; $i++) {
			$frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
		}

		return $frame;
	}

	/**
	 * Decode message
	 * @param  string $data message
	 * @return string
	 */
	public function decode($data) {
		$unmaskedPayload = '';
		$decodedData = [];

		// estimate frame type:
		$firstByteBinary = sprintf('%08b', ord($data[0]));
		$secondByteBinary = sprintf('%08b', ord($data[1]));
		$opcode = bindec(substr($firstByteBinary, 4, 4));
		$isMasked = ($secondByteBinary[0] == '1') ? true : false;
		$payloadLength = ord($data[1]) & 127;

		// unmasked frame is received:
		if (!$isMasked) {
			return array('type' => '', 'payload' => '', 'error' => 'protocol error (1002)');
		}

		switch ($opcode) {
		// text frame:
		case 1:
			$decodedData['type'] = 'text';
			break;

		case 2:
			$decodedData['type'] = 'binary';
			break;

		// connection close frame:
		case 8:
			$decodedData['type'] = 'close';
			break;

		// ping frame:
		case 9:
			$decodedData['type'] = 'ping';
			break;

		// pong frame:
		case 10:
			$decodedData['type'] = 'pong';
			break;

		default:
			return array('type' => '', 'payload' => '', 'error' => 'unknown opcode (1003)');
		}

		if ($payloadLength === 126) {
			$mask = substr($data, 4, 4);
			$payloadOffset = 8;
			$dataLength = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset;
		} elseif ($payloadLength === 127) {
			$mask = substr($data, 10, 4);
			$payloadOffset = 14;
			$tmp = '';
			for ($i = 0; $i < 8; $i++) {
				$tmp .= sprintf('%08b', ord($data[$i + 2]));
			}
			$dataLength = bindec($tmp) + $payloadOffset;
			unset($tmp);
		} else {
			$mask = substr($data, 2, 4);
			$payloadOffset = 6;
			$dataLength = $payloadLength + $payloadOffset;
		}

		/**
		 * We have to check for large frames here. socket_recv cuts at 1024 bytes
		 * so if websocket-frame is > 1024 bytes we have to wait until whole
		 * data is transferd.
		 */
		if (strlen($data) < $dataLength) {
			return false;
		}

		if ($isMasked) {
			for ($i = $payloadOffset; $i < $dataLength; $i++) {
				$j = $i - $payloadOffset;
				if (isset($data[$i])) {
					$unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
				}
			}
			$decodedData['payload'] = $unmaskedPayload;
		} else {
			$payloadOffset = $payloadOffset - 4;
			$decodedData['payload'] = substr($data, $payloadOffset);
		}

		return $decodedData;
	}

	public function onOpen($connect, $info) {
		// echo "open\n";
		// fwrite($connect, $this->encode('Привет'));
	}

	public function onClose($connect) {
		// echo "close\n";
	}

	/**
	 * Handle user message
	 * @param  Resource $connect user connection
	 * @param  array 	&$online list of online users
	 * @param  string 	$data    message
	 */
	public function onMessage($connect, &$online, $data) {
		$dataDecoded = $this->decode($data);
		if ($this->isJson($dataDecoded['payload'])) {
			$dataEcoded = $this->encode($dataDecoded['payload']);
			$payload = json_decode($dataDecoded['payload'], true);

			if (isset($payload['type'])) {
				// Auth
				if ($payload['type'] == self::TYPE_AUTH && !empty($payload['api_key'])) {
					$user = JWTAuth::setToken($payload['api_key'])->authenticate();
					if ($user) {
						$online[$user->id] = $connect;
						$user->setOnline();
						$this->setOnline($user, $online, true);
					}
				}

				// Message
				if ($payload['type'] == self::TYPE_MESSAGE) {
					$user = JWTAuth::setToken($payload['api_key'])->authenticate();
					if ($user) {
						$recipientId = $payload['message']['recipient_id'];
						if (isset($online[$recipientId])) {
							print_r([$online[$recipientId]]);

							fwrite($online[$recipientId], $dataEcoded);
						}
						$this->messageService->create($user, $payload['message']);
					}
				}
			}
		}
	}

	/**
	 * Update user online status
	 * @param User    $user     user object
	 * @param array   $online   online users
	 * @param boolean $isOnline new online status
	 */
	public function setOnline($user, $online, $isOnline) {
		print_r(['online', $user->id]);
		$message = json_encode([
			'type' => 'online',
			'user' => $user->toArray(),
			'is_online' => $isOnline,
		]);

		$dataEcoded = $this->encode($message);
		foreach ($online as $key => $connect) {
			if ($key != $user->id) {
				fwrite($connect, $dataEcoded);
			}
		}
	}

	function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}
}