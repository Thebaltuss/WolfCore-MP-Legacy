# Changelog

## WolfCore-MP Legacy 1.0.0 — archived source snapshot (2026-11-19)

### Network compatibility

- Preserved gameplay/protocol base `589` (Minecraft Bedrock 1.20.0).
- Added selectable client adapters for `594`, `618`, `622`, `630` and `649`.
- Added per-protocol block hash translation, item palettes, recipe conversion, per-session serialization and chunk conversion where implemented.
- Addressed selected animation, jukebox, movement and packet layout issues in newer clients.

### Gameplay

- Expanded native Redstone logic: pistons, sticky pistons, hopper transfers, repeaters, comparators, redstone torches, delayed updates and related mechanics.
- Added the bamboo building family and scaffolding, including block-state serialization.
- Added bamboo raft and chest-raft items; vehicle functionality remains incomplete.
- Added client skin/login handling changes and quality-of-life fixes for creative placement.
- Removed Minecraft Education Edition chemistry content.

### Security work

- Integrated several hardening changes concerning JWT/logins, malformed JSON, resource packs, form responses, packet array limits, entity despawn and broadcast amplification.
- This is a description of included mitigations, **not an independently audited vulnerability-free release**.

### Repository cleanup

- Extracted the shipped PHAR source and bundled dependencies.
- Removed 29 root-level temporary TXT reports, two protocol 649 test PHP scripts and one `security/` check script from the publication archive.
- Left executable protocol support, game logic and original third-party license notices intact.
- Added English/Russian README, packaging notes, license documents and packaging utility.

### Known limitations

- Protocol 649 item palettes are partially reconstructed; new 1.20.60 block mappings are not complete.
- Protocol 630 mappings require additional verification with real clients.
- Not all vanilla Redstone components and animations are implemented.
- Scaffolding movement differs in some cases; vehicle functions for bamboo rafts are limited.
