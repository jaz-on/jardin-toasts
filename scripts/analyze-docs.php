<?php
/**
 * Script d'analyse de documentation
 *
 * Parse tous les fichiers markdown dans /docs/
 * Extrait les composants documentés (classes, fonctions, hooks)
 * Identifie les dépendances entre modules
 * Génère un rapport de cohérence
 *
 * Usage: php scripts/analyze-docs.php
 */

// Vérifier que le script est exécuté depuis la racine du projet
if ( ! file_exists( __DIR__ . '/../jardin-toasts.php' ) ) {
	echo "Erreur : Ce script doit être exécuté depuis la racine du projet.\n";
	exit( 1 );
}

$docs_dir = __DIR__ . '/../docs';
$report   = [
	'components'   => [],
	'hooks'        => [],
	'functions'    => [],
	'dependencies' => [],
	'files'        => [],
];

/**
 * Parse un fichier markdown et extrait les informations
 *
 * @param string $file_path Chemin du fichier
 * @return array Informations extraites
 */
function parse_markdown_file( $file_path ) {
	$content = file_get_contents( $file_path );
	$info    = [
		'classes'     => [],
		'functions'   => [],
		'hooks'       => [],
		'dependencies' => [],
	];

	// Extraire les classes JB_*
	preg_match_all( '/`JB_([A-Za-z_]+)`/', $content, $classes );
	if ( ! empty( $classes[1] ) ) {
		$info['classes'] = array_unique( $classes[1] );
	}

	// Extraire les fonctions jb_*
	preg_match_all( '/`jb_([a-z_]+)\(\)`/', $content, $functions );
	if ( ! empty( $functions[1] ) ) {
		$info['functions'] = array_unique( $functions[1] );
	}

	// Extraire les hooks WordPress
	preg_match_all( '/`([a-z_]+)`\s*-\s*(.*)/', $content, $hooks );
	if ( ! empty( $hooks[1] ) ) {
		$info['hooks'] = array_unique( $hooks[1] );
	}

	// Extraire les dépendances (WordPress, Composer, etc.)
	if ( preg_match( '/Dependencies?[:\s]*(.*?)(?:\n\n|\Z)/is', $content, $matches ) ) {
		$info['dependencies'] = array_filter( array_map( 'trim', explode( "\n", $matches[1] ) ) );
	}

	return $info;
}

/**
 * Parcourir récursivement le dossier docs
 *
 * @param string $dir Dossier à parcourir
 * @param array  $report Rapport à remplir
 */
function scan_docs_directory( $dir, &$report ) {
	$files = glob( $dir . '/*.md' );
	$dirs  = glob( $dir . '/*', GLOB_ONLYDIR );

	foreach ( $files as $file ) {
		$relative_path = str_replace( __DIR__ . '/../', '', $file );
		$info          = parse_markdown_file( $file );

		$report['files'][ $relative_path ] = $info;

		// Agréger les composants
		foreach ( $info['classes'] as $class ) {
			if ( ! isset( $report['components'][ $class ] ) ) {
				$report['components'][ $class ] = [];
			}
			$report['components'][ $class ][] = $relative_path;
		}

		// Agréger les fonctions
		foreach ( $info['functions'] as $function ) {
			if ( ! isset( $report['functions'][ $function ] ) ) {
				$report['functions'][ $function ] = [];
			}
			$report['functions'][ $function ][] = $relative_path;
		}

		// Agréger les hooks
		foreach ( $info['hooks'] as $hook ) {
			if ( ! isset( $report['hooks'][ $hook ] ) ) {
				$report['hooks'][ $hook ] = [];
			}
			$report['hooks'][ $hook ][] = $relative_path;
		}
	}

	// Parcourir les sous-dossiers
	foreach ( $dirs as $subdir ) {
		scan_docs_directory( $subdir, $report );
	}
}

// Scanner la documentation
echo "Analyse de la documentation...\n";
scan_docs_directory( $docs_dir, $report );

// Générer le rapport
echo "\n=== RAPPORT D'ANALYSE ===\n\n";

echo "Composants documentés (" . count( $report['components'] ) . ") :\n";
foreach ( $report['components'] as $class => $files ) {
	echo "  - JB_$class (documenté dans " . count( $files ) . " fichier(s))\n";
	foreach ( $files as $file ) {
		echo "    → $file\n";
	}
}

echo "\nFonctions documentées (" . count( $report['functions'] ) . ") :\n";
foreach ( $report['functions'] as $function => $files ) {
	echo "  - jb_$function() (documenté dans " . count( $files ) . " fichier(s))\n";
}

echo "\nHooks documentés (" . count( $report['hooks'] ) . ") :\n";
foreach ( $report['hooks'] as $hook => $files ) {
	echo "  - $hook (documenté dans " . count( $files ) . " fichier(s))\n";
}

echo "\nFichiers analysés : " . count( $report['files'] ) . "\n";

// Générer un fichier JSON avec le rapport complet
$json_file = __DIR__ . '/docs-analysis-report.json';
file_put_contents( $json_file, json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
echo "\nRapport JSON généré : $json_file\n";

echo "\n=== ANALYSE TERMINÉE ===\n";

