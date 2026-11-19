<?php

declare(strict_types=1);

namespace wolfcore\network\mcpe\protocol;

use wolfcore\network\mcpe\protocol\serializer\PacketSerializer;

/** Bedrock 630 inventory UI preferences. No 1.20.50 gameplay is added. */
final class SetPlayerInventoryOptionsPacket extends DataPacket implements ClientboundPacket, ServerboundPacket{
    public const NETWORK_ID = ProtocolInfo::SET_PLAYER_INVENTORY_OPTIONS_PACKET;

    public int $leftTab;
    public int $rightTab;
    public bool $filtering;
    public int $inventoryLayout;
    public int $craftingLayout;

    public static function create(int $leftTab, int $rightTab, bool $filtering, int $inventoryLayout, int $craftingLayout) : self{
        $pk = new self();
        $pk->leftTab = $leftTab;
        $pk->rightTab = $rightTab;
        $pk->filtering = $filtering;
        $pk->inventoryLayout = $inventoryLayout;
        $pk->craftingLayout = $craftingLayout;
        return $pk;
    }

    protected function decodePayload(PacketSerializer $in) : void{
        $this->leftTab = $in->getVarInt();
        $this->rightTab = $in->getVarInt();
        $this->filtering = $in->getBool();
        $this->inventoryLayout = $in->getVarInt();
        $this->craftingLayout = $in->getVarInt();
        $this->assertRanges();
    }

    private function assertRanges() : void{
        if($this->leftTab < 0 || $this->leftTab > 6 ||
           $this->rightTab < 0 || $this->rightTab > 3 ||
           $this->inventoryLayout < 0 || $this->inventoryLayout > 4 ||
           $this->craftingLayout < 0 || $this->craftingLayout > 4){
            throw new PacketDecodeException('Invalid 630 inventory option enum');
        }
    }

    protected function encodePayload(PacketSerializer $out) : void{
        if($out->getProtocolVersion() < 630){
            throw new \LogicException('Inventory options require protocol 630+');
        }
        $out->putVarInt($this->leftTab);
        $out->putVarInt($this->rightTab);
        $out->putBool($this->filtering);
        $out->putVarInt($this->inventoryLayout);
        $out->putVarInt($this->craftingLayout);
    }

    public function handle(PacketHandlerInterface $handler) : bool{
        return method_exists($handler, 'handleSetPlayerInventoryOptions') ? $handler->handleSetPlayerInventoryOptions($this) : false;
    }
}
