<?php

/*
 * Copyright (C) PocketMine Team
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

declare(strict_types=1);

namespace wolfcore\network;

use wolfcore\utils\MainLogger;
use function base64_encode;
use function bin2hex;
use function min;
use function strlen;
use function substr;

/**
 * Bounds diagnostic dumps of untrusted network data, without altering the original data.
 */
final class NetworkPacketDebug{
	private const MAX_DUMP_BYTES = 1024;

	private function __construct(){}

	/**
	 * The stock logger discards debug messages when debugging is disabled.
	 * Other Logger implementations have no standard query API, so conservatively
	 * allow a bounded debug message to be passed to them.
	 */
	public static function isEnabled(\Logger $logger) : bool{
		return !($logger instanceof MainLogger) || $logger->isDebugEnabled();
	}

	public static function base64Dump(string $data) : string{
		return self::dump($data, false);
	}

	public static function hexDump(string $data) : string{
		return self::dump($data, true);
	}

	private static function dump(string $data, bool $hex) : string{
		$total = strlen($data);
		$shown = min($total, self::MAX_DUMP_BYTES);
		$chunk = substr($data, 0, self::MAX_DUMP_BYTES);
		$dump = $hex ? bin2hex($chunk) : base64_encode($chunk);
		$encoding = $hex ? "hex" : "base64";
		$omitted = $total - $shown;
		return "($total bytes; first $shown bytes as $encoding): " . $dump
			. ($omitted > 0 ? " ... ($omitted bytes omitted)" : "");
	}
}
