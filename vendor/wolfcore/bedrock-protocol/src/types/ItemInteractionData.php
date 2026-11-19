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

namespace wolfcore\network\mcpe\protocol\types;

use wolfcore\network\mcpe\protocol\PacketDecodeException;
use wolfcore\network\mcpe\protocol\serializer\PacketSerializer;
use wolfcore\network\mcpe\protocol\types\inventory\InventoryTransactionChangedSlotsHack;
use wolfcore\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use function count;
use function strlen;

final class ItemInteractionData{
	private const MAX_REQUEST_CHANGED_SLOTS = 10;
	/**
	 * @param InventoryTransactionChangedSlotsHack[] $requestChangedSlots
	 */
	public function __construct(
		private int $requestId,
		private array $requestChangedSlots,
		private UseItemTransactionData $transactionData
	){}

	public function getRequestId() : int{
		return $this->requestId;
	}

	/**
	 * @return InventoryTransactionChangedSlotsHack[]
	 */
	public function getRequestChangedSlots() : array{
		return $this->requestChangedSlots;
	}

	public function getTransactionData() : UseItemTransactionData{
		return $this->transactionData;
	}

	public static function read(PacketSerializer $in) : self{
		$requestId = $in->getVarInt();
		$requestChangedSlots = [];
		if($requestId !== 0){
			$len = $in->getUnsignedVarInt();
			if($len > self::MAX_REQUEST_CHANGED_SLOTS){
				throw new PacketDecodeException("Too many changed slot entries in item interaction ($len > " . self::MAX_REQUEST_CHANGED_SLOTS . ")");
			}
			// Each entry contains at least one container ID byte and a slot-count VarInt.
			// Reject truncated lists before constructing any entry objects.
			if($len * 2 > strlen($in->getBuffer()) - $in->getOffset()){
				throw new PacketDecodeException("Truncated changed slot entries in item interaction");
			}
			for($i = 0; $i < $len; ++$i){
				$requestChangedSlots[] = InventoryTransactionChangedSlotsHack::read($in);
			}
		}
		$transactionData = new UseItemTransactionData();
		$transactionData->decode($in);
		return new ItemInteractionData($requestId, $requestChangedSlots, $transactionData);
	}

	public function write(PacketSerializer $out) : void{
		$out->putVarInt($this->requestId);
		if($this->requestId !== 0){
			$out->putUnsignedVarInt(count($this->requestChangedSlots));
			foreach($this->requestChangedSlots as $changedSlot){
				$changedSlot->write($out);
			}
		}
		$this->transactionData->encode($out);
	}
}
