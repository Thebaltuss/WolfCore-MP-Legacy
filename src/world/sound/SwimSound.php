<?php

declare(strict_types=1);

namespace wolfcore\world\sound;

use wolfcore\math\Vector3;
use wolfcore\network\mcpe\protocol\LevelSoundEventPacket;
use wolfcore\network\mcpe\protocol\types\LevelSoundEvent;

/** Remote players' swimming audio, shared by the 589 and 594 wire formats. */
final class SwimSound implements Sound{
	public function encode(Vector3 $pos) : array{
		return [LevelSoundEventPacket::nonActorSound(LevelSoundEvent::SWIM, $pos, false)];
	}
}
