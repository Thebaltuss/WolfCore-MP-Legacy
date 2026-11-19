<?php
/* WolfCore native redstone simulation; original work, LGPL-3.0-or-later. */
declare(strict_types=1);

namespace wolfcore\redstone;

use wolfcore\block\Block;
use wolfcore\block\Button;
use wolfcore\block\DaylightSensor;
use wolfcore\block\DetectorRail;
use wolfcore\block\Door;
use wolfcore\block\FenceGate;
use wolfcore\block\Hopper;
use wolfcore\block\Note;
use wolfcore\block\Tripwire;
use wolfcore\block\Lever;
use wolfcore\block\Opaque;
use wolfcore\block\Piston;
use wolfcore\block\PistonHead;
use wolfcore\block\PoweredRail;
use wolfcore\block\ActivatorRail;
use wolfcore\block\Redstone;
use wolfcore\block\RedstoneComparator;
use wolfcore\block\RedstoneLamp;
use wolfcore\block\RedstoneRepeater;
use wolfcore\block\RedstoneTorch;
use wolfcore\block\RedstoneWire;
use wolfcore\block\SimplePressurePlate;
use wolfcore\block\Trapdoor;
use wolfcore\block\TripwireHook;
use wolfcore\block\TNT;
use wolfcore\block\VanillaBlocks;
use wolfcore\block\WeightedPressurePlate;
use wolfcore\math\Facing;
use wolfcore\math\Vector3;
use wolfcore\world\Position;
use wolfcore\inventory\Inventory;
use wolfcore\block\tile\Container;
use wolfcore\block\tile\Hopper as TileHopper;
use wolfcore\block\inventory\FurnaceInventory;
use wolfcore\entity\object\ItemEntity;
use wolfcore\math\AxisAlignedBB;
use wolfcore\world\World;

/**
 * Signal engine for native blocks. Uses incremental dirty updates to avoid a world-wide
 * scan on each tick, with a fixed work budget to protect server TPS.
 */
final class RedstoneEngine{
	/** @var array<int, array<string, array{int,int,int}>> */
	private static array $dirty = [];
	/** Last power level for mechanically openable blocks; do not override manual opening while unpowered. */
	private static array $lastPower = [];
	/** Repeater events are evaluated on game ticks, independently of the PMMP block-update queue. */
	private static array $repeaterEvents = [];
	private static array $repeaterInputs = [];
	private static array $repeaterLocks = [];
	/** Delayed torch inversions; one pending transition per torch. */
	private static array $torchEvents = [];
	/** The next comparator update tick; calculate the live analogue input on execution. */
	private static array $comparatorEvents = [];
	/** Piston edges are handled by the engine so updates are not lost behind the ordinary block queue. */
	private static array $pistonEvents = [];
	private static array $worldTime = [];
	private const MAX_DIRTY = 25000;
	private const WORK_PER_TICK = 3500;
	private const DIRECTIONS = [Facing::DOWN, Facing::UP, Facing::NORTH, Facing::SOUTH, Facing::WEST, Facing::EAST];

	private static function step(int $facing) : Vector3{
		return match($facing){
			Facing::DOWN => new Vector3(0, -1, 0),
			Facing::UP => new Vector3(0, 1, 0),
			Facing::NORTH => new Vector3(0, 0, -1),
			Facing::SOUTH => new Vector3(0, 0, 1),
			Facing::WEST => new Vector3(-1, 0, 0),
			Facing::EAST => new Vector3(1, 0, 0)
		};
	}

	private static function valid(World $world, int $x, int $y, int $z) : bool{
		return $world->isInWorld($x, $y, $z) && $world->isChunkLoaded($x >> 4, $z >> 4);
	}

	private static function at(World $world, int $x, int $y, int $z) : ?Block{
		return self::valid($world, $x, $y, $z) ? $world->getBlockAt($x, $y, $z) : null;
	}

	private static function add(World $world, int $x, int $y, int $z) : void{
		if(!self::valid($world, $x, $y, $z)) return;
		$id = spl_object_id($world);
		$key = "$x:$y:$z";
		if(isset(self::$dirty[$id][$key]) || count(self::$dirty[$id] ?? []) >= self::MAX_DIRTY) return;
		self::$dirty[$id][$key] = [$x, $y, $z];
	}

