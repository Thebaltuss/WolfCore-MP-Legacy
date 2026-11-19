<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace wolfcore\network\mcpe\convert;

use wolfcore\data\bedrock\item\BlockItemIdMap;
use wolfcore\data\bedrock\item\ItemDeserializer;
use wolfcore\data\bedrock\item\ItemSerializer;
use wolfcore\data\bedrock\item\ItemTypeDeserializeException;
use wolfcore\data\bedrock\item\ItemTypeSerializeException;
use wolfcore\data\bedrock\item\SavedItemData;
use wolfcore\item\Item;
use wolfcore\nbt\tag\CompoundTag;
use wolfcore\network\mcpe\protocol\serializer\ItemTypeDictionary;
use wolfcore\utils\AssumptionFailedError;

/**
 * This class handles translation between network item ID+metadata to WolfCore-MP internal ID+metadata and vice versa.
 */
final class ItemTranslator{
	public const NO_BLOCK_RUNTIME_ID = 0; //this is technically a valid block runtime ID, but is used to represent "no block" (derp mojang)

	public function __construct(
		private ItemTypeDictionary $itemTypeDictionary,
		private BlockStateDictionary $blockStateDictionary,
		private ItemSerializer $itemSerializer,
		private ItemDeserializer $itemDeserializer,
		private BlockItemIdMap $blockItemIdMap,
        private bool $networkStatesUse594Names = false,
        private int $wireProtocol = 589
	){}

	/**
	 * @return int[]|null
	 * @phpstan-return array{int, int, ?int}|null
	 */
	public function toNetworkIdQuiet(Item $item) : ?array{
		try{
			return $this->toNetworkId($item);
		}catch(ItemTypeSerializeException){
			return null;
		}
	}

	/**
	 * @return int[]
	 * @phpstan-return array{int, int, ?int}
	 *
	 * @throws ItemTypeSerializeException
	 */
	public function toNetworkId(Item $item) : array{
		//TODO: we should probably come up with a cache for this

		$itemData = $this->itemSerializer->serializeType($item);

        $blockStateData = $itemData->getBlock();
        $itemName = $itemData->getName();
        if($this->networkStatesUse594Names && $blockStateData !== null){
            $wireName = BlockNetworkHash::baseToProtocolState($blockStateData, $this->wireProtocol)->getName();
            // Change the item identifier only for explicitly flattened blocks.
            // Other block items may have a distinct name from their block state.
            if(BlockNetworkHash::flattenedBaseName($wireName, $this->wireProtocol) === $itemName){
                $itemName = $wireName;
            }
        }
        $numericId = $this->itemTypeDictionary->fromStringId($itemName);

		if($blockStateData !== null){
			$blockRuntimeId = $this->blockStateDictionary->lookupStateIdFromData($blockStateData);
			if($blockRuntimeId === null){
				throw new AssumptionFailedError("Unmapped blockstate returned by blockstate serializer: " . $blockStateData->toNbt());
			}
		}else{
			$blockRuntimeId = null;
		}

        // Once a colour gets its own item ID, legacy colour metadata must not
        // be sent in the modern item stack (block states already carry colour).
        $wireMeta = $this->networkStatesUse594Names && BlockNetworkHash::flattenedBaseName($itemName, $this->wireProtocol) !== null
            ? 0 : $itemData->getMeta();
        return [$numericId, $wireMeta, $blockRuntimeId];
	}

	/**
	 * @throws ItemTypeSerializeException
	 */
	public function toNetworkNbt(Item $item) : CompoundTag{
		//TODO: this relies on the assumption that network item NBT is the same as disk item NBT, which may not always
		//be true - if we stick on an older world version while updating network version, this could be a problem (and
		//may be a problem for multi version implementations)
		return $this->itemSerializer->serializeStack($item)->toNbt();
	}

	/**
	 * @throws TypeConversionException
	 */
	public function fromNetworkId(int $networkId, int $networkMeta, int $networkBlockRuntimeId) : Item{
		try{
			$stringId = $this->itemTypeDictionary->fromIntId($networkId);
		}catch(\InvalidArgumentException $e){
			//TODO: a quiet version of fromIntId() would be better than catching InvalidArgumentException
			throw TypeConversionException::wrap($e, "Invalid network itemstack ID $networkId");
		}

        $flattenedBaseName = $this->networkStatesUse594Names ? BlockNetworkHash::flattenedBaseName($stringId, $this->wireProtocol) : null;
        $baseItemName = $flattenedBaseName ?? $stringId;
		$blockStateData = null;
		if($this->blockItemIdMap->lookupBlockId($baseItemName) !== null){
			$blockStateData = $this->blockStateDictionary->generateDataFromStateId($networkBlockRuntimeId);
			if($blockStateData === null){
				throw new TypeConversionException("Blockstate runtimeID $networkBlockRuntimeId does not correspond to any known blockstate");
			}
            if($flattenedBaseName !== null && $blockStateData->getName() !== $stringId){
                throw new TypeConversionException("Item $stringId has mismatched block state " . $blockStateData->getName());
            }
            if($this->networkStatesUse594Names){
                $blockStateData = BlockNetworkHash::protocolStateToBase($blockStateData, $this->wireProtocol);
            }
		}elseif($networkBlockRuntimeId !== self::NO_BLOCK_RUNTIME_ID){
			throw new TypeConversionException("Item $stringId is not a blockitem, but runtime ID $networkBlockRuntimeId was provided");
		}

		try{
			return $this->itemDeserializer->deserializeType(new SavedItemData($baseItemName, $networkMeta, $blockStateData));
		}catch(ItemTypeDeserializeException $e){
			throw TypeConversionException::wrap($e, "Invalid network itemstack data");
		}
	}
}
