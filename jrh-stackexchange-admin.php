<?php

/**
* StackExchange Questions Admin
*
* Configure StackExchange Questions plugin to include options such as choosing the
* SE site to pull data from, adding API keys, and other settings.
*/

class jrh_stackexchange_admin
{
    /**
    * API Settings
    */
    private $_api = "https://api.stackexchange.com";
    private $_apiKey = "";
    private $_apiClientId = "";

    /**
    * Admin Initialization
    *
    * This method is called automatically upon registering the admin menu
    */
    public function jrh_stackexchange_admin()
    {
        // Initialize Option Settings Menu
        add_options_page( 'StackExchange Questions', 'StackExchange Questions', 'manage_options', 'jrh-stackexchange-admin', array( &$this, 'manage_options' ) );

        // Register Settings
        register_setting( 'jrh_stackexchange_options', 'jrh_stackexchange_options', array( &$this, 'validate_options' ) );

        // Define Settings Sections
        add_settings_section( 'jrh-stackexchange-options-basic', 'Basic Settings', null, 'jrh-stackexchange-admin');
        add_settings_section( 'jrh-stackexchange-options-api', 'API Settings', null, 'jrh-stackexchange-admin');

        // Define Settings
        add_settings_field(
            'jrh-stackexchange-site',
            'Site',
            array( &$this, 'get_sites' ),
            'jrh-stackexchange-admin',
            'jrh-stackexchange-options-basic'
        );
        add_settings_field(
            'jrh-stackexchange-client_id',
            'Client ID',
            array( &$this, 'get_client_id' ),
            'jrh-stackexchange-admin',
            'jrh-stackexchange-options-api'
        );
        add_settings_field(
            'jrh-stackexchange-client_key',
            'Client Key',
            array( &$this, 'get_client_key' ),
            'jrh-stackexchange-admin',
            'jrh-stackexchange-options-api'
        );

        // Load Current API key/client_id
        $options = get_option( 'jrh_stackexchange_options' );
        $this->_apiKey = isset($options['client_key']) ? $options['client_key'] : "";
        $this->_apiClientId = isset($options['client_id']) ? intval($options['client_id']) : "";
    }

    /**
    * Manage Options
    *
    * Displays form to manage plugin options
    */
    public function manage_options() 
    {
        // Verify Logged In
        if ( !current_user_can( 'manage_options' ) )  {
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        ?>
        <div class="wrap">

            <div id="icon-options-general" class="icon32"><br></div> <h2>StackExchange Questions</h2><br>
            
            Configure options for this plugin. This plugin must use a specific site in the StackExchange Network which are available in the list below.
            If you do not select a site, it will use StackOverflow by default. Additionally, you can have more requests available to StackExchange by 
            registering your application at <a href="http://www.stackapps.com">StackApps</a> and getting an API Key. If you have these available, you can 
            set them here as well.<br>

            <form action="options.php" method="post">

                <?php settings_fields('jrh_stackexchange_options'); ?>
                <?php do_settings_sections('jrh-stackexchange-admin'); ?>

                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                </p>
            </form>

        </div>

        <?php
    }

    /**
    * Option Validation
    */
    public function validate_options($options)
    {
        // Ensure Client ID is a number
        $client_id = isset($options['client_id']) ? $options['client_id'] : "";
        if( strlen(trim($client_id)) > 0 && !is_numeric($client_id) )
        {
            // Use previous value
            $oldOptions = get_option( 'jrh_stackexchange_options' );
            $options['client_id'] = isset($oldOptions['client_id']) ? $oldOptions['client_id'] : "";
            add_settings_error(
                'jrh_stackexchange_options',
                'jrh_stackexchange_client_id_error',
                'If using a Client ID, it must be numeric!',
                'error'
            );
        }
        return $options;
    }

    /**
    * Callback: Responsible for generating HTML Select element containing list of sites.
    */
    public function get_sites()
    {
        // List of sites available
        $sites = $this->_loadSites();
        ksort($sites);

        // Get current option
        $options = get_option( 'jrh_stackexchange_options' );
        $current_site = isset($options['site']) ? $options['site'] : false;

        // Display select box
        echo("<select name='jrh_stackexchange_options[site]'>");
        $selected = "";
        foreach( $sites as $site_display => $site_key )
        {
            if($current_site == $site_key)
            {
                $selected = " SELECTED ";
            } else {
                $selected = "";
            }
            echo("<option $selected value='$site_key'>$site_display</option>");
        }
        echo("</select>");
    }

    /**
    * Callback: Responsible for generating HTML Input element containing Client ID.
    */
    public function get_client_id()
    {
        // Get current option
        $options = get_option( 'jrh_stackexchange_options' );
        $client_id = isset($options['client_id']) ? $options['client_id'] : "";

        // Display input
        echo("<input type='text' id='client_id' name='jrh_stackexchange_options[client_id]' value='$client_id'>");
    }

    /**
    * Callback: Responsible for generating HTML Input element containing Client Key.
    */
    public function get_client_key()
    {
        // Get current option
        $options = get_option( 'jrh_stackexchange_options' );
        $client_key = isset($options['client_key']) ? $options['client_key'] : "";

        // Display input
        echo("<input type='text' id='client_key' name='jrh_stackexchange_options[client_key]' value='$client_key'>");
    }

    /**
    * Load All Sites in StackExchange Network
    *
    * @uses StackExchange API api.stackexchange.com
    *
    * Load sites from Transient cache if available, otherwise pull fresh copy and cache it.
    */
    private function _loadSites($forceRefresh = false)
    {
        // Check cache
        $siteOutput = get_transient( 'jrh_stackexchange_sites' );

        // Make call
        if(!is_array($siteOutput) || $forceRefresh === true)
        {
            $response = wp_remote_get( $this->_api . "/sites/?pagesize=1000&key=" . $this->_apiKey . "&client_id=" . $this->_apiClientId );
            if($response['response']['code'] == '200')
            {
                $sites = isset($response['body']) ? json_decode($response['body']) : array();
                $sites = isset($sites->items) ? $sites->items : array();
                $siteOutput = array();

                // Get data
                foreach($sites as $site)
                {
                    $siteOutput[$site->name] = $site->api_site_parameter;
                } 
                
                // Cache for 1 week
                set_transient( 'jrh_stackexchange_sites', $siteOutput, 60 * 60 * 24 * 7 );
            } else {
                add_settings_error(
                    'jrh_stackexchange_options',
                    'jrh_stackexchange_api_error',
                    'An error occurred attempting to contact the StackExchange API! ' . $response['response']['message'],
                    'error'
                );
            }
        }

        return $siteOutput;
    }
}

?>