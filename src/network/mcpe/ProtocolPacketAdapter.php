<?php

declare(strict_types=1);

namespace wolfcore\network\mcpe;

use wolfcore\network\mcpe\convert\BlockNetworkHash;
use wolfcore\network\mcpe\convert\TypeConverter;
use wolfcore\network\mcpe\protocol\ClientboundPacket;
use wolfcore\network\mcpe\protocol\AddActorPacket;
use wolfcore\network\mcpe\protocol\types\entity\EntityIds;
use wolfcore\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use wolfcore\network\mcpe\protocol\types\entity\IntMetadataProperty;
use wolfcore\network\mcpe\protocol\MobEquipmentPacket;
use wolfcore\network\mcpe\protocol\MobArmorEquipmentPacket;
use wolfcore\network\mcpe\protocol\AddItemActorPacket;
use wolfcore\network\mcpe\protocol\types\inventory\ItemStack;
use wolfcore\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use wolfcore\network\mcpe\protocol\LevelEventPacket;
use wolfcore\network\mcpe\protocol\LevelSoundEventPacket;
use wolfcore\network\mcpe\protocol\UpdateBlockPacket;
use wolfcore\network\mcpe\protocol\UpdateSubChunkBlocksPacket;
use wolfcore\network\mcpe\protocol\ProtocolVersions;
use wolfcore\network\mcpe\protocol\types\UpdateSubChunkBlocksPacketEntry;
use wolfcore\network\mcpe\protocol\types\LevelEvent;
use wolfcore\network\mcpe\protocol\types\LevelSoundEvent;
use wolfcore\network\mcpe\protocol\types\ParticleIds;

/** Adapts core packet block runtime IDs to the target client's selected wire representation. */
final class ProtocolPacketAdapter{
    private function __construct(){}

