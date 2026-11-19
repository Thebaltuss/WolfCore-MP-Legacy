<?php
/* WolfCore native piston movement improvements, LGPL-3.0-or-later. */
declare(strict_types=1);

namespace wolfcore\redstone;

use wolfcore\block\Air;
use wolfcore\block\Bedrock;
use wolfcore\block\Flowable;
use wolfcore\block\Block;
use wolfcore\block\Piston;
use wolfcore\block\PistonHead;
use wolfcore\block\Slime;
use wolfcore\block\VanillaBlocks;
use wolfcore\math\Facing;
use wolfcore\math\Vector3;
use wolfcore\world\World;
use wolfcore\world\sound\PistonSound;

/**
 * Calculates all piston displacements before changing anything in the world.
 * Supports 12-block limits, axis and slime-attachment movement, safe tile NBT copying,
 * and immovable world-border/obsidian/extended-piston checks.
 * Motion is atomic from the game's block-update perspective, but not animated.
 */
final class PistonMovement{
	private const DIRECTIONS = [Facing::DOWN, Facing::UP, Facing::NORTH, Facing::SOUTH, Facing::WEST, Facing::EAST];

	/** @return array{int,int,int} */
	private static function offset(int $face) : array{
		return match($face){
			Facing::DOWN => [0, -1, 0], Facing::UP => [0, 1, 0],
			Facing::NORTH => [0, 0, -1], Facing::SOUTH => [0, 0, 1],
			Facing::WEST => [-1, 0, 0], Facing::EAST => [1, 0, 0]
		};
	}

	private static function valid(World $w, int $x, int $y, int $z) : bool{
		return $w->isInWorld($x, $y, $z) && $w->isChunkLoaded($x >> 4, $z >> 4);
	}

	private static function key(int $x, int $y, int $z) : string{ return "$x:$y:$z"; }

	private static function isFragile(Block $b) : bool{
		return $b->canBeReplaced() || $b instanceof Flowable;
	}

	private static function isMovable(Block $b) : bool{
		// Match the most common Bedrock immovable blocks, including blocks that are
		// breakable by players but must never be displaced by a piston.
		static $fixedIds = null;
		$fixedIds ??= [
			VanillaBlocks::OBSIDIAN()->getTypeId() => true,
			VanillaBlocks::CRYING_OBSIDIAN()->getTypeId() => true,
			VanillaBlocks::GLOWING_OBSIDIAN()->getTypeId() => true,
			VanillaBlocks::REINFORCED_DEEPSLATE()->getTypeId() => true,
			VanillaBlocks::ENDER_CHEST()->getTypeId() => true,
			VanillaBlocks::ENCHANTING_TABLE()->getTypeId() => true
		];
		if($b instanceof Bedrock || $b instanceof PistonHead || !$b->getBreakInfo()->isBreakable() ||
			isset($fixedIds[$b->getTypeId()])){
			return false;
		}
		return !($b instanceof Piston && $b->isExtended());
	}

	/**
	 * @return array<string, array{int,int,int,Block}>|null Null means impossible movement.
	 */
	private static function collect(World $w, int $px, int $py, int $pz, int $startX, int $startY, int $startZ, int $dx, int $dy, int $dz, bool $pullMode = false) : ?array{
		$queue = [[$startX, $startY, $startZ]];
		$move = [];
		$seen = [];
		for($i = 0; $i < count($queue); ++$i){
			[$x, $y, $z] = $queue[$i];
			$key = self::key($x, $y, $z);
			if(isset($seen[$key])) continue;
			$seen[$key] = true;
			if(!self::valid($w, $x, $y, $z)) return null;
			if($x === $px && $y === $py && $z === $pz) return null;
			$b = $w->getBlockAt($x, $y, $z);
			// Redstone wire, torches, buttons, repeaters and fragile vegetation
			// break when hit by a piston; they cannot be pushed as full blocks.
			if($b instanceof Air || self::isFragile($b)) continue;
			if(!self::isMovable($b)) return null;
			$move[$key] = [$x, $y, $z, $b];
			if(count($move) > 12) return null;
			// Pulling a normal block must never drag the unrelated block behind it.
			// Pushing, in contrast, must collect the entire obstructing line.
			if(!$pullMode) $queue[] = [$x + $dx, $y + $dy, $z + $dz];
			if($b instanceof Slime){
				foreach(self::DIRECTIONS as $side){
					[$ax, $ay, $az] = self::offset($side);
					// Slime pulls adjacent blocks unless the neighbour is the driving piston itself.
					if($x + $ax === $px && $y + $ay === $py && $z + $az === $pz) continue;
					$queue[] = [$x + $ax, $y + $ay, $z + $az];
				}
			}
		}
		if($pullMode){
			// A slime assembly must not overwrite immovable obstacles at its destination.
			foreach($move as [$x, $y, $z]){
				$tx = $x + $dx; $ty = $y + $dy; $tz = $z + $dz;
				if(!self::valid($w, $tx, $ty, $tz)) return null;
				if(isset($move[self::key($tx, $ty, $tz)])) continue;
				$target = $w->getBlockAt($tx, $ty, $tz);
				if(!($target instanceof Air) && !self::isFragile($target)) return null;
			}
		}
		return $move;
	}

