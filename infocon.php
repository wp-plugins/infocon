<?php
/*
Plugin Name: InfoCON Widget
PLugin URI: http://www.mutube.com/projects/wordpress/infocon-widget/?utm_source=plugin&utm_medium=admin
Description: Adds the <a href="http://http://isc.sans.org/infocon.php">InfoCON</a> internet stability monitor to your blog.
Author: Martin Fitzpatrick
Version: 2.0
Author URI: http://www.mutube.com?utm_source=plugin&utm_medium=admin
*/

//Lots of the thanks to the default Google search widget which this
//plugin was heavily built on.

/*  Copyright 2006  MARTIN FITZPATRICK  (email : martin.fitzpatrick@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

		if(!function_exists('get_directoryurl') ){
		function get_directoryurl($directory)
		{
			$start=strlen($_SERVER['DOCUMENT_ROOT']);
			$length=strrpos($directory,'/')-$start;
			return substr($directory,$start,$length);
		}}



function infocon_icon() {

                $options = get_option('widget_infocon');

                if ((($options['last-updated']+900)<time()) || ($options['last-updated']=='') || true)
                {
                   $text="";
                   $handle = @fopen("http://isc.sans.org/infocon.txt", "r");
                   if ($handle) {
                      while (!feof($handle)) {
                         $text.= fgets($handle, 4096);
                      }
                      fclose($handle);
                   }

                   $infocon_status=trim($text);

                   $options['last-status']=$infocon_status;
                   $options['last-updated']=time();
                   update_option('widget_infocon', $options);

                } else {$infocon_status=$options['last-status'];}

                switch ($infocon_status){
                    case 'green': $img='infocon_green.png';
                          break;
                    case 'yellow': $img='infocon_yellow.png';
                          break;
                    case 'orange': $img='infocon_orange.png';
                          break;
                    case 'red': $img='infocon_ref.png';
                          break;
                    default: $img='infocon_unknown.png';
                }

                $img= get_directoryurl(__FILE__) . "/images/" . $img;

                echo('<a href="http://isc.sans.org/infocon.php"><img src="' . $img . '" style="width:180px;height:30px;border:0px;" title="Updated ' . floor((time()-$options['last-updated'])/60) . ' minutes ago."/></a>');
				?>
<p style="text-align:right"><small>Powered by <a href="http://www.mutube.com/projects/wordpress/infocon/?utm_source=plugin&utm_medium=sidebar">Infocon</a></small></p>
				<?php
		}

// Put functions into one big function we'll call at the plugins_loaded
// action. This ensures that all required plugin functions are defined.
function widget_infocon_init() {

	// Check for the required plugin functions. This will prevent fatal
	// errors occurring when you deactivate the dynamic-sidebar plugin.
	if ( !function_exists('register_sidebar_widget') )
		return;


	// This is the function that outputs InfoCON status.
	function widget_infocon($args) {

		// $args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys. Default tags: li and h2.
		extract($args);

		// Each widget can store its own options. We keep strings here.
		$options = get_option('widget_infocon');
		$title = $options['title'];

		// These lines generate our output. Widgets can be very complex
		// but as you can see here, they can also be very, very simple.
		echo $before_widget . $before_title . $title . $after_title;
                infocon_icon(); //main call to get infocon icon
		echo $after_widget;
	}

	// Admin panel widget configuration
	function widget_infocon_control() {

		// Get our options and see if we're handling a form submission.
		$options = get_option('widget_infocon');
		if ( !is_array($options) )
			$options = array('title'=>'InfoCON');
		if ( $_POST['infocon-submit'] ) {

			// Remember to sanitize and format use input appropriately.
			$options['title'] = strip_tags(stripslashes($_POST['infocon-title']));
			update_option('widget_infocon', $options);
		}

		// Be sure you format your options to be valid HTML attributes.
		$title = htmlspecialchars($options['title'], ENT_QUOTES);

		// Here is our little form segment. Notice that we don't need a
		// complete form. This will be embedded into the existing form.
		echo '<p style="text-align:right;"><label for="infocon-title">Title: <input style="width: 200px;" id="infocon-title" name="infocon-title" type="text" value="'.$title.'" /></label></p>';
		echo '<input type="hidden" id="infocon-submit" name="infocon-submit" value="1" />';
	}

	// This registers our widget so it appears with the other available
	// widgets and can be dragged and dropped into any active sidebars.
	register_sidebar_widget('InfoCON Widget', 'widget_infocon');

	// This registers our optional widget control form. Because of this
	// our widget will have a button that reveals a 300x100 pixel form.
	register_widget_control('InfoCON Widget', 'widget_infocon_control', 300, 100);
}

// Run our code later in case this loads prior to any required plugins.
add_action('plugins_loaded', 'widget_infocon_init');

?>
