<?php
/**
 * Documentation analysis script.
 *
 * Parses markdown under /docs/, extracts documented components (classes, functions, hooks),
 * dependencies, and writes a consistency report.
 *
 * Usage: php scripts/analyze-docs.php
 */

// Must be run from the plugin root (where jardin-toasts.php lives).
if ( ! file_exists( __DIR__ . '/../jardin-toasts.php' ) ) {
	echo "Error: run this script from the plugin root directory.\n";
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
 * Parse a markdown file and extract structured info.
 *
 * @param string $file_path Absolute path.
 * @return array Extracted data.
 */
function parse_markdown_file( $file_path ) {
	$content = file_get_contents( $file_path );
	$info    = [
		'classes'      => [],
		'functions'    => [],
		'hooks'        => [],
		'dependencies' => [],
	];

	// Classes JT_*
	preg_match_all( '/`JT_([A-Za-z_]+)`/', $content, $classes );
	if ( ! empty( $classes[1] ) ) {
		$info['classes'] = array_unique( $classes[1] );
	}

	// Functions jt_*
	preg_match_all( '/`jt_([a-z_]+)\(\)`/', $content, $functions );
	if ( ! empty( $functions[1] ) ) {
		$info['functions'] = array_unique( $functions[1] );
	}

	// WordPress hooks (heuristic).
	preg_match_all( '/`([a-z_]+)`\s*-\s*(.*)/', $content, $hooks );
	if ( ! empty( $hooks[1] ) ) {
		$info['hooks'] = array_unique( $hooks[1] );
	}

	if ( preg_match( '/Dependencies?[:\s]*(.*?)(?:\n\n|\Z)/is', $content, $matches ) ) {
		$info['dependencies'] = array_filter( array_map( 'trim', explode( "\n", $matches[1] ) ) );
	}

	return $info;
}

/**
 * Recursively scan docs/.
 *
 * @param string $dir    Directory.
 * @param array  $report Report accumulator (by reference).
 */
function scan_docs_directory( $dir, &$report ) {
	$files = glob( $dir . '/*.md' );
	$dirs  = glob( $dir . '/*', GLOB_ONLYDIR );

	foreach ( $files as $file ) {
		$relative_path = str_replace( __DIR__ . '/../', '', $file );
		$info          = parse_markdown_file( $file );

		$report['files'][ $relative_path ] = $info;

		foreach ( $info['classes'] as $class ) {
			if ( ! isset( $report['components'][ $class ] ) ) {
				$report['components'][ $class ] = [];
			}
			$report['components'][ $class ][] = $relative_path;
		}

		foreach ( $info['functions'] as $function ) {
			if ( ! isset( $report['functions'][ $function ] ) ) {
				$report['functions'][ $function ] = [];
			}
			$report['functions'][ $function ][] = $relative_path;
		}

		foreach ( $info['hooks'] as $hook ) {
			if ( ! isset( $report['hooks'][ $hook ] ) ) {
				$report['hooks'][ $hook ] = [];
			}
			$report['hooks'][ $hook ][] = $relative_path;
		}
	}

	foreach ( $dirs as $subdir ) {
		scan_docs_directory( $subdir, $report );
	}
}

echo "Scanning documentation...\n";
scan_docs_directory( $docs_dir, $report );

echo "\n=== ANALYSIS REPORT ===\n\n";

echo 'Documented components (' . count( $report['components'] ) . "):\n";
foreach ( $report['components'] as $class => $files ) {
	echo "  - JT_$class (in " . count( $files ) . " file(s))\n";
	foreach ( $files as $file ) {
		echo "    → $file\n";
	}
}

echo "\nDocumented functions (" . count( $report['functions'] ) . "):\n";
foreach ( $report['functions'] as $function => $files ) {
	echo "  - jt_$function() (in " . count( $files ) . " file(s))\n";
}

echo "\nDocumented hooks (" . count( $report['hooks'] ) . "):\n";
foreach ( $report['hooks'] as $hook => $files ) {
	echo "  - $hook (in " . count( $files ) . " file(s))\n";
}

echo "\nFiles scanned: " . count( $report['files'] ) . "\n";

$json_file = __DIR__ . '/docs-analysis-report.json';
file_put_contents( $json_file, json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
echo "\nJSON report written: $json_file\n";

echo "\n=== DONE ===\n";