	/** Move a validated group in farthest-first order, preserving NBT of tile-bearing blocks. */
	private static function move(World $w, array $blocks, int $dx, int $dy, int $dz) : void{
		usort($blocks, static function(array $a, array $b) use($dx, $dy, $dz) : int{
			return ($b[0] * $dx + $b[1] * $dy + $b[2] * $dz) <=> ($a[0] * $dx + $a[1] * $dy + $a[2] * $dz);
		});
		$tiles = [];
		foreach($blocks as [$x, $y, $z, $b]){
			$tile = $w->getTile(new Vector3($x, $y, $z));
			if($tile !== null){
				$tiles[self::key($x, $y, $z)] = $tile->saveNBT();
			}
		}
		foreach($blocks as [$x, $y, $z, $block]){
			$tx = $x + $dx; $ty = $y + $dy; $tz = $z + $dz;
			$dest = $w->getBlockAt($tx, $ty, $tz);
			if(!($dest instanceof Air) && self::isFragile($dest)){
				// Replaceable vegetation and similar fragile blocks are destroyed at their destination.
				$w->useBreakOn(new Vector3($tx, $ty, $tz));
			}
			$w->setBlockAt($tx, $ty, $tz, $block);
			if(isset($tiles[$key = self::key($x, $y, $z)])){
				$newTile = $w->getTile(new Vector3($tx, $ty, $tz));
				$newTile?->readSaveData($tiles[$key]);
			}
			$w->setBlockAt($x, $y, $z, VanillaBlocks::AIR());
		}
	}

	public static function tick(Piston $piston, bool $powered) : void{
		if($piston->isExtended() === $powered) return;
		$p = $piston->getPosition();
		$w = $p->getWorld();
		$x = $p->getFloorX(); $y = $p->getFloorY(); $z = $p->getFloorZ();
		$facing = $piston->getFacing();
		[$dx, $dy, $dz] = self::offset($facing);
		$fx = $x + $dx; $fy = $y + $dy; $fz = $z + $dz;
		if(!self::valid($w, $fx, $fy, $fz)) return;

		if($powered){
			$blocks = self::collect($w, $x, $y, $z, $fx, $fy, $fz, $dx, $dy, $dz);
			if($blocks === null) return;
			// Break fragile target blocks before extending the head, even if no
			// solid block is being pushed. Otherwise redstone dust silently moves.
			$front = $w->getBlockAt($fx, $fy, $fz);
			if(!($front instanceof Air) && self::isFragile($front)){
				$w->useBreakOn(new Vector3($fx, $fy, $fz));
			}
			// Resolve movements before placing the head, including slime assemblies.
			self::move($w, array_values($blocks), $dx, $dy, $dz);
			$w->setBlockAt($x, $y, $z, $piston->setExtended(true));
			$head = ($piston->isSticky() ? VanillaBlocks::STICKY_PISTON_ARM() : VanillaBlocks::PISTON_ARM())->setFacing($facing);
			$w->setBlockAt($fx, $fy, $fz, $head);
			$w->addSound(new Vector3($x + 0.5, $y + 0.5, $z + 0.5), new PistonSound(true));
		}else{
			$head = $w->getBlockAt($fx, $fy, $fz);
			$w->setBlockAt($x, $y, $z, $piston->setExtended(false));
			if(!($head instanceof PistonHead) || $head->getFacing() !== $facing) return;
			$w->setBlockAt($fx, $fy, $fz, VanillaBlocks::AIR());
			$w->addSound(new Vector3($x + 0.5, $y + 0.5, $z + 0.5), new PistonSound(false));
			if(!$piston->isSticky()) return;
			$nx = $fx + $dx; $ny = $fy + $dy; $nz = $fz + $dz;
			if(!self::valid($w, $nx, $ny, $nz)) return;
			$blocks = self::collect($w, $x, $y, $z, $nx, $ny, $nz, -$dx, -$dy, -$dz, true);
			if($blocks !== null){
				self::move($w, array_values($blocks), -$dx, -$dy, -$dz);
			}
		}
	}
}
