<?php
/*
 * Plugin Name:     Log WP_Mail
 * Description:     Log outgoing wp_mail
 * Author:          Premiere Themes
 * Author URI:      https://premierethemes.com
 * Text Domain:     logwpmail
 * Domain Path:     /languages
 * Version:         0.1
*/

add_action('wp_mail_succeeded', 'logwpmail_logmail_succeeded', 1, 10000);
add_action('wp_mail_failed', 'logwpmail_logmail_failed');

function logwpmail_logmail_succeeded($mail_data) {
    $time = time();
    $date = date("Y-m-d H:i:s");

    $file = logwpmail_file_info();
    $logdir = $file['logdir'];
    $logfile = $file['logfile_prefix'];

    if( !is_dir( $logdir ) ) {

        if(!mkdir( $logdir)) {
            //echo "Error creating dir $logfile<br/>";
        }
    }

    if(!is_dir($logdir)) {
        esc_html_e("No logdir $logdir, returning....");
        return;
    }

    // log mail message data
    file_put_contents( $logdir.$logfile . "success.log",  '"' . implode(", ", $mail_data['to']) . '"' . ", \"" . addslashes( $mail_data['subject'] ) . "\", \"" . addslashes( $mail_data['message'] ) . "\", " . $date . ", $time\n", FILE_APPEND );
    file_put_contents( $logdir.$logfile . "success.log",  count($mail_data['headers']) ? implode("\n", $mail_data['headers']) : "No headers". ", " . $date . ", $time\n", FILE_APPEND );
    file_put_contents( $logdir.$logfile . "success.log",  count($mail_data['headers']) ?  implode("\n", $mail_data['attachments']): "No attachments". ", " . $date . ", $time\n", FILE_APPEND );


    return;
}


function logwpmail_logmail_failed($mail_data) {
    $time = time();
    $date = date("Y-m-d H:i:s");

    $file = logwpmail_file_info();
    $logdir = $file['logdir'];
    $logfile = $file['logfile_prefix'];

    if( !is_dir( $logdir ) ) {

        if(!mkdir( $logdir)) {
            //echo "Error creating dir $logfile<br/>";
        }
    }



    if(!is_dir($logdir)) {
        esc_html_e("no logdir $logdir, returning....");
        return;
    }

    // log mail message data
    file_put_contents( $logdir.$logfile . "failed.log",  '"' . implode(", ", $mail_data['to']) . '"' . ", \"" . addslashes( $mail_data['subject'] ) . "\", \"" . addslashes( $mail_data['message'] ) . "\", " . $date . ", $time\n", FILE_APPEND );
    file_put_contents( $logdir.$logfile . "failed.log",  count($mail_data['headers']) ? implode("\n", $mail_data['headers']) : "No headers". ", " . $date . ", $time\n", FILE_APPEND );
    file_put_contents( $logdir.$logfile . "failed.log",  count($mail_data['headers']) ?  implode("\n", $mail_data['attachments']): "No attachments". ", " . $date . ", $time\n", FILE_APPEND );

    return;
}

/**
 * @internal never define functions inside callbacks.
 * these functions could be run multiple times; this would result in a fatal error.
 */
 
/**
 * custom option and settings
 */
function logwpmail_settings_init() {
    // Register a new setting for "logwpmail" page.
    //register_setting( 'logwpmail', 'logwpmail_options' );
    register_setting( 'logwpmail', 'logwpmail_purge_options' );

    // Register a new section in the "logwpmail" page.
    add_settings_section(
        'logwpmail_section_developers',
        esc_html( 'Configure Log wp_mail settings.', 'logwpmail' ), 'logwpmail_section_developers_callback',
        'logwpmail'
    );
    
    // Register a new section in the "logwpmail" page.
    add_settings_section(
        'logwpmail_section_developers_output',
        esc_html( 'Log output', 'logwpmail' ), 'logwpmail_section_developers_callback_output',
        'logwpmail'
    );

    // Register a new field in the "logwpmail_section_developers" section, inside the "logwpmail" page.
    add_settings_field(
        'logwpmail_field_purge', // As of WP 4.6 this value is used only internally.
                                // Use $args' label_for to populate the id inside the callback.
            esc_html( 'Purge', 'logwpmail' ),
        'logwpmail_field_purge_cb',
        'logwpmail',
        'logwpmail_section_developers',
        array(
            'label_for'         => 'logwpmail_field_purge',
            'class'             => 'logwpmail_row',
            'logwpmail_custom_data' => 'custom',
        )
    );
}
 

function logwpmail_file_info() {
    $logdir = plugin_dir_path(__FILE__) . "log/";
    $logfile = "LWPMAIL-" . date("Ymd") . "-";

    return array( 'logfile_prefix' => $logfile,
                        'logdir' => $logdir );
}

/**
 * Register our logwpmail_settings_init to the admin_init action hook.
 */
add_action( 'admin_init', 'logwpmail_settings_init' );
 
/**
 * Custom option and settings:
 *  - callback functions
 */
 