	/** Call for every ordinary block change, including removal of a power source. */
	public static function changed(World $world, int $x, int $y, int $z) : void{
		self::add($world, $x, $y, $z);
		foreach(self::DIRECTIONS as $f){
			 $v = self::step($f);
			$dx = $v->x; $dy = $v->y; $dz = $v->z;
			self::add($world, $x + (int) $dx, $y + (int) $dy, $z + (int) $dz);
		}
		// Redstone can climb or descend a single block.
		foreach(Facing::HORIZONTAL as $f){
			$v = self::step($f);
			self::add($world, $x + $v->x, $y + 1, $z + $v->z);
			self::add($world, $x + $v->x, $y - 1, $z + $v->z);
		}
		// Changes to a power source also affect the receivers on the far side of a solid conductor.
		foreach(self::DIRECTIONS as $f){
			$v = self::step($f);
			$nx = $x + $v->x; $ny = $y + $v->y; $nz = $z + $v->z;
			$neighbor = self::at($world, $nx, $ny, $nz);
			if($neighbor instanceof Opaque){
				foreach(self::DIRECTIONS as $side){
					$d = self::step($side);
					self::add($world, $nx + $d->x, $ny + $d->y, $nz + $d->z);
				}
			}
		}
	}

	/** Notify comparators when the inventory changed without a block state update. */
	public static function containerContentsChanged(Position $pos) : void{
		if(!$pos->isValid()) return;
		$world = $pos->getWorld();
		$x = $pos->getFloorX(); $y = $pos->getFloorY(); $z = $pos->getFloorZ();
		foreach(Facing::HORIZONTAL as $face){
			$v = self::step($face);
			// A comparator can read a container directly, or through one opaque block.
			foreach([1, 2] as $distance){
				$cx = $x + $v->x * $distance;
				$cz = $z + $v->z * $distance;
				$cmp = self::at($world, $cx, $y, $cz);
				if($cmp instanceof RedstoneComparator && $cmp->getFacing() === Facing::opposite($face)){
					self::add($world, $cx, $y, $cz);
				}
			}
		}
	}

	/** Advance this world after its ordinary block update pass. */
	public static function tick(World $world) : void{
		$id = spl_object_id($world);
		self::$worldTime[$id] = (self::$worldTime[$id] ?? 0) + 1;
		self::flushRepeaterEvents($world);
		self::flushComparatorEvents($world);
		self::flushTorchEvents($world);
		self::flushPistonEvents($world);
		for($n = 0; $n < self::WORK_PER_TICK && !empty(self::$dirty[$id]); ++$n){
			$key = array_key_first(self::$dirty[$id]);
			[$x, $y, $z] = self::$dirty[$id][$key];
			unset(self::$dirty[$id][$key]);
			if(($block = self::at($world, $x, $y, $z)) !== null){
				self::update($world, $x, $y, $z, $block);
			}
		}
		if(empty(self::$dirty[$id])) unset(self::$dirty[$id]);
	}

	private static function powerChanged(World $world, int $x, int $y, int $z, bool $on) : bool{
		$id = spl_object_id($world);
		$key = "$x:$y:$z";
		$old = self::$lastPower[$id][$key] ?? false;
		self::$lastPower[$id][$key] = $on;
		return $old !== $on;
	}

	/** Repeater side-locking accepts directional repeater/comparator outputs, not ordinary dust. */
	public static function repeaterLocked(RedstoneRepeater $r) : bool{
		$p = $r->getPosition();
		$x = $p->getFloorX(); $y = $p->getFloorY(); $z = $p->getFloorZ();
		foreach([Facing::rotateY($r->getFacing(), true), Facing::rotateY($r->getFacing(), false)] as $side){
			$v = self::step($side);
			$b = self::at($p->getWorld(), $x + $v->x, $y + $v->y, $z + $v->z);
			if(($b instanceof RedstoneRepeater || $b instanceof RedstoneComparator) &&
				self::output($b, Facing::opposite($side)) > 0){
				return true;
			}
		}
		return false;
	}

	/** Schedules input edges in FIFO order; short ON pulses must survive delay. */
	private static function updateRepeater(RedstoneRepeater $r) : void{
		$p = $r->getPosition(); $w = $p->getWorld();
		$id = spl_object_id($w); $key = $p->getFloorX() . ":" . $p->getFloorY() . ":" . $p->getFloorZ();
		$input = self::repeaterInput($r);
		$locked = self::repeaterLocked($r);
		$wasLocked = self::$repeaterLocks[$id][$key] ?? false;
		self::$repeaterLocks[$id][$key] = $locked;
		if($locked){
			// A locked repeater freezes its output; discard transitions that were queued before locking.
			unset(self::$repeaterEvents[$id][$key]);
			self::$repeaterInputs[$id][$key] = $input;
			return;
		}
		$oldInput = self::$repeaterInputs[$id][$key] ?? $r->isPowered();
		self::$repeaterInputs[$id][$key] = $input;
		$queue = self::$repeaterEvents[$id][$key] ?? [];
		if($input !== $oldInput || ($wasLocked && $input !== $r->isPowered()) || ($queue === [] && $input !== $r->isPowered())){
			$time = (self::$worldTime[$id] ?? 0) + 2 * $r->getDelay();
			if($queue !== []){
				$last = $queue[count($queue) - 1];
				// Preserve order even when multiple input edges arrive before a previous edge expires.
				// A repeater must not shorten an ON/OFF pulse below its configured delay.
				$time = max($time, $last[0] + 2 * $r->getDelay());
			}
			$queue[] = [$time, $input];
			self::$repeaterEvents[$id][$key] = $queue;
		}
	}

