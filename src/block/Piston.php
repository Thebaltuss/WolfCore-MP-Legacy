<?php
/* WolfCore native piston - LGPL-3.0-or-later */
declare(strict_types=1);

namespace wolfcore\block;

use wolfcore\block\tile\PistonArm as PistonTile;
use wolfcore\data\runtime\RuntimeDataDescriber;
use wolfcore\item\Item;
use wolfcore\math\Facing;
use wolfcore\math\Axis;
use wolfcore\math\Vector3;
use wolfcore\player\Player;
use wolfcore\world\BlockTransaction;

class Piston extends Opaque{
	private int $facing = Facing::NORTH;
	private bool $extended = false;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo, private bool $sticky = false){
		parent::__construct($idInfo, $name, $typeInfo);
	}

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->facing($this->facing);
		$w->bool($this->extended);
	}
	/**
	 * Bedrock piston facing_direction uses the reverse horizontal indices to the
	 * generic PMMP facing mapping. Up/down retain the generic indices.
	 * Involution: the same conversion is used on serialization and loading.
	 */
	public static function networkFacing(int $facing) : int{
		Facing::validate($facing);
		return Facing::axis($facing) === Axis::Y ? $facing : Facing::opposite($facing);
	}

	public function getFacing() : int{ return $this->facing; }
	public function setFacing(int $facing) : self{
		Facing::validate($facing);
		$this->facing = $facing;
		return $this;
	}
	public function isExtended() : bool{ return $this->extended; }
	public function setExtended(bool $extended) : self{
		$this->extended = $extended;
		return $this;
	}
	public function isSticky() : bool{ return $this->sticky; }

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			$pitch = $player->getLocation()->pitch;
			$this->facing = $pitch < -45 ? Facing::DOWN : ($pitch > 45 ? Facing::UP : Facing::opposite($player->getHorizontalFacing()));
		}else{
			// Machine/plugin placement has no player look vector: point away from the clicked support face.
			$this->facing = $face;
		}
		$this->extended = false;
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void{
		// The redstone engine owns the two-tick activation delay. Do not also
		// schedule a second direct movement that can fire after the signal changed.
		\wolfcore\redstone\RedstoneEngine::queuePistonUpdate($this);
	}

	public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []) : bool{
		if($this->extended){
			$head = $this->getSide($this->facing);
			if($head instanceof PistonHead && $head->getFacing() === $this->facing){
				$this->position->getWorld()->setBlock($head->getPosition(), VanillaBlocks::AIR());
			}
		}
		return parent::onBreak($item, $player, $returnedItems);
	}

	public function onScheduledUpdate() : void{
		// Legacy queued updates may survive a reload; respect the same edge delay.
		\wolfcore\redstone\RedstoneEngine::queuePistonUpdate($this);
	}

	public function readStateFromWorld() : Block{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof PistonTile){
			$this->extended = $tile->isExtended();
		}
		return $this;
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof PistonTile){
			$tile->setExtended($this->extended, $this->sticky);
		}
	}
}
