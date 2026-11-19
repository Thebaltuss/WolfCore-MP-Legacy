# Building WolfCore-MP Legacy

This snapshot was **recovered from the supplied, signed PHAR archive**. It contains all extracted runtime `src/`, `resources/` and `vendor/` files. The original source checkout, original Git history, root `composer.json` and `composer.lock` were not present inside the PHAR.

The `vendor/` directory is **part of this source release** because several dependencies in `vendor/wolfcore/` contain fork-specific changes, including multiple Bedrock protocol adapters. Do not run `composer update` against an arbitrary upstream manifest; it may overwrite modified libraries.

## Rebuild PHAR

From the project root, using a PHP CLI with the PHAR extension:

```bash
php -d phar.readonly=0 tools/build-phar.php
```

This creates `dist/WolfCore-MP-Legacy.phar`. The packaging process puts only the `src/`, `resources/`, and `vendor/` trees in the PHAR. Documentation, tests and temporary patch notes are intentionally not bundled.

The original archive had SHA-256 PHAR signing, a startup stub loading `src/WolfCore.php`, and Composer root reference `200e5f940c39ea17a1a16c6afe4b191df15896dd`. The packaging script preserves this metadata reference because the embedded Composer installation checks it at startup. **This value is a packaged dependency reference, not a claim that this repository corresponds to that Git commit.**

## Run

Launch the resulting PHAR using a PHP environment meeting the native extension requirements listed in `src/WolfCore.php`, e.g.:

```bash
php dist/WolfCore-MP-Legacy.phar
```

A standard PHP installation without the PocketMine/WolfCore native extensions will not start the server. Package generation and PHP syntax checks **do not** verify functional multiplayer gameplay, plugin compatibility, world upgrades or security.

## Source-mode caveat

Launching `src/WolfCore.php` directly outside a PHAR may fail its Composer dependency revision check if the source checkout's git revision differs from the embedded Composer reference. The rebuilt PHAR is the intended reproducible launch artifact for this extracted snapshot; a future full source repository should re-establish proper Composer dependency management rather than bypassing the consistency check.