	private static function flushRepeaterEvents(World $w) : void{
		$id = spl_object_id($w);
		$now = self::$worldTime[$id] ?? 0;
		foreach(self::$repeaterEvents[$id] ?? [] as $key => $queue){
			[$x, $y, $z] = array_map('intval', explode(':', $key, limit: 4));
			$r = self::at($w, $x, $y, $z);
			if(!($r instanceof RedstoneRepeater)){
				unset(self::$repeaterEvents[$id][$key], self::$repeaterInputs[$id][$key], self::$repeaterLocks[$id][$key]);
				continue;
			}
			if(self::repeaterLocked($r)){
				unset(self::$repeaterEvents[$id][$key]);
				self::$repeaterLocks[$id][$key] = true;
				continue;
			}
			while($queue !== [] && $queue[0][0] <= $now){
				[, $on] = array_shift($queue);
				if($r->isPowered() !== $on){
					$r->setPowered($on);
					$w->setBlockAt($x, $y, $z, $r);
				}
			}
			if($queue === []){
				unset(self::$repeaterEvents[$id][$key]);
			}else{
				self::$repeaterEvents[$id][$key] = $queue;
			}
		}
	}

	/** Called only by existing legacy world schedules, never switches output without the delay/locking checks. */
	public static function repeaterScheduledTick(RedstoneRepeater $r) : void{
		self::updateRepeater($r);
	}

	/** When unloading worlds, drop all transient circuit state. */
	public static function forget(World $w) : void{
		$id = spl_object_id($w);
		unset(self::$dirty[$id], self::$lastPower[$id], self::$worldTime[$id], self::$repeaterEvents[$id], self::$repeaterInputs[$id], self::$repeaterLocks[$id], self::$torchEvents[$id], self::$comparatorEvents[$id], self::$pistonEvents[$id]);
	}

	/** Output from source in the direction towards the destination. */
	private static function output(Block $block, int $towards) : int{
		if($block instanceof Redstone) return 15;
		if($block instanceof Lever) return $block->isActivated() ? 15 : 0;
		if($block instanceof Button) return $block->isPressed() ? 15 : 0;
		if($block instanceof SimplePressurePlate) return $block->isPressed() ? 15 : 0;
		if($block instanceof WeightedPressurePlate) return $block->getOutputSignalStrength();
		if($block instanceof RedstoneTorch){
			// The torch never powers the block it is attached to, including wall-mounted torches.
			return $block->isLit() && $towards !== Facing::opposite($block->getFacing()) ? 15 : 0;
		}
		if($block instanceof RedstoneWire) return $block->getOutputSignalStrength();
		// Bedrock "direction"/legacy horizontal facing records the BACK (input) side.
		// The output is on the opposite side. Never accept signal travelling backwards.
		if($block instanceof RedstoneRepeater) return $block->isPowered() && Facing::opposite($block->getFacing()) === $towards ? 15 : 0;
		if($block instanceof RedstoneComparator) return Facing::opposite($block->getFacing()) === $towards ? $block->getOutputSignalStrength() : 0;
		if($block instanceof DaylightSensor) return $block->getOutputSignalStrength();
		if($block instanceof TripwireHook) return $block->isPowered() ? 15 : 0;
		if($block instanceof DetectorRail) return $block->isActivated() ? 15 : 0;
		return 0;
	}

	/** Opaque normal blocks conduct power received from adjacent components.
	 * Deliberately stops at one block (no accidental infinite transmission through stone).
	 */
	private static function incomingToConductor(World $w, int $x, int $y, int $z, int $excludeSide, bool $strongOnly = false) : int{
		$max = 0;
		foreach(self::DIRECTIONS as $direction){
			if($direction === $excludeSide) continue;
			$v = self::step($direction);
			$src = self::at($w, $x + $v->x, $y + $v->y, $z + $v->z);
			if($src !== null){
				// Dust powers its supporting/pointed-at block only weakly.
				// A weakly powered block can activate hoppers and repeaters,
				// but cannot feed another dust and amplify a sorting circuit.
				if($strongOnly && $src instanceof RedstoneWire) continue;
				$towardsConductor = Facing::opposite($direction);
				// Torches only STRONGLY power a solid conductor directly above them.
				// Their side/bottom output can still directly operate dust, lamps, etc.
				if($src instanceof RedstoneTorch && $towardsConductor !== Facing::UP) continue;
				$max = max($max, self::output($src, $towardsConductor));
			}
		}
		return $max;
	}

