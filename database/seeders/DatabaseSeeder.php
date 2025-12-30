<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Department;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create departments
        $departments = [
            ['name' => 'Human Resources', 'description' => 'Handles recruitment, employee relations, and benefits'],
            ['name' => 'Information Technology', 'description' => 'Manages technology infrastructure and support'],
            ['name' => 'Marketing', 'description' => 'Responsible for brand management and promotions'],
            ['name' => 'Finance', 'description' => 'Handles accounting, budgeting, and financial reporting'],
            ['name' => 'Operations', 'description' => 'Manages daily business activities and processes'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }

        // Create admin user
        Employee::create([
            'emp_id' => 'EMP001',
            'full_name' => 'John Smith',
            'username' => 'admin',
            'email' => 'admin@company.com',
            'phone' => '(555) 123-4567',
            'department' => 'Information Technology',
            'position' => 'System Administrator',
            'hire_date' => '2020-01-15',
            'status' => 'active',
            'password_hash' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create sample employees
        $employees = [
            [
                'emp_id' => 'EMP002',
                'full_name' => 'Sarah Johnson',
                'username' => 'sarah',
                'email' => 'sarah.j@company.com',
                'phone' => '(555) 234-5678',
                'department' => 'Human Resources',
                'position' => 'HR Manager',
                'hire_date' => '2020-02-15',
                'status' => 'active',
                'password_hash' => Hash::make('password'),
                'role' => 'employee',
            ],
            [
                'emp_id' => 'EMP003',
                'full_name' => 'Michael Williams',
                'username' => 'michael',
                'email' => 'michael.w@company.com',
                'phone' => '(555) 345-6789',
                'department' => 'Information Technology',
                'position' => 'Software Developer',
                'hire_date' => '2020-03-22',
                'status' => 'active',
                'password_hash' => Hash::make('password'),
                'role' => 'employee',
            ],
            [
                'emp_id' => 'EMP004',
                'full_name' => 'Emily Brown',
                'username' => 'emily',
                'email' => 'emily.b@company.com',
                'phone' => '(555) 456-7890',
                'department' => 'Marketing',
                'position' => 'Marketing Specialist',
                'hire_date' => '2020-05-10',
                'status' => 'active',
                'password_hash' => Hash::make('password'),
                'role' => 'employee',
            ],
        ];

        foreach ($employees as $emp) {
            Employee::create($emp);
        }

        // Create system settings
        $settings = [
            ['setting_key' => 'work_start_time', 'setting_value' => '10:30', 'description' => 'Default work start time'],
            ['setting_key' => 'work_end_time', 'setting_value' => '19:30', 'description' => 'Default work end time'],
            ['setting_key' => 'lunch_duration', 'setting_value' => '60', 'description' => 'Default lunch duration in minutes'],
            ['setting_key' => 'late_threshold', 'setting_value' => '15', 'description' => 'Minutes after start time considered late'],
            ['setting_key' => 'casual_leave', 'setting_value' => '10', 'description' => 'Per Year Casual Leave'],
            ['setting_key' => 'sick_leave', 'setting_value' => '10', 'description' => 'Per year sick leave'],
            ['setting_key' => 'half_day_time', 'setting_value' => '04:30', 'description' => 'Working Time for Half day'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::create($setting);
        }

        // Create super admin
        $this->call(SuperAdminSeeder::class);
    }
}