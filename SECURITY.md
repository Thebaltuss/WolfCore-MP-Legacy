# Security Policy

## Status

WolfCore-MP Legacy is a source snapshot based on PocketMine-MP 5.2.1 and the Minecraft Bedrock 1.20.0 game core. This branch includes targeted defensive changes, but it has **not** received a complete independent security audit. Do not represent it as immune to exploits or automatically safe for public hosting.

The submitted modification notes describe hardening for the following upstream advisory families:

| Advisory | Area | Mitigation category described in snapshot |
|---|---|---|
| [GHSA-79rc-jjh6-rc89](https://github.com/pmmp/PocketMine-MP/security/advisories/GHSA-79rc-jjh6-rc89) | Invalid EC keys | Curve/key validation on login paths |
| [GHSA-92jh-gwch-jq38](https://github.com/pmmp/PocketMine-MP/security/advisories/GHSA-92jh-gwch-jq38) | Invalid JSON types | JSON/JWT type validation |
| [GHSA-h6j3-j35f-v2x7](https://github.com/pmmp/PocketMine-MP/security/advisories/GHSA-h6j3-j35f-v2x7) | Uninitialized login objects | Strict login data parsing |
| [GHSA-h6rj-3m53-887h](https://github.com/pmmp/PocketMine-MP/security/advisories/GHSA-h6rj-3m53-887h) | Login logging DoS | Bounded logging / input sizes |
| [GHSA-xp4f-g2cm-rhg7](https://github.com/pmmp/PocketMine-MP/security/advisories/GHSA-xp4f-g2cm-rhg7) | Excess JWT fields | Count/length limits |
| [GHSA-g274-c6jj-h78p](https://github.com/pmmp/PocketMine-MP/security/advisories/GHSA-g274-c6jj-h78p) | Expensive `explode()` usage | Bounded input processing (not complete in every call site) |
| [GHSA-fqqv-56h5-f57g](https://github.com/pmmp/PocketMine-MP/security/advisories/GHSA-fqqv-56h5-f57g) | Resource-pack DoS | Deduplication and request limits |
| [GHSA-788v-5pfp-93ff](https://github.com/pmmp/PocketMine-MP/security/advisories/GHSA-788v-5pfp-93ff) | Oversized form responses | JSON size and state checks |
| [GHSA-7hmv-4j2j-pp6f](https://github.com/pmmp/PocketMine-MP/security/advisories/GHSA-7hmv-4j2j-pp6f) | Actor event amplification | Server-controlled event processing |
| [GHSA-f9jp-856v-8642](https://github.com/pmmp/PocketMine-MP/security/advisories/GHSA-f9jp-856v-8642) | Entity despawn item duplication | Lifecycle/interaction guards |
| [GHSA-pg53-p7qp-65cv](https://github.com/pmmp/PocketMine-MP/security/advisories/GHSA-pg53-p7qp-65cv) | Resource-pack handshake replay | State machine / completion guards |

These are **implementation claims derived from the supplied change notes**, not proof that each advisory is fully remediated in all runtime paths. More testing and a full upstream diff are needed.

## Reporting a vulnerability

Please use GitHub's private vulnerability reporting feature, if enabled on the repository. Avoid disclosing exploit details in public issues before the maintainer has had an opportunity to investigate.

## Production guidance

Keep backups, restrict network exposure, use hosting-level UDP protection, and test unexpected packet lengths, connection churn, and concurrent mixed-version sessions. Consider a maintained modern base for security-critical public deployments.