	/** True output directly adjacent to a position, optionally excluding one face. */
	public static function powerAt(World $world, int $x, int $y, int $z, ?int $except = null) : int{
		$max = 0;
		foreach(self::DIRECTIONS as $direction){
			if($direction === $except) continue;
			$v = self::step($direction);
			if(($source = self::at($world, $x + $v->x, $y + $v->y, $z + $v->z)) === null) continue;
			$level = self::output($source, Facing::opposite($direction));
			if($level === 0 && $source instanceof Opaque && !($source instanceof Piston)){
				$level = self::incomingToConductor($world, $x + $v->x, $y + $v->y, $z + $v->z, Facing::opposite($direction));
			}
			$max = max($max, $level);
			if($max === 15) break;
		}
		return $max;
	}

	/** Relative direction: input is the block on the given side of the receiver. */
	private static function sideInput(World $world, int $x, int $y, int $z, int $side) : int{
		$v = self::step($side);
		$b = self::at($world, $x + $v->x, $y + $v->y, $z + $v->z);
		if($b === null) return 0;
		$power = self::output($b, Facing::opposite($side));
		if($power === 0 && $b instanceof Opaque && !($b instanceof Piston)){
			$power = self::incomingToConductor($world, $x + $v->x, $y + $v->y, $z + $v->z, Facing::opposite($side));
		}
		return $power;
	}

	private static function wirePower(World $world, int $x, int $y, int $z) : int{
		$max = 0;
		foreach(self::DIRECTIONS as $side){
			$v = self::step($side);
			$b = self::at($world, $x + $v->x, $y + $v->y, $z + $v->z);
			if($b === null) continue;
			// Redstone wires never connect straight up/down. Higher or
			// lower wires instead connect diagonally via the step check below.
			if($b instanceof RedstoneWire && ($side === Facing::UP || $side === Facing::DOWN)) continue;
			$level = self::output($b, Facing::opposite($side));
			if($level === 0 && $b instanceof Opaque && !($b instanceof Piston)){
				$level = self::incomingToConductor($world, $x + $v->x, $y + $v->y, $z + $v->z, Facing::opposite($side), true);
			}
			$max = max($max, $b instanceof RedstoneWire ? $level - 1 : $level);
			if($max === 15) return 15;
		}
		foreach(Facing::HORIZONTAL as $side){
			$v = self::step($side);
			$adj = self::at($world, $x + $v->x, $y, $z + $v->z);
			if($adj === null) continue;
			// Climb onto a block, or run down onto a lower block.
			// A solid ceiling above the lower wire prevents climbing upward.
			if($adj->isSolid()){
				$overhead = self::at($world, $x, $y + 1, $z);
				if($overhead !== null && $overhead->isSolid()) continue;
			}
			$dy = $adj->isSolid() ? 1 : -1;
			$b = self::at($world, $x + $v->x, $y + $dy, $z + $v->z);
			if($b instanceof RedstoneWire){
				$max = max($max, $b->getOutputSignalStrength() - 1);
			}
		}
		return max(0, min(15, $max));
	}

	public static function repeaterInput(RedstoneRepeater $r) : bool{
		$p = $r->getPosition();
		return self::sideInput($p->getWorld(), $p->getFloorX(), $p->getFloorY(), $p->getFloorZ(), $r->getFacing()) > 0;
	}

	/** Reads a container's vanilla comparator level. Empty = 0; nonempty = 1..15. */
	private static function containerSignal(World $world, int $x, int $y, int $z) : ?int{
		if(!self::valid($world, $x, $y, $z)) return null;
		$tile = $world->getTile(new Vector3($x, $y, $z));
		if(!($tile instanceof \wolfcore\block\tile\Container)) return null;
		// Use the combined 54-slot inventory for paired chests.
		$inventory = $tile->getInventory();
		$slots = $inventory->getSize();
		if($slots <= 0) return 0;
		$fullness = 0.0;
		$nonempty = false;
		foreach($inventory->getContents() as $item){
			if($item->isNull()) continue;
			$nonempty = true;
			$fullness += min(1.0, $item->getCount() / max(1, $item->getMaxStackSize()));
		}
		return $nonempty ? 1 + (int) floor(14 * $fullness / $slots) : 0;
	}

	/** @return array{int,bool} output and lamp state */
	public static function comparatorOutput(RedstoneComparator $c) : array{
		$p = $c->getPosition();
		$w = $p->getWorld();
		$x = $p->getFloorX(); $y = $p->getFloorY(); $z = $p->getFloorZ();
		$rear = self::sideInput($w, $x, $y, $z, $c->getFacing());
		$dir = self::step($c->getFacing());
		$bx = $x + $dir->x; $by = $y + $dir->y; $bz = $z + $dir->z;
		$inventorySignal = self::containerSignal($w, $bx, $by, $bz);
		if($inventorySignal === null){
			// In vanilla, an opaque block between the comparator and a container
			// lets the comparator read the inventory two blocks behind it.
			$behind = self::at($w, $bx, $by, $bz);
			if($behind instanceof Opaque){
				$inventorySignal = self::containerSignal($w, $bx + $dir->x, $by + $dir->y, $bz + $dir->z);
			}
		}
		if($inventorySignal !== null) $rear = $inventorySignal;
		$side = max(self::sideInput($w, $x, $y, $z, Facing::rotateY($c->getFacing(), true)), self::sideInput($w, $x, $y, $z, Facing::rotateY($c->getFacing(), false)));
		$out = $c->isSubtractMode() ? max(0, $rear - $side) : ($rear >= $side ? $rear : 0);
		return [$out, $out > 0];
	}


