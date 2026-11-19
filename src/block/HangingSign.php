<?php

declare(strict_types=1);

namespace wolfcore\block;

use wolfcore\data\runtime\RuntimeDataDescriber;
use wolfcore\item\Item;
use wolfcore\math\Axis;
use wolfcore\math\Facing;
use wolfcore\math\Vector3;
use wolfcore\player\Player;
use wolfcore\world\BlockTransaction;

/** Ceiling and wall variants of Bedrock hanging signs (1.20). */
class HangingSign extends BaseSign{
    private bool $hanging = true;
    private bool $attached = false;
    private int $facing = Facing::NORTH;
    private int $rotation = 0;

    protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
        $w->bool($this->hanging);
        $w->bool($this->attached);
        $w->horizontalFacing($this->facing);
        $w->boundedInt(4, 0, 15, $this->rotation);
    }

    public function isHanging() : bool{ return $this->hanging; }
    public function setHanging(bool $hanging) : self{ $this->hanging = $hanging; return $this; }
    public function isAttached() : bool{ return $this->attached; }
    public function setAttached(bool $attached) : self{ $this->attached = $attached; return $this; }
    public function getFacing() : int{ return $this->facing; }
    public function setFacing(int $facing) : self{ $this->facing = Facing::axis($facing) === Axis::Y ? Facing::NORTH : $facing; return $this; }
    public function getRotation() : int{ return $this->rotation; }
    public function setRotation(int $rotation) : self{
        if($rotation < 0 || $rotation > 15){
            throw new \InvalidArgumentException("Rotation must be in range 0-15");
        }
        $this->rotation = $rotation;
        return $this;
    }

    protected function getSupportingFace() : int{
        return $this->hanging ? Facing::UP : Facing::opposite($this->facing);
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
        if($face === Facing::UP){
            return false;
        }
        $this->hanging = $face === Facing::DOWN;
        $this->attached = false;
        if($this->hanging){
            if($player !== null){
                $yaw = $player->getLocation()->getYaw();
                $this->rotation = ((int) floor((($yaw + 180) * 16 / 360) + 0.5)) & 15;
            }
        }elseif(Facing::axis($face) !== Axis::Y){
            $this->facing = $face;
        }
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }
}
