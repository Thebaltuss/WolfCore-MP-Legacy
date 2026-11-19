<?php

declare(strict_types=1);

namespace wolfcore\network\mcpe\protocol;

use wolfcore\network\mcpe\protocol\serializer\PacketSerializer;
use wolfcore\network\mcpe\protocol\types\BlockPosition;

/** Bedrock 630+, client request to disable a slot on the 1.20.50 crafter. */
final class PlayerToggleCrafterSlotRequestPacket extends DataPacket implements ServerboundPacket{
    public const NETWORK_ID = ProtocolInfo::PLAYER_TOGGLE_CRAFTER_SLOT_REQUEST_PACKET;

    public BlockPosition $position;
    public int $slot;
    public bool $disabled;

    public static function create(BlockPosition $position, int $slot, bool $disabled) : self{
        if($slot < 0 || $slot > 8){
            throw new \InvalidArgumentException('Crafter slot index must be in range 0..8');
        }
        $pk = new self();
        $pk->position = $position;
        $pk->slot = $slot;
        $pk->disabled = $disabled;
        return $pk;
    }

    protected function decodePayload(PacketSerializer $in) : void{
        $this->position = $in->getBlockPosition();
        $this->slot = $in->getByte();
        $this->disabled = $in->getBool();
        if($this->slot > 8){
            throw new PacketDecodeException('Invalid crafter slot ' . $this->slot);
        }
    }

    protected function encodePayload(PacketSerializer $out) : void{
        if($out->getProtocolVersion() < 630){
            throw new \LogicException('Crafter slot request requires protocol 630+');
        }
        $out->putBlockPosition($this->position);
        $out->putByte($this->slot);
        $out->putBool($this->disabled);
    }

    public function handle(PacketHandlerInterface $handler) : bool{
        // Keep PacketHandlerInterface ABI unchanged (plugin API 1.0.0).
        return method_exists($handler, 'handlePlayerToggleCrafterSlotRequest') ? $handler->handlePlayerToggleCrafterSlotRequest($this) : false;
    }
}