	/**
	 * Vanilla comparators sample their current input when the delayed update runs.
	 * Never replay a queue of stale 2->3->2 readings: it can leave the lower
	 * hopper unlocked after an item sorter has returned to its 41-item baseline.
	 */
	private static function updateComparator(RedstoneComparator $c) : void{
		$p = $c->getPosition();
		$w = $p->getWorld();
		$id = spl_object_id($w);
		$key = $p->getFloorX() . ":" . $p->getFloorY() . ":" . $p->getFloorZ();
		[$output] = self::comparatorOutput($c);
		if($output === $c->getOutputSignalStrength()){
			unset(self::$comparatorEvents[$id][$key]);
		}else{
			// Keep the first due tick while an input is changing. The signal
			// is sampled again at execution instead of queuing stale edges.
			self::$comparatorEvents[$id][$key] ??= (self::$worldTime[$id] ?? 0) + 2;
		}
	}

	private static function flushComparatorEvents(World $world) : void{
		$id = spl_object_id($world);
		$now = self::$worldTime[$id] ?? 0;
		foreach(self::$comparatorEvents[$id] ?? [] as $key => $due){
			if($due > $now) continue;
			unset(self::$comparatorEvents[$id][$key]);
			[$x, $y, $z] = array_map('intval', explode(':', $key, limit: 4));
			$c = self::at($world, $x, $y, $z);
			if(!($c instanceof RedstoneComparator)) continue;
			// The inventory may have changed during the comparator's delay.
			[$output] = self::comparatorOutput($c);
			if($c->getOutputSignalStrength() !== $output || $c->isPowered() !== ($output > 0)){
				$world->setBlockAt($x, $y, $z, $c->setOutputSignalStrength($output)->setPowered($output > 0));
			}
		}
		if(empty(self::$comparatorEvents[$id])) unset(self::$comparatorEvents[$id]);
	}

	/** Called on periodic polling of container inventory changes. */
	public static function comparatorScheduledTick(RedstoneComparator $c) : void{
		self::updateComparator($c);
	}

	/** Is the torch's attachment block powered? Do not chain weak power through stone blocks. */
	public static function torchLit(RedstoneTorch $torch) : bool{
		$p = $torch->getPosition();
		$world = $p->getWorld();
		$supportDirection = Facing::opposite($torch->getFacing());
		$v = self::step($supportDirection);
		$x = $p->getFloorX() + $v->x;
		$y = $p->getFloorY() + $v->y;
		$z = $p->getFloorZ() + $v->z;
		$support = self::at($world, $x, $y, $z);
		if($support === null){
			// The support chunk is not loaded. Retain the current state until it can be sampled.
			return $torch->isLit();
		}
		// Non-conducting support blocks (glass, fences, etc.) cannot turn torches off.
		if(!($support instanceof Opaque)) return true;
		// Only direct power into the supporting block counts; exclude the torch's own face.
		return self::incomingToConductor($world, $x, $y, $z, $torch->getFacing()) === 0;
	}

	/** Schedule a torch inversion two game ticks after its attachment block changes power. */
	private static function updateTorch(RedstoneTorch $torch) : void{
		$p = $torch->getPosition();
		$world = $p->getWorld();
		$id = spl_object_id($world);
		$key = $p->getFloorX() . ':' . $p->getFloorY() . ':' . $p->getFloorZ();
		if(self::torchLit($torch) === $torch->isLit()){
			// A brief input pulse that has already ended must not produce a stale inversion.
			unset(self::$torchEvents[$id][$key]);
		}else{
			self::$torchEvents[$id][$key] ??= (self::$worldTime[$id] ?? 0) + 2;
		}
	}

	/** Called when an old scheduled block update targets a torch. */
	public static function torchScheduledTick(RedstoneTorch $torch) : void{
		self::updateTorch($torch);
	}

	private static function flushTorchEvents(World $world) : void{
		$id = spl_object_id($world);
		$now = self::$worldTime[$id] ?? 0;
		foreach(self::$torchEvents[$id] ?? [] as $key => $due){
			if($due > $now) continue;
			unset(self::$torchEvents[$id][$key]);
			[$x, $y, $z] = array_map('intval', explode(':', $key, limit: 4));
			$torch = self::at($world, $x, $y, $z);
			if($torch instanceof RedstoneTorch){
				$shouldLight = self::torchLit($torch);
				if($torch->isLit() !== $shouldLight){
					$world->setBlockAt($x, $y, $z, $torch->setLit($shouldLight));
				}
			}
		}
		if(empty(self::$torchEvents[$id])) unset(self::$torchEvents[$id]);
	}

