<?php

namespace Database\Seeders;

use App\Models\CustomField;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customFields = [
            [
                'name' => 'birthday',
                'label' => 'Birthday',
                'type' => 'date',
                'is_filterable' => true,
            ],
            [
                'name' => 'company_name',
                'label' => 'Company Name',
                'type' => 'text',
                'is_filterable' => true,
            ],
            [
                'name' => 'job_title',
                'label' => 'Job Title',
                'type' => 'text',
                'is_filterable' => true,
            ],
            [
                'name' => 'address',
                'label' => 'Address',
                'type' => 'textarea',
                'is_filterable' => false,
            ],
            [
                'name' => 'website',
                'label' => 'Website',
                'type' => 'text',
                'is_filterable' => false,
            ],
            [
                'name' => 'notes',
                'label' => 'Notes',
                'type' => 'textarea',
                'is_filterable' => false,
            ],
        ];

        foreach ($customFields as $field) {
            CustomField::create($field);
        }
    }
}
