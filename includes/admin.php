<?php
/**
 * Admin specific hooks.
 *
 * @package SuperSaaS
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Adds the SuperSaaS settings page.
 */
function supersaas_add_admin_menu()
{
  add_options_page(__('SuperSaaS Settings', 'supersaas'), 'SuperSaaS', 'manage_options', 'supersaas-settings', 'supersaas_options');
}

/**
 * Registers the SuperSaaS settings.
 */
function supersaas_register_settings()
{
  register_setting('supersaas-settings', 'ss_account_name');
  register_setting('supersaas-settings', 'ss_display_choice'); // new
  register_setting('supersaas-settings', 'ss_autologin_enabled'); // new
  register_setting('supersaas-settings', 'ss_password'); // NOTE: this is an API KEY, not a user password; the "ss_password" key is used for backwards compatibility
  register_setting('supersaas-settings', 'ss_widget_script'); // new

  register_setting('supersaas-settings', 'ss_schedule');
  register_setting('supersaas-settings', 'ss_button_label');
  register_setting('supersaas-settings', 'ss_button_image');
  register_setting('supersaas-settings', 'ss_domain', 'domain_from_url');
}

/**
 * Register JS for page
 *
 * @uses "admin_enqueue_scripts" action
 */
function supersaas_register_assets()
{
  wp_register_script("supersaas_custom_js_script", plugin_dir_url(__FILE__) . 'js/admin.js', array('jquery'), null);
  wp_enqueue_script('supersaas_custom_js_script');
}


/**
 * Sanitizes the custom domain settings field.
 *
 * @param string $ss_domain The value of the custom domain.
 *
 * @return string The domain (and port) name part of the URL.
 */
function domain_from_url($ss_domain)
{
  $url_parts = parse_url($ss_domain);
  if (isset($url_parts['host'])) {
    $domain = $url_parts['host'];
    if (isset($url_parts['port'])) {
      $domain .= ':' . $url_parts['port'];
    }

    return $domain;
  } else {
    return $ss_domain;
  }
}

/**
 * Outputs the content of the SuperSaaS options page.
 */
