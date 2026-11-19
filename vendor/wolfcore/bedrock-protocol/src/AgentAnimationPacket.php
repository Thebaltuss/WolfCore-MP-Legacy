<?php
/* Protocol 594, Minecraft Bedrock 1.20.10; Educational Agent animation. */
declare(strict_types=1);
namespace wolfcore\network\mcpe\protocol;
use wolfcore\network\mcpe\protocol\serializer\PacketSerializer;
final class AgentAnimationPacket extends DataPacket implements ClientboundPacket{
    public const NETWORK_ID = ProtocolInfo::AGENT_ANIMATION_PACKET;
    private int $animation = 0;
    private int $actorRuntimeId = 0;
    public static function create(int $animation, int $actorRuntimeId) : self{
        $pkt = new self(); $pkt->animation=$animation; $pkt->actorRuntimeId=$actorRuntimeId;return $pkt;
    }
    public function getAnimation() : int{return $this->animation;}
    public function getActorRuntimeId() : int{return $this->actorRuntimeId;}
    protected function decodePayload(PacketSerializer $in) : void{
        $this->animation=$in->getByte();
        $this->actorRuntimeId=$in->getActorRuntimeId();
    }
    protected function encodePayload(PacketSerializer $out) : void{
        $out->putByte($this->animation);
        $out->putActorRuntimeId($this->actorRuntimeId);
    }
    public function handle(PacketHandlerInterface $handler) : bool{return false;}
}