    public static function adapt(ClientboundPacket $packet, int $protocol, bool $baseItemIds = false) : ClientboundPacket{
        if(!ProtocolVersions::usesHashedBlockIds($protocol)){
            return $packet;
        }
        // Falling blocks carry their block runtime ID as VARIANT metadata
        // rather than in UpdateBlockPacket. Keep other actors' VARIANT intact.
        if($packet instanceof AddActorPacket && $packet->type === EntityIds::FALLING_BLOCK){
            $variant = $packet->metadata[EntityMetadataProperties::VARIANT] ?? null;
            if($variant instanceof IntMetadataProperty){
                $copy = clone $packet;
                $copy->metadata[EntityMetadataProperties::VARIANT] = new IntMetadataProperty(
                    BlockNetworkHash::toSigned32(BlockNetworkHash::baseIdToHash($variant->getValue(), $protocol))
                );
                return $copy;
            }
        }
        if($baseItemIds && ($packet instanceof MobEquipmentPacket || $packet instanceof AddItemActorPacket)){
            $copy = clone $packet;
            $copy->item = self::convertBaseStackWrapper($copy->item, $protocol);
            return $copy;
        }
        if($baseItemIds && $packet instanceof MobArmorEquipmentPacket){
            $copy = clone $packet;
            $copy->head = self::convertBaseStackWrapper($copy->head, $protocol);
            $copy->chest = self::convertBaseStackWrapper($copy->chest, $protocol);
            $copy->legs = self::convertBaseStackWrapper($copy->legs, $protocol);
            $copy->feet = self::convertBaseStackWrapper($copy->feet, $protocol);
            return $copy;
        }
        if($packet instanceof UpdateBlockPacket){
            $copy = clone $packet;
            $copy->blockRuntimeId = BlockNetworkHash::baseIdToHash($packet->blockRuntimeId, $protocol);
            return $copy;
        }
        if($packet instanceof UpdateSubChunkBlocksPacket){
            $convert = static function(array $updates) use ($protocol) : array{
                $out = [];
                foreach($updates as $entry){
                    $out[] = new UpdateSubChunkBlocksPacketEntry(
                        $entry->getBlockPosition(), BlockNetworkHash::baseIdToHash($entry->getBlockRuntimeId(), $protocol),
                        $entry->getFlags(), $entry->getSyncedUpdateActorUniqueId(), $entry->getSyncedUpdateType()
                    );
                }
                return $out;
            };
            return UpdateSubChunkBlocksPacket::create($packet->getBaseBlockPosition(), $convert($packet->getLayer0Updates()), $convert($packet->getLayer1Updates()));
        }
        if($packet instanceof LevelSoundEventPacket && in_array($packet->sound, [
            LevelSoundEvent::PLACE, LevelSoundEvent::BREAK,
            LevelSoundEvent::HIT, LevelSoundEvent::ITEM_USE_ON,
            // These events ALSO carry the block's network runtime ID in
            // extraData. LAND was left out in the previous 594 adapter,
            // causing a grass landing to play the sound of a different block.
            LevelSoundEvent::LAND, LevelSoundEvent::STEP, LevelSoundEvent::HEAVY_STEP
        ], true) && $packet->extraData >= 0){
            $copy = clone $packet;
            $copy->extraData = BlockNetworkHash::toSigned32(BlockNetworkHash::baseIdToHash($packet->extraData, $protocol));
            return $copy;
        }
        if($packet instanceof LevelEventPacket){
            $id = $packet->eventId;
            if($id === LevelEvent::PARTICLE_DESTROY || $id === LevelEvent::PARTICLE_PUNCH_BLOCK){
                $copy = clone $packet;
                if($id === LevelEvent::PARTICLE_PUNCH_BLOCK){
                    // Legacy 589 overlays the face in bits 24..26 of the 24-bit block ID.
                    // 594 uses all 32 bits for the hash. Encode the face in the event ID.
                    $face = ($packet->eventData >> 24) & 0xff;
                    $copy->eventId = match($face){
                        0 => LevelEvent::PARTICLE_PUNCH_BLOCK_DOWN,
                        1 => LevelEvent::PARTICLE_PUNCH_BLOCK_UP,
                        2 => LevelEvent::PARTICLE_PUNCH_BLOCK_NORTH,
                        3 => LevelEvent::PARTICLE_PUNCH_BLOCK_SOUTH,
                        4 => LevelEvent::PARTICLE_PUNCH_BLOCK_WEST,
                        5 => LevelEvent::PARTICLE_PUNCH_BLOCK_EAST,
                        default => LevelEvent::PARTICLE_PUNCH_BLOCK_DOWN
                    };
                    $copy->eventData = BlockNetworkHash::toSigned32(
                        BlockNetworkHash::baseIdToHash($packet->eventData & 0xffffff, $protocol)
                    );
                }else{
                    $copy->eventData = BlockNetworkHash::toSigned32(BlockNetworkHash::baseIdToHash($packet->eventData, $protocol));
                }
                return $copy;
            }
            if($id === (LevelEvent::ADD_PARTICLE_MASK | ParticleIds::TERRAIN)){
                $copy = clone $packet;
                $copy->eventData = BlockNetworkHash::toSigned32(BlockNetworkHash::baseIdToHash($packet->eventData, $protocol));
                if($protocol >= 649){
                    $copy->eventId = LevelEvent::ADD_PARTICLE_MASK | (ParticleIds::TERRAIN + 1);
                }
                return $copy;
            }
            if($protocol >= 649 && ($id & LevelEvent::ADD_PARTICLE_MASK) === LevelEvent::ADD_PARTICLE_MASK){
                $particleId = $id & ~LevelEvent::ADD_PARTICLE_MASK;
                if($particleId >= 18 && $particleId <= 255){
                    $copy = clone $packet;
                    $copy->eventId = LevelEvent::ADD_PARTICLE_MASK | ($particleId + 1);
                    return $copy;
                }
            }
        }
        return $packet;
    }

    /**
     * Broadcast packets are encoded using the core (589) item dictionary.
     * Direct packets from a NetworkSession/InventoryManager already use the
     * target session's item dictionary and MUST NOT be translated again.
     * The caller marks broadcast packets explicitly; numeric IDs cannot be
     * distinguished by value because 589/649 namespaces overlap.
     */
    private static function convertBaseStackWrapper(ItemStackWrapper $wrapper, int $protocol) : ItemStackWrapper{
        $item = $wrapper->getItemStack();
        if($item->isNull()){
            return $wrapper;
        }
        // Non-block items have runtime block ID zero. They still require
        // remapping (e.g. diamond sword 318 in 589 -> 320 in 649).
        $baseConverter = TypeConverter::getInstance();
        $wireConverter = TypeConverter::getForProtocol($protocol);
        $coreItem = $baseConverter->netItemStackToCore($item);
        $translated = $wireConverter->coreItemStackToNet($coreItem);
        return new ItemStackWrapper($wrapper->getStackId(), new ItemStack(
            $translated->getId(), $translated->getMeta(), $item->getCount(),
            $translated->getBlockRuntimeId(),
            $item->getNbt(), $item->getCanPlaceOn(), $item->getCanDestroy(),
            $translated->getShieldBlockingTick()
        ));
    }
}
