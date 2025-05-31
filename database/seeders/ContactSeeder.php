<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\ContactCustomFieldValue;
use App\Models\CustomField;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample contacts
        $contacts = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '555-123-4567',
                'gender' => 'male',
                'status' => 'active',
                'custom_fields' => [
                    'birthday' => '1985-06-15',
                    'company_name' => 'Acme Corporation',
                    'job_title' => 'Marketing Director',
                    'address' => '123 Main St, Anytown, USA',
                    'website' => 'www.johndoe.com',
                    'notes' => 'Met at the tech conference in March.'
                ]
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '555-987-6543',
                'gender' => 'female',
                'status' => 'active',
                'custom_fields' => [
                    'birthday' => '1990-02-28',
                    'company_name' => 'Tech Innovators',
                    'job_title' => 'Software Engineer',
                    'address' => '456 Oak Ave, Somewhere, USA',
                    'notes' => 'Referred by John Doe.'
                ]
            ],
            [
                'name' => 'Robert Johnson',
                'email' => 'robert.johnson@example.com',
                'phone' => '555-456-7890',
                'gender' => 'male',
                'status' => 'active',
                'custom_fields' => [
                    'company_name' => 'Global Solutions',
                    'job_title' => 'CEO',
                    'website' => 'www.globalsolutions.com'
                ]
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emily.davis@example.com',
                'phone' => '555-789-0123',
                'gender' => 'female',
                'status' => 'active',
                'custom_fields' => [
                    'birthday' => '1988-11-12',
                    'company_name' => 'Creative Designs',
                    'job_title' => 'Art Director',
                    'address' => '789 Pine St, Elsewhere, USA'
                ]
            ],
            [
                'name' => 'Michael Wilson',
                'email' => 'michael.wilson@example.com',
                'phone' => '555-321-6547',
                'gender' => 'male',
                'status' => 'active',
                'custom_fields' => [
                    'company_name' => 'Wilson Consulting',
                    'job_title' => 'Consultant',
                    'notes' => 'Prefers to be contacted via email.'
                ]
            ]
        ];

        // Get all custom fields
        $customFields = CustomField::all()->keyBy('name');

        foreach ($contacts as $contactData) {
            $customFieldsData = $contactData['custom_fields'] ?? [];
            unset($contactData['custom_fields']);

            // Create contact
            $contact = Contact::create($contactData);

            // Add custom field values
            foreach ($customFieldsData as $fieldName => $value) {
                if (isset($customFields[$fieldName])) {
                    ContactCustomFieldValue::create([
                        'contact_id' => $contact->id,
                        'custom_field_id' => $customFields[$fieldName]->id,
                        'value' => $value
                    ]);
                }
            }
        }
    }
}