	public static function pressurePlateTick(\wolfcore\block\PressurePlate $plate) : void{
		$p = $plate->getPosition();
		$w = $p->getWorld();
		$entities = $w->getNearbyEntities(new \wolfcore\math\AxisAlignedBB($p->x, $p->y + 0.75, $p->z, $p->x + 1, $p->y + 1.2, $p->z + 1));
		if($plate instanceof SimplePressurePlate){
			$pressed = false;
			foreach($entities as $entity){
				if($entity instanceof \wolfcore\player\Player || !($plate instanceof \wolfcore\block\StonePressurePlate)){
					$pressed = true; break;
				}
			}
			if($pressed !== $plate->isPressed()) $w->setBlock($p, $plate->setPressed($pressed));
		}elseif($plate instanceof WeightedPressurePlate){
			$strength = min(15, count($entities));
			if($strength !== $plate->getOutputSignalStrength()) $w->setBlock($p, $plate->setOutputSignalStrength($strength));
		}
	}

	/**
	 * One-item insertion: always use inventory slot order, with no item loss or duplication.
	 * Only the actual source/target slots are modified, which keeps viewer inventories in sync.
	 * @param int[]|null $sourceSlots
	 * @param int[]|null $targetSlots
	 */
	private static function transfer(Inventory $source, Inventory $target, ?array $sourceSlots = null, ?array $targetSlots = null) : bool{
		$sourceSlots ??= range(0, $source->getSize() - 1);
		$targetSlots ??= range(0, $target->getSize() - 1);
		foreach($sourceSlots as $sourceSlot){
			if($sourceSlot < 0 || $sourceSlot >= $source->getSize()) continue;
			$item = $source->getItem($sourceSlot);
			if($item->isNull()) continue;
			foreach($targetSlots as $targetSlot){
				if($targetSlot < 0 || $targetSlot >= $target->getSize()) continue;
				$existing = $target->getItem($targetSlot);
				$max = min($item->getMaxStackSize(), $target->getMaxStackSize());
				if(!$existing->isNull()){
					if(!$existing->canStackWith($item) || $existing->getCount() >= $max) continue;
					$existing->setCount($existing->getCount() + 1);
					$target->setItem($targetSlot, $existing);
				}else{
					$one = clone $item;
					$one->setCount(1);
					$target->setItem($targetSlot, $one);
				}
				$item->setCount($item->getCount() - 1);
				$source->setItem($sourceSlot, $item);
				return true;
			}
		}
		return false;
	}

	/** Hopper output to another container's exposed slots. */
	private static function insertInto(TileHopper $hopper, Container $destination, int $fromFace) : bool{
		$source = $hopper->getRealInventory();
		$target = $destination->getInventory();
		if($target === $source) return false;
		// Furnaces only accept smeltables from above, and fuels from the side.
		if($target instanceof FurnaceInventory){
			if($fromFace === Facing::UP){
				return self::transfer($source, $target, null, [FurnaceInventory::SLOT_INPUT]);
			}
			if($fromFace === Facing::DOWN) return false;
			foreach($source->getContents() as $index => $item){
				if(!$item->isNull() && $item->getFuelTime() > 0 && self::transfer($source, $target, [$index], [FurnaceInventory::SLOT_FUEL])) return true;
			}
			return false;
		}
		return self::transfer($source, $target);
	}

	/** Hopper input from a container immediately above. */
	private static function extractFrom(Container $source, TileHopper $hopper) : bool{
		$input = $source->getInventory();
		$output = $hopper->getRealInventory();
		if($input === $output) return false;
		if($input instanceof FurnaceInventory){
			return self::transfer($input, $output, [FurnaceInventory::SLOT_RESULT]);
		}
		return self::transfer($input, $output);
	}

	/** Collect as much of one item entity as can fit; leave any remainder in the world. */
	private static function collectItem(World $world, Vector3 $position, TileHopper $hopper, bool $hasContainerAbove) : bool{
		$inventory = $hopper->getRealInventory();
		$box = new AxisAlignedBB(
			$position->x, $position->y, $position->z,
			$position->x + 1, $position->y + ($hasContainerAbove ? 1.0 : 1.75), $position->z + 1
		);
		foreach($world->getNearbyEntities($box) as $entity){
			if(!($entity instanceof ItemEntity) || $entity->isFlaggedForDespawn()) continue;
			$item = $entity->getItem();
			$amount = $inventory->getAddableItemQuantity($item);
			if($amount <= 0) continue;
			$pickup = clone $item;
			$pickup->setCount($amount);
			$leftovers = $inventory->addItem($pickup);
			$inserted = $amount - (isset($leftovers[0]) ? $leftovers[0]->getCount() : 0);
			if($inserted <= 0) continue;
			$remaining = $item->getCount() - $inserted;
			if($remaining <= 0) $entity->flagForDespawn();
			else $entity->setStackSize($remaining);
			return true;
		}
		return false;
	}

