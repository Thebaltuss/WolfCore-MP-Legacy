<?php

/*
 * This file is part of BedrockProtocol.
 * Copyright (C) 2014-2022 PocketMine Team <https://github.com/pmmp/BedrockProtocol>
 *
 * BedrockProtocol is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace wolfcore\network\mcpe\protocol;

use wolfcore\network\mcpe\protocol\serializer\PacketSerializer;

class DisconnectPacket extends DataPacket implements ClientboundPacket, ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::DISCONNECT_PACKET;

	public ?string $message = null;

	/**
	 * 1.20.40+ (protocol 622) adds a disconnect reason before the
	 * hide-screen flag. Zero means the legacy generic disconnect reason.
	 * Kept optional to preserve old create() calls and plugin API.
	 */
	public int $reason = 0;

	/**
	 * @generate-create-func
	 */
	public static function create(?string $message) : self{
		$result = new self;
		$result->message = $message;
		return $result;
	}

	public function canBeSentBeforeLogin() : bool{
		return true;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->reason = $in->getProtocolVersion() >= 622 ? $in->getVarInt() : 0;
		$hideDisconnectionScreen = $in->getBool();
		$this->message = !$hideDisconnectionScreen ? $in->getString() : null;
	}

	protected function encodePayload(PacketSerializer $out) : void{
		if($out->getProtocolVersion() >= 622){
			$out->putVarInt($this->reason);
		}
		$out->putBool($this->message === null);
		if($this->message !== null){
			$out->putString($this->message);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleDisconnect($this);
	}
}