function supersaas_options()
{
  if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.')); // WPCS: XSS.EscapeOutput OK.
  }


  ?>
  <div class="wrap">
    <h2><?php _e('SuperSaaS Settings', 'supersaas'); // WPCS: XSS.EscapeOutput OK.?></h2>

    <form method="post" action="options.php">
      <?php settings_fields('supersaas-settings'); ?>
      <p>
        <span style="font-weight: 600; font-size: 14px;">
          <?php _e('SuperSaaS account name', 'supersaas'); // WPCS: XSS.EscapeOutput OK.?>
          <em>:</em>
        </span>
        <input type="text" name="ss_account_name"
               value="<?php echo get_option('ss_account_name'); // WPCS: XSS.EscapeOutput OK.?>"
               required
        />
        <br/>
      </p>

      <div style="font-weight: 600; font-size: 14px;">
        How would you like to show your SuperSaaS schedule?
      </div>

      <fieldset>
        <legend class="screen-reader-text"><span>SuperSaaS schedule displays</span></legend>
        <div>
          <label>
            <input name="ss_display_choice" type="radio" value="regular_btn"
                   class="tog" <?php echo get_option('ss_display_choice') === 'regular_btn' ? 'checked' : ''; // WPCS: XSS.EscapeOutput OK.?>
            />
            Show a button that forwards the user to my SuperSaaS calendar
          </label>
        </div>
        <div>
          <label>
            <input name="ss_display_choice" type="radio" value="popup_btn"
                   class="tog" <?php echo get_option('ss_display_choice') === 'popup_btn' ? 'checked' : ''; // WPCS: XSS.EscapeOutput OK.?>
            />
            Show a SuperSaaS widget containing the calendar as a button or a frame directly on my site
          </label>
        </div>
      </fieldset>

      <p>
        <label>
          <input type="checkbox" name="ss_autologin_enabled"
                 value="1"
                 <?php echo get_option('ss_autologin_enabled') === '1' ? 'checked' : ''; // WPCS: XSS.EscapeOutput OK.?>
          />
          If the user is logged into WordPress, log them into your SuperSaaS account with the WordPress user name
        </label>

        <br/>
        <span id="ss_password" class="<?php echo get_option('ss_autologin_enabled') === '1' ? '' : 'hidden' ?>">
          Automatically logging in the user requires your <a href="http://www.supersaas.com/accounts/edit" target="_blank">API key</a>:
          <input type="text" name="ss_password" value="<?php echo get_option('ss_password'); // WPCS: XSS.EscapeOutput OK.?>"/>
        </span>
      </p>

      <p id="ss_widget_script" class="<?php echo get_option('ss_display_choice') === 'regular_btn' ? 'hidden' : '' ?>">
        Paste your <a href="https://www.supersaas.com/info/doc/integration/integration_with_widget" target="_blank">widget script</a> here:
        <br/>
        <textarea name="ss_widget_script" rows="9" cols="80" placeholder="Paste the script here">
          <?php echo get_option('ss_widget_script'); // WPCS: XSS.EscapeOutput OK.?>
        </textarea>
      </p>

      <table class="form-table">
        <tr>
          <th scope="row">
            <?php _e('Schedule name', 'supersaas'); // WPCS: XSS.EscapeOutput OK.?>
          </th>
          <td>
            <input type="text" name="ss_schedule"
                   value="<?php echo get_option('ss_schedule'); // WPCS: XSS.EscapeOutput OK.?>"
            />
            <br/>
            <span class='description'>
              The default name of the schedule. <br/>
              Leave blank for <a href="https://www.supersaas.com/accounts/access#account_list_schedules_1" target="_blank">default behaviour</a> (can be overwritten in shortcode)
            </span>
          </td>
        </tr>
        <tr id="ss_button_settings" class="<?php echo get_option('ss_display_choice') === 'popup_btn' ? 'hidden' : '' ?>">
          <th scope="row">
            Button Settings
            <em>(<?php _e('optional', 'supersaas'); // WPCS: XSS.EscapeOutput OK.?>)</em>
          </th>
          <td>
            <input type="text" name="ss_button_label"
                   value="<?php echo get_option('ss_button_label') ? get_option('ss_button_label') : __('Book Now!', 'supersaas'); // WPCS: XSS.EscapeOutput OK.?>"
            />
            <br/>
            <span class='description'>
              <?php _e("The text to be put on the button that is displayed, for example 'Create Appointment'.", 'supersaas'); // WPCS: XSS.EscapeOutput OK.?>
            </span>
            <br/>
            <input type="text" name="ss_button_image"
                   value="<?php echo get_option('ss_button_image'); // WPCS: XSS.EscapeOutput OK.?>"
            />
            <br/>
            <span class='description'>
              <?php _e('Location of an image file to use as the button. Can be left blank.', 'supersaas'); // WPCS: XSS.EscapeOutput OK.?>
            </span>
          </td>
        </tr>

        <tr id="ss_domain" class="<?php echo get_option('ss_display_choice') === 'popup_btn' ? 'hidden' : '' ?>">
          <th scope="row">
            <?php _e('Custom domain name', 'supersaas'); // WPCS: XSS.EscapeOutput OK.?>
            <em>(<?php _e('optional', 'supersaas'); // WPCS: XSS.EscapeOutput OK.?>)</em>
          </th>
          <td>
            <input type="text" name="ss_domain"
                   value="<?php echo get_option('ss_domain'); // WPCS: XSS.EscapeOutput OK.?>"
            />
            <br/>
            <span class='description'>
              <?php _e('If you created a custom domain name that points to SuperSaaS enter it here. Can be left blank.', 'supersaas'); // WPCS: XSS.EscapeOutput OK.?>
            </span>
          </td>
        </tr>
      </table>

      <p class="submit">
        <input type="submit" class="button-primary"
               value="<?php _e('Save Changes'); // WPCS: XSS.EscapeOutput OK.?>"
        />
      </p>
    </form>
  </div>

  <?php
}