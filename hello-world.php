<?php
/*
Plugin Name: Hello World
Version: 0.1
Description: In tribute to the famous "Hello Dolly" plugin by Matt Mullenweg comes this new plugin. And how could someone possible name a new default plugin other than "Hello World", as it's THE definition for a default example :)
Author: Bernhard Kau
Author URI: http://kau-boys.de
Plugin URI: https://github.com/2ndkauboy/hello-world
Text Domain: hello-world
Domain Path: /languages
*/

function hello_world_lyric() {
	// get the chosen lyrics files file for the user
	$lyrics_file = get_user_option( 'hello_world_lyrics', get_current_user_id() );
	$lyrics_file_path = plugin_dir_path( __FILE__ ) . 'lyrics/' . $lyrics_file;
	// check if file exsists
	if ( empty( $lyrics_file ) || ! file_exists( $lyrics_file_path ) ) {
		return false;
	}

	// These are the lyrics to show
	$lyrics = file_get_contents( $lyrics_file_path );

	// Here we split it into lines
	$lyrics = explode( "\n", $lyrics );

	// And then randomly choose a line
	return wptexturize( $lyrics[ mt_rand( 0, count( $lyrics ) - 1 ) ] );
}

// This just echoes the chosen line, we'll position it later
function hello_world_admin_notice() {

	$chosen = hello_world_lyric();

	if ( ! empty( $chosen ) ) {
		echo "<p id='hello_world'>$chosen</p>";
	}
}

// Now we set that function up to execute when the admin_notices action is called
add_action( 'admin_notices', 'hello_world_admin_notice' );

// We need some CSS to position the paragraph
function hello_world_css() {
	// This makes sure that the positioning is also good for right-to-left languages
	$x = is_rtl() ? 'left' : 'right';

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
}

add_action( 'admin_head', 'hello_world_css' );

function hello_world_menu() {
	add_options_page( 'Hello World Lyrics', 'Hello World', 'read', 'hello-world', 'hello_world_options' );
}

add_action( 'admin_menu', 'hello_world_menu' );

function hello_world_options() {

	$settings_saved = false;

	if ( isset( $_POST[ 'save' ] ) ) {
		update_user_option( get_current_user_id(), 'hello_world_lyrics', $_POST[ 'hello_world_lyrics' ] );
		$settings_saved = true;
	}

	?>

	<div class="wrap">
		<h1><?php _e( 'Hello World Lyrics', 'hello-world' ); ?></h1>
		<?php if ( $settings_saved ) : ?>
			<div id="message" class="updated fade">
				<p><strong><?php _e( 'Options saved.' ) ?></strong></p>
			</div>
		<?php endif ?>
		<h2>
			<?php _e( 'Choose the lyrics you want to be shown in the Dashboard.', 'hello-world' ) ?>
		</h2>
		<form method="post" action="">
			<div>
				<p>
					<label for="hello_world_lyrics"><?php _e( 'Available lyrics files:', 'hello-world' ) ?></label>
				</p>
				<select id="hello_world_lyrics" name="hello_world_lyrics">
					<option value="">none (hide lyrics)</option>
					<?php foreach( glob( plugin_dir_path( __FILE__ ) . 'lyrics/*.txt' ) as $lyrics_file ) : ?>
						<option><?php echo basename( $lyrics_file ) ?></option>
					<?php endforeach ?>
				</select>
			</div>
			<p class="submit">
				<input class="button-primary" name="save" type="submit" value="<?php _e( 'Save Changes' ) ?>" />
			</p>
		</form>
	</div>

<?php
}