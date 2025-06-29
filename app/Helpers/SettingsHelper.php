<?php

if (!function_exists('get_setting')) {
    /**
     * Get a setting value by key
     */
    function get_setting($key, $default = null)
    {
        return \App\Models\Setting::get($key, $default);
    }
}

if (!function_exists('set_setting')) {
    /**
     * Set a setting value by key
     */
    function set_setting($key, $value, $type = 'text')
    {
        return \App\Models\Setting::set($key, $value, $type);
    }
}

if (!function_exists('get_settings_group')) {
    /**
     * Get all settings in a group
     */
    function get_settings_group($group)
    {
        return \App\Models\Setting::getGroup($group);
    }
}

if (!function_exists('app_name')) {
    /**
     * Get the application name from settings
     */
    function app_name()
    {
        return get_setting('app_name', config('app.name', 'SabiStore'));
    }
}

if (!function_exists('app_description')) {
    /**
     * Get the application description from settings
     */
    function app_description()
    {
        return get_setting('app_description', 'Multi-tenant SaaS platform for vendors and buyers');
    }
}

if (!function_exists('paystack_public_key')) {
    /**
     * Get Paystack public key from settings
     */
    function paystack_public_key()
    {
        return get_setting('paystack_public_key', config('services.paystack.public_key'));
    }
}

if (!function_exists('paystack_secret_key')) {
    /**
     * Get Paystack secret key from settings
     */
    function paystack_secret_key()
    {
        return get_setting('paystack_secret_key', config('services.paystack.secret_key'));
    }
}

if (!function_exists('certificate_footer_text')) {
    /**
     * Get certificate footer text from settings
     */
    function certificate_footer_text()
    {
        return get_setting('certificate_footer_text', 'This certificate is awarded in recognition of successful completion of the course.');
    }
}

if (!function_exists('wallet_enabled')) {
    /**
     * Check if wallet feature is enabled
     */
    function wallet_enabled()
    {
        return (bool) get_setting('wallet_enabled', true);
    }
}

if (!function_exists('learning_enabled')) {
    /**
     * Check if learning feature is enabled
     */
    function learning_enabled()
    {
        return (bool) get_setting('learning_enabled', true);
    }
}

if (!function_exists('reseller_enabled')) {
    /**
     * Check if reseller feature is enabled
     */
    function reseller_enabled()
    {
        return (bool) get_setting('reseller_enabled', true);
    }
}
