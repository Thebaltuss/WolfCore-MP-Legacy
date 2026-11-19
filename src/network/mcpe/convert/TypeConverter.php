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

use wolfcore\block\VanillaBlocks;
use wolfcore\crafting\ExactRecipeIngredient;
use wolfcore\crafting\MetaWildcardRecipeIngredient;
use wolfcore\crafting\RecipeIngredient;
use wolfcore\crafting\TagWildcardRecipeIngredient;
use wolfcore\data\bedrock\BedrockDataFiles;
use wolfcore\data\bedrock\item\BlockItemIdMap;
use wolfcore\item\Item;
use wolfcore\item\VanillaItems;
use wolfcore\nbt\NbtException;
use wolfcore\nbt\tag\CompoundTag;
use wolfcore\network\mcpe\protocol\serializer\ItemTypeDictionary;
use wolfcore\network\mcpe\protocol\ProtocolVersions;
use wolfcore\network\mcpe\protocol\types\GameMode as ProtocolGameMode;
use wolfcore\network\mcpe\protocol\types\inventory\ItemStack;
use wolfcore\network\mcpe\protocol\types\recipe\IntIdMetaItemDescriptor;
use wolfcore\network\mcpe\protocol\types\recipe\RecipeIngredient as ProtocolRecipeIngredient;
use wolfcore\network\mcpe\protocol\types\recipe\StringIdMetaItemDescriptor;
use wolfcore\network\mcpe\protocol\types\recipe\TagItemDescriptor;
use wolfcore\player\GameMode;
use wolfcore\utils\AssumptionFailedError;
use wolfcore\utils\Filesystem;
use wolfcore\utils\SingletonTrait;
use wolfcore\world\format\io\GlobalBlockStateHandlers;
use wolfcore\world\format\io\GlobalItemDataHandlers;
use function get_class;

class TypeConverter{
	use SingletonTrait;

	private const PM_ID_TAG = "___Id___";

	private const RECIPE_INPUT_WILDCARD_META = 0x7fff;

	private BlockItemIdMap $blockItemIdMap;
	private BlockTranslator $blockTranslator;
	private ItemTranslator $itemTranslator;
	private ItemTypeDictionary $itemTypeDictionary;
	private int $shieldRuntimeId;
	private int $protocol;

	private SkinAdapter $skinAdapter;

    /** @var array<int, TypeConverter> */
    private static array $protocolConverters = [];

    public static function getForProtocol(int $protocol) : self{
        if(!ProtocolVersions::usesHashedBlockIds($protocol)){
            return self::getInstance();
        }
        return self::$protocolConverters[$protocol] ??= new self($protocol);
    }

	public function __construct(int $protocol = ProtocolVersions::BASE_PROTOCOL){
        $this->protocol = $protocol;
		//TODO: inject stuff via constructor
		$this->blockItemIdMap = BlockItemIdMap::getInstance();

		$canonicalBlockStatesRaw = Filesystem::fileGetContents(BedrockDataFiles::CANONICAL_BLOCK_STATES_NBT);
		$metaMappingRaw = Filesystem::fileGetContents(BedrockDataFiles::BLOCK_STATE_META_MAP_JSON);
        $baseDictionary = BlockStateDictionary::loadFromString($canonicalBlockStatesRaw, $metaMappingRaw);
        $this->blockTranslator = new BlockTranslator(
            ProtocolVersions::usesHashedBlockIds($protocol) ? BlockNetworkHash::hashedDictionary($baseDictionary, $protocol) : $baseDictionary,
            GlobalBlockStateHandlers::getSerializer()
        );

		$this->itemTypeDictionary = ItemTypeDictionaryFromDataHelper::loadFromString(Filesystem::fileGetContents(
            match(ProtocolVersions::getItemPaletteProtocol($protocol)){
                649 => BedrockDataFiles::REQUIRED_ITEM_LIST_649_JSON,
                630 => BedrockDataFiles::REQUIRED_ITEM_LIST_630_JSON,
                618 => BedrockDataFiles::REQUIRED_ITEM_LIST_618_JSON,
                594 => BedrockDataFiles::REQUIRED_ITEM_LIST_594_JSON,
                default => BedrockDataFiles::REQUIRED_ITEM_LIST_JSON,
            }
        ));
		$this->shieldRuntimeId = $this->itemTypeDictionary->fromStringId("minecraft:shield");

		$this->itemTranslator = new ItemTranslator(
			$this->itemTypeDictionary,
			$this->blockTranslator->getBlockStateDictionary(),
			GlobalItemDataHandlers::getSerializer(),
			GlobalItemDataHandlers::getDeserializer(),
			$this->blockItemIdMap,
            ProtocolVersions::usesHashedBlockIds($protocol),
            $protocol
		);

		$this->skinAdapter = new LegacySkinAdapter();
	}

