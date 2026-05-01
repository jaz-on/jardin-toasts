# Admin DataViews spike (Sync tab)

The **Synchronization** tab can show a read-only **DataViews** table when the webpack bundle is present.

## Build

From the plugin root:

```bash
npm install
npm run build
```

This generates (gitignored by default):

- `build/admin-dataviews.js`
- `build/admin-dataviews.css`
- `build/admin-dataviews.asset.php`

[`admin/class-admin.php`](../admin/class-admin.php) enqueues these assets only when `build/admin-dataviews.asset.php` exists. Without a build, the DataViews panel is omitted (no runtime error).

## Source files

- [`admin/src/dataviews-sync.js`](../admin/src/dataviews-sync.js) — React mount + `@wordpress/dataviews/wp`.
- [`admin/src/dataviews-sync.scss`](../admin/src/dataviews-sync.scss) — imports DataViews component styles.
- [`webpack.config.cjs`](../webpack.config.cjs) — adds the `admin-dataviews` entry alongside the default `@wordpress/scripts` entry.

## WordPress / PHP targets

Align with the Jardin stack: **PHP 8.2+**, test locally on **8.4**; **WordPress 7.0** beta where possible.
