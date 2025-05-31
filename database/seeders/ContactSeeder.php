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
                'name' => 'Raj Tiwari',
                'email' => 'rajtiwariyng@gmail.com',
                'phone' => '7827795345',
                'gender' => 'male',
                'status' => 'active',
                'custom_fields' => [
                    'birthday' => '1992-06-15',
                    'company_name' => 'Acme Corporation',
                    'job_title' => 'Marketing Director',
                    'address' => '123 Main St, Delhi, IN',
                    'website' => 'www.rajtiwariyng.com',
                    'notes' => 'Met at the tech conference in March.'
                ]
            ],
            [
                'name' => 'Menka Tiwari',
                'email' => 'menka@gmail.com',
                'phone' => '9319927634',
                'gender' => 'female',
                'status' => 'active',
                'custom_fields' => [
                    'birthday' => '1996-02-28',
                    'company_name' => 'Tech Innovators',
                    'job_title' => 'Software Engineer',
                    'address' => '456 Buxar, Bihar, IN',
                    'notes' => 'Referred by Raj Tiwari.'
                ]
            ],
            [
                'name' => 'Reyansh Tiwari',
                'email' => 'reyansh@gmail.com',
                'phone' => '9806050001',
                'gender' => 'male',
                'status' => 'active',
                'custom_fields' => [
                    'company_name' => 'Reyansh Solutions',
                    'job_title' => 'CEO',
                    'website' => 'www.reyansh.com'
                ]
            ],
            [
                'name' => 'Sapna Singh',
                'email' => 'sapnasingh@gmail.com',
                'phone' => '785202587',
                'gender' => 'female',
                'status' => 'active',
                'custom_fields' => [
                    'birthday' => '1988-11-12',
                    'company_name' => 'Creative Designs',
                    'job_title' => 'Art Director',
                    'address' => 'juhu, Mubmai, IN'
                ]
            ],
            [
                'name' => 'Vivek Yadav',
                'email' => 'vivekyadav@gmail.com.com',
                'phone' => '7854525458',
                'gender' => 'male',
                'status' => 'active',
                'custom_fields' => [
                    'company_name' => 'Yadav Consulting',
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
