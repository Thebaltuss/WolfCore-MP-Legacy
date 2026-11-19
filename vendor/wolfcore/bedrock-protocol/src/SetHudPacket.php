<?php

declare(strict_types=1);

namespace wolfcore\network\mcpe\protocol;

use wolfcore\network\mcpe\protocol\serializer\PacketSerializer;

/** Protocol 649 / 1.20.60 HUD visibility, independent of the 589 gameplay API. */
final class SetHudPacket extends DataPacket implements ClientboundPacket{
    public const NETWORK_ID = ProtocolInfo::SET_HUD_PACKET;

    /** @var int[] */
    public array $elements = [];
    /** 0 = hide, 1 = reset to default. */
    public int $visibility = 1;

    /** @param int[] $elements */
    public static function create(array $elements, int $visibility) : self{
        $packet = new self();
        $packet->elements = array_values($elements);
        $packet->visibility = $visibility;
        $packet->validate();
        return $packet;
    }

    private function validate() : void{
        if(count($this->elements) > 11 || !in_array($this->visibility, [0, 1], true)){
            throw new \InvalidArgumentException('Invalid 649 HUD settings');
        }
        foreach($this->elements as $element){
            if(!is_int($element) || $element < 0 || $element > 10){
                throw new \InvalidArgumentException('Invalid 649 HUD element');
            }
        }
    }

    protected function decodePayload(PacketSerializer $in) : void{
        if($in->getProtocolVersion() < 649){
            throw new PacketDecodeException('SetHud is only available for 649+');
        }
        $count = $in->getUnsignedVarInt();
        if($count > 11){
            throw new PacketDecodeException('Too many HUD elements');
        }
        $this->elements = [];
        for($i = 0; $i < $count; ++$i){
            $this->elements[] = $in->getByte();
        }
        $this->visibility = $in->getByte();
        $this->validate();
    }

    protected function encodePayload(PacketSerializer $out) : void{
        if($out->getProtocolVersion() < 649){
            throw new \LogicException('SetHud is only available for protocol 649+');
        }
        $this->validate();
        $out->putUnsignedVarInt(count($this->elements));
        foreach($this->elements as $element){
            $out->putByte($element);
        }
        $out->putByte($this->visibility);
    }

    public function handle(PacketHandlerInterface $handler) : bool{
        return method_exists($handler, 'handleSetHud') ? $handler->handleSetHud($this) : false;
    }
}
