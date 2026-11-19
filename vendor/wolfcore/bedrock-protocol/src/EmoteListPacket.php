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
use Ramsey\Uuid\UuidInterface;
use function count;
use function intdiv;
use function strlen;

class EmoteListPacket extends DataPacket implements ClientboundPacket, ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::EMOTE_LIST_PACKET;
	private const MAX_EMOTE_IDS = 512;
	public const MAX_EMOTE_LIST_PACKET_BYTES = self::MAX_EMOTE_IDS * 16 + 32; // UUIDs plus packet header and length prefixes

	private int $playerActorRuntimeId;
	/** @var UuidInterface[] */
	private array $emoteIds;

	/**
	 * @generate-create-func
	 * @param UuidInterface[] $emoteIds
	 */
	public static function create(int $playerActorRuntimeId, array $emoteIds) : self{
		$result = new self;
		$result->playerActorRuntimeId = $playerActorRuntimeId;
		$result->emoteIds = $emoteIds;
		return $result;
	}

	public function getPlayerActorRuntimeId() : int{ return $this->playerActorRuntimeId; }

	/** @return UuidInterface[] */
	public function getEmoteIds() : array{ return $this->emoteIds; }

	protected function decodePayload(PacketSerializer $in) : void{
		$this->playerActorRuntimeId = $in->getActorRuntimeId();
		$len = $in->getUnsignedVarInt();
		if($len > self::MAX_EMOTE_IDS){
			throw new PacketDecodeException("Too many emote UUIDs (max " . self::MAX_EMOTE_IDS . ")");
		}
		// Each UUID takes exactly 16 bytes. Reject truncated lists before allocating objects.
		if($len > intdiv(strlen($in->getBuffer()) - $in->getOffset(), 16)){
			throw new PacketDecodeException("Truncated emote UUID list");
		}
		$this->emoteIds = [];
		for($i = 0; $i < $len; ++$i){
			$this->emoteIds[] = $in->getUUID();
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putActorRuntimeId($this->playerActorRuntimeId);
		$out->putUnsignedVarInt(count($this->emoteIds));
		foreach($this->emoteIds as $emoteId){
			$out->putUUID($emoteId);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleEmoteList($this);
	}
}
