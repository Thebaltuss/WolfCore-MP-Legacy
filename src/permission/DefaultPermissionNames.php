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

namespace wolfcore\permission;

final class DefaultPermissionNames{
	public const BROADCAST_ADMIN = "wolfcore.broadcast.admin";
	public const BROADCAST_USER = "wolfcore.broadcast.user";
	public const COMMAND_BAN_IP = "wolfcore.command.ban.ip";
	public const COMMAND_BAN_LIST = "wolfcore.command.ban.list";
	public const COMMAND_BAN_PLAYER = "wolfcore.command.ban.player";
	public const COMMAND_CLEAR_OTHER = "wolfcore.command.clear.other";
	public const COMMAND_CLEAR_SELF = "wolfcore.command.clear.self";
	public const COMMAND_DEFAULTGAMEMODE = "wolfcore.command.defaultgamemode";
	public const COMMAND_DIFFICULTY = "wolfcore.command.difficulty";
	public const COMMAND_DUMPMEMORY = "wolfcore.command.dumpmemory";
	public const COMMAND_EFFECT_OTHER = "wolfcore.command.effect.other";
	public const COMMAND_EFFECT_SELF = "wolfcore.command.effect.self";
	public const COMMAND_ENCHANT_OTHER = "wolfcore.command.enchant.other";
	public const COMMAND_ENCHANT_SELF = "wolfcore.command.enchant.self";
	public const COMMAND_GAMEMODE_OTHER = "wolfcore.command.gamemode.other";
	public const COMMAND_GAMEMODE_SELF = "wolfcore.command.gamemode.self";
	public const COMMAND_GC = "wolfcore.command.gc";
	public const COMMAND_GIVE_OTHER = "wolfcore.command.give.other";
	public const COMMAND_GIVE_SELF = "wolfcore.command.give.self";
	public const COMMAND_HELP = "wolfcore.command.help";
	public const COMMAND_KICK = "wolfcore.command.kick";
	public const COMMAND_KILL_OTHER = "wolfcore.command.kill.other";
	public const COMMAND_KILL_SELF = "wolfcore.command.kill.self";
	public const COMMAND_LIST = "wolfcore.command.list";
	public const COMMAND_ME = "wolfcore.command.me";
	public const COMMAND_OP_GIVE = "wolfcore.command.op.give";
	public const COMMAND_OP_TAKE = "wolfcore.command.op.take";
	public const COMMAND_PARTICLE = "wolfcore.command.particle";
	public const COMMAND_PLUGINS = "wolfcore.command.plugins";
	public const COMMAND_SAVE_DISABLE = "wolfcore.command.save.disable";
	public const COMMAND_SAVE_ENABLE = "wolfcore.command.save.enable";
	public const COMMAND_SAVE_PERFORM = "wolfcore.command.save.perform";
	public const COMMAND_SAY = "wolfcore.command.say";
	public const COMMAND_SEED = "wolfcore.command.seed";
	public const COMMAND_SETWORLDSPAWN = "wolfcore.command.setworldspawn";
	public const COMMAND_SPAWNPOINT_OTHER = "wolfcore.command.spawnpoint.other";
	public const COMMAND_SPAWNPOINT_SELF = "wolfcore.command.spawnpoint.self";
	public const COMMAND_STATUS = "wolfcore.command.status";
	public const COMMAND_STOP = "wolfcore.command.stop";
	public const COMMAND_TELEPORT_OTHER = "wolfcore.command.teleport.other";
	public const COMMAND_TELEPORT_SELF = "wolfcore.command.teleport.self";
	public const COMMAND_TELL = "wolfcore.command.tell";
	public const COMMAND_TIME_ADD = "wolfcore.command.time.add";
	public const COMMAND_TIME_QUERY = "wolfcore.command.time.query";
	public const COMMAND_TIME_SET = "wolfcore.command.time.set";
	public const COMMAND_TIME_START = "wolfcore.command.time.start";
	public const COMMAND_TIME_STOP = "wolfcore.command.time.stop";
	public const COMMAND_TIMINGS = "wolfcore.command.timings";
	public const COMMAND_TITLE_OTHER = "wolfcore.command.title.other";
	public const COMMAND_TITLE_SELF = "wolfcore.command.title.self";
	public const COMMAND_TRANSFERSERVER = "wolfcore.command.transferserver";
	public const COMMAND_UNBAN_IP = "wolfcore.command.unban.ip";
	public const COMMAND_UNBAN_PLAYER = "wolfcore.command.unban.player";
	public const COMMAND_VERSION = "wolfcore.command.version";
	public const COMMAND_WHITELIST_ADD = "wolfcore.command.whitelist.add";
	public const COMMAND_WHITELIST_DISABLE = "wolfcore.command.whitelist.disable";
	public const COMMAND_WHITELIST_ENABLE = "wolfcore.command.whitelist.enable";
	public const COMMAND_WHITELIST_LIST = "wolfcore.command.whitelist.list";
	public const COMMAND_WHITELIST_RELOAD = "wolfcore.command.whitelist.reload";
	public const COMMAND_WHITELIST_REMOVE = "wolfcore.command.whitelist.remove";
	public const GROUP_CONSOLE = "wolfcore.group.console";
	public const GROUP_OPERATOR = "wolfcore.group.operator";
	public const GROUP_USER = "wolfcore.group.user";
}
