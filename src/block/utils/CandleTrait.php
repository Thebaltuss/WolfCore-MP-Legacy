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

namespace wolfcore\block\utils;

use wolfcore\block\Block;
use wolfcore\data\runtime\RuntimeDataDescriber;
use wolfcore\entity\projectile\Projectile;
use wolfcore\item\Durable;
use wolfcore\item\enchantment\VanillaEnchantments;
use wolfcore\item\Item;
use wolfcore\item\ItemTypeIds;
use wolfcore\math\RayTraceResult;
use wolfcore\math\Vector3;
use wolfcore\player\Player;
use wolfcore\world\sound\FireExtinguishSound;
use wolfcore\world\sound\FlintSteelSound;

trait CandleTrait{
	private bool $lit = false;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->bool($this->lit);
	}

	public function getLightLevel() : int{
		return $this->lit ? 3 : 0;
	}

	public function isLit() : bool{ return $this->lit; }

	/** @return $this */
	public function setLit(bool $lit) : self{
		$this->lit = $lit;
		return $this;
	}

	/** @see Block::onInteract() */
	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($item->getTypeId() === ItemTypeIds::FLINT_AND_STEEL || $item->hasEnchantment(VanillaEnchantments::FIRE_ASPECT())){
			if($this->lit){
				return true;
			}
			if($item instanceof Durable){
				$item->applyDamage(1);
			}
			$this->position->getWorld()->addSound($this->position, new FlintSteelSound());
			$this->position->getWorld()->setBlock($this->position, $this->setLit(true));

			return true;
		}
		if($item->isNull()){ //candle can only be extinguished with an empty hand
			if(!$this->lit){
				return true;
			}
			$this->position->getWorld()->addSound($this->position, new FireExtinguishSound());
			$this->position->getWorld()->setBlock($this->position, $this->setLit(false));

			return true;
		}

		//yes, this is intentional! in vanilla, if the candle is not interacted with, a block is placed.
		return false;
	}

	/** @see Block::onProjectileHit() */
	public function onProjectileHit(Projectile $projectile, RayTraceResult $hitResult) : void{
		if(!$this->lit && $projectile->isOnFire()){
			$this->position->getWorld()->setBlock($this->position, $this->setLit(true));
		}
	}
}
