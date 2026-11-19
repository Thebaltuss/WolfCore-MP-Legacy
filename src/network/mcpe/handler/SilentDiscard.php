<?php
/*
 * This file is part of WolfCore-MP.
 * Copyright (C) PocketMine Team.
 * Licensed under the GNU Lesser General Public License v3.0 or later.
 */
declare(strict_types=1);

namespace wolfcore\network\mcpe\handler;

use wolfcore\network\mcpe\protocol\Packet;

/** Mark an unimplemented packet as safe to ignore without a debug log or decode. */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class SilentDiscard{
	/** @phpstan-param class-string<covariant Packet> $packetClass */
	public function __construct(
		public readonly string $packetClass,
		public readonly string $comment = ""
	){}
}
