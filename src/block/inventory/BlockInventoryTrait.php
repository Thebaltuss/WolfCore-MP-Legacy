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

namespace wolfcore\block\inventory;

use wolfcore\world\Position;
use wolfcore\item\Item;
use wolfcore\redstone\RedstoneEngine;

trait BlockInventoryTrait{
	protected Position $holder;

	public function getHolder() : Position{
		return $this->holder;
	}

	/** Keep comparators in sync after players, plugins and hoppers modify a container. */
	protected function onSlotChange(int $index, Item $before) : void{
		parent::onSlotChange($index, $before);
		if($this->holder->isValid()){
			RedstoneEngine::containerContentsChanged($this->holder);
		}
	}

	/** @param Item[] $itemsBefore */
	protected function onContentChange(array $itemsBefore) : void{
		parent::onContentChange($itemsBefore);
		if($this->holder->isValid()){
			RedstoneEngine::containerContentsChanged($this->holder);
		}
	}
}
