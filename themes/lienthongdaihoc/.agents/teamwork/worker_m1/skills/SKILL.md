---
name: php-wordpress
version: "2.0.0"
description: WordPress development mastery - themes, plugins, Gutenberg blocks, and REST API
sasmp_version: "1.3.0"
bonded_agent: 04-php-wordpress
bond_type: PRIMARY_BOND
atomic: true
category: cms
---

# WordPress Development Skill

> Atomic skill for mastering WordPress theme and plugin development

## Overview

Comprehensive skill for building WordPress themes, plugins, and Gutenberg blocks. Covers WordPress 6.x with focus on modern development practices and security.

## WP-CLI Commands & Data Integrity Notes
- Safe post status transitions: use `wp_update_post(['ID' => $id, 'post_status' => 'draft'])`.
- Never use `wp_delete_post()` when soft-deprecating or archiving.
- Meta handling: `update_post_meta($id, $meta_key, $meta_value)`.
- Array meta sanitization and array_values re-indexing when pruning IDs.
- Transient flushing: `delete_transient()`.
- WP-CLI registration: `WP_CLI::add_command('ltdh audit-data', ...)`.
