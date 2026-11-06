<?php

const ACCOUNT_TYPES = [
    'Customer',
    'Technician',
    'Administrator'
];

const STATES = [
    'AL',
    'AK',
    'AZ',
    'AR',
    'CA',
    'CO',
    'CT',
    'DE',
    'DC',
    'FL',
    'GA',
    'HI',
    'ID',
    'IL',
    'IN',
    'IA',
    'KS',
    'KY',
    'LA',
    'ME',
    'MD',
    'MA',
    'MI',
    'MN',
    'MS',
    'MO',
    'MT',
    'NE',
    'NV',
    'NH',
    'NJ',
    'NM',
    'NY',
    'NC',
    'ND',
    'OH',
    'OK',
    'OR',
    'PA',
    'RI',
    'SC',
    'SD',
    'TN',
    'TX',
    'UT',
    'VT',
    'VA',
    'WA',
    'WV',
    'WI',
    'WY'
];

function validateForm(
    $page,
    $data,
    $editingEmployee = false,
    array $productOptions = [],
    array $categoryOptions = []
) {
    $errors = [];

    $get = function ($field) use ($data) {
        $value = $data[$field] ?? '';

        return is_string($value) ? $value : '';
    };

    $text = function (
        $field,
        $label,
        $max,
        $required = true
    ) use ($data, $get, &$errors) {
        if (
            array_key_exists($field, $data) &&
            !is_string($data[$field])
        ) {
            $errors[] = "$label is invalid.";
            return '';
        }

        $value = trim($get($field));

        $length = function_exists('mb_strlen')
            ? mb_strlen($value, 'UTF-8')
            : strlen($value);

        if ($required && $value === '') {
            $errors[] = "$label is required.";
        }

        if ($length > $max) {
            $errors[] = "$label cannot exceed $max characters.";
        }

        return $value;
    };

    $choice = function (
        $field,
        $label,
        array $allowed
    ) use ($get, &$errors) {
        if (!in_array($get($field), $allowed, true)) {
            $errors[] = "Select a valid $label.";
        }
    };

    $email = function () use ($text, &$errors) {
        $value = $text('email', 'Email address', 150);

        if (
            $value !== '' &&
            !filter_var($value, FILTER_VALIDATE_EMAIL)
        ) {
            $errors[] = 'Enter a valid email address.';
        }
    };

    // Passwords are checked without trimming their contents.
    $existingPassword = function (
        $field,
        $label
    ) use ($data, $get, &$errors) {
        if (
            !isset($data[$field]) ||
            !is_string($data[$field]) ||
            $get($field) === ''
        ) {
            $errors[] = "$label is required.";
            return;
        }

        if (
            strlen($get($field)) > 72 ||
            strpos($get($field), "\0") !== false
        ) {
            $errors[] = "$label is invalid.";
        }
    };

    $newPassword = function (
        $field
    ) use ($get, &$errors) {
        $value = $get($field);

        if (
            strlen($value) < 8 ||
            strlen($value) > 72 ||
            strpos($value, "\0") !== false ||
            !preg_match('/[A-Z]/', $value) ||
            !preg_match('/[a-z]/', $value) ||
            !preg_match('/[0-9]/', $value) ||
            !preg_match('/[^a-zA-Z0-9]/', $value)
        ) {
            $errors[] =
                'New passwords must contain 8–72 characters, '
                . 'including uppercase and lowercase letters, '
                . 'a number, and a special character. '
                . 'The maximum length is 72 bytes.';
        }
    };

    $confirmPassword = function () use (
        $get,
        $existingPassword,
        &$errors
    ) {
        $existingPassword(
            'confirm_password',
            'Password confirmation'
        );

        if ($get('new_password') !== $get('confirm_password')) {
            $errors[] = 'The new passwords do not match.';
        }
    };

    switch ($page) {
        case 'register':
        case 'profile':
        case 'customer-edit':
            $text('first_name', 'First name', 50);
            $text('last_name', 'Last name', 50);
            $email();

            $text('street_address', 'Street address', 150);
            $text('city', 'City', 75);
            $choice('state', 'state', STATES);

            $zip = $text('zip_code', 'ZIP code', 10);

            if (
                $zip !== '' &&
                !preg_match('/^\d{5}(-\d{4})?$/', $zip)
            ) {
                $errors[] =
                    'Use a ZIP code such as 23456 or 23456-1234.';
            }

            $phone = $text('phone', 'Phone number', 20);

            if (
                $phone !== '' &&
                !preg_match(
                    '/^(?:\+?1[ .-]?)?(?:\(\d{3}\)|\d{3})[ .-]?\d{3}[ .-]?\d{4}$/',
                    $phone
                )
            ) {
                $errors[] =
                    'Enter a valid 10-digit US phone number.';
            }

            if ($page === 'register') {
                $newPassword('new_password');
            }
            break;

        case 'login':
            $choice(
                'account_type',
                'account type',
                ACCOUNT_TYPES
            );

            $login = $text(
                'login_id',
                'Customer email or employee User ID',
                150
            );

            if ($get('account_type') === 'Customer') {
                if (
                    $login !== '' &&
                    !filter_var($login, FILTER_VALIDATE_EMAIL)
                ) {
                    $errors[] =
                        'Customers must enter a valid email address.';
                }
            } elseif (strlen($login) > 50) {
                $errors[] =
                    'Employee User IDs cannot exceed 50 characters.';
            }

            $existingPassword('password', 'Password');
            break;

        case 'new-complaint':
            $choice(
                'product_id',
                'product or service',
                array_map(
                    'strval',
                    array_keys($productOptions)
                )
            );

            $choice(
                'category_id',
                'complaint category',
                array_map(
                    'strval',
                    array_keys($categoryOptions)
                )
            );

            $text(
                'description',
                'Complaint description',
                5000
            );
            break;

        case 'update-complaint':
            $text(
                'technician_notes',
                'Technician notes',
                5000,
                false
            );

            $choice(
                'status',
                'status',
                ['Open', 'Closed']
            );

            $closed = $get('status') === 'Closed';

            $text(
                'resolution_notes',
                'Resolution notes',
                5000,
                $closed
            );

            $date = $text(
                'resolution_date',
                'Resolution date',
                10,
                $closed
            );

            if ($date !== '') {
                $valid = false;

                if (preg_match(
                    '/^(\d{4})-(\d{2})-(\d{2})$/',
                    $date,
                    $parts
                )) {
                    $valid = (int) $parts[1] >= 1000
                        && checkdate(
                            (int) $parts[2],
                            (int) $parts[3],
                            (int) $parts[1]
                        );
                }

                if (!$valid) {
                    $errors[] =
                        'Enter a valid resolution date.';
                }
            }
            break;

        case 'change-password':
            $existingPassword(
                'current_password',
                'Current or temporary password'
            );

            $newPassword('new_password');
            $confirmPassword();
            break;

        case 'employee-form':
            $text('user_id', 'User ID', 50);
            $text('first_name', 'First name', 50);
            $text('last_name', 'Last name', 50);
            $email();

            $employeePhone = $text(
                'phone_number',
                'Phone number',
                20,
                false
            );

            if (
                $employeePhone !== '' &&
                !preg_match(
                    '/^(?:\+?1[ .-]?)?(?:\(\d{3}\)|\d{3})[ .-]?\d{3}[ .-]?\d{4}$/',
                    $employeePhone
                )
            ) {
                $errors[] =
                    'Enter a valid 10-digit US phone number.';
            }

            $choice(
                'employee_level',
                'employee level',
                ['Technician', 'Administrator']
            );

            if (!$editingEmployee) {
                $newPassword('new_password');
                $confirmPassword();
            }
            break;

        case 'assign':
            $complaintId = $text(
                'complaint_id',
                'Complaint ID',
                10
            );

            $technicianId = $text(
                'technician_id',
                'Technician ID',
                10
            );

            if (
                $complaintId !== '' &&
                !preg_match(
                    '/^[1-9][0-9]{0,9}$/',
                    $complaintId
                )
            ) {
                $errors[] = 'Select a valid complaint.';
            }

            if (
                $technicianId !== '' &&
                !preg_match(
                    '/^[1-9][0-9]{0,9}$/',
                    $technicianId
                )
            ) {
                $errors[] = 'Select a valid technician.';
            }

            // The controller checks these IDs against MySQL.
            break;

            // Administrator password-reset confirmation is checked
            // directly in PageController against the signed-in admin.
    }

    return $errors;
}
