# Plugin WordPress : Jardin Toasts

## Spécifications Techniques Complètes

### Informations du projet
- **Nom du plugin :** Jardin Toasts
- **Slug WordPress.org :** `jardin-toasts`
- **Display name :** Jardin Toasts for Untappd
- **Conformité trademark :** ✅ Respect guidelines WordPress.org

### Contraintes du projet
- ❌ Pas d'API Untappd officielle disponible
- ✅ RSS feed permanent (25 derniers check-ins) - DONNÉES LIMITÉES
- ✅ Scraping obligatoire de chaque URL pour métadonnées complètes
- 📊 Volumétrie : ~200 check-ins historiques
- 🎨 Thème agnostique (templates surchargeables)
- 🖼️ Import local des images dans Media Library WordPress
- 🌍 Tout le contenu est public
- 🌐 Plugin en anglais (standard de développement)
- 📦 Publication prévue sur WordPress.org
- 🔓 Repo GitHub public dès le début

---

## 1. Système de Synchronisation

### A. Synchronisation RSS (automatique avec polling adaptatif)

**Fréquence : Polling adaptatif intelligent**

**Stratégie selon activité :**
```php
// Récupérer le dernier check-in importé
$last_checkin_date = get_option('jb_last_checkin_date');
$days_since_last = (time() - strtotime($last_checkin_date)) / DAY_IN_SECONDS;

if ($days_since_last < 7) {
    // Utilisateur actif → check fréquent
    $cron_schedule = 'sixhourly'; // Toutes les 6 heures
} elseif ($days_since_last < 30) {
    // Utilisateur moyennement actif
    $cron_schedule = 'daily'; // 1x/jour
} else {
    // Utilisateur inactif
    $cron_schedule = 'weekly'; // 1x/semaine (veille uniquement)
}

// Enregistrer le schedule dynamique
wp_clear_scheduled_hook('jb_rss_sync');
wp_schedule_event(time(), $cron_schedule, 'jb_rss_sync');
```

**Optimisation ressources :**
```php
// Fetch RSS (léger, ~5KB)
$rss = fetch_feed($rss_url);

// Extraire seulement le GUID du premier item
$latest_guid = $rss->get_items()[0]->get_id();

// Comparer avec le dernier connu
$last_imported_guid = get_option('jb_last_imported_guid');

if ($latest_guid === $last_imported_guid) {
    // Rien de nouveau → SKIP (0 ressources supplémentaires)
    log_info('No new check-ins, skipping sync');
    return;
}

// Nouveaux check-ins détectés → process complet
foreach ($rss->get_items() as $item) {
    // ... scraping + import
}
```

**Structure du flux RSS Untappd :**
```xml
<item>
  <title>Jason is drinking a Meteor Blonde De Garde by Brasserie Meteor at Untappd at Home</title>
  <link>https://untappd.com/user/jaz_on/checkin/1527514863</link>
  <guid>https://untappd.com/user/jaz_on/checkin/1527514863</guid>
  <description>
    <!-- Parfois contient photo en CDATA -->
    <![CDATA[<img src="https://images.untp.beer/..." />]]>
  </description>
  <pubDate>Sun, 09 Nov 2025 18:13:18 +0000</pubDate>
</item>
```

**⚠️ Limitation critique du RSS :**
Le flux RSS ne contient **PAS** :
- Note/rating (0-5)
- ABV % / IBU
- Style de bière
- Commentaire complet
- Type de service (Draft/Bottle/Can)
- Toasts (likes)

**Processus de synchronisation :**
1. Fetch RSS feed Untappd
2. Parse XML → extraire titre, link, guid, date
3. Pour chaque check-in :
   - Parser le titre : extraire beer_name, brewery_name, venue
   - Vérifier si existe déjà (via `untappd_checkin_id` du guid)
   - **Si nouveau → SCRAPER l'URL du check-in pour métadonnées complètes**
   - Télécharger image → Media Library (si présente)
   - Créer CPT avec toutes les données
   - Associer taxonomies
4. Logger les résultats (succès/erreurs)

**Gestion des erreurs :**
- Retry automatique en cas d'échec réseau (3 tentatives)
- Si scraping échoue → check-in en draft + notification
- Email de notification si échec persistant
- Logs détaillés dans `wp-content/uploads/jardin-toasts/logs/`

### B. Crawler Historique (manuel par batch avec checkpoints)

**Stratégie : Progressif Sécurisé avec Batches**

**Interface admin avec gestion manuelle :**
- Bouton "Import Historical Check-ins"
- Settings avant lancement :
  - Batch size : 25, 50, 100 check-ins par exécution
  - Delay : 3-5 secondes entre requêtes
  - Mode : "Manual" ou "Background (WP-Cron)"
- Barre de progression en temps réel avec ETA
- Logs en direct dans l'interface
- Système pause/reprise

**Mode Manuel (recommandé pour ~200 check-ins) :**
```php
// L'admin clique "Start Import"
// → Le navigateur reste ouvert
// → Process synchrone avec feedback en temps réel
// → AJAX calls toutes les 5 secondes pour mettre à jour progress

1. Scrape page 1 (25 check-ins) → 3 min
2. Checkpoint automatique (transient)
3. Continue page 2...
4. Si timeout PHP (30 sec) → arrêt propre + message
5. Bouton "Resume" disponible

// Limite : ~8 pages max (200 check-ins) avant timeout browser
```

