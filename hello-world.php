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
	/** These are the lyrics to Hello World */
	$lyrics = file_get_contents( plugin_dir_path( __FILE__ ) . '/lyrics/hello-world.txt' );

	// Here we split it into lines
	$lyrics = explode( "\n", $lyrics );

	// And then randomly choose a line
	return wptexturize( $lyrics[ mt_rand( 0, count( $lyrics ) - 1 ) ] );
}

// This just echoes the chosen line, we'll position it later
function hello_world_admin_notice() {
	$chosen = hello_world_lyric();
	echo "<p id='hello_world'>$chosen</p>";
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