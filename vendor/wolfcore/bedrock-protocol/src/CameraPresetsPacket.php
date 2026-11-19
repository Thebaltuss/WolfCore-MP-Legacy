<?php
/* Bedrock camera packet 617+ structured wire format; old NBT format retained for 589/594. */
declare(strict_types=1);
namespace wolfcore\network\mcpe\protocol;

use wolfcore\nbt\tag\CompoundTag;
use wolfcore\network\mcpe\protocol\serializer\PacketSerializer;
use wolfcore\network\mcpe\protocol\types\CacheableNbt;

class CameraPresetsPacket extends DataPacket implements ClientboundPacket{
    public const NETWORK_ID = ProtocolInfo::CAMERA_PRESETS_PACKET;
    private ?CacheableNbt $data = null;
    /** @var list<array<string, mixed>> */
    private array $presets = [];

    /** Legacy API for 589/594. For 618+ use createWithPresets(). */
    public static function create(CacheableNbt $data) : self{
        $result = new self(); $result->data = $data; return $result;
    }
    /** @param list<array<string, mixed>> $presets */
    public static function createWithPresets(array $presets) : self{
        $result = new self(); $result->presets = $presets; return $result;
    }
    public function getData() : CacheableNbt{
        return $this->data ?? new CacheableNbt(CompoundTag::create());
    }
    /** @return list<array<string, mixed>> */
    public function getPresets() : array{ return $this->presets; }

    protected function decodePayload(PacketSerializer $in) : void{
        if($in->getProtocolVersion() < 618){
            $this->data = new CacheableNbt($in->getNbtCompoundRoot()); return;
        }
        $count = $in->getUnsignedVarInt();
        if($count > 128){ throw new PacketDecodeException('Too many camera presets'); }
        $this->presets = [];
        for($i=0; $i<$count; ++$i){
            $item = [
                'name' => $in->getString(), 'parent' => $in->getString(),
                'position' => self::getOptionalCoordinates($in, ['x','y','z']),
                'rotation' => self::getOptionalCoordinates($in, ['x','y']),
                'audio_listener' => $in->getBool() ? $in->getByte() : null,
                'player_effects' => $in->getBool() ? $in->getBool() : null,
            ];
            $this->presets[] = $item;
        }
        $this->data = null;
    }
    protected function encodePayload(PacketSerializer $out) : void{
        if($out->getProtocolVersion() < 618){
            if($this->data === null){ throw new \InvalidArgumentException('Legacy camera presets require NBT'); }
            $out->put($this->data->getEncodedNbt()); return;
        }
        if($this->data !== null){
            throw new \InvalidArgumentException('Protocol 618+ camera presets require createWithPresets(), not NBT');
        }
        if(count($this->presets)>128){ throw new \InvalidArgumentException('Too many camera presets'); }
        $out->putUnsignedVarInt(count($this->presets));
        foreach($this->presets as $item){
            $out->putString((string) ($item['name'] ?? ''));
            $out->putString((string) ($item['parent'] ?? ''));
            self::putOptionalCoordinates($out, $item['position'] ?? [], ['x','y','z']);
            self::putOptionalCoordinates($out, $item['rotation'] ?? [], ['x','y']);
            $audio=$item['audio_listener'] ?? null;
            $out->putBool($audio !== null);
            if($audio !== null){ $out->putByte((int) $audio); }
            $effects=$item['player_effects'] ?? null;
            $out->putBool($effects !== null);
            if($effects !== null){ $out->putBool((bool) $effects); }
        }
    }
    /** @param list<string> $axes */
    private static function getOptionalCoordinates(PacketSerializer $in, array $axes) : array{
        $result=[];
        foreach($axes as $axis){ $result[$axis] = $in->getBool() ? $in->getLFloat() : null; }
        return $result;
    }
    /** @param array<string, float|null> $coords @param list<string> $axes */
    private static function putOptionalCoordinates(PacketSerializer $out, array $coords, array $axes) : void{
        foreach($axes as $axis){
            $v = $coords[$axis] ?? null; $out->putBool($v !== null);
            if($v !== null){ $out->putLFloat((float) $v); }
        }
    }
    public function handle(PacketHandlerInterface $handler) : bool{ return $handler->handleCameraPresets($this); }
}