**Mode Background (optionnel pour gros comptes) :**
```php
// L'admin clique "Start Background Import"
// → WP-Cron prend le relais
// → 1 batch/heure jusqu'à complétion
// → Notification email quand terminé

wp_schedule_single_event(time() + HOUR_IN_SECONDS, 'jb_background_import_batch');

// Checkpoint WordPress option
update_option('jb_import_checkpoint', [
    'current_page' => 3,
    'total_imported' => 75,
    'last_checkin_id' => '123456',
    'started_at' => time(),
]);
```

**Sélecteurs HTML à parser :**
```html
Cibles à extraire :
- .checkin-info          → Données principales
- .beer-details          → Nom bière, brasserie, style
- .details               → ABV %, IBU
- .rating-serving        → Note (OBLIGATOIRE) + type de service
- .photo                 → Image (OPTIONNELLE)
- .checkin-comment       → Commentaire utilisateur (optionnel)
- .venue-name            → Lieu de consommation
- .caps .count           → Nombre de likes (toasts)
```

### C. Logique de Publication (Scénario A - Strict avec Rating Obligatoire)

**Données OBLIGATOIRES pour status "publish" :**
```php
✓ Beer name         (extrait du RSS title ou scraped)
✓ Brewery name      (extrait du RSS title ou scraped)
✓ Check-in date     (pubDate du RSS)
✓ Rating (0-5)      (SCRAPED - OBLIGATOIRE)
```

**Données OPTIONNELLES :**
```php
○ Photo             (RSS description CDATA ou scraped) - OPTIONNELLE
○ Comment           (scraped)
○ Beer style        (scraped)
○ ABV %             (scraped)
○ IBU               (scraped)
○ Serving type      (scraped)
○ Venue             (RSS title ou scraped)
○ Toast count       (scraped)
```

**Règles de publication :**
```
IF (beer_name AND brewery_name AND date AND rating):
    → post_status = 'publish'
    → Stocker rating_raw ET rating_rounded (selon mapping)
    
ELSE IF (beer_name AND brewery_name AND date) BUT rating is NULL:
    → post_status = 'draft'
    → add_post_meta('_jb_incomplete_reason', 'missing_rating')
    → Ajouter à retry queue
    → notification admin
    
ELSE IF (scraping failed 3 times):
    → Option A (par défaut): Garder en draft indéfiniment
    → Option B (configurable): Publier avec flag "Rating unavailable"
    → Bouton admin "Retry failed imports" pour re-scraper
    
ELSE:
    → Skip (log error)
```

**Système de retry automatique + manuel :**
```php
// Retry automatique
- Tentative 1: Immédiat
- Tentative 2: +6 heures (via WP-Cron)
- Tentative 3: +24 heures (via WP-Cron)
- Après 3 échecs: reste en draft

// Retry manuel
- Bouton admin "Retry Failed Imports"
- Sélection multiple des drafts à re-tenter
- Force un nouveau scraping immédiat
```

**Notifications admin :**
- Dashboard notice : "X check-ins in draft awaiting review"
- Email digest quotidien si drafts en attente (désactivable)
- Link direct vers la liste des drafts filtrés
- Compteur dans menu admin : "Jardin Toasts (3)" = 3 drafts

---

## 2. Système de Notation et Mapping

### A. Architecture du système de notation

**Stockage double des notes :**
```php
'_jb_rating_raw'      => 4.25,  // Note originale Untappd (float 0-5)
'_jb_rating_rounded'  => 4,     // Note arrondie selon règles (int 0-5)
```

**Règles de mapping par défaut :**
```
0.0 - 0.9  →  0 stars ⭐
1.0 - 1.9  →  1 star  ⭐
2.0 - 2.9  →  2 stars ⭐⭐
3.0 - 3.4  →  3 stars ⭐⭐⭐
3.5 - 4.4  →  4 stars ⭐⭐⭐⭐
4.5 - 5.0  →  5 stars ⭐⭐⭐⭐⭐
```

**Fonction de mapping :**
```php
/**
 * Map Untappd rating to rounded star rating
 * 
 * @param float $raw_rating Original Untappd rating (0-5)
 * @return int Rounded rating (0-5)
 */
function jb_map_rating($raw_rating) {
    $rules = get_option('jb_rating_rules', [
        ['min' => 0.0, 'max' => 0.9, 'round' => 0],
        ['min' => 1.0, 'max' => 1.9, 'round' => 1],
        ['min' => 2.0, 'max' => 2.9, 'round' => 2],
        ['min' => 3.0, 'max' => 3.4, 'round' => 3],
        ['min' => 3.5, 'max' => 4.4, 'round' => 4],
        ['min' => 4.5, 'max' => 5.0, 'round' => 5],
    ]);
    
    foreach ($rules as $rule) {
        if ($raw_rating >= $rule['min'] && $raw_rating <= $rule['max']) {
            return $rule['round'];
        }
    }
    
    return round($raw_rating); // Fallback
}
```

### B. Labels personnalisés

**Stockage WordPress option :**
```php
// Default labels in English (standardized)
$default_labels = [
    0 => 'Undrinkable - Not even beer',
    1 => 'Terrible - Only if there\'s no alternative',
    2 => 'Mediocre - Meh, it\'s okay I guess',
    3 => 'Decent - A solid thirst quencher',
    4 => 'Great - Now we\'re talking! A real pleasure',
    5 => 'Exceptional - Buy it with your eyes closed. Masterpiece!',
];

// User can customize in admin settings (stored in DB)
$rating_labels = get_option('jb_rating_labels', $default_labels);

// Example French customization (user-defined):
update_option('jb_rating_labels', [
    0 => 'Dégueulasse, à fuir comme la peste',
    1 => 'Soit je ne pouvais pas refuser, soit j\'étais ivre',
    2 => 'Ça passe quand y\'a pas d\'alternative',
    3 => 'Ok là ça commence à être okay',
    4 => 'Ah bah voilà, ça c\'est de la bière !',
    5 => 'Tu veux te faire plaisir ? Achète les yeux fermés !',
]);
```

