<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // App Settings
            [
                'key' => 'app_name',
                'value' => 'SabiStore',
                'type' => 'text',
                'group' => 'app',
                'label' => 'Application Name',
                'description' => 'The name of your application',
                'is_public' => true,
                'order' => 1,
            ],
            [
                'key' => 'app_description',
                'value' => 'Multi-tenant SaaS platform for vendors and buyers',
                'type' => 'textarea',
                'group' => 'app',
                'label' => 'Application Description',
                'description' => 'Brief description of your application',
                'is_public' => true,
                'order' => 2,
            ],
            [
                'key' => 'app_logo',
                'value' => null,
                'type' => 'file',
                'group' => 'app',
                'label' => 'Application Logo',
                'description' => 'Upload your application logo',
                'is_public' => true,
                'order' => 3,
            ],

            // Payment Settings
            [
                'key' => 'paystack_public_key',
                'value' => '',
                'type' => 'text',
                'group' => 'payment',
                'label' => 'Paystack Public Key',
                'description' => 'Your Paystack public key for payment processing',
                'is_public' => false,
                'order' => 1,
            ],
            [
                'key' => 'paystack_secret_key',
                'value' => '',
                'type' => 'text',
                'group' => 'payment',
                'label' => 'Paystack Secret Key',
                'description' => 'Your Paystack secret key for payment processing',
                'is_public' => false,
                'order' => 2,
            ],

            // Feature Toggles
            [
                'key' => 'wallet_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'features',
                'label' => 'Enable Wallet',
                'description' => 'Enable or disable wallet functionality',
                'is_public' => true,
                'order' => 1,
            ],
            [
                'key' => 'learning_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'features',
                'label' => 'Enable Learning Center',
                'description' => 'Enable or disable learning center functionality',
                'is_public' => true,
                'order' => 2,
            ],
            [
                'key' => 'reseller_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'features',
                'label' => 'Enable Reseller Program',
                'description' => 'Enable or disable reseller program functionality',
                'is_public' => true,
                'order' => 3,
            ],

            // Certificate Settings
            [
                'key' => 'certificate_footer_text',
                'value' => 'This certificate is awarded in recognition of successful completion of the course.',
                'type' => 'textarea',
                'group' => 'certificates',
                'label' => 'Certificate Footer Text',
                'description' => 'Text to display at the bottom of certificates',
                'is_public' => false,
                'order' => 1,
            ],
            [
                'key' => 'certificate_background_image',
                'value' => null,
                'type' => 'file',
                'group' => 'certificates',
                'label' => 'Certificate Background Image',
                'description' => 'Background image for certificates',
                'is_public' => false,
                'order' => 2,
            ],
            [
                'key' => 'certificate_font_family',
                'value' => 'Arial',
                'type' => 'text',
                'group' => 'certificates',
                'label' => 'Certificate Font Family',
                'description' => 'Font family for certificate text',
                'is_public' => false,
                'order' => 3,
            ],
            [
                'key' => 'certificate_font_size',
                'value' => '16',
                'type' => 'number',
                'group' => 'certificates',
                'label' => 'Certificate Font Size',
                'description' => 'Font size for certificate text',
                'is_public' => false,
                'order' => 4,
            ],
            [
                'key' => 'certificate_text_color',
                'value' => '#000000',
                'type' => 'text',
                'group' => 'certificates',
                'label' => 'Certificate Text Color',
                'description' => 'Color for certificate text (hex code)',
                'is_public' => false,
                'order' => 5,
            ],

            // Certificate Position Settings
            [
                'key' => 'certificate_name_position_x',
                'value' => '50',
                'type' => 'number',
                'group' => 'certificates',
                'label' => 'Name Position X (%)',
                'description' => 'Horizontal position of user name on certificate (0-100%)',
                'is_public' => false,
                'order' => 6,
            ],
            [
                'key' => 'certificate_name_position_y',
                'value' => '40',
                'type' => 'number',
                'group' => 'certificates',
                'label' => 'Name Position Y (%)',
                'description' => 'Vertical position of user name on certificate (0-100%)',
                'is_public' => false,
                'order' => 7,
            ],
            [
                'key' => 'certificate_course_position_x',
                'value' => '50',
                'type' => 'number',
                'group' => 'certificates',
                'label' => 'Course Position X (%)',
                'description' => 'Horizontal position of course name on certificate (0-100%)',
                'is_public' => false,
                'order' => 8,
            ],
            [
                'key' => 'certificate_course_position_y',
                'value' => '60',
                'type' => 'number',
                'group' => 'certificates',
                'label' => 'Course Position Y (%)',
                'description' => 'Vertical position of course name on certificate (0-100%)',
                'is_public' => false,
                'order' => 9,
            ],
            [
                'key' => 'certificate_date_position_x',
                'value' => '50',
                'type' => 'number',
                'group' => 'certificates',
                'label' => 'Date Position X (%)',
                'description' => 'Horizontal position of date on certificate (0-100%)',
                'is_public' => false,
                'order' => 10,
            ],
            [
                'key' => 'certificate_date_position_y',
                'value' => '80',
                'type' => 'number',
                'group' => 'certificates',
                'label' => 'Date Position Y (%)',
                'description' => 'Vertical position of date on certificate (0-100%)',
                'is_public' => false,
                'order' => 11,
            ],

            // Email Settings
            [
                'key' => 'smtp_host',
                'value' => '',
                'type' => 'text',
                'group' => 'email',
                'label' => 'SMTP Host',
                'description' => 'SMTP server hostname',
                'is_public' => false,
                'order' => 1,
            ],
            [
                'key' => 'smtp_port',
                'value' => '587',
                'type' => 'number',
                'group' => 'email',
                'label' => 'SMTP Port',
                'description' => 'SMTP server port',
                'is_public' => false,
                'order' => 2,
            ],
            [
                'key' => 'smtp_username',
                'value' => '',
                'type' => 'text',
                'group' => 'email',
                'label' => 'SMTP Username',
                'description' => 'SMTP authentication username',
                'is_public' => false,
                'order' => 3,
            ],
            [
                'key' => 'smtp_password',
                'value' => '',
                'type' => 'text',
                'group' => 'email',
                'label' => 'SMTP Password',
                'description' => 'SMTP authentication password',
                'is_public' => false,
                'order' => 4,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
