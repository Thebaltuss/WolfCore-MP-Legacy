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
use function count;

class ResourcePackClientResponsePacket extends DataPacket implements ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::RESOURCE_PACK_CLIENT_RESPONSE_PACKET;

	public const STATUS_REFUSED = 1;
	public const STATUS_SEND_PACKS = 2;
	public const STATUS_HAVE_ALL_PACKS = 3;
	public const STATUS_COMPLETED = 4;

	private const MAX_PACK_IDENTIFIER_LENGTH = 128;
	private const MAX_PACK_IDENTIFIERS = 256;

	public int $status;
	/** @var string[] */
	public array $packIds = [];

	/**
	 * @generate-create-func
	 * @param string[] $packIds
	 */
	public static function create(int $status, array $packIds) : self{
		$result = new self;
		$result->status = $status;
		$result->packIds = $packIds;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->status = $in->getByte();
		$entryCount = $in->getLShort();
		if($entryCount > self::MAX_PACK_IDENTIFIERS){
			throw new PacketDecodeException("Too many resource pack identifiers (maximum " . self::MAX_PACK_IDENTIFIERS . ")");
		}
		$this->packIds = [];
		while($entryCount-- > 0){
			$length = $in->getUnsignedVarInt();
			if($length > self::MAX_PACK_IDENTIFIER_LENGTH){
				throw new PacketDecodeException("Resource pack identifier exceeds maximum length");
			}
			$this->packIds[] = $in->get($length);
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putByte($this->status);
		$out->putLShort(count($this->packIds));
		foreach($this->packIds as $id){
			$out->putString($id);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleResourcePackClientResponse($this);
	}
}
