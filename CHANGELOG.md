# Changelog

All notable changes to this project will be documented in this file.
## [Unreleased]
### Fixed
- `missing` no longer reports keys that hold an object or an array (`"user"`, `"tags"`) as missing.
- `missing_some` counts dotted keys (`"user.name"`) as present; it only checked top-level keys.

## [0.1.1] - 2026-03-16
### Added
- Passtrough for empty stdClass object works now
- Avoid negates zero (`-0`) in php for compliance.