<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

?>
<div class="wrap" id="exp-imp-settings">
    
    <div class= "maspik-setting-header">
      <div class="notice-pointer"><h2></h2></div>

        <?php 
          echo "<div class='upsell-btn " . maspik_add_pro_class() . "'>";
          maspik_get_pro();
          maspik_activate_license();
          echo "</div>";

        ?>
        <div class="maspik-setting-header-wrap">
          <h1 class="maspik-title">MASPIK.</h1>
            <?php
              echo '<h3 class="maspik-protag '. maspik_add_pro_class() .'">Pro</h3>';
              
            ?>
        </div> 

    </div>

    <div class="maspik-spam-head maspik-import-export-head">
        <h2 class='maspik-header maspik-spam-header'><?php esc_html_e('Maspik Import/Export Settings', 'contact-forms-anti-spam'); ?></h2>
        <p>
            <?php echo esc_html__('Please note that importing/exporting settings will affect most of the Maspik configuration.', 'contact-forms-anti-spam'); ?><br>
            <?php echo esc_html__('Imported settings will override existing ones, and there is no option to undo the imported settings.', 'contact-forms-anti-spam'); ?><br>
            <?php echo esc_html__('Use this feature with caution.', 'contact-forms-anti-spam'); ?>
        </p>
    </div>

    <div class="maspik-import-export-layout">
        <section class="maspik-import-export-card">
            <h2><?php echo esc_html__('Export Settings', 'contact-forms-anti-spam'); ?></h2>
            <p class="maspik-import-export-card-desc">
                <?php echo esc_html__('Download your current Maspik configuration as a JSON file so you can back it up or move it to another site.', 'contact-forms-anti-spam'); ?>
            </p>
            <form id="export-settings-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="Maspik_export_settings">
                <?php wp_nonce_field('Maspik_export_settings_nonce', 'Maspik_export_settings_nonce_field'); ?>
                <?php submit_button(__('Export Settings', 'contact-forms-anti-spam'),'export-import-btn'); ?>
            </form>
        </section>

        <section class="maspik-import-export-card">
            <h2><?php echo esc_html__('Import Settings', 'contact-forms-anti-spam'); ?></h2>
            <p class="maspik-import-export-card-desc">
                <?php echo esc_html__('Upload a valid Maspik JSON export file to replace the current settings.', 'contact-forms-anti-spam'); ?>
            </p>
            <form id="import-settings-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="Maspik_import_settings">
                <?php wp_nonce_field('Maspik_import_settings_nonce', 'Maspik_import_settings_nonce_field'); ?>
                <label class="maspik-import-export-file">
                    <span><?php echo esc_html__('Choose JSON file', 'contact-forms-anti-spam'); ?></span>
                    <input type="file" name="maspik-settings" accept=".json,application/json" required>
                </label>
                <p class="maspik-import-export-help"><?php echo esc_html__('Supported file type: .json', 'contact-forms-anti-spam'); ?></p>
                <?php submit_button(__('Import Settings', 'contact-forms-anti-spam'),'export-import-btn'); ?>
            </form>
        </section>
    </div>
</div>
<?php echo get_maspik_footer(); ?>

<script>
(function () {
    try {
        var params = new URLSearchParams(window.location.search);
        if (params.get('maspik_import_deprecated') !== '1') {
            return;
        }
        var msg = <?php
            echo wp_json_encode(
                sprintf(
                    /* translators: %s: Minimum Maspik version number. */
                    __(
                        'This settings file was exported from Maspik older than version %s. Lists and several options will not transfer correctly from this format. Update Maspik on the site you export from to the latest release, export a new file, then import that file again.',
                        'contact-forms-anti-spam'
                    ),
                    maspik_import_minimum_export_plugin_version()
                )
            );
        ?>;
        window.alert(msg);
        params.delete('maspik_import_deprecated');
        var search = params.toString();
        var newUrl = window.location.pathname + (search ? '?' + search : '') + window.location.hash;
        window.history.replaceState(null, '', newUrl);
    } catch (e) {}
})();
</script>

<?php