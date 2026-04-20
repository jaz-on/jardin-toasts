# Untappd data and scraping — user notice

This plugin is **not affiliated with Untappd**. It uses **public** RSS feeds and HTML pages that Untappd may change at any time; scraping can fail or require plugin updates when the site markup changes.

**You** (the site owner) are responsible for:

- Using only data you have the right to republish on your WordPress site.
- Complying with Untappd’s terms of use and acceptable use, and with applicable law in your jurisdiction.
- Configuring **reasonable delays** between requests (defaults are conservative) to limit load on Untappd’s servers.

The plugin does not bypass authentication or access non-public profiles. Rate limiting and logging are provided to aid responsible use.

For technical behaviour (HTTP, retries, logs), see the architecture and scraping documentation under `docs/architecture/`.
