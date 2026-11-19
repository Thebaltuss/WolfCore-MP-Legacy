<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace wolfcore\block;

use wolfcore\block\tile\Comparator;
use wolfcore\block\utils\AnalogRedstoneSignalEmitterTrait;
use wolfcore\block\utils\HorizontalFacingTrait;
use wolfcore\block\utils\PoweredByRedstoneTrait;
use wolfcore\block\utils\SupportType;
use wolfcore\data\runtime\RuntimeDataDescriber;
use wolfcore\item\Item;
use wolfcore\math\AxisAlignedBB;
use wolfcore\math\Facing;
use wolfcore\math\Vector3;
use wolfcore\player\Player;
use wolfcore\world\BlockTransaction;
use function assert;

class RedstoneComparator extends Flowable{
	use HorizontalFacingTrait;
	use AnalogRedstoneSignalEmitterTrait;
	use PoweredByRedstoneTrait;

	protected bool $isSubtractMode = false;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->horizontalFacing($this->facing);
		$w->bool($this->isSubtractMode);
		$w->bool($this->powered);
	}

	public function readStateFromWorld() : Block{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof Comparator){
			$this->signalStrength = $tile->getSignalStrength();
		}

		return $this;
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		assert($tile instanceof Comparator);
		$tile->setSignalStrength($this->signalStrength);
	}

	public function isSubtractMode() : bool{
		return $this->isSubtractMode;
	}

	/** @return $this */
	public function setSubtractMode(bool $isSubtractMode) : self{
		$this->isSubtractMode = $isSubtractMode;
		return $this;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [AxisAlignedBB::one()->trim(Facing::UP, 7 / 8)];
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($this->canBeSupportedBy($blockReplace->getSide(Facing::DOWN))){
			if($player !== null){
				// Bedrock "direction" stores the REAR/input face.
				$this->facing = Facing::opposite($player->getHorizontalFacing());
			}
			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		$this->isSubtractMode = !$this->isSubtractMode;
		$this->position->getWorld()->setBlock($this->position, $this);
		return true;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->canBeSupportedBy($this->getSide(Facing::DOWN))){
			$this->position->getWorld()->useBreakOn($this->position);
		}else{
			// Restart periodic inventory checks for comparators loaded from existing chunks.
			$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 5);
		}
	}

	private function canBeSupportedBy(Block $block) : bool{
		return !$block->getSupportType(Facing::UP)->equals(SupportType::NONE());
	}

	public function onPostPlace() : void{
		$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 5);
	}

	public function onScheduledUpdate() : void{
		\wolfcore\redstone\RedstoneEngine::comparatorScheduledTick($this);
		// Inventory contents change without block placement. Poll at a limited rate.
		$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 5);
	}
}
