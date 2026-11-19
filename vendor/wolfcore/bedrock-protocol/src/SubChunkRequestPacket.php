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
use wolfcore\network\mcpe\protocol\types\SubChunkPosition;
use wolfcore\network\mcpe\protocol\types\SubChunkPositionOffset;
use function count;
use function intdiv;
use function strlen;

class SubChunkRequestPacket extends DataPacket implements ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::SUB_CHUNK_REQUEST_PACKET;
	//Bound client-controlled object allocations; this server does not support client subchunk requests.
	public const MAX_SUBCHUNK_REQUESTS = 256;

	private int $dimension;
	private SubChunkPosition $basePosition;
	/**
	 * @var SubChunkPositionOffset[]
	 * @phpstan-var list<SubChunkPositionOffset>
	 */
	private array $entries;

	/**
	 * @generate-create-func
	 * @param SubChunkPositionOffset[] $entries
	 * @phpstan-param list<SubChunkPositionOffset> $entries
	 */
	public static function create(int $dimension, SubChunkPosition $basePosition, array $entries) : self{
		$result = new self;
		$result->dimension = $dimension;
		$result->basePosition = $basePosition;
		$result->entries = $entries;
		return $result;
	}

	public function getDimension() : int{ return $this->dimension; }

	public function getBasePosition() : SubChunkPosition{ return $this->basePosition; }

	/**
	 * @return SubChunkPositionOffset[]
	 * @phpstan-return list<SubChunkPositionOffset>
	 */
	public function getEntries() : array{ return $this->entries; }

	protected function decodePayload(PacketSerializer $in) : void{
		$this->dimension = $in->getVarInt();
		$this->basePosition = SubChunkPosition::read($in);

		$count = $in->getLInt();
		if($count < 0 || $count > self::MAX_SUBCHUNK_REQUESTS){
			throw new PacketDecodeException("Invalid subchunk request count (max " . self::MAX_SUBCHUNK_REQUESTS . ")");
		}
		//Each entry is exactly 3 signed offset bytes. Detect truncated payloads before creating objects.
		if($count > intdiv(strlen($in->getBuffer()) - $in->getOffset(), 3)){
			throw new PacketDecodeException("Truncated subchunk request entries");
		}
		$this->entries = [];
		for($i = 0; $i < $count; ++$i){
			$this->entries[] = SubChunkPositionOffset::read($in);
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putVarInt($this->dimension);
		$this->basePosition->write($out);

		$out->putLInt(count($this->entries));
		foreach($this->entries as $entry){
			$entry->write($out);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleSubChunkRequest($this);
	}
}
