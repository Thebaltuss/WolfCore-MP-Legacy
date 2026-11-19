<?php

declare(strict_types=1);

namespace wolfcore\network\mcpe\protocol;

use wolfcore\math\Vector3;
use wolfcore\network\mcpe\protocol\serializer\PacketSerializer;

/** Bedrock 630 packet #16; clientbound position after server-authoritative movement. */
final class ServerPlayerPostMovePositionPacket extends DataPacket implements ClientboundPacket{
    public const NETWORK_ID = ProtocolInfo::SERVER_PLAYER_POST_MOVE_POSITION_PACKET;

    public Vector3 $position;

    public static function create(Vector3 $position) : self{
        $pk = new self();
        $pk->position = $position;
        return $pk;
    }

    protected function decodePayload(PacketSerializer $in) : void{
        $this->position = $in->getVector3();
    }

    protected function encodePayload(PacketSerializer $out) : void{
        if($out->getProtocolVersion() < 630){
            throw new \LogicException('ServerPostMovePosition requires protocol 630+');
        }
        $out->putVector3($this->position);
    }

    public function handle(PacketHandlerInterface $handler) : bool{
        return method_exists($handler, 'handleServerPlayerPostMovePosition') ? $handler->handleServerPlayerPostMovePosition($this) : false;
    }
}