	public function getBlockTranslator() : BlockTranslator{ return $this->blockTranslator; }

	public function getItemTypeDictionary() : ItemTypeDictionary{ return $this->itemTypeDictionary; }

	public function getItemTranslator() : ItemTranslator{ return $this->itemTranslator; }

	public function getSkinAdapter() : SkinAdapter{ return $this->skinAdapter; }

	public function setSkinAdapter(SkinAdapter $skinAdapter) : void{
		$this->skinAdapter = $skinAdapter;
	}

	/**
	 * Returns a client-friendly gamemode of the specified real gamemode
	 * This function takes care of handling gamemodes known to MCPE (as of 1.1.0.3, that includes Survival, Creative and Adventure)
	 *
	 * @internal
	 */
	public function coreGameModeToProtocol(GameMode $gamemode) : int{
		switch($gamemode->id()){
			case GameMode::SURVIVAL()->id():
				return ProtocolGameMode::SURVIVAL;
			case GameMode::CREATIVE()->id():
			case GameMode::SPECTATOR()->id():
				return ProtocolGameMode::CREATIVE;
			case GameMode::ADVENTURE()->id():
				return ProtocolGameMode::ADVENTURE;
			default:
				throw new AssumptionFailedError("Unknown game mode");
		}
	}

	public function protocolGameModeToCore(int $gameMode) : ?GameMode{
		switch($gameMode){
			case ProtocolGameMode::SURVIVAL:
				return GameMode::SURVIVAL();
			case ProtocolGameMode::CREATIVE:
				return GameMode::CREATIVE();
			case ProtocolGameMode::ADVENTURE:
				return GameMode::ADVENTURE();
			case ProtocolGameMode::CREATIVE_VIEWER:
			case ProtocolGameMode::SURVIVAL_VIEWER:
				return GameMode::SPECTATOR();
			default:
				return null;
		}
	}

	public function coreRecipeIngredientToNet(?RecipeIngredient $ingredient) : ProtocolRecipeIngredient{
		if($ingredient === null){
			return new ProtocolRecipeIngredient(null, 0);
		}
		if($ingredient instanceof MetaWildcardRecipeIngredient){
            // The base 589 crafting-table recipe accepts any legacy wood
            // metadata of minecraft:planks. In 630 these are separate item
            // identifiers, so the legacy numeric ID is no longer valid.
            // The native minecraft:planks item tag represents that group.
            if($this->protocol >= 630 && $ingredient->getItemId() === 'minecraft:planks'){
                $descriptor = new TagItemDescriptor('minecraft:planks');
            }else{
                $id = $this->itemTypeDictionary->fromStringId($ingredient->getItemId());
                $descriptor = new IntIdMetaItemDescriptor($id, self::RECIPE_INPUT_WILDCARD_META);
            }
		}elseif($ingredient instanceof ExactRecipeIngredient){
			$item = $ingredient->getItem();
			[$id, $meta, $blockRuntimeId] = $this->itemTranslator->toNetworkId($item);
			if($blockRuntimeId !== null){
                // 594 concrete variants use distinct item identifiers with no legacy colour metadata.
                $wireBlock = $this->blockTranslator->getBlockStateDictionary()->generateDataFromStateId($blockRuntimeId);
                $is594Concrete = $this->protocol >= 594 && $wireBlock !== null && BlockNetworkHash::flattenedBaseName($wireBlock->getName(), $this->protocol) !== null;
				$meta = $this->blockTranslator->getBlockStateDictionary()->getMetaFromStateId($blockRuntimeId);
				if($meta === null){
					throw new AssumptionFailedError("Every block state should have an associated meta value");
				}
                if($is594Concrete){
                    $meta = 0;
                }
			}
			$descriptor = new IntIdMetaItemDescriptor($id, $meta);
		}elseif($ingredient instanceof TagWildcardRecipeIngredient){
			$descriptor = new TagItemDescriptor($ingredient->getTagName());
		}else{
			throw new \LogicException("Unsupported recipe ingredient type " . get_class($ingredient) . ", only " . ExactRecipeIngredient::class . " and " . MetaWildcardRecipeIngredient::class . " are supported");
		}

		return new ProtocolRecipeIngredient($descriptor, 1);
	}

