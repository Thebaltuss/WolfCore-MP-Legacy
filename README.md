# 🐺 WolfCore-MP Legacy

**A community-maintained, independently modified PocketMine-MP 5.2.1-based server core for Minecraft: Bedrock Edition.**

[![Core version](https://img.shields.io/badge/WolfCore--MP-1.0.0-blue)](#versioning)
[![Minecraft base](https://img.shields.io/badge/gameplay-1.20.0-539d63)](#minecraft-versions)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777bb4)](#requirements)
[![License](https://img.shields.io/badge/license-LGPL--3.0-or--later-lightgrey)](LICENSE)

WolfCore-MP Legacy preserves **Minecraft Bedrock 1.20.0 gameplay as its base** while adding protocol-compatibility layers for newer clients, expanded Redstone and bamboo content, and a collection of security hardening changes. It is intended as a preservation and development branch, not as an official PocketMine-MP distribution.

> [!IMPORTANT]
> A client protocol being recognized does **not** guarantee perfect gameplay compatibility. Several newer protocol mappings are incomplete, and client-level acceptance tests have not been demonstrated for this published snapshot. Make backups before deploying or modifying worlds.

## Highlights

- **Multi-protocol Bedrock support:** six protocol numbers registered, with per-client packet handling and block/item translation.
- **Redstone expansion:** native server-side signal engine; piston movement (up to 12 blocks), sticky pistons, repeaters, comparators, torches, lamps, hoppers and related updates.
- **Bamboo content:** blocks, stairs, slabs, mosaics, fences, gates, doors, trapdoors, buttons, signs, hanging signs and related recipes/registrations; bamboo raft items are also recognized.
- **Scaffolding:** implemented, but movement/descent differs from vanilla in some circumstances.
- **Security hardening:** mitigations for several publicly described denial-of-service, login parsing, entity lifecycle, and packet-validation issues.
- **Other compatibility fixes:** player skins, login `sandboxId`, animation/sound handling, creative-flight interactions and selected network packet changes.
- **No Minecraft Education chemistry content:** the Education chemistry items, blocks, and related resources were removed from this fork.

## Minecraft versions

The **world, block, and gameplay base remains Minecraft Bedrock 1.20.0** (protocol `589`). The following **client protocol IDs are registered in the packaged source**:

| Protocol | Target Minecraft Bedrock releases | Notes |
|:--:|:--|:--|
| `589` | 1.20.0–1.20.1 | Native gameplay baseline |
| `594` | 1.20.10, 1.20.12–1.20.15 | Converted block hashes and client packet adaptations |
| `618` | 1.20.30–1.20.32 | Hash-mode palette and packet adaptation |
| `622` | 1.20.40–1.20.41 | Based in part on 618 conversion paths |
| `630` | 1.20.50–1.20.51 | Newer packets and transformed item/block data; some mappings may be incomplete |
| `649` | 1.20.60–1.20.62 | Batch/packet changes and item mapping; full vanilla palettes are **not** included |

**Why 618, not 619?** The actual `ProtocolInfo` and `ProtocolVersions` registers in this source list `618`.

These versions use the **RakNet** transport path in this legacy branch. No NetherNet support is claimed.

## Gameplay changes

### Redstone

WolfCore includes an enhanced Redstone implementation with piston extension/retraction, slime attachments, repeater timing and locking, comparator signals, torch updates, hopper transfers and container-based signal changes. The simulation has a bounded per-tick work budget.

**Known non-vanilla gaps:** this is not a fully verified Bedrock-parity implementation. Moving-block interpolation, full dust power semantics, honey-block interactions, selected observer/dispenser/dropper behavior and movement involving unloaded chunks may be limited. **Command blocks are not implemented.** See the source for the exact capabilities of each block.

### Bamboo and scaffolding

Bamboo block families, mosaics, signs, hanging signs and their variations are registered in the core. Bamboo raft and chest-raft **items** are registered; complete raft placement/vehicle functionality is **not** guaranteed by this snapshot. Scaffolding can be used and climbed, although descending/player movement is not fully vanilla-identical.

## Security

Multiple patches and input-validation limits have been ported or independently implemented, covering selected JWT/login payloads, resource-pack negotiation, packet parsing, large arrays, form responses, broadcast amplification, and entity despawn behavior.

**This is not a certification that all vulnerabilities are fixed.** A separate security audit, fuzzing, and practical protocol tests are still recommended. See [SECURITY.md](SECURITY.md) for the categories addressed and the reporting process.

## Requirements

- PHP **8.1 or later** (the native dependencies must also be compatible with the chosen PHP build).
- Required PocketMine/WolfCore native extensions, including `pmmpthread`, `leveldb`, `chunkutils2`, `crypto`, `igbinary`, `morton`, plus standard extensions checked by `src/WolfCore.php`.
- A compatible RakNet-capable Bedrock server environment.
- The bundled `vendor/` dependency snapshot; it contains **modified first-party-fork dependencies** and must not be casually replaced by upstream Composer packages.

The available source is extracted from the supplied PHAR. It is **not** a restored original Git repository and does **not** include the original Composer manifest/lockfile. See [BUILDING.md](BUILDING.md) before rebuilding.

## Project layout

```text
src/             WolfCore-MP server sources
resources/       Server resources and configuration
vendor/          Bundled Composer packages and forked protocol libraries
build/           Package metadata used by the rebuilding script
tools/           PHAR packaging utility
README_RU.md     Russian-language overview
BUILDING.md      How to rebuild using the preserved vendor snapshot
CHANGELOG.md     Summary of changes in this Legacy snapshot
SECURITY.md      Security scope, limitations and disclosure
LICENSE          LGPL version 3
COPYING          GPL version 3 (incorporated by LGPLv3)
```

## Build from extracted source

Run with PHP configured to allow creating PHAR archives:

```bash
php -d phar.readonly=0 tools/build-phar.php
```

The command outputs `dist/WolfCore-MP-Legacy.phar`. For normal server use, launch the PHAR with a compatible PHP binary; the script must be run in a server directory with the appropriate native extensions installed.

## Versioning

- **WolfCore-MP:** `1.0.0` (this fork's own core version).
- **Plugin API:** `1.0.0` in this snapshot.
- **Gameplay base:** Minecraft Bedrock `1.20.0`.
- **Network protocols:** tracked separately in the multi-protocol compatibility layer.

## Credits and attribution

- **WolfCore-MP Legacy maintainer and author of the fork's modifications:** **Thebaltusss**.
- **Original project and upstream authors:** [PocketMine-MP](https://github.com/pmmp/PocketMine-MP), **PocketMine Team and its contributors**.
- **Base:** PocketMine-MP **5.2.1**, subsequently modified. Third-party component copyrights and license files under `vendor/` remain in effect.

This independent fork is **not affiliated with or endorsed by PocketMine Team, Mojang, or Microsoft**.

## License

WolfCore-MP Legacy is distributed under the **GNU Lesser General Public License, version 3 or later** for covered core code, consistent with the inherited source headers. See [LICENSE](LICENSE) and [COPYING](COPYING). Different bundled components may have their own licensing notices; do not remove those from `vendor/`.

## ❤️ Support development

If you find WolfCore-MP useful and want to support the maintainer, BTC donations are welcome.

**Bitcoin (BTC):**

```text
1BtGYHqinpvGGobzhoKPQGAu88a9zdqCCu
```

Donations are optional and are not required to use the project.
"# WolfCore-MP-Legacy" 
