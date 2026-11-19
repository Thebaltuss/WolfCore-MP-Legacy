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
use function intdiv;
use function strlen;

class ClientCacheBlobStatusPacket extends DataPacket implements ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::CLIENT_CACHE_BLOB_STATUS_PACKET;
	// Bound client-controlled hash arrays to prevent excessive allocations and CPU usage.
	private const MAX_TOTAL_HASHES = 4096;

	/** @var int[] xxHash64 subchunk data hashes */
	private array $hitHashes = [];
	/** @var int[] xxHash64 subchunk data hashes */
	private array $missHashes = [];

	/**
	 * @generate-create-func
	 * @param int[] $hitHashes
	 * @param int[] $missHashes
	 */
	public static function create(array $hitHashes, array $missHashes) : self{
		$result = new self;
		$result->hitHashes = $hitHashes;
		$result->missHashes = $missHashes;
		return $result;
	}

	/**
	 * @return int[]
	 */
	public function getHitHashes() : array{
		return $this->hitHashes;
	}

	/**
	 * @return int[]
	 */
	public function getMissHashes() : array{
		return $this->missHashes;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$missCount = $in->getUnsignedVarInt();
		$hitCount = $in->getUnsignedVarInt();
		// Check both counters before reading hashes. Subtraction prevents overflow on addition.
		if($missCount > self::MAX_TOTAL_HASHES || $hitCount > self::MAX_TOTAL_HASHES - $missCount){
			throw new PacketDecodeException("Too many client cache blob hashes (max " . self::MAX_TOTAL_HASHES . ")");
		}
		// Every xxHash64 is exactly 8 bytes. Reject truncated payloads before filling arrays.
		if($missCount + $hitCount > intdiv(strlen($in->getBuffer()) - $in->getOffset(), 8)){
			throw new PacketDecodeException("Truncated client cache blob hash list");
		}
		for($i = 0; $i < $missCount; ++$i){
			$this->missHashes[] = $in->getLLong();
		}
		for($i = 0; $i < $hitCount; ++$i){
			$this->hitHashes[] = $in->getLLong();
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putUnsignedVarInt(count($this->missHashes));
		$out->putUnsignedVarInt(count($this->hitHashes));
		foreach($this->missHashes as $hash){
			$out->putLLong($hash);
		}
		foreach($this->hitHashes as $hash){
			$out->putLLong($hash);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleClientCacheBlobStatus($this);
	}
}