/**
 * Developers section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function logwpmail_section_developers_callback( $args ) {
    /*
    <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Save log files locally', 'logwpmail' ); ?></p>
    */

    ?>
    <?php
}
 
function logwpmail_section_developers_callback_output( $args ) {
    $file = logwpmail_file_info();
    $successfile = $file['logdir'].$file['logfile_prefix']."success.log";
    $failedfile = $file['logdir'].$file['logfile_prefix']."failed.log";

    ?>
    <p id="<?php esc_attr__( $args['id'] ); ?>"><?php esc_html_e( 'View message logs', 'logwpmail' ); ?></p>
    <div>
        <?php 
            if(is_file($successfile)):
        ?>
        <p>Success</p>
        <textarea name="" id="" cols="90" rows="10"><?php  echo file_get_contents($successfile ) ; ?></textarea>
        <?php endif; ?>
</div>
    <div>
    <?php 
            if(is_file($failedfile)):
        ?>
        <p>Error</p>
        <textarea name="" id="" cols="90" rows="10"><?php  echo file_get_contents($failedfile ) ; ?></textarea>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Pill field callbakc function.
 *
 * WordPress has magic interaction with the following keys: label_for, class.
 * - the "label_for" key value is used for the "for" attribute of the <label>.
 * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
 * Note: you can add custom key value pairs to be used inside your callbacks.
 *
 * @param array $args
 */
function logwpmail_field_purge_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option( 'logwpmail_purge_options' );
    $every = 'every';
    $days = __('day(s)', 'logwpmail');

        // purge messages
        if( isset($options[$args['label_for']])) {
            $file = logwpmail_file_info();

            if(!is_dir($file['logdir'])) {
                return;
            }
                $logfile = $file['logdir'].$file['logfile_prefix'];

                if($handle = opendir($file['logdir'])) {
                    /* This is the correct way to loop over the directory. */
                    while (false !== ($entry = readdir($handle))) {
                        if( $entry == "." || $entry == "..") {
                            //echo "dots, skip...<br/>";
                            continue;
                        }

                        $target = $file['logdir'].$entry;

                        if( $target == $logfile."success.log") {
                            //echo "today's success logfile skip<br/>";
                            continue;
                        } else if( $target == $logfile."failed.log") {
                            //echo "today's failed logfile skip<br/>";
                            continue;
                        }

                        if(!is_numeric( $options['purge_days'])) {
                            esc_html_e("Purge days not set, skipping....", "logwpmail");
                            continue;
                        }

                        $purge_time = $options['purge_days'] * 86400;

                        $mtime = filemtime( $target );
                        $fileage = (time()-$mtime);

                        //echo "mtime: $mtime " . (time()-$mtime) . "<br/>";
                        //echo "purging";
                        
                        if( ($fileage > $purge_time ) ) {
                            if(unlink( $target )) {
                                esc_html_e("Purged $target<br/>");
                            } else {
                                esc_html_e("Error purging $target<br/>");
                            }
                        } else {
                            esc_html_e("Not time to purge $target");
                        }
                        
                        
                    }
                }


            }
    ?>
    <section>
        <p><input type="checkbox" value="purge" name="logwpmail_purge_options[<?php echo esc_html_e( $args['label_for'] ); ?>]" <?php isset( $options[ $args['label_for'] ] ) ? ( checked( $options[ $args['label_for'] ], esc_html('purge', "logwpmail"), true ) ) : ( '' ); ?> /> Purge files 
    <?php esc_html_e($every); ?> <input type="number" name="logwpmail_purge_options[purge_days]" value="<?php esc_html_e($options['purge_days']); ?>" /> <?php  esc_html_e($days); ?></p>
    </section>
    <?php
}


/**
 * Add the top level menu page.
 */
function logwpmail_options_page() {
    add_menu_page(
        'Log WP_Mail',
        'Log Wp_Mail',
        'manage_options',
        'logwpmail',
        'logwpmail_options_page_html'
    );
}
 
 
/**
 * Register our logwpmail_options_page to the admin_menu action hook.
 */
add_action( 'admin_menu', 'logwpmail_options_page' );
 
 
/**
 * Top level menu callback function
 */
function logwpmail_options_page_html() {
    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
 
    // add error/update messages
 
    // check if the user have submitted the settings
    // WordPress will add the "settings-updated" $_GET parameter to the url
    if ( isset( $_GET['settings-updated'] ) ) {
        // add settings saved message with the class of "updated"
        add_settings_error( 'logwpmail_messages', 'logwpmail_message', esc_html( 'Settings Saved', 'logwpmail' ), 'updated' );
    }
 
    // show error/update messages
    settings_errors( 'logwpmail_messages' );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            // output security fields for the registered setting "logwpmail"
            settings_fields( 'logwpmail' );
            // output setting sections and their fields
            // (sections are registered for "logwpmail", each field is registered to a specific section)
            do_settings_sections( 'logwpmail' );
            // output save settings button
            submit_button( 'Save Settings' );
            ?>
        </form>
    </div>
    <?php
}