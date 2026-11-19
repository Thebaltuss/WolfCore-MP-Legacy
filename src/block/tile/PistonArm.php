<?php
/* WolfCore piston arm tile NBT - LGPL-3.0-or-later */
declare(strict_types=1);

namespace wolfcore\block\tile;

use wolfcore\block\Block;
use wolfcore\block\Piston;
use wolfcore\data\bedrock\block\BlockStateNames;
use wolfcore\math\Facing;
use wolfcore\nbt\tag\CompoundTag;
use wolfcore\nbt\tag\IntTag;

class PistonArm extends Spawnable{
	private bool $extended = false;
	private bool $sticky = false;
	/**
	 * A fake facing state is only necessary to force a client re-render when the
	 * piston moves. Sending it on first placement briefly turns the base the
	 * wrong way before the correct state packet arrives.
	 */
	private bool $needsRenderUpdate = false;

	public function readSaveData(CompoundTag $nbt) : void{
		$tag = $nbt->getTag("State");
		$this->extended = ($tag instanceof \wolfcore\nbt\tag\IntTag || $tag instanceof \wolfcore\nbt\tag\ByteTag) && $tag->getValue() === 2;
		$this->sticky = $nbt->getByte("Sticky", 0) !== 0;
		// Loading saved piston state is not an animation/state transition.
		$this->needsRenderUpdate = false;
	}
	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setByte("State", $this->extended ? 2 : 0);
		$nbt->setByte("Sticky", $this->sticky ? 1 : 0);
	}
	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setByte("State", $this->extended ? 2 : 0)
			->setByte("NewState", $this->extended ? 2 : 0)
			->setByte("Sticky", $this->sticky ? 1 : 0)
			->setFloat("Progress", $this->extended ? 1.0 : 0.0)
			->setFloat("LastProgress", $this->extended ? 1.0 : 0.0)
			->setTag("AttachedBlocks", new \wolfcore\nbt\tag\ListTag([], \wolfcore\nbt\NBT::TAG_Int))
			->setTag("BreakBlocks", new \wolfcore\nbt\tag\ListTag([], \wolfcore\nbt\NBT::TAG_Int));
	}
	/**
	 * In Bedrock the extended bit is kept in PistonArm NBT, not the block palette.
	 * Force the client to redraw the base only when the arm actually changes
	 * extension state. Never send the temporary facing state on placement.
	 */
	public function getRenderUpdateBugWorkaroundStateProperties(Block $block) : array{
		if($block instanceof Piston && $this->needsRenderUpdate){
			// Block updates are broadcast to all chunk viewers from one packet list.
			$this->needsRenderUpdate = false;
			// The fake state must differ from the ACTUAL wire facing (which is not
			// the generic PMMP facing for a horizontal Bedrock piston).
			return [BlockStateNames::FACING_DIRECTION => new IntTag(Piston::networkFacing(Facing::opposite($block->getFacing())))];
		}
		return [];
	}

	public function isExtended() : bool{ return $this->extended; }
	public function setExtended(bool $extended, bool $sticky) : void{
		if($this->extended !== $extended){
			$this->needsRenderUpdate = true;
		}
		$this->extended = $extended;
		$this->sticky = $sticky;
		$this->clearSpawnCompoundCache();
	}
}
