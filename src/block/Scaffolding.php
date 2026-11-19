<?php

/*
 * Scaffolding mechanics for Minecraft Bedrock Edition.
 * Stability 0-6 is supported; distance 7 falls, rather than refusing placement.
 */

declare(strict_types=1);

namespace wolfcore\block;

use wolfcore\block\utils\Fallable;
use wolfcore\block\utils\FallableTrait;
use wolfcore\block\utils\SupportType;
use wolfcore\data\runtime\RuntimeDataDescriber;
use wolfcore\entity\Entity;
use wolfcore\entity\Living;
use wolfcore\entity\Location;
use wolfcore\entity\object\FallingBlock;
use wolfcore\item\Item;
use wolfcore\math\AxisAlignedBB;
use wolfcore\math\Facing;
use wolfcore\math\Vector3;
use wolfcore\player\Player;
use wolfcore\world\BlockTransaction;

class Scaffolding extends Transparent implements Fallable{
    use FallableTrait {
        onNearbyBlockChange as private unusedFallableNearbyBlockChange;
    }

    private int $stability = 0;
    private bool $stabilityCheck = false;

    protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
        $w->boundedInt(3, 0, 7, $this->stability);
        $w->bool($this->stabilityCheck);
    }

    public function getStability() : int{
        return $this->stability;
    }

    public function setStability(int $stability) : self{
        if($stability < 0 || $stability > 7){
            throw new \InvalidArgumentException('Scaffolding stability must be from 0 to 7');
        }
        $this->stability = $stability;
        return $this;
    }

    public function hasStabilityCheck() : bool{
        return $this->stabilityCheck;
    }

    public function setStabilityCheck(bool $check) : self{
        $this->stabilityCheck = $check;
        return $this;
    }

    public function isSolid() : bool{
        return false;
    }

    public function canClimb() : bool{
        return true;
    }

    public function getSupportType(int $facing) : SupportType{
        return $facing === Facing::UP ? SupportType::FULL() : SupportType::NONE();
    }

    /**
     * The top acts as a walkable platform when standing on it. The world
     * collision handler permits players to pass through it when climbing
     * from inside/below or sneaking to descend.
     *
     * @return AxisAlignedBB[]
     */
    protected function recalculateCollisionBoxes() : array{
        return [AxisAlignedBB::one()->trim(Facing::DOWN, 14 / 16)];
    }

    public function hasEntityCollision() : bool{
        return true;
    }

    public function onEntityInside(Entity $entity) : bool{
        if($entity instanceof Living){
            $entity->resetFallDistance();
        }
        return true;
    }

    /**
     * In Bedrock the stability value is the horizontal distance from a
     * scaffold whose support is a solid block directly underneath.
     * Vertical columns retain the stability of the scaffold below.
     */
    private function measureStability() : int{
        $below = $this->getSide(Facing::DOWN);
        if($below instanceof self){
            return $below->getStability();
        }
        if($below->isSolid() && !$below->canBeReplaced()){
            return 0;
        }

        $minimum = 7;
        foreach($this->getHorizontalSides() as $neighbour){
            if($neighbour instanceof self){
                $minimum = min($minimum, $neighbour->getStability() + 1);
            }
        }
        return min(7, $minimum);
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
        // Distance 7 is a valid placement. It begins falling on the next
        // block tick if it does not acquire support first.
        $this->stability = $this->measureStability();
        $this->stabilityCheck = false;
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function onPostPlace() : void{
        $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
    }

    public function onNearbyBlockChange() : void{
        $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
    }

    public function onScheduledUpdate() : void{
        $newStability = $this->measureStability();
        $world = $this->position->getWorld();

        if($newStability >= 7){
            // An unsupported scaffold becomes a falling block (Bedrock).
            // Don't destroy it and don't duplicate an item drop here.
            $pos = $this->position;
            $world->setBlock($pos, VanillaBlocks::AIR());
            $falling = new FallingBlock(Location::fromObject($pos->add(0.5, 0, 0.5), $world),
                (clone $this)->setStability(7)->setStabilityCheck(false));
            $falling->spawnToAll();
        }elseif($newStability !== $this->stability || $this->stabilityCheck){
            $world->setBlock($this->position,
                (clone $this)->setStability($newStability)->setStabilityCheck(false));
        }
    }

    public function getDropsForCompatibleTool(Item $item) : array{
        return [$this->asItem()];
    }

    public function getDropsForIncompatibleTool(Item $item) : array{
        return [$this->asItem()];
    }

    public function getFlammability() : int{
        return 60;
    }

    public function getFlameEncouragement() : int{
        return 60;
    }
}
