<?php
/*
Plugin Name: Minecraft Server Status
Plugin URI: http://franga2000.com
Description: A simple Minecraft Server Status widget for your Wordpress website
Version: 1.3
Author: franga2000, Flashacker13
Author URI: http://franga2000.com/
License: GPLv2 or later.
*/

add_filter('plugin_action_links', 'MCServerStatus_plugin_action_links', 10, 2);

function MCServerStatus_plugin_action_links($links, $file) {
    static $this_plugin;

    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }

    if ($file == $this_plugin) {
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/widgets.php">Settings</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}

class MCServerStatus extends WP_Widget {
	function __construct() {
		parent::__construct(false, $name = __('Minecraft Server Status'));
	}
	
	function form() {
		if (isset($_POST['submitted'])) {
			update_option('MCServerStatus_widget_title', $_POST['widgettitle']);
			update_option('MCServerStatus_widget_server', $_POST['server']);
			update_option('MCServerStatus_widget_port', $_POST['port']);
			update_option('MCServerStatus_widget_head-size', $_POST['head-size']);
			update_option('MCServerStatus_widget_pl', $_POST['pl']);
		}
?>
		<label for="widgettitle">Widget Title:</label><br/>
		<input type="text" class="widefat" name="widgettitle" value="<?php echo stripslashes(get_option('MCServerStatus_widget_title')); ?>" placeholder="Server status" required/>
		<br/><br/>
		
		<label for="server">Server Address:</label><br/>
		<input type="text" class="widefat" name="server" value="<?php echo stripslashes(get_option('MCServerStatus_widget_server')); ?>" placeholder="mc.server.tld" required/>
		<br/><br/>
		
		<label for="port">Server Port:</label><br/>
		<input type="number" class="widefat" name="port" value="<?php echo stripslashes(get_option('MCServerStatus_widget_port')); ?>" placeholder="25565" required/>
		<br/><br/>
		
		<label for="pl">Player list behavior:</label><br/>
		<select name="pl">
			<option value="pl-collapsed" <?php if (get_option('MCServerStatus_widget_pl') == "pl-collapsed") echo 'selected'; ?>>Collapsed by default</option>
			<option value="pl-expanded" <?php if (get_option('MCServerStatus_widget_pl') == "pl-expanded") echo 'selected'; ?>>Expanded by default</option>
		</select>
		<br/><br/>
		
		<label for="head-size">Head size:</label><br/>
		<input type="number" class="widefat" name="head-size" value="<?php echo stripslashes(get_option('MCServerStatus_widget_head-size')); ?>" placeholder="20" required/>
		<br/><br/>
		
		<input type="hidden" name="submitted" value="1" />
		<?php
	}
	
	function update() {
		
	}
	
	function widget($args, $instance) {
		$players = Array();
		require __DIR__ . '/MinecraftQuery.class.php';
		
		$Query = new MinecraftQuery();
		try {
			$Query->Connect(get_option('MCServerStatus_widget_server'), get_option('MCServerStatus_widget_port', "25565"));
			$info = $Query->GetInfo();
			$online = true;
		} catch (MinecraftQueryException $e){
			$online = false;}
		?>
			<div class="widget MCServerStatus">
				<h3 class="widget-title widget_primary_title"><?php echo get_option('MCServerStatus_widget_title'); ?></h3>
				<b>IP: </b><?php echo get_option('MCServerStatus_widget_server'); ?><br/>
				<b>Port: </b><?php echo get_option('MCServerStatus_widget_port'); ?><br/>
                <?php
				if ($online){
                ?>
                <img src="<?php echo plugins_url('img/online-icon.png', __FILE__);?>"><p style="color:green; display:inline;"><?php echo 'ONLINE'; ?></p><br>
                <?php
                }
                else{
				?>
                <img src="<?php echo plugins_url('img/offline-icon.png', __FILE__);?>"><p style="color:red; display:inline;"><?php echo 'OFFLINE'; ?></p><br>
                <?php
				}
                ?>
				<span id="players-toggle" title="Click to toggle">Players:</span>
				<ul id="players" <?php if(get_option('MCServerStatus_widget_pl') == "pl-collapsed") echo 'style="display:none;"'; ?>>
					<?php
					if( ( $Players = $Query->GetPlayers( ) ) == false ){
					echo "No Players Online!";
					}
					else {
					foreach($Query->GetPlayers() as $key => $player) {
						echo '<li class="player"><img src="http://cravatar.eu/helmavatar/' . $player . '/' . get_option("MCServerStatus_widget_head-size", "20") . '.png"> ' . $player . '</li>';
					}
					}
					?>
				</ul>
			</div>
			<script>
				document.getElementById("players-toggle").onclick = function() {
					var element = document.getElementById("players");
					if (element.style.display == "none") {
						element.style.display = "";
					} else {
						element.style.display = "none";
					}
				};
			</script>
			<style>
				#players li {
					list-style-type: none !important; /* I used !important because some free themes hard-code styles at the end and this doesn't work. I know it's bad practice but I had no choice */
				}
				
				#players-toggle {
					cursor: pointer;
					text-decoration: underline;
				}
			</style>
		<?php
	}
}

add_action('widgets_init', 'register_MCServerStatus');

function register_MCServerStatus() {
	register_widget('MCServerStatus');
}

//add_filter ('pre_set_site_transient_update_plugins', 'display_transient_update_plugins');
?>
