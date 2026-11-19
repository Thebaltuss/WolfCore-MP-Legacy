<?php
/*
 * This file is part of WolfCore-MP.
 * Copyright (C) PocketMine Team.
 * Licensed under the GNU Lesser General Public License v3.0 or later.
 */
declare(strict_types=1);

namespace wolfcore\network\mcpe\handler;

/** Decision for packets sent by the client in the current network session phase. */
enum PacketHandlerAction{
	case HANDLED;
	case DISCARD_SILENT;
	case DISCARD_WITH_DEBUG;
}