**Interface admin "Rating System" :**
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
RATING MAPPING RULES
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

☑ Enable rating rounding
☐ Display original rating in tooltip (hover effect)

Untappd ratings (0-5 with decimals) will be mapped:

0.0 - 0.9  →  ⭐ (0 stars)
1.0 - 1.9  →  ⭐ (1 star)
2.0 - 2.9  →  ⭐⭐ (2 stars)
3.0 - 3.4  →  ⭐⭐⭐ (3 stars)
3.5 - 4.4  →  ⭐⭐⭐⭐ (4 stars)
4.5 - 5.0  →  ⭐⭐⭐⭐⭐ (5 stars)

[Edit Mapping Rules] (Advanced)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
RATING LABELS (Customize Messages)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Customize the description for each rating level (max 500 chars):

0 stars label:
[Undrinkable - Not even beer_________________________________]

1 star label:
[Terrible - Only if there's no alternative__________________]

2 stars label:
[Mediocre - Meh, it's okay I guess__________________________]

3 stars label:
[Decent - A solid thirst quencher___________________________]

4 stars label:
[Great - Now we're talking! A real pleasure_________________]

5 stars label:
[Exceptional - Buy it with your eyes closed. Masterpiece!___]

☑ Display labels on single check-in pages
☐ Display labels in archive (grid cards)
☐ Display labels in list view

[Reset to Defaults] [Save Rating Settings]
```

### C. Affichage frontend

**Template tag :**
```php
/**
 * Display rating with stars and optional label
 * 
 * @param int $post_id Post ID
 * @param bool $show_label Show custom label
 * @param bool $show_raw Show original rating in tooltip
 */
function jb_display_rating($post_id, $show_label = true, $show_raw = true) {
    $raw = get_post_meta($post_id, '_jb_rating_raw', true);
    $rounded = get_post_meta($post_id, '_jb_rating_rounded', true);
    $labels = get_option('jb_rating_labels', []);
    
    $output = '<div class="jb-rating">';
    
    // Stars
    $stars = str_repeat('⭐', $rounded);
    if ($show_raw && $raw != $rounded) {
        $output .= '<span class="jb-stars" title="Original rating: ' . esc_attr($raw) . '">' . $stars . '</span>';
    } else {
        $output .= '<span class="jb-stars">' . $stars . '</span>';
    }
    
    // Label
    if ($show_label && !empty($labels[$rounded])) {
        $output .= '<p class="jb-rating-label">' . esc_html($labels[$rounded]) . '</p>';
    }
    
    $output .= '</div>';
    
    return apply_filters('jb_rating_display', $output, $post_id, $raw, $rounded);
}
```

---

**Configuration :**
```php
'public' => true,
'show_in_rest' => true,        // Support Gutenberg
'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
'has_archive' => true,
'rewrite' => ['slug' => 'checkins'],
'menu_icon' => 'dashicons-beer',
'show_in_menu' => true,
'capability_type' => 'post',
'map_meta_cap' => true,
```

**Structure :**
- **Post Title :** `{beer_name} - {brewery_name}` (auto-slug WordPress)
- **Post Content :** Commentaire du check-in
- **Post Date :** Date réelle du check-in (importante pour tri chronologique)
- **Featured Image :** Photo de la bière (import local ou placeholder)
- **Post Name (slug) :** Auto-généré par WordPress depuis le title

### Taxonomies

**Auto-création immédiate (pas de draft) avec notification :**

```php
// beer_style - Hiérarchique (catégories) - Sert de FILTRE
'hierarchical' => true
'slug' => 'beer-style'
'show_in_rest' => true
Exemples : IPA > American IPA, Stout > Imperial Stout
Auto-création immédiate + notification admin pour review/merge

// brewery - Non-hiérarchique (tags) - Sert de FILTRE
'hierarchical' => false
'slug' => 'brewery'
'show_in_rest' => true
Auto-création immédiate + notification admin

// venue - Non-hiérarchique (tags)
'hierarchical' => false
'slug' => 'venue'
'show_in_rest' => true
Auto-création immédiate (optionnel dans settings)
```

**Gestion auto-création avec notification :**
```php
/**
 * Auto-create taxonomy terms with admin notification
 */
function jb_assign_taxonomy($post_id, $taxonomy, $term_name) {
    if (empty($term_name)) {
        return;
    }
    
    // Normalize term name
    $term_name = trim($term_name);
    
    // Check if term exists
    $term = term_exists($term_name, $taxonomy);
    
    if (!$term) {
        // Create new term immediately
        $term = wp_insert_term($term_name, $taxonomy);
        
        if (is_wp_error($term)) {
            error_log("Jardin Toasts: Failed to create term {$term_name}: " . $term->get_error_message());
            return;
        }
        
        // Log for admin notification
        $new_terms = get_option('jb_new_terms_created', []);
        $new_terms[] = [
            'taxonomy' => $taxonomy,
            'term' => $term_name,
            'term_id' => $term['term_id'],
            'created_at' => current_time('mysql'),
            'source_checkin' => $post_id,
        ];
        update_option('jb_new_terms_created', $new_terms);
        
        // Trigger admin notice (cleared after viewing)
        set_transient('jb_new_terms_notice', count($new_terms), WEEK_IN_SECONDS);
    }
    
    // Assign term to post
    wp_set_object_terms($post_id, $term_name, $taxonomy, true);
}
```

**Admin notice pour review/merge :**
```php
add_action('admin_notices', 'jb_new_terms_admin_notice');

function jb_new_terms_admin_notice() {
    $count = get_transient('jb_new_terms_notice');
    if (!$count) {
        return;
    }
    
    $terms_page_url = admin_url('edit-tags.php?taxonomy=beer_style&post_type=beer_checkin');
    
    printf(
        '<div class="notice notice-info is-dismissible">
            <p><strong>Jardin Toasts:</strong> %d new taxonomy term(s) created during import. 
            <a href="%s">Review and merge duplicates if needed</a></p>
        </div>',
        absint($count),
        esc_url($terms_page_url)
    );
}
```

### Custom Fields (Meta Keys)

**Identifiants uniques :**
```php
'_jb_checkin_id'         // string - UNIQUE (ex: 1527514863)
'_jb_beer_id'            // int (Untappd beer ID)
'_jb_brewery_id'         // int (Untappd brewery ID)
'_jb_checkin_url'        // URL du check-in original
```

**Données bière :**
```php
'_jb_beer_name'          // string
'_jb_brewery_name'       // string
'_jb_beer_style'         // string (redondant avec taxo, pour recherche)
'_jb_beer_abv'           // float (ex: 5.5)
'_jb_beer_ibu'           // int (ex: 45)
'_jb_beer_description'   // text longue (description officielle)
```

**Données check-in :**
```php
'_jb_rating'             // float (0-5, ex: 4.25) - OBLIGATOIRE
'_jb_serving_type'       // string (Draft, Bottle, Can, Cask)
'_jb_purchase_venue'     // string (si différent du lieu de dégustation)
'_jb_checkin_date'       // datetime ISO 8601
```

**Lieu de consommation :**
```php
'_jb_venue_name'         // string
'_jb_venue_city'         // string
'_jb_venue_country'      // string
'_jb_venue_lat'          // float (optionnel - pour future map)
'_jb_venue_lng'          // float (optionnel - pour future map)
```

**Données sociales :**
```php
'_jb_toast_count'        // int (nombre de likes)
'_jb_comment_count'      // int (nombre de commentaires)
'_jb_badges_earned'      // array (badges débloqués - Phase 3)
```

**Métadonnées techniques :**
```php
'_jb_source'             // string ('rss' ou 'crawler')
'_jb_scraped_at'         // datetime (dernière tentative de scraping)
'_jb_scraping_attempts'  // int (nombre de tentatives)
'_jb_incomplete_reason'  // string (pourquoi en draft)
```

---

## 3. Structure du Plugin

```
jardin-toasts/
├── jardin-toasts.php                   # Main plugin file (WordPress.org ready)
├── readme.txt                         # WordPress.org readme (required)
├── LICENSE                            # GPL-2.0-or-later (required)
├── composer.json                      # PHP Dependencies (Symfony DomCrawler)
├── package.json                       # npm dependencies (@wordpress/scripts)
├── .gitignore                         # Git exclusions
├── .wordpress-org/                    # WordPress.org assets
│   ├── banner-1544x500.png           # Repo banner
│   ├── banner-772x250.png            # Repo banner (small)
│   ├── icon-256x256.png              # Plugin icon
│   └── screenshot-1.png              # Screenshots for .org
│
├── includes/
│   ├── class-activator.php           # Plugin activation hooks
│   ├── class-deactivator.php         # Plugin deactivation
│   ├── class-post-type.php           # CPT registration
│   ├── class-taxonomies.php          # Taxonomies registration
│   ├── class-meta-fields.php         # Custom fields registration
│   ├── class-rss-parser.php          # RSS sync with SimplePie
│   ├── class-scraper.php             # HTML scraper (Symfony DomCrawler)
│   ├── class-importer.php            # Data import/processing logic
│   ├── class-image-handler.php       # Media Library management
│   ├── class-action-scheduler.php    # Action Scheduler integration
│   └── class-settings.php            # Admin settings (WordPress Settings API)
│
├── admin/
│   ├── class-admin.php               # Admin hooks & initialization
│   ├── views/
│   │   ├── settings-general.php      # Tab 1: General settings
│   │   ├── settings-import.php       # Tab 2: Import interface
│   │   ├── settings-rating.php       # Tab 3: Rating system
│   │   ├── settings-taxonomies.php   # Tab 4: Taxonomies review
│   │   ├── settings-advanced.php     # Tab 5: Advanced/Debug
│   │   └── dashboard-widget.php      # WP Dashboard widget
│   ├── assets/
│   │   ├── css/
│   │   │   └── admin.css
│   │   └── js/
│   │       ├── admin.js
│   │       └── import-progress.js    # AJAX progress bar
│   └── partials/
│       └── metabox-checkin.php       # Custom metabox for CPT
│
├── public/
│   ├── class-public.php              # Frontend hooks
│   ├── templates/                    # Default templates (overridable)
│   │   ├── archive-beer_checkin.php
│   │   ├── single-beer_checkin.php
│   │   ├── taxonomy-beer-style.php
│   │   ├── taxonomy-brewery.php
│   │   └── taxonomy-venue.php
│   ├── assets/
│   │   ├── css/
│   │   │   └── public.css
│   │   ├── js/
│   │   │   └── public.js
│   │   └── images/
│   │       └── beer-placeholder.svg  # TODO: Add placeholder
│   └── partials/
│       ├── checkin-card.php          # Reusable check-in card
│       └── rating-stars.php          # Rating display component
│
├── blocks/                            # Gutenberg blocks (React/JSX)
│   ├── src/
│   │   ├── checkins-list/
│   │   │   ├── block.json
│   │   │   ├── index.js
│   │   │   ├── edit.js               # Editor interface (React)
│   │   │   ├── save.js               # Frontend render
│   │   │   └── style.scss
│   │   ├── checkin-card/
│   │   │   ├── block.json
│   │   │   ├── index.js
│   │   │   ├── edit.js
│   │   │   ├── save.js
│   │   │   └── style.scss
│   │   └── stats-dashboard/
│   │       ├── block.json
│   │       ├── index.js
│   │       ├── edit.js
│   │       ├── save.js
│   │       └── style.scss
│   └── build/                        # Compiled blocks (generated by npm)
│       ├── checkins-list/
│       ├── checkin-card/
│       └── stats-dashboard/
│
├── languages/
│   ├── jardin-toasts.pot              # Translation template (generated)
│   ├── jardin-toasts-fr_FR.po         # French translation (optional)
│   └── jardin-toasts-fr_FR.mo         # Compiled French translation
│
└── tests/                            # Unit tests (optional but recommended)
    ├── bootstrap.php
    └── test-importer.php
```
├── includes/
│   ├── class-activator.php           # Plugin activation
│   ├── class-deactivator.php         # Plugin deactivation
│   ├── class-post-type.php           # CPT registration
│   ├── class-taxonomies.php          # Taxonomies registration
│   ├── class-meta-fields.php         # Custom fields registration
│   ├── class-rss-sync.php            # RSS synchronization
│   ├── class-crawler.php             # Historical web scraper
│   ├── class-importer.php            # Data import/processing logic
│   ├── class-image-handler.php       # Media Library management
│   ├── class-deduplicator.php        # Duplicate detection
│   └── class-settings.php            # Admin settings manager
│
├── admin/
│   ├── class-admin.php               # Admin hooks
│   ├── views/
│   │   ├── settings-page.php         # Main settings UI
│   │   ├── import-page.php           # Manual import UI
│   │   ├── logs-page.php             # Import logs viewer
│   │   └── stats-page.php            # Dashboard stats
│   ├── assets/
│   │   ├── css/
│   │   │   └── admin.css
│   │   └── js/
│   │       ├── admin.js
│   │       └── import-progress.js    # AJAX progress bar
│   └── partials/
│       └── metabox-checkin.php       # Custom metabox
│
├── public/
│   ├── class-public.php              # Frontend hooks
│   ├── templates/                    # Default templates
│   │   ├── archive-beer_checkin.php
│   │   ├── single-beer_checkin.php
│   │   ├── taxonomy-beer_style.php
│   │   ├── taxonomy-brewery.php
│   │   └── taxonomy-venue.php
│   ├── assets/
│   │   ├── css/
│   │   │   └── public.css
│   │   └── js/
│   │       └── public.js
│   └── partials/
│       ├── checkin-card.php          # Composant check-in
│       └── rating-stars.php          # Étoiles de notation
│
├── blocks/                            # Gutenberg blocks (optionnels)
│   ├── src/
│   │   ├── checkins-list/
│   │   │   ├── index.js
│   │   │   ├── edit.js
│   │   │   └── block.json
│   │   ├── checkin-card/
│   │   │   ├── index.js
│   │   │   ├── edit.js
│   │   │   └── block.json
│   │   └── stats-widget/
│   │       ├── index.js
│   │       ├── edit.js
│   │       └── block.json
│   └── build/                        # Compiled blocks
│
├── languages/
│   ├── untappd-sync.pot              # Translation template
│   ├── untappd-sync-fr_FR.po
│   └── untappd-sync-fr_FR.mo
│
└── tests/                            # Unit tests (optionnel)
    ├── bootstrap.php
    └── test-importer.php
```

---

## 4. Blocks Gutenberg (optionnels mais recommandés)

### Block 1 : `jardin-toasts/checkins-list`

**Paramètres configurables :**
- Nombre de check-ins à afficher (défaut: 12)
- Ordre d'affichage :
  - Récent (par date)
  - Meilleure note
  - Alphabétique (par bière)
- Filtres disponibles :
  - Par style de bière
  - Par brasserie
  - Par note minimale
- Layouts :
  - Grille (2, 3 ou 4 colonnes)
  - Liste verticale
  - Timeline chronologique
  - Masonry (Pinterest-like)

### Block 2 : `jardin-toasts/checkin-card`

**Éléments affichés :**
- Photo de la bière (avec fallback)
- Nom de la bière + brasserie
- Note en étoiles (visual)
- Style de bière
- ABV % / IBU
- Date du check-in
- Commentaire (tronqué, expandable)
- Lieu (si présent)
- Lien vers check-in complet

### Block 3 : `jardin-toasts/stats-dashboard`

**Statistiques calculées :**
- Total de check-ins
- Note moyenne globale
- Brasserie favorite (plus de check-ins)
- Style de bière préféré
- Bière la mieux notée
- Graphiques optionnels :
  - Evolution temporelle (Chart.js)
  - Distribution par style (camembert)
  - Top 10 brasseries (bar chart)

---

## 5. Admin Settings Page

### Onglet 1 : "Synchronisation"

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
SYNCHRONISATION AUTOMATIQUE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

☑ Activer la synchronisation automatique

Fréquence : [Quotidien ▼]
Heure d'exécution : [03:00]

URL du flux RSS Untappd :
[https://untappd.com/rss/user/USERNAME_____________]

Dernière synchronisation :
✓ 10/11/2025 à 03:14:22
   → 3 nouveaux check-ins importés

[Synchroniser maintenant]  [Voir les logs]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
NOTIFICATIONS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

☑ M'envoyer un email après chaque sync
☐ Seulement en cas d'erreur

Email de notification :
[admin@example.com__________________________]
```

### Onglet 2 : "Import Historique"

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CRAWLER MANUEL
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

URL du profil Untappd :
[https://untappd.com/user/USERNAME_____________]

Portée de l'import :
◉ Tous les check-ins
○ Limiter à : [___] pages
○ À partir de la date : [JJ/MM/AAAA]

Options :
☑ Importer les images
☑ Créer les taxonomies automatiquement
☐ Écraser les check-ins existants

Délai entre requêtes : [2] secondes
(Recommandé : 2-5 sec pour éviter le blocage)

[Démarrer l'import]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
PROGRESSION
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Statut : En cours...
[████████████░░░░░░░░░░░░░░] 45%

Check-ins traités : 127 / 282
Images importées : 114 / 127
Taxonomies créées : 18 styles, 67 brasseries

Temps écoulé : 4m 23s
Temps estimé restant : 5m 12s

[Pause] [Annuler]
```

### Onglet 3 : "Options Générales"

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
IMAGES
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

☑ Importer les images en local (Media Library)
☐ Utiliser les URLs Untappd directement (hotlink)

Taille maximale des images :
Largeur : [1200] px | Hauteur : [1200] px

☑ Générer automatiquement les thumbnails WordPress
☐ Compresser les images (requiert plugin tiers)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
DONNÉES
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

☑ Importer les données sociales (toasts, commentaires)
☑ Importer les lieux de consommation
☐ Importer les badges obtenus

Déduplication :
◉ Par ID Untappd (recommandé)
○ Par nom bière + date

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
DEBUG & LOGS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

☐ Activer le mode debug (logs détaillés)
☐ Logger les requêtes HTTP

Durée de conservation des logs : [30] jours

[Voir les logs] [Vider les logs]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
DANGER ZONE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

[Réinitialiser les paramètres]
[Supprimer tous les check-ins] ⚠️ Irréversible !
[Désinstaller complètement]
```

---

## 6. Templates Frontend (thème-agnostique)

### Design Philosophy : Database + Grid Hybrid

**Archive view (liste principale) :**
- **Desktop :** Grid 3 colonnes avec cards visuelles
- **Tablet :** Grid 2 colonnes
- **Mobile :** Liste simple 1 colonne
- **Filtres en sidebar/top :** Style, Brasserie, Note, Date
- **Search bar** pour recherche texte full
- **Toggle view :** Bouton pour switcher Grid ↔ Table view

**Grid Cards (visual mode) :**
```
┌─────────────────┐
│  [Beer Photo]   │
│                 │
│  ★★★★☆ 4.25     │
│  IPA | 6.5% ABV │
│  Brewery Name   │
│  Venue | Date   │
└─────────────────┘
```

**Table view (database mode) :**
```
Photo | Beer Name | Brewery | Style | Rating | ABV | Date | Venue
─────────────────────────────────────────────────────────────────
[img] | IPA Name  | Brewery | IPA   | 4.25★  | 6.5 | Nov 9| Home
```

**Single check-in (detailed view) :**
- Hero image grande (si disponible)
- Sidebar avec toutes les métadonnées en "data sheet"
- Commentaire complet en prose
- Navigation prev/next check-in
- Related : "Autres bières de cette brasserie"

### Système de fallback
```php
// Ordre de recherche des templates :
1. /wp-content/themes/mon-theme/untappd/archive-checkin.php
2. /wp-content/themes/mon-theme/archive-beer_checkin.php
3. /wp-content/plugins/untappd-sync/public/templates/archive-beer_checkin.php
```

### Hooks de customisation
```php
// Avant la liste de check-ins
do_action('jb_before_checkins_list');

// Après chaque check-in individuel
do_action('jb_after_checkin_card', $post_id);

// Modifier le template chargé
$template = apply_filters('jb_checkin_template', $template, $post_id);

// Modifier les classes CSS
$classes = apply_filters('jb_checkin_classes', $classes, $post_id);

// Modifier les données affichées
$checkin_data = apply_filters('jb_checkin_data', $data, $post_id);
```

### Template Tags disponibles
```php
// Récupérer toutes les métadonnées
jb_get_checkin_data($post_id);

// Afficher la note en étoiles
jb_rating_stars($rating, $echo = true);

// Afficher le style de bière
jb_beer_style($post_id, $link = true);

// Afficher la brasserie
jb_brewery_link($post_id);

// Afficher le lieu
jb_venue_info($post_id);

// Afficher l'image de la bière
jb_beer_image($post_id, $size = 'medium');
```

---

## 7. Gestion des Images

### Processus d'import
```
1. Télécharger l'image depuis URL Untappd
2. Vérifier si l'image existe déjà (hash MD5)
3. Si nouvelle :
   - wp_insert_attachment()
   - wp_generate_attachment_metadata()
   - set_post_thumbnail($checkin_id, $attachment_id)
4. Ajouter alt text : "{beer_name} - {brewery_name}"
5. Ajouter caption : "Check-in du {date}"
```

### Optimisations
- **Resize automatique :** Max 1200×1200px
- **Formats générés :** thumbnail, medium, large
- **Lazy loading :** Natif WordPress (loading="lazy")
- **Compression :** Optionnelle via plugins tiers
- **WebP :** Support automatique si disponible

### Gestion des erreurs
- Image manquante → Utiliser placeholder par défaut
- Timeout téléchargement → Retry 3× puis skip
- Format invalide → Logger et continuer

---

## 8. Performance & Optimisation

### Caching
```php
// Cache des statistiques (1 heure)
$stats = wp_cache_get('jb_global_stats', 'jardin-toasts');
if (false === $stats) {
    $stats = calculate_stats();
    wp_cache_set('jb_global_stats', $stats, 'jardin-toasts', HOUR_IN_SECONDS);
}

// Transients pour données lourdes
set_transient('jb_top_breweries', $data, DAY_IN_SECONDS);
```

### Index database
```sql
-- Index unique sur l'ID Untappd check-in
ALTER TABLE wp_postmeta 
ADD UNIQUE INDEX jb_checkin_id (meta_key, meta_value(191))
WHERE meta_key = '_jb_checkin_id';

-- Index composé pour recherches
ALTER TABLE wp_posts 
ADD INDEX post_type_date (post_type, post_date);
```

### Optimisation requêtes
```php
// Éviter les meta queries multiples
'posts_per_page' => 24,
'no_found_rows' => false,  // Nécessaire pour pagination
'update_post_term_cache' => true,
'update_post_meta_cache' => true,
```

### Lazy loading
- Images : `loading="lazy"` (natif)
- Check-ins : Pagination AJAX
- Taxonomies : Chargement à la demande

---

## 9. Sécurité

### Sanitization
```php
// Textes simples
$beer_name = sanitize_text_field($input);

// Textes riches (commentaires)
$comment = wp_kses_post($input);

// URLs
$url = esc_url_raw($input);

// Nombres
$rating = floatval($input);
$abv = abs(floatval($input));
```

### Validation
```php
// Vérifier format URL
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    return new WP_Error('invalid_url', 'URL invalide');
}

// Vérifier domaine Untappd
if (strpos($url, 'untappd.com') === false) {
    return new WP_Error('invalid_domain', 'URL doit être untappd.com');
}
```

### Nonces & Capabilities
```php
// Vérifier les permissions admin
if (!current_user_can('manage_options')) {
    wp_die('Accès refusé');
}

// Vérifier le nonce
check_ajax_referer('untappd_import_nonce', 'security');
```

### Rate Limiting
```php
// Crawler : pause entre requêtes
sleep($delay_seconds);

// Limiter tentatives import
$attempts = get_transient('untappd_import_attempts');
if ($attempts > 3) {
    return new WP_Error('rate_limit', 'Trop de tentatives');
}
```

---

## 10. Standards & Compatibilité

### Requis système
- **PHP :** 8.2+ (recommandé 8.3)
- **WordPress :** 6.7+
- **MySQL :** 5.7+ / MariaDB 10.3+
- **Extensions PHP :**
  - `curl` ou `allow_url_fopen`
  - `dom` (pour parsing HTML)
  - `json`
  - `mbstring`

### Coding Standards
- **WPCS :** WordPress Coding Standards
- **PHPStan :** Level 5 minimum
- **i18n :** Internationalisation complète
- **Accessibilité :** WCAG 2.1 AA

### Dependencies (Composer)
```json
{
  "require": {
    "guzzlehttp/guzzle": "^7.8",
    "symfony/dom-crawler": "^6.4",
    "symfony/css-selector": "^6.4"
  },
  "require-dev": {
    "phpunit/phpunit": "^10.5",
    "wp-coding-standards/wpcs": "^3.0"
  }
}
```

---

## 11. Roadmap & Évolutions

### Phase 1 : MVP (Version 1.0)
- ✅ Custom Post Type + Taxonomies + Métadonnées
- ✅ Synchronisation RSS automatique
- ✅ Crawler historique manuel
- ✅ Import images en local
- ✅ Templates frontend par défaut
- ✅ Page settings admin complète
- ✅ Logs et gestion erreurs

### Phase 2 : Fonctionnalités avancées (Version 1.5)
- □ Blocks Gutenberg (3 blocks)
- □ Statistiques avancées avec graphiques
- □ Dashboard widget WordPress
- □ Shortcodes pour anciennes versions
- □ Export CSV/JSON des check-ins
- □ Recherche/filtres frontend AJAX

### Phase 3 : Pro features (Version 2.0)
- □ API REST endpoints custom
- □ Webhooks pour sync temps réel
- □ Mode carte interactive (Google Maps)
- □ Wishlist / Bières à essayer
- □ Notes privées sur check-ins
- □ Gestion cave personnelle (cellar)
- □ Comparaison avec amis Untappd

### Phase 4 : Communauté (Version 3.0)
- □ Multi-utilisateurs (chacun son profil)
- □ Agrégation profils d'équipe
- □ Export pour autres plateformes
- □ Import depuis BeerAdvocate, RateBeer
- □ PWA support (mode hors-ligne)

---

## 12. Documentation

### Pour utilisateurs
- README.txt WordPress.org
- Guide de démarrage rapide
- FAQ complète
- Captures d'écran

### Pour développeurs
- PHPDoc sur toutes les fonctions
- Hooks & filters reference
- Template hierarchy diagram
- Code examples GitHub wiki

### Support
- Forum WordPress.org
- GitHub Issues
- Documentation site dédié (optionnel)

---

## 13. Internationalisation (i18n) & WordPress.org Preparation

### Full i18n Implementation

**Text Domain : `jardin-toasts`**

**All user-facing strings must be translatable :**
```php
// Single string
__('Beer Check-ins', 'jardin-toasts')

// String with output
_e('Import Historical Check-ins', 'jardin-toasts')

// String with context
_x('Brewery', 'taxonomy singular name', 'jardin-toasts')

// Plural forms
_n('%s check-in', '%s check-ins', $count, 'jardin-toasts')

// Escaped output
esc_html__('Rating System', 'jardin-toasts')
esc_attr__('Beer photo', 'jardin-toasts')
```

**Load text domain in main plugin file :**
```php
add_action('plugins_loaded', 'jb_load_textdomain');

function jb_load_textdomain() {
    load_plugin_textdomain(
        'jardin-toasts',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages/'
    );
}
```

**Generate .pot file for translators :**
```bash
# Using WP-CLI
wp i18n make-pot . languages/jardin-toasts.pot

# Or manually via Poedit
```

### WordPress.org Submission Checklist

**✅ Required Files :**
- `jardin-toasts.php` - Main file with standard headers
- `readme.txt` - WordPress.org format (see below)
- `LICENSE` - GPL-2.0-or-later full text

**✅ Code Requirements :**
- GPL-2.0-or-later license declaration in headers
- No minified/obfuscated code (build files must be excluded from SVN)
- Proper data sanitization and escaping
- Nonces for all forms
- Capability checks for admin actions
- No phone-home or tracking without opt-in
- Prefix all functions/classes to avoid conflicts

**✅ readme.txt Format :**
```
=== Jardin Toasts for Untappd ===
Contributors: jazon
Donate link: https://example.com/
Tags: beer, untappd, checkin, brewery, rating
Requires at least: 6.4
Tested up to: 7.0
Stable tag: 1.0.0
Requires PHP: 8.2
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Import and display your Untappd beer check-ins on your WordPress site.

== Description ==

Jardin Toasts allows you to automatically sync your Untappd check-ins to your WordPress site...

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/jardin-toasts/`
2. Activate the plugin through the 'Plugins' screen
3. Go to Jardin Toasts > Settings to configure

== Frequently Asked Questions ==

= Is this plugin official from Untappd? =
No, this is an independent plugin...

== Screenshots ==

1. Check-ins archive page
2. Single check-in view
3. Settings page
4. Rating system configuration

== Changelog ==

= 1.0.0 =
* Initial release
* RSS sync functionality
* Historical import crawler
* Rating system with custom labels
* Three Gutenberg blocks

== Upgrade Notice ==

= 1.0.0 =
Initial release.
```

**✅ Assets for WordPress.org Directory :**
- `banner-1544x500.png` - Repo header banner
- `banner-772x250.png` - Smaller banner (optional)
- `icon-256x256.png` - Plugin icon (required)
- `icon-128x128.png` - Smaller icon (optional)
- Screenshots (PNG or JPG, 1280x960 recommended)

**✅ Security Best Practices :**
```php
// Sanitize input
$untappd_url = sanitize_url($_POST['untappd_url']);

// Escape output
echo esc_html($beer_name);
echo esc_attr($beer_style);
echo esc_url($checkin_url);

// Nonce verification
check_admin_referer('jb_import_action', 'jb_import_nonce');

// Capability check
if (!current_user_can('manage_options')) {
    wp_die(__('Insufficient permissions', 'jardin-toasts'));
}

// Use WP HTTP API (not curl)
wp_remote_get($url, ['timeout' => 10]);
```

---

### Tests unitaires (PHPUnit)
```php
// Tester l'import d'un check-in
test_import_single_checkin()

// Tester la déduplication
test_duplicate_detection()

// Tester le parsing RSS
test_rss_parser()
```

### Tests d'intégration
- Import complet 100 check-ins
- Sync RSS avec mise à jour
- Gestion erreurs réseau
- Performance avec 1000+ posts

### CI/CD (optionnel)
- GitHub Actions pour tests auto
- PHPCS validation sur chaque commit
- PHPStan analyse statique

---

## 15. Notes d'implémentation

### Ordre de développement recommandé

1. **Structure de base**
   - Fichier principal + activation/désactivation
   - CPT + taxonomies + métadonnées
   - Settings page basique

2. **RSS Sync (priorité)**
   - Parser RSS Untappd
   - Importer dans CPT
   - Gestion images basique

3. **Crawler historique**
   - Scraper HTML profil
   - Pagination
   - Rate limiting

4. **Templates frontend**
   - Archive + Single
   - Taxonomies
   - Template tags

5. **Polish & optimisation**
   - Caching
   - Logs
   - Blocks Gutenberg
   - i18n

### Points d'attention

⚠️ **Scraping :** Untappd peut changer son HTML à tout moment
→ Prévoir des fallbacks et logs détaillés

⚠️ **Rate limiting :** Risque de ban IP si trop de requêtes
→ Implémenter des délais et checkpoints

⚠️ **Images :** Volumétrie importante (plusieurs centaines)
→ Option pour skip images ou compression

⚠️ **Performance :** Beaucoup de métadonnées par post
→ Index database + cache agressif

---

## Ressources utiles

- **WordPress Plugin Handbook :** https://developer.wordpress.org/plugins/
- **Symfony DomCrawler :** https://symfony.com/doc/current/components/dom_crawler.html
- **Guzzle HTTP Client :** https://docs.guzzlephp.org/
- **WordPress Coding Standards :** https://developer.wordpress.org/coding-standards/
- **PHPStan :** https://phpstan.org/

---

*Document vivant - Version 1.0 - 10/11/2025*