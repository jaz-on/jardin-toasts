<?php
/**
 * RSS queue helpers.
 *
 * @package JardinToasts
 */

namespace JB\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class RssQueueTest
 */
class RssQueueTest extends TestCase {

	/**
	 * Later row replaces duplicate check-in ID in existing queue.
	 *
	 * @return void
	 */
	public function test_jb_rss_queue_merge_unique_replaces_duplicate_in_queue() {
		$queue = array(
			array(
				'checkin_id' => '1',
				'beer_name'  => 'Old',
			),
		);
		$add   = array(
			array(
				'checkin_id' => '1',
				'beer_name'  => 'New',
			),
		);
		$merged = jb_rss_queue_merge_unique( $queue, $add );
		$this->assertCount( 1, $merged );
		$this->assertSame( 'New', $merged[0]['beer_name'] );
	}

	/**
	 * Appends new IDs and preserves order of first-seen queue entries.
	 *
	 * @return void
	 */
	public function test_jb_rss_queue_merge_unique_appends_new_ids() {
		$queue = array( array( 'checkin_id' => '1' ) );
		$add   = array( array( 'checkin_id' => '2' ) );
		$merged = jb_rss_queue_merge_unique( $queue, $add );
		$this->assertCount( 2, $merged );
		$this->assertSame( '1', (string) $merged[0]['checkin_id'] );
		$this->assertSame( '2', (string) $merged[1]['checkin_id'] );
	}
}
