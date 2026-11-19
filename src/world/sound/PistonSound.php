<?php
/* WolfCore piston sound implementation - LGPL-3.0-or-later */
declare(strict_types=1);

namespace wolfcore\world\sound;

use wolfcore\math\Vector3;
use wolfcore\network\mcpe\protocol\LevelSoundEventPacket;
use wolfcore\network\mcpe\protocol\types\LevelSoundEvent;

final class PistonSound implements Sound{
	public function __construct(private bool $extending){}

	public function encode(Vector3 $pos) : array{
		return [LevelSoundEventPacket::nonActorSound($this->extending ? LevelSoundEvent::PISTON_OUT : LevelSoundEvent::PISTON_IN, $pos, false)];
	}
}