	/** Vanilla Bedrock: push then pull, at most one item each, every 8 game ticks. */
	public static function hopperTick(Hopper $hopper) : void{
		$p = $hopper->getPosition(); $world = $p->getWorld();
		$tile = $world->getTile($p);
		if(!($tile instanceof TileHopper) || $tile->getTransferCooldown() > 0) return;

		$pushed = false;
		$destPos = $p->getSide($hopper->getFacing());
		if(self::valid($world, $destPos->getFloorX(), $destPos->getFloorY(), $destPos->getFloorZ())){
			$dest = $world->getTile($destPos);
			if($dest instanceof Container && $dest !== $tile){
				$destWasEmpty = $dest instanceof TileHopper && $dest->getRealInventory()->getContents() === [];
				$pushed = self::insertInto($tile, $dest, Facing::opposite($hopper->getFacing()));
				if($pushed && $destWasEmpty){
					// An empty hopper receiving a push must wait 8 ticks before forwarding.
					$dest->setTransferCooldown(8);
				}
			}
		}

		$pulled = false;
		$above = $p->getSide(Facing::UP);
		$source = self::valid($world, $above->getFloorX(), $above->getFloorY(), $above->getFloorZ()) ? $world->getTile($above) : null;
		if($source instanceof Container && $source !== $tile){
			$pulled = self::extractFrom($source, $tile);
		}
		// A hopper beneath another container pulls from that container instead of pulling
		// loose items above it; items physically inside the hopper can still be collected.
		if(!$pulled && self::collectItem($world, $p, $tile, $source instanceof Container)){
			$pulled = true;
		}
		if($pushed || $pulled){
			$tile->setTransferCooldown(8);
		}
	}

	public static function tripwireTick(Tripwire $wire) : void{
		$p = $wire->getPosition(); $world = $p->getWorld();
		$box = new \wolfcore\math\AxisAlignedBB($p->x + 0.05, $p->y, $p->z + 0.05, $p->x + 0.95, $p->y + 1, $p->z + 0.95);
		$triggered = !$wire->isDisarmed() && count($world->getNearbyEntities($box)) > 0;
		if($wire->isTriggered() !== $triggered) $world->setBlock($p, $wire->setTriggered($triggered));
	}

	public static function tripwireHookTick(TripwireHook $hook) : void{
		$p = $hook->getPosition(); $world = $p->getWorld();
		$v = self::step($hook->getFacing());
		$foundEnd = null; $triggered = false;
		for($i = 1; $i <= 40; ++$i){
			$b = self::at($world, $p->getFloorX() + $v->x * $i, $p->getFloorY(), $p->getFloorZ() + $v->z * $i);
			if($b instanceof Tripwire){
				$triggered = $triggered || $b->isTriggered();
				continue;
			}
			if($i > 1 && $b instanceof TripwireHook && $b->getFacing() === Facing::opposite($hook->getFacing())) $foundEnd = $b;
			break;
		}
		$connected = $foundEnd !== null;
		$powered = $connected && $triggered;
		if($hook->isConnected() !== $connected || $hook->isPowered() !== $powered){
			$world->setBlock($p, $hook->setConnected($connected)->setPowered($powered));
		}
		if($foundEnd !== null && ($foundEnd->isConnected() !== $connected || $foundEnd->isPowered() !== $powered)){
			$world->setBlock($foundEnd->getPosition(), $foundEnd->setConnected($connected)->setPowered($powered));
		}
	}

	/**
	 * Queue a 2-game-tick piston activation edge independent of PMMP delayed updates.
	 * Circuit changes may arrive after the world's normal scheduled-update phase;
	 * keeping a dedicated event makes the behaviour stable across tick orderings.
	 */
	public static function queuePistonUpdate(Piston $piston) : void{
		self::updatePiston($piston);
	}

	private static function updatePiston(Piston $piston) : void{
		$p = $piston->getPosition();
		$world = $p->getWorld();
		$id = spl_object_id($world);
		$key = $p->getFloorX() . ':' . $p->getFloorY() . ':' . $p->getFloorZ();
		$powered = self::powerAt($world, $p->getFloorX(), $p->getFloorY(), $p->getFloorZ(), $piston->getFacing()) > 0;
		if($powered === $piston->isExtended()){
			unset(self::$pistonEvents[$id][$key]);
			return;
		}
		$previous = self::$pistonEvents[$id][$key] ?? null;
		if($previous === null || $previous[1] !== $powered){
			self::$pistonEvents[$id][$key] = [(self::$worldTime[$id] ?? 0) + 2, $powered];
		}
	}

