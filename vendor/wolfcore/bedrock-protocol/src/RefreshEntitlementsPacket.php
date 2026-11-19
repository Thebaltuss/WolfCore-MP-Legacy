<?php
/*
 * Protocol 618+ client-to-server entitlement refresh notification.
 * Empty body by design (Bedrock packet ID 305).
 */
declare(strict_types=1);
namespace wolfcore\network\mcpe\protocol;
use wolfcore\network\mcpe\protocol\serializer\PacketSerializer;
final class RefreshEntitlementsPacket extends DataPacket implements ServerboundPacket{
    public const NETWORK_ID = ProtocolInfo::REFRESH_ENTITLEMENTS_PACKET;
    public static function create() : self{ return new self(); }
    protected function decodePayload(PacketSerializer $in) : void{}
    protected function encodePayload(PacketSerializer $out) : void{}
    public function handle(PacketHandlerInterface $handler) : bool{ return false; }
}
