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

class PurchaseReceiptPacket extends DataPacket implements ServerboundPacket{
	//Bound client-controlled allocations before reading any receipt contents.
	private const MAX_RECEIPTS = 64;
	private const MAX_RECEIPT_BYTES = 16 * 1024;
	private const MAX_TOTAL_RECEIPT_BYTES = 64 * 1024;
	//64 KiB of receipt data + 64 three-byte length prefixes + packet header/count overhead.
	//Checked before decoding to avoid processing oversized packets and trailing junk.
	public const MAX_RECEIPT_PACKET_BYTES = 64 * 1024 + 64 * 3 + 6;
	public const NETWORK_ID = ProtocolInfo::PURCHASE_RECEIPT_PACKET;

	/** @var string[] */
	public array $entries = [];

	/**
	 * @generate-create-func
	 * @param string[] $entries
	 */
	public static function create(array $entries) : self{
		$result = new self;
		$result->entries = $entries;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$count = $in->getUnsignedVarInt();
		if($count > self::MAX_RECEIPTS){
			throw new PacketDecodeException("Too many purchase receipts");
		}

		$totalBytes = 0;
		$this->entries = [];
		for($i = 0; $i < $count; ++$i){
			//Read the wire length first: getString() would allocate the string before a size check.
			$length = $in->getUnsignedVarInt();
			if($length > self::MAX_RECEIPT_BYTES || $length > self::MAX_TOTAL_RECEIPT_BYTES - $totalBytes){
				throw new PacketDecodeException("Purchase receipt data exceeds the allowed size");
			}
			$this->entries[] = $in->get($length);
			$totalBytes += $length;
		}
		if(!$in->feof()){
			throw new PacketDecodeException("Unexpected trailing data in purchase receipts");
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putUnsignedVarInt(count($this->entries));
		foreach($this->entries as $entry){
			$out->putString($entry);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handlePurchaseReceipt($this);
	}
}