	public function netRecipeIngredientToCore(ProtocolRecipeIngredient $ingredient) : ?RecipeIngredient{
		$descriptor = $ingredient->getDescriptor();
		if($descriptor === null){
			return null;
		}

		if($descriptor instanceof TagItemDescriptor){
			return new TagWildcardRecipeIngredient($descriptor->getTag());
		}

		if($descriptor instanceof IntIdMetaItemDescriptor){
			$stringId = $this->itemTypeDictionary->fromIntId($descriptor->getId());
			$meta = $descriptor->getMeta();
		}elseif($descriptor instanceof StringIdMetaItemDescriptor){
			$stringId = $descriptor->getId();
			$meta = $descriptor->getMeta();
		}else{
			throw new \LogicException("Unsupported conversion of recipe ingredient to core item stack");
		}

		if($meta === self::RECIPE_INPUT_WILDCARD_META){
            // The client may send a wildcard for an individual flattened item.
            // It is no longer an all-metadata base item in 630, so preserve
            // its exact variant (rather than permitting every plank colour).
            if($this->protocol >= 630 && BlockNetworkHash::flattenedBaseName($stringId, $this->protocol) !== null){
                $wireId = $this->blockTranslator->getBlockStateDictionary()->lookupStateIdFromData(
                    new \wolfcore\data\bedrock\block\BlockStateData($stringId, [], 0)
                );
                if($wireId !== null){
                    return new ExactRecipeIngredient($this->itemTranslator->fromNetworkId(
                        $this->itemTypeDictionary->fromStringId($stringId), 0, $wireId
                    ));
                }
            }
			return new MetaWildcardRecipeIngredient($stringId);
		}

		$blockRuntimeId = null;
        // In 594 each concrete colour is a distinct item ID, but recipe
        // descriptors don't contain a block-state hash. Resolve by name.
        if($this->protocol >= 594 && BlockNetworkHash::flattenedBaseName($stringId, $this->protocol) !== null){
            $blockRuntimeId = $this->blockTranslator->getBlockStateDictionary()->lookupStateIdFromData(
                new \wolfcore\data\bedrock\block\BlockStateData($stringId, [], 0)
            );
            if($blockRuntimeId === null){
                throw new TypeConversionException("No 594 block state for recipe ingredient " . $stringId);
            }
            $meta = 0;
        }elseif(($blockId = $this->blockItemIdMap->lookupBlockId($stringId)) !== null){
			$blockRuntimeId = $this->blockTranslator->getBlockStateDictionary()->lookupStateIdFromIdMeta($blockId, $meta);
			if($blockRuntimeId !== null){
				$meta = 0;
			}
		}
		$result = $this->itemTranslator->fromNetworkId(
			$this->itemTypeDictionary->fromStringId($stringId),
			$meta,
			$blockRuntimeId ?? ItemTranslator::NO_BLOCK_RUNTIME_ID
		);
		return new ExactRecipeIngredient($result);
	}

	public function coreItemStackToNet(Item $itemStack) : ItemStack{
		if($itemStack->isNull()){
			return ItemStack::null();
		}
		$nbt = $itemStack->getNamedTag();
		if($nbt->count() === 0){
			$nbt = null;
		}else{
			$nbt = clone $nbt;
		}

		$idMeta = $this->itemTranslator->toNetworkIdQuiet($itemStack);
		if($idMeta === null){
			//Display unmapped items as INFO_UPDATE, but stick something in their NBT to make sure they don't stack with
			//other unmapped items.
			[$id, $meta, $blockRuntimeId] = $this->itemTranslator->toNetworkId(VanillaBlocks::INFO_UPDATE()->asItem());
			if($nbt === null){
				$nbt = new CompoundTag();
			}
			$nbt->setLong(self::PM_ID_TAG, $itemStack->getStateId());
		}else{
			[$id, $meta, $blockRuntimeId] = $idMeta;
		}

		return new ItemStack(
			$id,
			$meta,
			$itemStack->getCount(),
			$blockRuntimeId ?? ItemTranslator::NO_BLOCK_RUNTIME_ID,
			$nbt,
			[],
			[],
			$id === $this->shieldRuntimeId ? 0 : null
		);
	}

	/**
	 * @throws TypeConversionException
	 */
	public function netItemStackToCore(ItemStack $itemStack) : Item{
		if($itemStack->getId() === 0){
			return VanillaItems::AIR();
		}
		$compound = $itemStack->getNbt();

		$itemResult = $this->itemTranslator->fromNetworkId($itemStack->getId(), $itemStack->getMeta(), $itemStack->getBlockRuntimeId());

		if($compound !== null){
			$compound = clone $compound;
		}

		$itemResult->setCount($itemStack->getCount());
		if($compound !== null){
			try{
				$itemResult->setNamedTag($compound);
			}catch(NbtException $e){
				throw TypeConversionException::wrap($e, "Bad itemstack NBT data");
			}
		}

		return $itemResult;
	}
}
