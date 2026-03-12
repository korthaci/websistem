# Websistem v1.0

A **developer-oriented, native PHP website management system** focused on clarity, control, and long-term maintainability.

Websistem is not a website builder and not a no‑code tool. It is designed for developers and technical users who want a predictable, lightweight system without the complexity of large CMS platforms or full-stack frameworks.

---

## What is Websistem?

Websistem is a native PHP-based website management engine that provides:

- A clean and isolated admin panel
- Theme-based frontend architecture
- Modular system structure
- Browser-based installer (no CLI required)
- Minimal external dependencies

It prioritizes **stability, transparency, and control** over abstraction and automation.

---

## What Websistem is NOT

To avoid confusion, Websistem is **not**:

- A no-code or drag‑and‑drop website builder
- A WordPress, Wix, or SaaS-style platform
- A framework replacement (Laravel, Symfony, etc.)
- A plugin marketplace–driven ecosystem

Basic knowledge of **HTML / CSS (and optionally JS)** is expected.

---

## Who is it for?

- Developers building custom websites
- Freelancers delivering client projects
- Agencies that want a reusable, controllable system
- Users who prefer native PHP over heavy abstractions

---

## Who is it NOT for?

- Users looking for a no‑code solution
- Plugin‑first workflows
- Enterprise-scale applications
- Real-time or highly distributed systems

---

## Core Features

- Web-based installation wizard
- MySQL and SQLite support
- Fully isolated admin panel (UIS2)
- Theme system with full HTML control
- Modular architecture (each module has its own router)
- Built-in translation infrastructure
- File and media management
- Multi-user admin system with permissions
- REST-compatible and AJAX-driven structure

---

## Themes

Frontend themes are fully independent from the core system.

- Each folder under `/tema/` represents a complete theme
- Themes control only presentation, not business logic
- Switching themes is handled from the admin panel

Themes are intended for developers who want **full control over markup**.

---

## Modules

Websistem includes a modular system where:

- Each module is self-contained
- Modules may define their own routes
- Modules can be embedded into content using placeholders

The module system is designed to support future commercial extensions.

---

## Installation

Installation is handled entirely through a **browser-based installer**.

- Upload files
- Open `/install`
- Follow the setup wizard

No command-line usage is required.

---

## Documentation

Full documentation is included in the `/documentation` directory.

**Online Documentation:** [https://n0n1.tr/documentation](https://n0n1.tr/documentation)

Topics include:

- Getting Started
- Installation
- System Architecture
- Module & Theme Structure
- FAQ
- Roadmap

---

## Licensing

Licensing depends on where you obtained this software.

- **GitHub releases** are licensed under the **Websistem Community License**
- **Commercial distributions** (Gumroad, Envato, etc.) are licensed under the **Websistem Commercial License**

You may build and sell websites using Websistem.
You may **not** sell, rebrand, or redistribute Websistem itself as a product.

Please review the included license file for details.

---

## Approach

Websistem values:

- Simplicity over convenience
- Control over abstraction
- Stability over trends

If something feels intentionally limited, it probably is.

---

**Websistem v1.0**  
Built for developers who want to stay in control.

---

**Website:** [https://n0n1.tr](https://n0n1.tr)
