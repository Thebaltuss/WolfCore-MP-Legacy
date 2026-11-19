<?php
/* Bedrock camera packet 617+ structured wire format; old NBT format retained for 589/594. */
declare(strict_types=1);
namespace wolfcore\network\mcpe\protocol;

use wolfcore\nbt\tag\CompoundTag;
use wolfcore\network\mcpe\protocol\serializer\PacketSerializer;
use wolfcore\network\mcpe\protocol\types\CacheableNbt;

class CameraInstructionPacket extends DataPacket implements ClientboundPacket{
    public const NETWORK_ID = ProtocolInfo::CAMERA_INSTRUCTION_PACKET;
    private ?CacheableNbt $data = null;
    /** @var array<string, mixed>|null */
    private ?array $instructionSet = null;
    private ?bool $clear = null;
    /** @var array<string, mixed>|null */
    private ?array $fade = null;

    /** Legacy NBT API, used by protocols 589/594. */
    public static function create(CacheableNbt $data) : self{
        $result = new self(); $result->data = $data; return $result;
    }
    /**
     * Structured camera instructions (protocol 618 and 622).
     * @param array<string, mixed>|null $set
     * @param array<string, mixed>|null $fade
     */
    public static function createWithInstruction(?array $set, ?bool $clear = null, ?array $fade = null) : self{
        $result = new self(); $result->instructionSet = $set; $result->clear = $clear;
        $result->fade = $fade; return $result;
    }
    public function getData() : CacheableNbt{
        return $this->data ?? new CacheableNbt(CompoundTag::create());
    }
    public function getInstructionSet() : ?array{ return $this->instructionSet; }
    public function getClear() : ?bool{ return $this->clear; }
    public function getFade() : ?array{ return $this->fade; }
    protected function decodePayload(PacketSerializer $in) : void{
        if($in->getProtocolVersion() < 618){
            $this->data = new CacheableNbt($in->getNbtCompoundRoot()); return;
        }
        if($in->getBool()){
            $this->instructionSet = [
                'runtime_id' => $in->getLInt(),
                'ease_data' => $in->getBool() ? ['type'=>$in->getByte(), 'duration'=>$in->getLFloat()] : null,
                'position' => $in->getBool() ? self::getVector3($in) : null,
                'rotation' => $in->getBool() ? self::getVector2($in) : null,
                'facing' => $in->getBool() ? self::getVector3($in) : null,
                'default' => $in->getBool() ? $in->getBool() : null,
            ];
        }else{
            $this->instructionSet = null;
        }
        $this->clear = $in->getBool() ? $in->getBool() : null;
        $this->fade = $in->getBool() ? [
            'fade_in_duration'=>$in->getLFloat(),
            'wait_duration'=>$in->getLFloat(),
            'fade_out_duration'=>$in->getLFloat(),
            'color_rgb'=>self::getVector3($in),
        ] : null;
        $this->data = null;
    }
    protected function encodePayload(PacketSerializer $out) : void{
        if($out->getProtocolVersion() < 618){
            if($this->data === null){ throw new \InvalidArgumentException('Legacy camera instruction requires NBT'); }
            $out->put($this->data->getEncodedNbt()); return;
        }
        if($this->data !== null){
            throw new \InvalidArgumentException('Protocol 618+ camera instructions require createWithInstruction(), not NBT');
        }
        $s=$this->instructionSet;
        $out->putBool($s !== null);
        if($s !== null){
            $out->putLInt((int) ($s['runtime_id'] ?? 0));
            $ease=$s['ease_data'] ?? null;
            $out->putBool($ease !== null);
            if($ease !== null){
                $out->putByte((int) ($ease['type'] ?? 0));
                $out->putLFloat((float) ($ease['duration'] ?? 0));
            }
            self::putOptionalVector($out, $s['position'] ?? null, 3);
            self::putOptionalVector($out, $s['rotation'] ?? null, 2);
            self::putOptionalVector($out, $s['facing'] ?? null, 3);
            $default=$s['default'] ?? null;
            $out->putBool($default !== null);
            if($default !== null){ $out->putBool((bool) $default); }
        }
        $out->putBool($this->clear !== null);
        if($this->clear !== null){ $out->putBool($this->clear); }
        $f=$this->fade;
        $out->putBool($f !== null);
        if($f !== null){
            $out->putLFloat((float) ($f['fade_in_duration'] ?? 0));
            $out->putLFloat((float) ($f['wait_duration'] ?? 0));
            $out->putLFloat((float) ($f['fade_out_duration'] ?? 0));
            self::putVector($out, $f['color_rgb'] ?? [], 3);
        }
    }
    private static function getVector3(PacketSerializer $in) : array{
        return ['x'=>$in->getLFloat(), 'y'=>$in->getLFloat(), 'z'=>$in->getLFloat()];
    }
    private static function getVector2(PacketSerializer $in) : array{
        return ['x'=>$in->getLFloat(), 'y'=>$in->getLFloat()];
    }
    private static function putOptionalVector(PacketSerializer $out, ?array $coords, int $dimension) : void{
        $out->putBool($coords !== null);
        if($coords !== null){ self::putVector($out,$coords,$dimension); }
    }
    private static function putVector(PacketSerializer $out, array $coords, int $dimension) : void{
        foreach($dimension===3 ? ['x','y','z'] : ['x','y'] as $axis){
            $out->putLFloat((float) ($coords[$axis] ?? 0));
        }
    }
    public function handle(PacketHandlerInterface $handler) : bool{ return $handler->handleCameraInstruction($this); }
}
