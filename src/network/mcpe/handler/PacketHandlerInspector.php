<?php
/*
 * This file is part of WolfCore-MP.
 * Copyright (C) PocketMine Team.
 * Licensed under the GNU Lesser General Public License v3.0 or later.
 */
declare(strict_types=1);

namespace wolfcore\network\mcpe\handler;

use wolfcore\network\mcpe\protocol\Packet;
use wolfcore\network\mcpe\protocol\PacketHandlerInterface;
use wolfcore\utils\AssumptionFailedError;

/**
 * Inspects protocol interface methods implemented by a handler to identify
 * which incoming packets are meaningful in each session state.
 */
final class PacketHandlerInspector{
	/** @var array<class-string<PacketHandler>, array<class-string<Packet>, PacketHandlerAction>> */
	private static array $cache = [];

	/** @return array<class-string<Packet>, PacketHandlerAction> */
	public static function getHandlerActions(PacketHandler $handler) : array{
		$handlerClass = $handler::class;
		if(isset(self::$cache[$handlerClass])){
			return self::$cache[$handlerClass];
		}

		$actions = [];
		$handlerReflection = new \ReflectionClass($handler);
		$interfaceReflection = new \ReflectionClass(PacketHandlerInterface::class);
		foreach($interfaceReflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $interfaceMethod){
			try{
				$implementation = $handlerReflection->getMethod($interfaceMethod->getName());
			}catch(\ReflectionException $e){
				throw new AssumptionFailedError("PacketHandler must implement PacketHandlerInterface", 0, $e);
			}
			$parameter = $implementation->getParameters()[0] ?? throw new AssumptionFailedError("PacketHandler interface method is missing a packet parameter");
			$argumentType = $parameter->getType();
			if(!$argumentType instanceof \ReflectionNamedType || $argumentType->isBuiltin()){
				continue;
			}
			$packetClass = $argumentType->getName();
			if(!is_a($packetClass, Packet::class, true)){
				continue;
			}
			$actions[$packetClass] = $implementation->getDeclaringClass()->getName() === PacketHandler::class
				? PacketHandlerAction::DISCARD_WITH_DEBUG
				: PacketHandlerAction::HANDLED;
		}

		foreach($handlerReflection->getAttributes(SilentDiscard::class) as $attribute){
			/** @var SilentDiscard $info */
			$info = $attribute->newInstance();
			$packetClass = $info->packetClass;
			if(!is_a($packetClass, Packet::class, true)){
				throw new \InvalidArgumentException("Invalid SilentDiscard packet class: " . $packetClass);
			}
			if(($actions[$packetClass] ?? null) === PacketHandlerAction::HANDLED){
				continue;
			}
			$actions[$packetClass] = PacketHandlerAction::DISCARD_SILENT;
		}

		return self::$cache[$handlerClass] = $actions;
	}
}
