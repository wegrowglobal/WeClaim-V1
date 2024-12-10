<?php

namespace Database\Seeders;

use App\Models\SystemConfig;
use Illuminate\Database\Seeder;

class SystemConfigSeeder extends Seeder
{
    public function run()
    {
        $configs = [
            // Claims Settings
            [
                'key' => 'mileage_rate',
                'value' => '0.60',
                'description' => 'Mileage Rate (RM per KM)',
                'group' => 'claims',
                'type' => 'number'
            ],
            [
                'key' => 'max_claim_amount',
                'value' => '5000',
                'description' => 'Maximum Claim Amount (RM)',
                'group' => 'claims',
                'type' => 'number'
            ],
            [
                'key' => 'claim_submission_deadline',
                'value' => '30',
                'description' => 'Claim Submission Deadline (Days)',
                'group' => 'claims',
                'type' => 'number'
            ],

            // Email Settings
            [
                'key' => 'email_notifications_enabled',
                'value' => 'true',
                'description' => 'Enable Email Notifications',
                'group' => 'notifications',
                'type' => 'boolean'
            ],
            [
                'key' => 'notification_sender_email',
                'value' => 'system@wegrow-global.com',
                'description' => 'Notification Sender Email',
                'group' => 'notifications',
                'type' => 'email'
            ],

            // Security Settings
            [
                'key' => 'password_expiry_days',
                'value' => '90',
                'description' => 'Password Expiry (Days)',
                'group' => 'security',
                'type' => 'number'
            ],
            [
                'key' => 'max_login_attempts',
                'value' => '5',
                'description' => 'Maximum Login Attempts',
                'group' => 'security',
                'type' => 'number'
            ],
            [
                'key' => 'session_timeout_minutes',
                'value' => '30',
                'description' => 'Session Timeout (Minutes)',
                'group' => 'security',
                'type' => 'number'
            ],

            // System Settings
            [
                'key' => 'maintenance_mode',
                'value' => 'false',
                'description' => 'Maintenance Mode',
                'group' => 'system',
                'type' => 'boolean'
            ],
            [
                'key' => 'system_timezone',
                'value' => 'Asia/Kuala_Lumpur',
                'description' => 'System Timezone',
                'group' => 'system',
                'type' => 'text'
            ],
            [
                'key' => 'date_format',
                'value' => 'Y-m-d',
                'description' => 'Date Format',
                'group' => 'system',
                'type' => 'text'
            ],

            // Company Settings
            [
                'key' => 'company_name',
                'value' => 'WeGrow Global',
                'description' => 'Company Name',
                'group' => 'company',
                'type' => 'text'
            ],
            [
                'key' => 'company_address',
                'value' => 'Your Company Address',
                'description' => 'Company Address',
                'group' => 'company',
                'type' => 'textarea'
            ],
            [
                'key' => 'company_phone',
                'value' => '+60123456789',
                'description' => 'Company Phone',
                'group' => 'company',
                'type' => 'text'
            ],

            // File Upload Settings
            [
                'key' => 'max_file_size',
                'value' => '10',
                'description' => 'Maximum File Size (MB)',
                'group' => 'uploads',
                'type' => 'number'
            ],
        ];

        foreach ($configs as $config) {
            SystemConfig::updateOrCreate(
                ['key' => $config['key']],
                $config
            );
        }
    }
}
