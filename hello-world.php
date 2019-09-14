<?php
/**
 * Hello World
 *
 * @package     HelloWorld
 * @author      Bernhard Kau
 * @license     GPLv3
 *
 * @wordpress-plugin
 * Plugin Name: Hello World
 * Version: 2.0.0
 * Description: In tribute to the famous "Hello Dolly" plugin by Matt Mullenweg comes this new plugin. And how could someone possible name a new default plugin other than "Hello World", as it's THE definition for a default example :)
 * Author: Bernhard Kau
 * Author URI: http://kau-boys.de
 * Plugin URI: https://github.com/2ndkauboy/hello-world
 * Text Domain: hello-world
 * Domain Path: /languages
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 */

/**
 * Get the random lyric
 *
 * @return string
 */
function hello_world_lyric() {
	// Get the chosen lyrics files file for the user.
	$lyrics_file = get_user_option( 'hello_world_lyrics', get_current_user_id() );
	// Check if file exists.
	if ( empty( $lyrics_file ) || ! file_exists( $lyrics_file ) ) {
		return false;
	}

	// These are the lyrics to show.
	$lyrics = file_get_contents( $lyrics_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	// Here we split it into lines.
	$lyrics = explode( "\n", $lyrics );

	// And then randomly choose a line.
	return wptexturize( $lyrics[ wp_rand( 0, count( $lyrics ) - 1 ) ] );
}

/**
 * This just echoes the chosen line, we'll position it later
 */
function hello_world_admin_notice() {
	$chosen = hello_world_lyric();

	if ( ! empty( $chosen ) ) {
		echo '<p id="hello_world">' . esc_html( $chosen ) . '</p>';
	}
}
add_action( 'admin_notices', 'hello_world_admin_notice' );

/**
 * We need some CSS to position the paragraph
 */
function hello_world_css() {
	// This makes sure that the positioning is also good for right-to-left languages.
	$x = is_rtl() ? 'left' : 'right';

	// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
	echo "
	<style type='text/css'>
	#hello_world {
		float: $x;
		padding-$x: 15px;
		padding-top: 5px;
		margin: 0;
		font-size: 11px;
	}
	</style>
	";
	// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'admin_head', 'hello_world_css' );

/**
 * Add the settings page to the menu
 */
function hello_world_menu() {
	add_options_page( __( 'Hello World Lyrics', 'hello-world' ), __( 'Hello World', 'hello-world' ), 'read', 'hello-world', 'hello_world_options' );
}

add_action( 'admin_menu', 'hello_world_menu' );

/**
 * The plugin options page
 */
function hello_world_options() {
	$settings_saved = false;

	if ( isset( $_POST['save'] ) && check_admin_referer( 'hello_world_options', 'hello_world_options_nonce' ) ) {
		$chosen_lyric = isset( $_POST['hello_world_lyrics'] ) ? sanitize_text_field( wp_unslash( $_POST['hello_world_lyrics'] ) ) : '';
		update_user_option( get_current_user_id(), 'hello_world_lyrics', $chosen_lyric );
		$settings_saved = true;
	}

	$current_lyric = get_user_option( 'hello_world_lyrics', get_current_user_id() );

	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Hello World Lyrics', 'hello-world' ); ?></h1>
		<?php if ( $settings_saved ) : ?>
			<div id="message" class="updated fade">
				<p><strong><?php echo esc_html__( 'Options saved.', 'hello-world' ); ?></strong></p>
			</div>
		<?php endif ?>
		<h2>
			<?php echo esc_html__( 'Choose the lyrics you want to be shown in the Dashboard.', 'hello-world' ); ?>
		</h2>
		<form method="post" action="">
			<div>
				<?php wp_nonce_field( 'hello_world_options', 'hello_world_options_nonce' ); ?>
				<p>
					<label for="hello_world_lyrics"><?php echo esc_html__( 'Available lyrics files:', 'hello-world' ); ?></label>
				</p>
				<select id="hello_world_lyrics" name="hello_world_lyrics">
					<option value=""><?php echo esc_html__( 'none (hide lyrics)', 'hello-world' ); ?></option>
					<?php foreach ( hello_world_get_available_lyrics() as $lyrics_file ) : ?>
						<option value="<?php echo esc_attr( $lyrics_file ); ?>" <?php selected( $lyrics_file, $current_lyric ); ?>>
							<?php echo esc_html( basename( $lyrics_file ) ); ?>
						</option>
					<?php endforeach ?>
				</select>
			</div>
			<p class="submit">
				<input class="button-primary" name="save" type="submit" value="<?php echo esc_html__( 'Save Changes', 'hello-world' ); ?>"/>
			</p>
		</form>
	</div>
	<?php
}

/**
 * Load the paths of all available lyrics
 *
 * @return array
 */
function hello_world_get_available_lyrics() {
	// Load lyrics bundles with the plugin.
	$plugin_lyrics = glob( plugin_dir_path( __FILE__ ) . 'lyrics/*.txt' );
	// Load lyrics from the uploads dir.
	$upload_dir    = wp_get_upload_dir();
	$custom_lyrics = glob( $upload_dir['basedir'] . '/hello-world-lyrics/*.txt' );

	return array_merge( $plugin_lyrics, $custom_lyrics );
}
