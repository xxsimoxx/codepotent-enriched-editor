<?php
/**
 * This file is part of the Enriched Editor ClassicPress plugin and is released under the same license.
 * For more information please see codepotent-enriched-editor.php.
 *
 * Copyright (c) 2007-2016 Andrew Ozz. All rights reserved.
 */

if ( ! defined( 'TADV_ADMIN_PAGE' ) ) {
	exit;
}

// TODO
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Access denied' );
}

$message = '';

$this->set_paths();
$imgpath = WYSIWYG_URL . 'images/';
$tadv_options_updated = false;
$settings = $admin_settings = array();



if ( isset( $_POST['tadv-save'] ) ) {
	check_admin_referer( 'tadv-save-buttons-order' );
	$this->save_settings();
} elseif ( isset( $_POST['tadv-restore-defaults'] ) ) {
	check_admin_referer( 'tadv-save-buttons-order' );

	// TODO: only for admin || SA
	$this->admin_settings = $this->get_default_admin_settings();
	update_option( 'tadv_admin_settings', $this->get_default_admin_settings() );

	// TODO: all users that can have settings
	$this->user_settings = $this->get_default_user_settings();
	update_option( 'tadv_settings', $this->get_default_user_settings() );

	$message = '<div class="updated notice notice-success is-dismissible"><p>' .  __( 'Default settings restored.', 'codepotent-enriched-editor' ) . '</p></div>';
} elseif ( isset( $_POST['tadv-export-settings'] ) ) {
	check_admin_referer( 'tadv-save-buttons-order' );

	$this->load_settings();
	$output = array( 'settings' => $this->user_settings );

	// TODO: only admin || SA
	$output['admin_settings'] = $this->admin_settings;

	?>
	<div class="wrap">
	<h2><?php esc_html_e( 'Enriched Editor Settings Export', 'codepotent-enriched-editor' ); ?></h2>

	<div class="tadv-import-export">
	<p>
	<?php esc_html_e( 'The settings are exported as a JSON encoded string.', 'codepotent-enriched-editor' ); ?>
	<?php esc_html_e( 'Please copy the content and save it in a <b>text</b> (.txt) file, using a plain text editor like Notepad.', 'codepotent-enriched-editor' ); ?>
	<?php esc_html_e( 'It is important that the export is not changed in any way, no spaces, line breaks, etc.', 'codepotent-enriched-editor' ); ?>
	</p>

	<form action="">
		<p><textarea readonly="readonly" id="tadv-export"><?php echo wp_json_encode( $output ); ?></textarea></p>
		<p><button type="button" class="button" id="tadv-export-select"><?php esc_html_e( 'Select All', 'codepotent-enriched-editor' ); ?></button></p>
	</form>
	<p><a href=""><?php esc_html_e( 'Back to Editor Settings', 'codepotent-enriched-editor' ); ?></a></p>
	</div>
	</div>
	<?php

	return;
} elseif ( isset( $_POST['tadv-import-settings'] ) ) {
	check_admin_referer( 'tadv-save-buttons-order' );

	// TODO: all users
	?>
	<div class="wrap">
	<h2><?php esc_html_e( 'Enriched Editor Settings Import', 'codepotent-enriched-editor' ); ?></h2>

	<div class="tadv-import-export">
	<p><?php esc_html_e( 'The settings are imported from a JSON encoded string. Please paste the exported string in the text area below.', 'codepotent-enriched-editor' );	?></p>

	<form action="" method="post">
		<p><textarea id="tadv-import" name="tadv-import"></textarea></p>
		<p>
			<button type="button" class="button" id="tadv-import-verify"><?php esc_html_e( 'Verify', 'codepotent-enriched-editor' ); ?></button>
			<input type="submit" class="button button-primary alignright" name="tadv-import-submit" value="<?php esc_html_e( 'Import', 'codepotent-enriched-editor' ); ?>" />
		</p>
		<?php wp_nonce_field( 'tadv-import' ); ?>
		<p id="tadv-import-error"></p>
	</form>
	<p><a href=""><?php esc_html_e( 'Back to Editor Settings', 'codepotent-enriched-editor' ); ?></a></p>
	</div>
	</div>
	<?php

	return;
} elseif ( isset( $_POST['tadv-import-submit'] ) && ! empty( $_POST['tadv-import'] ) && is_string( $_POST['tadv-import'] ) ) {
	check_admin_referer( 'tadv-import' );

	// TODO: all users
	$import = json_decode( trim( wp_unslash( $_POST['tadv-import'] ) ), true ); // phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	if ( ! is_array( $import ) ) {
		$message = '<div class="error"><p>' .  __( 'Importing of settings failed.', 'codepotent-enriched-editor' ) . '</p></div>';
	} else {
		$this->save_settings( $import );
	}
}

