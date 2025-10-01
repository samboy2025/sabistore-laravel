<?php

if (!function_exists('get_setting')) {
    /**
     * Get a setting value by its key.
     *
     * @param string $key The key of the setting to retrieve.
     * @param mixed|null $default The default value to return if the setting is not found.
     * @return mixed The value of the setting or the default value.
     */
    function get_setting(string $key, mixed $default = null): mixed
    {
        return \App\Models\Setting::get($key, $default);
    }
}

if (!function_exists('set_setting')) {
    /**
     * Set a setting value by its key.
     *
     * @param string $key The key of the setting to set.
     * @param mixed $value The value to store.
     * @param string $type The data type of the setting (e.g., 'text', 'boolean').
     * @return \App\Models\Setting The updated or created setting model.
     */
    function set_setting(string $key, mixed $value, string $type = 'text'): \App\Models\Setting
    {
        return \App\Models\Setting::set($key, $value, $type);
    }
}

if (!function_exists('get_settings_group')) {
    /**
     * Get all settings within a specific group.
     *
     * @param string $group The name of the group to retrieve settings for.
     * @return \Illuminate\Support\Collection A collection of settings for the specified group.
     */
    function get_settings_group(string $group): \Illuminate\Support\Collection
    {
        return \App\Models\Setting::getGroup($group);
    }
}

if (!function_exists('app_name')) {
    /**
     * Get the application name from the settings.
     *
     * @return string The application name.
     */
    function app_name(): string
    {
        return get_setting('app_name', config('app.name', 'SabiStore'));
    }
}

if (!function_exists('app_description')) {
    /**
     * Get the application description from the settings.
     *
     * @return string The application description.
     */
    function app_description(): string
    {
        return get_setting('app_description', 'Multi-tenant SaaS platform for vendors and buyers');
    }
}

if (!function_exists('paystack_public_key')) {
    /**
     * Get the Paystack public key from the settings.
     *
     * @return string|null The Paystack public key.
     */
    function paystack_public_key(): ?string
    {
        return get_setting('paystack_public_key', config('services.paystack.public_key'));
    }
}

if (!function_exists('paystack_secret_key')) {
    /**
     * Get the Paystack secret key from the settings.
     *
     * @return string|null The Paystack secret key.
     */
    function paystack_secret_key(): ?string
    {
        return get_setting('paystack_secret_key', config('services.paystack.secret_key'));
    }
}

if (!function_exists('certificate_footer_text')) {
    /**
     * Get the default certificate footer text from the settings.
     *
     * @return string The certificate footer text.
     */
    function certificate_footer_text(): string
    {
        return get_setting('certificate_footer_text', 'This certificate is awarded in recognition of successful completion of the course.');
    }
}

if (!function_exists('wallet_enabled')) {
    /**
     * Check if the wallet feature is enabled in the settings.
     *
     * @return bool True if the wallet feature is enabled, false otherwise.
     */
    function wallet_enabled(): bool
    {
        return (bool) get_setting('wallet_enabled', true);
    }
}

if (!function_exists('learning_enabled')) {
    /**
     * Check if the learning center feature is enabled in the settings.
     *
     * @return bool True if the learning feature is enabled, false otherwise.
     */
    function learning_enabled(): bool
    {
        return (bool) get_setting('learning_enabled', true);
    }
}

if (!function_exists('reseller_enabled')) {
    /**
     * Check if the reseller feature is enabled in the settings.
     *
     * @return bool True if the reseller feature is enabled, false otherwise.
     */
    function reseller_enabled(): bool
    {
        return (bool) get_setting('reseller_enabled', true);
    }
}
