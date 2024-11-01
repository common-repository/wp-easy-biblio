<?php
/*
Plugin Name: WP Easy Biblio
Plugin URI: http://jlafuentec.ivore.com/wordpress-easy-biblio-plugin/
Description: WP Easy Biblio
Version: 0.0.1
Author: Jonatan Lafuente Castillo
Author URI: http://jlafuentec.ivore.com/

== Installation ==
1. Upload the 'wp-easy-biblio.php' file to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. That's it!


Copyright 2009. UOC - Jonatan Lafuente Castillo (email: jlafuentec@uoc.edu) http://www.uoc.edu/

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

global $citas;

function wpeb_parse_body($body) {
	global $citas;
	$citas = null;

	//parsing de las citas encontradas
	$reg_xpr = "|\{{cite(.*?)}}|is";
	$repl = "";  
	$body = preg_replace_callback($reg_xpr, "wpeb_parse_cite", $body);


	if (sizeof($citas)>0) {
		//anyadimos el bloque a pie de texto con el listado de las citas

//		<hr /><strong>'._e("References").'</strong>

		$c = '
		<div style="margin-top:1em">
		<ol>
		';
	//	<div style="-moz-column-count:3; -webkit-column-count:3; column-count:3; margin-top:1em">
	//inicio tabla
	//$citas = null;
		foreach ($citas as $cita){
			$a_cita = explode ("|", $cita);
			$t = null;
			foreach ($a_cita as $campos){
				$aa_cita = explode ("=", $campos);
				$key_val = trim($aa_cita[0]);
				//$key_val = strval(trim($key_val, "\x20"));
				$key_val = strval(@trim($key_val, "\xc2\xa0"));
				$t[$key_val] = trim($aa_cita[1]);
			}
			$ok_citas[] = $t;
		}

		foreach ($ok_citas as $key => $cita){
			$str = ''; //start new line
			if(array_key_exists('last', $cita) && array_key_exists('first', $cita) ) {
				$last_first_coma = ', ';
			}
			else {
				$last_first_coma = ' ';
			}
			
			if(array_key_exists('date', $cita)) {
				$par1 = ' (';
				$par2 = ')';
			}
			else {
				$par1 = ' ';
				$par2 = '';
			}
			
			$str = '<li id="noteFoot'.$key.'"><b><a href="#nbFoot'.$key.'" title="">^</a></b>'.$cita['last'].$last_first_coma.$cita['first'].$par1.$cita['date'].$par2.'. "<a href="'.$cita['url'].'">'.$cita['title'].'</a>". '.$cita['publisher'].'. Fecha acceso: '.$cita['accessdate'].'</li>'."\n";
			$c.=$str;
		}
		
		//fin del listado
		$c.= '
		</ol>
		</div>
		';
	}

	return $body.$c;
}

function wpeb_parse_cite($matches)
{
	global $citas;
  //print_r($matches);

  //inserta la cita en el array
  $citas[] = $matches[1];
  
  //devuelve el link a la cita
  $id = sizeof($citas)-1;
  return '<sup id="nbFoot'.$id.'"><a href="#noteFoot'.$id.'" title="">['.$id.']</a></sup>';  
  
  //return $matches[1].($matches[2]+1);
}


function wpeb_admin_menu() {  
	add_options_page('WP Easy Biblio Options', 'Easy Biblio', 8, __FILE__, 'wpeb_admin');
}

function wpeb_admin() {
	if($_POST['wpeb_update'] == 'Y') {
		//Form data sent
		$nofollow_login = $_POST['wpeb_nofollow_login'];
		update_option('wpeb_nofollow_login', $nofollow_login);
	?>  
    	<div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
	<?php  
	} else {
		//Normal page display
		$nofollow_login = get_option('wpeb_nofollow_login');
		
		if (empty($nofollow_login)) 	$nofollow_login = "unchecked";
	}
?>
	
	<div class="wrap">
		<h2><?php _e('WP Easy Biblio') ?></h2>
			
		<form name="wpeb_admin_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
			<input type="hidden" name="wpeb_update" value="Y">
			<table>
					<tr>
						<td colspan="2">
							<h3>Nothing</h3>
							<p>Nothing to set up here, so far.</p>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2"><p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options') ?>" /></p></td>
					</tr>
			</table>
		</form>
	</div>
<?php
}


function wpeb_install() {
	if(get_option('wpeb_installed') <> true){
		update_option('wpeb_installed', true);
	}
	//valores iniciales
	add_option('wpeb_a', 'a');
}


if (isset($_GET['activate']) && $_GET['activate'] == 'true'){
	add_action('init', 'wpeb_install');
}

if (isset($_GET['deactivate']) && $_GET['deactivate'] == 'true'){
//	set_option('wpeb_installed', false);
}

//anyadimos las acciones
add_action('admin_menu', 'wpeb_admin_menu');
add_filter('the_content', 'wpeb_parse_body', 2);

?>