if ( empty( $_POST ) ) {
	$this->check_plugin_version();
}

$this->load_settings();

if ( empty( $this->toolbar_1 ) && empty( $this->toolbar_2 ) && empty( $this->toolbar_3 ) && empty( $this->toolbar_4 ) ) {
	$message = '<div class="error"><p>' . esc_html__( 'ERROR: All toolbars are empty. Default settings loaded.', 'codepotent-enriched-editor' ) . '</p></div>';

	$this->admin_settings = $this->get_default_admin_settings();
	$this->user_settings = $this->get_default_user_settings();
	$this->load_settings();
}

$all_buttons = $this->get_all_buttons();

?>
<div class="wrap" id="contain">
<h2><?php esc_html_e( 'Enriched Editor Settings', 'codepotent-enriched-editor' ); ?></h2>
<?php

// TODO admin || SA
$this->warn_if_unsupported();

if ( isset( $_POST['tadv-save'] ) && empty( $message ) ) {
	?><div class="updated notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'codepotent-enriched-editor' ); ?></p></div><?php
} else {
	// Sanitized some lines before.
	echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

?>
<form id="tadvadmin" method="post" action="">

<p class="tadv-submit">
	<input class="button-primary button-large" type="submit" name="tadv-save" value="<?php esc_html_e( 'Save Changes', 'codepotent-enriched-editor' ); ?>" />
</p>

<div id="tadvzones">

<p><label>
<input type="checkbox" name="options[]" id="menubar" value="menubar" <?php if ( $this->check_user_setting( 'menubar' ) ) { echo ' checked="checked"'; } ?>>
<?php esc_html_e( 'Enable the editor menu.', 'codepotent-enriched-editor' ); ?>
</label></p>

<div id="tadv-mce-menu" class="mce-container mce-menubar mce-toolbar mce-first mce-stack-layout-item
	<?php if ( $this->check_user_setting( 'menubar' ) ) { echo ' enabled'; } ?>">
	<div class="mce-container-body mce-flow-layout">
		<div class="mce-widget mce-btn mce-menubtn mce-first mce-flow-layout-item">
			<button type="button">
				<span class="tadv-translate">File</span>
				<i class="mce-caret"></i>
			</button>
		</div>
		<div class="mce-widget mce-btn mce-menubtn mce-flow-layout-item">
			<button type="button">
				<span class="tadv-translate">Edit</span>
				<i class="mce-caret"></i>
			</button>
		</div>
		<div class="mce-widget mce-btn mce-menubtn mce-flow-layout-item">
			<button type="button">
				<span class="tadv-translate">Insert</span>
				<i class="mce-caret"></i>
			</button>
		</div>
		<div class="mce-widget mce-btn mce-menubtn mce-flow-layout-item mce-toolbar-item">
			<button type="button">
				<span class="tadv-translate">View</span>
				<i class="mce-caret"></i>
			</button>
		</div>
		<div class="mce-widget mce-btn mce-menubtn mce-flow-layout-item">
			<button type="button">
				<span class="tadv-translate">Format</span>
				<i class="mce-caret"></i>
			</button>
		</div>
		<div class="mce-widget mce-btn mce-menubtn mce-flow-layout-item">
			<button type="button">
				<span class="tadv-translate">Table</span>
				<i class="mce-caret"></i>
			</button>
		</div>
		<div class="mce-widget mce-btn mce-menubtn mce-last mce-flow-layout-item">
			<button type="button">
				<span class="tadv-translate">Tools</span>
				<i class="mce-caret"></i>
			</button>
		</div>
	</div>
</div>

<?php

$mce_text_buttons = array( 'styleselect', 'formatselect', 'fontselect', 'fontsizeselect' );

for ( $i = 1; $i < 5; $i++ ) {
	$toolbar = "toolbar_$i";

	?>
	<div class="tadvdropzone mce-toolbar">
	<ul id="toolbar_<?php echo esc_html($i); ?>" class="container">
	<?php

	foreach( $this->$toolbar as $button ) {
		if ( strpos( $button, 'separator' ) !== false || in_array( $button, array( 'moveforward', 'movebackward', 'absolute' ) ) ) {
			continue;
		}

		if ( isset( $all_buttons[$button] ) ) {
			$name = $all_buttons[$button];
			unset( $all_buttons[$button] );
		} else {
			// error?..
			continue;
		}

		?><li class="tadvmodule" id="<?php echo esc_html($button); ?>">
			<?php

			if ( in_array( $button, $mce_text_buttons, true ) ) {
				?>
				<div class="tadvitem mce-widget mce-btn mce-menubtn mce-fixed-width mce-listbox">
					<div class="the-button">
						<span class="descr"><?php echo esc_html($name); ?></span>
						<i class="mce-caret"></i>
						<input type="hidden" class="tadv-button" name="toolbar_<?php echo esc_html($i); ?>[]" value="<?php echo esc_html($button); ?>" />
					</div>
				</div>
				<?php
			} else {
				?>
				<div class="tadvitem">
					<i class="mce-ico mce-i-<?php echo esc_html($button); ?>" title="<?php echo esc_html($name); ?>"></i>
					<span class="descr"><?php echo esc_html($name); ?></span>
					<input type="hidden" class="tadv-button" name="toolbar_<?php echo esc_html($i); ?>[]" value="<?php echo esc_html($button); ?>" />
				</div>
				<?php
			}

			?>
		</li><?php

	}

	?>
	</ul></div>
	<?php
}

?>
</div>

<p><?php esc_html_e( 'Drag buttons from the unused buttons below and drop them in the toolbars above, or drag the buttons in the toolbars to rearrange them.', 'codepotent-enriched-editor' ); ?></p>

<div id="unuseddiv">
<h3><?php esc_html_e( 'Unused Buttons', 'codepotent-enriched-editor' ); ?></h3>
<ul id="unused" class="container">
<?php

foreach( $all_buttons as $button => $name ) {
	if ( strpos( $button, 'separator' ) !== false ) {
		continue;
	}

	?><li class="tadvmodule" id="<?php echo esc_html($button); ?>">
		<?php

		if ( in_array( $button, $mce_text_buttons, true ) ) {
			?>
			<div class="tadvitem mce-widget mce-btn mce-menubtn mce-fixed-width mce-listbox">
				<div class="the-button">
					<span class="descr"><?php echo esc_html($name); ?></span>
					<i class="mce-caret"></i>
					<input type="hidden" class="tadv-button" name="unused[]" value="<?php echo esc_html($button); ?>" />
				</div>
			</div>
			<?php
		} else {
			?>
			<div class="tadvitem">
				<i class="mce-ico mce-i-<?php echo esc_html($button); ?>" title="<?php echo esc_html($name); ?>"></i>
				<span class="descr"><?php echo esc_html($name); ?></span>
				<input type="hidden" class="tadv-button" name="unused[]" value="<?php echo esc_html($button); ?>" />
			</div>
			<?php
		}

		?>
	</li><?php

}

?>
</ul>
</div>

<div class="advanced-options">
	<h3><?php esc_html_e( 'Options', 'codepotent-enriched-editor' ); ?></h3>
	<div>
		<label><input type="checkbox" name="options[]" value="advlist" id="advlist" <?php if ( $this->check_user_setting( 'advlist' ) ) echo ' checked="checked"'; ?> />
		<?php esc_html_e( 'Enhanced Lists', 'codepotent-enriched-editor' ); ?></label>
		<p>
			<?php esc_html_e( 'Enable advanced list options, including upper-case, lower-case, disk, square, circle, etc.', 'codepotent-enriched-editor' ); ?>
		</p>
	</div>
	<div>
		<label><input type="checkbox" name="options[]" value="contextmenu" id="contextmenu" <?php if ( $this->check_user_setting( 'contextmenu' ) ) echo ' checked="checked"'; ?> />
		<?php esc_html_e( 'Right-Click', 'codepotent-enriched-editor' ); ?></label>
		<p>
			<?php esc_html_e( 'Enable link and table insertion by right-clicking the editor.', 'codepotent-enriched-editor' ); ?>
		</p>
	</div>
	<div>
		<label><input type="checkbox" name="options[]" value="advlink" id="advlink" <?php if ( $this->check_user_setting( 'advlink' ) ) echo ' checked="checked"'; ?> />
		<?php esc_html_e( 'Simplified Links', 'codepotent-enriched-editor' ); ?></label>
		<p>
			<?php esc_html_e( 'Use a simplified dialog for creating links.', 'codepotent-enriched-editor' ); ?>
		</p>
	</div>
	<div>
		<label><input type="checkbox" name="options[]" value="fontsize_formats" id="fontsize_formats" <?php if ( $this->check_user_setting( 'fontsize_formats' ) ) echo ' checked="checked"'; ?> />
		<?php esc_html_e( 'Font Sizing', 'codepotent-enriched-editor' ); ?></label>
		<p><?php esc_html_e( 'Use pixels (instead of points) for Font Sizes menu.', 'codepotent-enriched-editor' ); ?></p>
	</div>
</div>
<?php

if ( ! is_multisite() || current_user_can( 'manage_sites' ) ) {
	?>
	<div class="advanced-options">
	<h3><?php esc_html_e( 'Advanced Options', 'codepotent-enriched-editor' ); ?></h3>
	<?php

	$has_editor_style = $this->has_editor_style();
	$disabled = ' disabled';

	if ( $has_editor_style === 'not-supporetd' || $has_editor_style === 'not-present' ) {
		add_editor_style();
	}

	if ( $this->has_editor_style() === 'present' ) {
		$disabled = '';
		$has_editor_style = 'present';
	}

	?>
	<div>
		<label><input type="checkbox" name="admin_options[]" value="importcss" id="importcss" <?php if ( ! $disabled && $this->check_admin_setting( 'importcss' ) ) echo ' checked="checked"'; echo $disabled; // phpcs:ignore Squiz.PHP.EmbeddedPhp.MultipleStatements,WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
		<?php esc_html_e( 'Custom Styles', 'codepotent-enriched-editor' ); ?></label>
		<p>
		<?php

		printf(
		esc_html__('Import styles from %s into the Formats menu.', 'codepotent-enriched-editor'),
		'<code>'.str_replace(site_url(), '', get_stylesheet_directory_uri()).'/assets/css/editor-style.css</code>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped



//		_e( 'Load the CSS classes from <code>/assets/css/editor-style.css</code> into the Formats menu.', 'codepotent-enriched-editor' );

		if ( $has_editor_style === 'not-supporetd' ) {
			?>
				<br>
				<span class="tadv-error"><?php esc_html_e( 'ERROR:', 'codepotent-enriched-editor' ); ?></span>
				<?php esc_html_e( 'Unable to locate ../assets/css/editor-style.css.', 'codepotent-enriched-editor' ); ?>
			<?php
		} elseif ( $disabled ) {
			?>
				<br>
				<span class="tadv-error"><?php esc_html_e( 'ERROR:', 'codepotent-enriched-editor' ); ?></span>
				<?php esc_html_e( 'Unable to locate ../assets/css/editor-style.css.', 'codepotent-enriched-editor' ); ?>
			<?php
		}

		if ( $has_editor_style === 'not-supporetd' || $disabled ) {
			esc_html_e( 'To use this option, add editor-style.css to your theme or a child theme. Enabling this option will also load that stylesheet in the editor.', 'codepotent-enriched-editor' );
		}

		?>
		</p>
	</div>
	<div>
		<label><input type="checkbox" name="admin_options[]" value="no_autop" id="no_autop" <?php if ( $this->check_admin_setting( 'no_autop' ) ) echo ' checked="checked"'; ?> />
		<?php esc_html_e( 'Preserve Tags', 'codepotent-enriched-editor' ); ?></label>
		<p>
			<?php esc_html_e( 'Preserve &lt;p&gt; and &lt;br&gt; tags without replacing them.', 'codepotent-enriched-editor' ); ?>
		</p>
	</div>
	</div>

	<div class="advanced-options">
	<h3><?php esc_html_e( 'Import/Export', 'codepotent-enriched-editor' ); ?></h3>
	<div>
		<p>
			<input type="submit" class="button" name="tadv-export-settings" value="<?php esc_html_e( 'Export Settings', 'codepotent-enriched-editor' ); ?>" /> &nbsp;
			<input type="submit" class="button" name="tadv-import-settings" value="<?php esc_html_e( 'Import Settings', 'codepotent-enriched-editor' ); ?>" />
		</p>
	</div>
	</div>
	<input type="hidden" name="tadv_enable_at[]" value="edit_post_screen">
	<input type="hidden" name="tadv_enable_at[]" value="rest_of_wpadmin">
	<input type="hidden" name="tadv_enable_at[]" value="on_front_end">

	<?php

}
?>

<p class="tadv-submit">
	<?php wp_nonce_field( 'tadv-save-buttons-order' ); ?>
	<input class="button" type="submit" name="tadv-restore-defaults" value="<?php esc_html_e( 'Restore Default Settings', 'codepotent-enriched-editor' ); ?>" />
	<input class="button-primary button-large" type="submit" name="tadv-save" value="<?php esc_html_e( 'Save Changes', 'codepotent-enriched-editor' ); ?>" />
</p>
</form>

<div id="wp-adv-error-message" class="tadv-error">
<?php esc_html_e( 'The [Toolbar toggle] button shows or hides the second, third, and forth button rows. It will only work when it is in the first row and there are buttons in the second row.', 'codepotent-enriched-editor' ); ?>
</div>
</div><!-- /wrap -->
