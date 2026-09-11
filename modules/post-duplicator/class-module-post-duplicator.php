<?php
/**
 * Post Duplicator extension.
 *
 * Adds a "Duplicate" row action to every duplicable post type.
 *
 * @package Xoom_Addons\Modules
 */

namespace Xoom_Addons\Modules\Post_Duplicator;

use Xoom_Addons\Abstracts\Base_Module;

defined( 'ABSPATH' ) || exit;

/**
 * Clones posts, pages and Elementor templates.
 */
class Module_Post_Duplicator extends Base_Module {

	/**
	 * Query argument that tells the handler which post to clone.
	 */
	const ACTION = 'xoom_duplicate_post';

	/**
	 * Nonce action prefix.
	 */
	const NONCE = 'xoom_duplicate_post_';

	/**
	 * Register the extension hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'post_row_actions', array( $this, 'row_action' ), 10, 2 );
		add_filter( 'page_row_actions', array( $this, 'row_action' ), 10, 2 );
		add_action( 'admin_action_' . self::ACTION, array( $this, 'duplicate' ) );
	}

	/**
	 * Add the duplicate link to a post row.
	 *
	 * @param array    $actions Existing row actions.
	 * @param \WP_Post $post    Post being listed.
	 * @return array
	 */
	public function row_action( $actions, $post ) {
		if ( ! $post instanceof \WP_Post ) {
			return $actions;
		}

		if ( ! $this->can_duplicate( $post ) ) {
			return $actions;
		}

		$url = wp_nonce_url(
			admin_url( 'admin.php?action=' . self::ACTION . '&post=' . $post->ID ),
			self::NONCE . $post->ID
		);

		$actions['xoom_duplicate'] = sprintf(
			'<a href="%1$s" aria-label="%2$s">%3$s</a>',
			esc_url( $url ),
			esc_attr(
				sprintf(
					/* translators: %s: post title. */
					__( 'Duplicate “%s”', 'xoom-addons-for-elementor' ),
					get_the_title( $post )
				)
			),
			esc_html__( 'Duplicate', 'xoom-addons-for-elementor' )
		);

		return $actions;
	}

	/**
	 * Whether the current user may clone the given post.
	 *
	 * @param \WP_Post $post Post to clone.
	 * @return bool
	 */
	private function can_duplicate( $post ) {
		$post_type = get_post_type_object( $post->post_type );

		if ( ! $post_type || empty( $post_type->public ) ) {
			return false;
		}

		return current_user_can( 'edit_post', $post->ID )
			&& current_user_can( $post_type->cap->create_posts );
	}

	/**
	 * Handle the duplicate request.
	 *
	 * @return void
	 */
	public function duplicate() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- verified with check_admin_referer() below.
		$post_id = isset( $_GET['post'] ) ? absint( wp_unslash( $_GET['post'] ) ) : 0;

		check_admin_referer( self::NONCE . $post_id );

		$post = get_post( $post_id );

		if ( ! $post instanceof \WP_Post ) {
			wp_die( esc_html__( 'The item you are trying to duplicate no longer exists.', 'xoom-addons-for-elementor' ), 404 );
		}

		if ( ! $this->can_duplicate( $post ) ) {
			wp_die( esc_html__( 'You are not allowed to duplicate this item.', 'xoom-addons-for-elementor' ), 403 );
		}

		$new_id = wp_insert_post(
			array(
				'post_title'     => sprintf(
					/* translators: %s: original post title. */
					__( '%s (Copy)', 'xoom-addons-for-elementor' ),
					$post->post_title
				),
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_status'    => 'draft',
				'post_type'      => $post->post_type,
				'post_author'    => get_current_user_id(),
				'menu_order'     => $post->menu_order,
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
			),
			true
		);

		if ( is_wp_error( $new_id ) ) {
			wp_die(
				esc_html( $new_id->get_error_message() ),
				esc_html__( 'Duplicate failed', 'xoom-addons-for-elementor' ),
				array( 'back_link' => true )
			);
		}

		$this->copy_meta( $post_id, $new_id );
		$this->copy_terms( $post, $new_id );

		wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $new_id ) );
		exit;
	}

	/**
	 * Copy post meta, skipping the fields WordPress regenerates.
	 *
	 * @param int $source_id Source post id.
	 * @param int $target_id New post id.
	 * @return void
	 */
	private function copy_meta( $source_id, $target_id ) {
		$meta = get_post_meta( $source_id );
		$skip = array( '_edit_lock', '_edit_last' );

		if ( ! is_array( $meta ) ) {
			return;
		}

		foreach ( $meta as $key => $values ) {
			if ( in_array( $key, $skip, true ) ) {
				continue;
			}

			foreach ( (array) $values as $value ) {
				add_post_meta( $target_id, $key, maybe_unserialize( $value ) );
			}
		}
	}

	/**
	 * Copy the source post's taxonomy assignments.
	 *
	 * @param \WP_Post $post     Source post.
	 * @param int      $target_id New post id.
	 * @return void
	 */
	private function copy_terms( $post, $target_id ) {
		$taxonomies = get_object_taxonomies( $post->post_type );

		foreach ( $taxonomies as $taxonomy ) {
			$terms = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );

			if ( is_wp_error( $terms ) || empty( $terms ) ) {
				continue;
			}

			wp_set_object_terms( $target_id, $terms, $taxonomy );
		}
	}
}