	private static function flushPistonEvents(World $world) : void{
		$id = spl_object_id($world);
		$now = self::$worldTime[$id] ?? 0;
		foreach(self::$pistonEvents[$id] ?? [] as $key => [$due, $expected]){
			if($due > $now) continue;
			unset(self::$pistonEvents[$id][$key]);
			[$x, $y, $z] = array_map('intval', explode(':', $key, limit: 4));
			$piston = self::at($world, $x, $y, $z);
			if(!($piston instanceof Piston)) continue;
			// Live sampling is important: a short circuit pulse must not leave a piston stuck.
			$powered = self::powerAt($world, $x, $y, $z, $piston->getFacing()) > 0;
			PistonMovement::tick($piston, $powered);
		}
		if(empty(self::$pistonEvents[$id])) unset(self::$pistonEvents[$id]);
	}

	/** Piston power checks include the side and bottom, excluding the extending face. */
	public static function pistonTick(Piston $piston) : void{
		self::queuePistonUpdate($piston);
	}

	private static function update(World $world, int $x, int $y, int $z, Block $block) : void{
		$pos = new Vector3($x, $y, $z);
		// Reset transient queue entries when a component is destroyed or replaced.
		$id = spl_object_id($world);
		$key = "$x:$y:$z";
		if(!($block instanceof RedstoneTorch)){
			unset(self::$torchEvents[$id][$key]);
		}
		if(!($block instanceof RedstoneRepeater)){
			unset(self::$repeaterInputs[$id][$key], self::$repeaterEvents[$id][$key], self::$repeaterLocks[$id][$key]);
		}
		if(!($block instanceof RedstoneComparator)){
			unset(self::$comparatorEvents[$id][$key]);
		}
		if($block instanceof RedstoneWire){
			$strength = self::wirePower($world, $x, $y, $z);
			if($block->getOutputSignalStrength() !== $strength){
				$world->setBlock($pos, $block->setOutputSignalStrength($strength));
			}
		}elseif($block instanceof RedstoneTorch){
			self::updateTorch($block);
		}elseif($block instanceof RedstoneRepeater){
			self::updateRepeater($block);
		}elseif($block instanceof RedstoneComparator){
			self::updateComparator($block);
		}elseif($block instanceof RedstoneLamp){
			$powered = self::powerAt($world, $x, $y, $z) > 0;
			if($powered !== $block->isPowered()) $world->setBlock($pos, $block->setPowered($powered));
		}elseif($block instanceof TNT){
			if(self::powerAt($world, $x, $y, $z) > 0) $block->ignite();
		}elseif($block instanceof Piston){
			self::updatePiston($block);
		}elseif($block instanceof Hopper){
			$powered = self::powerAt($world, $x, $y, $z) > 0;
			if($powered !== $block->isPowered()) $world->setBlock($pos, $block->setPowered($powered));
			$world->scheduleDelayedBlockUpdate($pos, 8);
		}elseif($block instanceof Trapdoor || $block instanceof FenceGate){
			$powered = self::powerAt($world, $x, $y, $z) > 0;
			if(self::powerChanged($world, $x, $y, $z, $powered) && $block->isOpen() !== $powered) $world->setBlock($pos, $block->setOpen($powered));
		}elseif($block instanceof Door){
			if($block->isTop()) return;
			$powered = max(self::powerAt($world, $x, $y, $z), self::powerAt($world, $x, $y + 1, $z)) > 0;
			if(self::powerChanged($world, $x, $y, $z, $powered) && $block->isOpen() !== $powered){
				$world->setBlock($pos, $block->setOpen($powered));
				$top = self::at($world, $x, $y + 1, $z);
				if($top instanceof Door && $top->hasSameTypeId($block)) $world->setBlockAt($x, $y + 1, $z, $top->setOpen($powered));
			}
		}elseif($block instanceof Note){
			$powered = self::powerAt($world, $x, $y, $z) > 0;
			if(self::powerChanged($world, $x, $y, $z, $powered) && $powered) $block->playNote();
		}elseif($block instanceof Tripwire || $block instanceof TripwireHook){
			$world->scheduleDelayedBlockUpdate($pos, 5);
		}elseif($block instanceof PoweredRail || $block instanceof ActivatorRail){
			$powered = self::powerAt($world, $x, $y, $z) > 0;
			if($block->isPowered() !== $powered) $world->setBlock($pos, $block->setPowered($powered));
		}elseif($block instanceof SimplePressurePlate || $block instanceof WeightedPressurePlate){
			$world->scheduleDelayedBlockUpdate($pos, 5);
		}elseif($block instanceof DaylightSensor){
			$world->scheduleDelayedBlockUpdate($pos, 20);
		}
	}
}
