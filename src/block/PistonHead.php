<?php
/* WolfCore native piston head - LGPL-3.0-or-later */
declare(strict_types=1);

namespace wolfcore\block;

use wolfcore\data\runtime\RuntimeDataDescriber;
use wolfcore\item\Item;
use wolfcore\item\VanillaItems;
use wolfcore\math\Facing;
use wolfcore\math\AxisAlignedBB;

class PistonHead extends Transparent{
	private int $facing = Facing::NORTH;
	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo, private bool $sticky = false){
		parent::__construct($idInfo, $name, $typeInfo);
	}
	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->facing($this->facing);
	}
	public function getFacing() : int{ return $this->facing; }
	public function setFacing(int $facing) : self{
		Facing::validate($facing);
		$this->facing = $facing;
		return $this;
	}
	public function isSticky() : bool{ return $this->sticky; }
	public function getDrops(Item $item) : array{ return []; }
	public function asItem() : Item{ return VanillaItems::AIR(); }
	protected function recalculateCollisionBoxes() : array{
		return [AxisAlignedBB::one()->trim(Facing::opposite($this->facing), 0.75)];
	}
	public function onNearbyBlockChange() : void{
		$behind = $this->getSide(Facing::opposite($this->facing));
		if(!($behind instanceof Piston) || !$behind->isExtended() || $behind->getFacing() !== $this->facing){
			$this->position->getWorld()->setBlock($this->position, VanillaBlocks::AIR());
		}
	}
}
