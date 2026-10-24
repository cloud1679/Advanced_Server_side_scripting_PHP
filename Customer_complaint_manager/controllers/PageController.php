<?php

require_once __DIR__ . '/../models/CustomerDB.php';
require_once __DIR__ . '/../models/EmployeeDB.php';
require_once __DIR__ . '/../models/ProductDB.php';
require_once __DIR__ . '/../models/ComplaintCategoryDB.php';
require_once __DIR__ . '/../models/ComplaintDB.php';

class PageController
{
    public function handleRequest()
    {
        $pages = [
            'login' => 'Customer Complaint Manager',
            'register' => 'Create Customer Account',
            'reset-password' => 'Reset Password',

            'customer-dashboard' => 'Customer Dashboard',
            'profile' => 'My Profile',
            'new-complaint' => 'Submit a Complaint',
            'my-complaints' => 'My Complaints',
            'complaint-details' => 'Complaint Details',

            'technician-dashboard' => 'Technician Dashboard',
            'technician' => 'Assigned Complaints',
            'update-complaint' => 'Update Complaint',
            'change-password' => 'Change Password',

            'admin-dashboard' => 'Administrator Dashboard',
            'customers' => 'Manage Customers',
            'customer-edit' => 'Update Customer',
            'employees' => 'Manage Employees',
            'employee-form' => 'Employee Information',
            'open-complaints' => 'Open Complaints',
            'unassigned' => 'Unassigned Complaints',
            'workload' => 'Technician Workload',
            'assign' => 'Assign Complaint'
        ];

        $page = $_GET['page'] ?? 'login';

        if (!is_string($page) || !isset($pages[$page])) {
            $page = 'login';
        }

        $title = $pages[$page];

        $editingEmployee = $page === 'employee-form'
            && ($_GET['mode'] ?? '') === 'edit';

        $isPost = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';

        $errors = [];
        $productOptions = [];
        $categoryOptions = [];
        $complaintChoicesAvailable = true;

        if ($page === 'new-complaint') {
            try {
                $productDB = new ProductDB();
                $categoryDB = new ComplaintCategoryDB();

                foreach ($productDB->getAll() as $product) {
                    $productOptions[$product->getProductId()] =
                        $product->getProductName();
                }

                foreach ($categoryDB->getAll() as $category) {
                    $categoryOptions[$category->getCategoryId()] =
                        $category->getCategoryName();
                }

                if (!$productOptions || !$categoryOptions) {
                    $complaintChoicesAvailable = false;
                    $errors[] =
                        'Products and complaint categories must be added '
                        . 'to the database before entering a complaint.';
                }
            } catch (PDOException $exception) {
                error_log($exception->getMessage());

                $complaintChoicesAvailable = false;
                $errors[] =
                    'Products and complaint categories could not be loaded. '
                    . 'Check the database connection and PHP error log.';
            }
        }

        if ($isPost) {
            $errors = array_merge(
                $errors,
                validateForm(
                    $page,
                    $_POST,
                    $editingEmployee,
                    $productOptions,
                    $categoryOptions
                )
            );
        }

        // Temporary sample customer for My Profile and the dashboard.
        // Administrator customer management uses MySQL below.
        $customer = [
            'first_name' => 'Alex',
            'last_name' => 'Morgan',
            'email' => 'alex@example.com',
            'street_address' => '123 Example Lane',
            'city' => 'Virginia Beach',
            'state' => 'VA',
            'zip_code' => '23456',
            'phone' => '757-555-0100'
        ];

        // Complaint screens remain sample-based for now.
        $complaints = [
            [
                'id' => '1001',
                'customer' => 'Alex Morgan',
                'product' => 'Laptop Computer',
                'category' => 'Product Defect',
                'status' => 'Open',
                'technician' => 'Taylor Reed',
                'description' =>
                'The laptop shuts down unexpectedly during use.',
                'notes' => 'The reported issue is being reviewed.'
            ],
            [
                'id' => '1002',
                'customer' => 'Jamie Lee',
                'product' => 'Network Setup',
                'category' => 'Service Issue',
                'status' => 'Open',
                'technician' => 'Unassigned',
                'description' =>
                'The network connection drops several times each day.',
                'notes' => 'No technician notes have been added.'
            ]
        ];

        $requestedId = $_GET['id'] ?? '1001';
        $selectedComplaint = $complaints[0];

        foreach ($complaints as $complaint) {
            if ($complaint['id'] === $requestedId) {
                $selectedComplaint = $complaint;
                break;
            }
        }

        $customerComplaints = [$complaints[0]];
        $assignedComplaints = [$complaints[0]];
        $unassignedComplaints = [$complaints[1]];

        // Temporary choices until complaint integration is completed.
        $technicians = [
            '1' => 'Taylor Reed',
            '2' => 'Jordan Smith'
        ];

        $workloads = [
            ['name' => 'Taylor Reed', 'count' => 1],
            ['name' => 'Jordan Smith', 'count' => 0]
        ];

        $complaintOptions = [];

        foreach ($complaints as $complaint) {
            $complaintOptions[$complaint['id']] =
                '#' . $complaint['id'] . ' — ' . $complaint['product'];
        }

        $customerLinks = [
            'customer-dashboard' => 'Customer Dashboard',
            'profile' => 'My Profile',
            'new-complaint' => 'Submit Complaint',
            'my-complaints' => 'My Complaints'
        ];

        $technicianLinks = [
            'technician-dashboard' => 'Technician Dashboard',
            'technician' => 'Assigned Complaints',
            'change-password' => 'Change Password'
        ];

        $adminLinks = [
            'admin-dashboard' => 'Administrator Dashboard',
            'customers' => 'Customers',
            'employees' => 'Employees',
            'open-complaints' => 'Open Complaints',
            'unassigned' => 'Unassigned Complaints',
            'workload' => 'Technician Workload',
            'assign' => 'Assign Complaint'
        ];

        $navigation = [];

        if (in_array($page, [
            'customer-dashboard',
            'profile',
            'new-complaint',
            'my-complaints',
            'complaint-details'
        ], true)) {
            $navigation = $customerLinks;
        }

        if (in_array($page, [
            'technician-dashboard',
            'technician',
            'update-complaint',
            'change-password'
        ], true)) {
            $navigation = $technicianLinks;
        }

        if (in_array($page, [
            'admin-dashboard',
            'customers',
            'customer-edit',
            'employees',
            'employee-form',
            'open-complaints',
            'unassigned',
            'workload',
            'assign'
        ], true)) {
            $navigation = $adminLinks;
        }

        $defaults = [];

        if ($page === 'profile') {
            $defaults = $customer;
        }

        if (in_array($page, ['login', 'reset-password'], true)) {
            $defaults['account_type'] = 'Customer';
        }

        if ($page === 'employee-form' && !$editingEmployee) {
            $defaults['employee_level'] = 'Technician';
        }

        if ($page === 'update-complaint') {
            $defaults['status'] = $selectedComplaint['status'];
        }

        if ($page === 'assign') {
            $defaults['complaint_id'] = $selectedComplaint['id'];
        }

        $values = $defaults;

        if ($isPost) {
            $values = [];

            foreach ($_POST as $key => $value) {
                if (is_string($value)) {
                    $values[$key] = $value;
                }
            }
        }

        // Variables supplied to the customer views.
        $customers = [];
        $customerRecord = null;
        $customerPageUnavailable = false;

        // Customer database operations.
        if (in_array($page, [
            'register',
            'customers',
            'customer-edit'
        ], true)) {
            try {
                $customerDB = new CustomerDB();

                if ($page === 'customers') {
                    $customers = $customerDB->getAll();
                }

                if ($page === 'customer-edit') {
                    $requestedCustomerId = $_GET['id'] ?? null;

                    $customerId = is_string($requestedCustomerId)
                        ? filter_var(
                            $requestedCustomerId,
                            FILTER_VALIDATE_INT,
                            ['options' => ['min_range' => 1]]
                        )
                        : false;

                    if ($customerId === false) {
                        $customerPageUnavailable = true;
                        $errors[] =
                            'Select a customer from the customer list.';
                    } else {
                        $customerRecord =
                            $customerDB->getById($customerId);

                        if ($customerRecord === null) {
                            $customerPageUnavailable = true;
                            $errors[] =
                                'The selected customer was not found.';
                        } elseif (!$isPost) {
                            $values = $customerRecord->toFormArray();
                        }
                    }
                }

                if (
                    $isPost &&
                    !$errors &&
                    !$customerPageUnavailable &&
                    in_array($page, [
                        'register',
                        'customer-edit'
                    ], true)
                ) {

                    $record = $page === 'register'
                        ? new Customer()
                        : $customerRecord;

                    $record->setFirstName($values['first_name']);
                    $record->setLastName($values['last_name']);
                    $record->setEmail($values['email']);
                    $record->setStreetAddress($values['street_address']);
                    $record->setCity($values['city']);
                    $record->setState($values['state']);
                    $record->setZipCode($values['zip_code']);
                    $record->setPhone($values['phone']);

                    if ($customerDB->emailExists(
                        $record->getEmail(),
                        $record->getCustomerId()
                    )) {
                        $errors[] =
                            'That email address is already registered.';
                    } elseif ($page === 'register') {
                        $record->setPasswordHash(
                            password_hash(
                                $values['new_password'],
                                PASSWORD_DEFAULT
                            )
                        );

                        $customerDB->insert($record);

                        header('Location: ?page=login&registered=1');
                        exit;
                    } else {
                        $customerDB->update($record);

                        header('Location: ?page=customers&updated=1');
                        exit;
                    }
                }
            } catch (PDOException $exception) {
                error_log($exception->getMessage());

                if (
                    $exception->getCode() === '23000' &&
                    (int) ($exception->errorInfo[1] ?? 0) === 1062
                ) {
                    $errors[] =
                        'That email address is already registered.';
                } else {
                    $customerPageUnavailable = true;
                    $errors[] =
                        'Customer information could not be loaded or saved. '
                        . 'Check the database connection and PHP error log.';
                }
            }
        }

        // Variables supplied to the employee views.
        $employees = [];
        $employeeRecord = null;
        $employeePageUnavailable = false;

        // Employee database operations.
        if (in_array($page, [
            'employees',
            'employee-form'
        ], true)) {
            try {
                $employeeDB = new EmployeeDB();

                if ($page === 'employees') {
                    $employees = $employeeDB->getAll();
                }

                if ($page === 'employee-form') {
                    if ($editingEmployee) {
                        $requestedUserId = $_GET['employee'] ?? '';

                        if (
                            !is_string($requestedUserId) ||
                            trim($requestedUserId) === ''
                        ) {
                            $employeePageUnavailable = true;
                            $errors[] =
                                'Select an employee from the employee list.';
                        } else {
                            $employeeRecord =
                                $employeeDB->getByUserId($requestedUserId);

                            if ($employeeRecord === null) {
                                $employeePageUnavailable = true;
                                $errors[] =
                                    'The selected employee was not found.';
                            } else {
                                if (!$isPost) {
                                    $values =
                                        $employeeRecord->toFormArray();
                                }

                                if (
                                    $isPost &&
                                    ($_POST['user_id'] ?? '') !==
                                    $employeeRecord->getUserId()
                                ) {
                                    $errors[] =
                                        'An existing employee User ID '
                                        . 'cannot be changed.';
                                }

                                $values['user_id'] =
                                    $employeeRecord->getUserId();
                            }
                        }
                    }

                    if (
                        $isPost &&
                        !$errors &&
                        !$employeePageUnavailable
                    ) {

                        $record = $editingEmployee
                            ? $employeeRecord
                            : new Employee();

                        if (!$editingEmployee) {
                            $record->setUserId($values['user_id']);
                        }

                        $record->setFirstName($values['first_name']);
                        $record->setLastName($values['last_name']);
                        $record->setEmail($values['email']);

                        $record->setPhoneExtension(
                            $values['phone_extension'] ?? ''
                        );

                        $record->setEmployeeLevel(
                            $values['employee_level']
                        );

                        if (
                            !$editingEmployee &&
                            $employeeDB->userIdExists(
                                $record->getUserId()
                            )
                        ) {
                            $errors[] =
                                'That employee User ID already exists.';
                        }

                        if ($employeeDB->emailExists(
                            $record->getEmail(),
                            $record->getEmployeeId()
                        )) {
                            $errors[] =
                                'That employee email address already exists.';
                        }

                        if (!$errors) {
                            if ($editingEmployee) {
                                $employeeDB->update($record);
                            } else {
                                $record->setPasswordHash(
                                    password_hash(
                                        $values['new_password'],
                                        PASSWORD_DEFAULT
                                    )
                                );

                                $employeeDB->insert($record);
                            }

                            header('Location: ?page=employees&saved=1');
                            exit;
                        }
                    }
                }
            } catch (PDOException $exception) {
                error_log($exception->getMessage());

                if (
                    $exception->getCode() === '23000' &&
                    (int) ($exception->errorInfo[1] ?? 0) === 1062
                ) {
                    $errors[] =
                        'The employee User ID or email address already exists.';
                } else {
                    $employeePageUnavailable = true;
                    $errors[] =
                        'Employee information could not be loaded or saved. '
                        . 'Check the database connection and PHP error log.';
                }
            }
        }

        // Complaint submission and customer complaint viewing.
        $complaintPageUnavailable = false;
        $customerOptions = [];
        $selectedCustomerId = '';

        if (in_array($page, [
            'new-complaint',
            'my-complaints',
            'complaint-details'
        ], true)) {
            // Prevent sample records appearing on database-connected pages.
            $customerComplaints = [];
            $selectedComplaint = null;

            try {
                $customerDB = new CustomerDB();
                $complaintDB = new ComplaintDB();

                foreach ($customerDB->getAll() as $customerObject) {
                    $customerOptions[$customerObject->getCustomerId()] =
                        $customerObject->getFullName()
                        . ' — ' . $customerObject->getEmail();
                }

                if ($page === 'new-complaint') {
                    if (!$customerOptions) {
                        $complaintPageUnavailable = true;
                        $errors[] =
                            'Create a customer account before submitting a complaint.';
                    }

                    if ($isPost) {
                        $submittedCustomerId = $_POST['customer_id'] ?? '';

                        $selectedCustomerId = is_string($submittedCustomerId)
                            ? $submittedCustomerId
                            : '';

                        $allowedCustomerIds = array_map(
                            'strval',
                            array_keys($customerOptions)
                        );

                        if (!in_array(
                            $selectedCustomerId,
                            $allowedCustomerIds,
                            true
                        )) {
                            $errors[] = 'Select a valid test customer.';
                        }

                        if (
                            !$errors &&
                            !$complaintPageUnavailable &&
                            $complaintChoicesAvailable
                        ) {

                            $complaint = new Complaint();

                            $complaint->setCustomerId($selectedCustomerId);
                            $complaint->setProductId($values['product_id']);
                            $complaint->setCategoryId($values['category_id']);
                            $complaint->setDescription($values['description']);

                            $complaintDB->insert($complaint);

                            header(
                                'Location: ?page=my-complaints&customer_id='
                                    . rawurlencode($selectedCustomerId)
                                    . '&saved=1'
                            );
                            exit;
                        }
                    }
                }

                if ($page === 'my-complaints') {
                    $requestedCustomerId = $_GET['customer_id'] ?? '';

                    $selectedCustomerId = is_string($requestedCustomerId)
                        ? $requestedCustomerId
                        : '';

                    if ($selectedCustomerId !== '') {
                        $allowedCustomerIds = array_map(
                            'strval',
                            array_keys($customerOptions)
                        );

                        if (!in_array(
                            $selectedCustomerId,
                            $allowedCustomerIds,
                            true
                        )) {
                            $errors[] = 'Select a valid test customer.';
                            $selectedCustomerId = '';
                        } else {
                            $customerComplaints =
                                $complaintDB->getByCustomerId($selectedCustomerId);
                        }
                    } elseif (
                        isset($_GET['customer_id']) &&
                        !is_string($_GET['customer_id'])
                    ) {
                        $errors[] = 'Select a valid test customer.';
                    }
                }

                if ($page === 'complaint-details') {
                    $requestedComplaintId = $_GET['id'] ?? null;

                    $complaintId = is_string($requestedComplaintId)
                        ? filter_var(
                            $requestedComplaintId,
                            FILTER_VALIDATE_INT,
                            ['options' => ['min_range' => 1]]
                        )
                        : false;

                    if ($complaintId === false) {
                        $complaintPageUnavailable = true;
                        $errors[] = 'Select a complaint from the complaint list.';
                    } else {
                        $selectedComplaint = $complaintDB->getById($complaintId);

                        if ($selectedComplaint === null) {
                            $complaintPageUnavailable = true;
                            $errors[] = 'The selected complaint was not found.';
                        }
                    }
                }
            } catch (PDOException $exception) {
                error_log($exception->getMessage());

                $complaintPageUnavailable = true;
                $errors[] =
                    'Complaint information could not be loaded or saved. '
                    . 'Check the database connection and PHP error log.';
            }
        }

        // Administrator complaint management.
        $adminComplaintPageUnavailable = false;

        if (in_array($page, [
            'open-complaints',
            'unassigned',
            'assign',
            'workload'
        ], true)) {
            // Remove sample data from these database-connected pages.
            $complaints = [];
            $unassignedComplaints = [];
            $complaintOptions = [];
            $technicians = [];
            $workloads = [];

            try {
                $complaintDB = new ComplaintDB();

                if ($page === 'open-complaints') {
                    $complaints = $complaintDB->getOpen();
                }

                if ($page === 'unassigned') {
                    $unassignedComplaints = $complaintDB->getUnassigned();
                }

                if ($page === 'workload') {
                    $workloads = $complaintDB->getTechnicianWorkload();
                }

                if ($page === 'assign') {
                    foreach ($complaintDB->getOpen() as $complaintRow) {
                        $complaintOptions[$complaintRow['id']] =
                            '#' . $complaintRow['id']
                            . ' — ' . $complaintRow['customer']
                            . ' — ' . $complaintRow['product']
                            . ' — Assigned: ' . $complaintRow['technician'];
                    }

                    foreach ($complaintDB->getTechnicians() as $technicianRow) {
                        $technicians[$technicianRow['employee_id']] =
                            $technicianRow['name']
                            . ' (' . $technicianRow['user_id'] . ')';
                    }

                    // Preselect the complaint when arriving from an Assign link.
                    if (!$isPost) {
                        $values['complaint_id'] = '';

                        $requestedComplaintId = $_GET['id'] ?? '';

                        if (
                            is_string($requestedComplaintId) &&
                            $requestedComplaintId !== ''
                        ) {
                            $allowedComplaintIds = array_map(
                                'strval',
                                array_keys($complaintOptions)
                            );

                            if (in_array(
                                $requestedComplaintId,
                                $allowedComplaintIds,
                                true
                            )) {
                                $values['complaint_id'] = $requestedComplaintId;
                            } else {
                                $errors[] =
                                    'That complaint is not available for assignment.';
                            }
                        } elseif (
                            isset($_GET['id']) &&
                            !is_string($_GET['id'])
                        ) {
                            $errors[] = 'Select a valid complaint.';
                        }
                    }

                    if ($isPost && !$errors) {
                        $submittedComplaintId = $values['complaint_id'] ?? '';
                        $submittedTechnicianId = $values['technician_id'] ?? '';

                        $allowedComplaintIds = array_map(
                            'strval',
                            array_keys($complaintOptions)
                        );

                        $allowedTechnicianIds = array_map(
                            'strval',
                            array_keys($technicians)
                        );

                        if (!in_array(
                            $submittedComplaintId,
                            $allowedComplaintIds,
                            true
                        )) {
                            $errors[] = 'Select an existing open complaint.';
                        }

                        if (!in_array(
                            $submittedTechnicianId,
                            $allowedTechnicianIds,
                            true
                        )) {
                            $errors[] = 'Select an existing technician.';
                        }

                        if (!$errors) {
                            $complaintDB->assignTechnician(
                                $submittedComplaintId,
                                $submittedTechnicianId
                            );

                            header('Location: ?page=open-complaints&assigned=1');
                            exit;
                        }
                    }
                }
            } catch (InvalidArgumentException $exception) {
                $errors[] = $exception->getMessage();
            } catch (PDOException $exception) {
                error_log($exception->getMessage());

                $adminComplaintPageUnavailable = true;
                $errors[] =
                    'Complaint information could not be loaded or saved. '
                    . 'Check the database connection and PHP error log.';
            }
        }

        // Technician complaint viewing and updates.
        $technicianComplaintPageUnavailable = false;
        $technicianOptions = [];
        $selectedTechnicianId = '';

        if (in_array($page, [
            'technician',
            'update-complaint'
        ], true)) {
            // Replace sample data on these pages.
            $assignedComplaints = [];
            $selectedComplaint = null;

            try {
                $complaintDB = new ComplaintDB();

                foreach ($complaintDB->getTechnicians() as $technicianRow) {
                    $technicianOptions[$technicianRow['employee_id']] =
                        $technicianRow['name']
                        . ' (' . $technicianRow['user_id'] . ')';
                }

                // Temporary technician selection until login is implemented.
                $requestedTechnicianId = $_GET['technician_id'] ?? '';

                if (is_string($requestedTechnicianId)) {
                    $selectedTechnicianId = $requestedTechnicianId;
                }

                $allowedTechnicianIds = array_map(
                    'strval',
                    array_keys($technicianOptions)
                );

                if (
                    $selectedTechnicianId !== '' &&
                    !in_array(
                        $selectedTechnicianId,
                        $allowedTechnicianIds,
                        true
                    )
                ) {
                    $errors[] = 'Select a valid test technician.';
                    $selectedTechnicianId = '';
                } elseif (
                    isset($_GET['technician_id']) &&
                    !is_string($_GET['technician_id'])
                ) {
                    $errors[] = 'Select a valid test technician.';
                }

                if (
                    $page === 'technician' &&
                    $selectedTechnicianId !== ''
                ) {
                    $assignedComplaints =
                        $complaintDB->getByTechnicianId($selectedTechnicianId);
                }

                if ($page === 'update-complaint') {
                    $requestedComplaintId = $_GET['id'] ?? null;

                    $complaintId = is_string($requestedComplaintId)
                        ? filter_var(
                            $requestedComplaintId,
                            FILTER_VALIDATE_INT,
                            ['options' => ['min_range' => 1]]
                        )
                        : false;

                    if ($selectedTechnicianId === '') {
                        $technicianComplaintPageUnavailable = true;
                        $errors[] =
                            'Select a technician from Assigned Complaints first.';
                    } elseif ($complaintId === false) {
                        $technicianComplaintPageUnavailable = true;
                        $errors[] = 'Select a valid complaint.';
                    } else {
                        $selectedComplaint =
                            $complaintDB->getById($complaintId);

                        if (
                            $selectedComplaint === null ||
                            (int) $selectedComplaint['technician_id'] !==
                            (int) $selectedTechnicianId ||
                            $selectedComplaint['status'] !== 'Open'
                        ) {
                            $technicianComplaintPageUnavailable = true;
                            $errors[] =
                                'This complaint is closed or is not assigned '
                                . 'to the selected technician.';
                        } elseif (!$isPost) {
                            $values = [
                                'status' => $selectedComplaint['status'],
                                'resolution_date' => '',
                                'resolution_notes' => '',
                                'technician_notes' => ''
                            ];
                        }

                        if (
                            $isPost &&
                            !$errors &&
                            !$technicianComplaintPageUnavailable
                        ) {
                            $complaintDB->saveTechnicianUpdate(
                                $complaintId,
                                $selectedTechnicianId,
                                $values['technician_notes'] ?? '',
                                $values['status'],
                                $values['resolution_date'] ?? '',
                                $values['resolution_notes'] ?? ''
                            );

                            header(
                                'Location: ?page=technician&technician_id='
                                    . rawurlencode($selectedTechnicianId)
                                    . '&saved=1'
                            );
                            exit;
                        }
                    }
                }
            } catch (InvalidArgumentException $exception) {
                $errors[] = $exception->getMessage();
            } catch (PDOException $exception) {
                error_log($exception->getMessage());

                $technicianComplaintPageUnavailable = true;
                $errors[] =
                    'Technician complaint information could not be loaded '
                    . 'or saved. Check the PHP error log.';
            }
        }

        // Customer profile viewing and saving.
        $profilePageUnavailable = false;
        $profileCustomerOptions = [];
        $profileCustomerId = '';
        $profileCustomerRecord = null;

        if ($page === 'profile') {
            // Remove the sample customer values from this page.
            if (!$isPost) {
                $values = [];
            }

            try {
                $customerDB = new CustomerDB();

                foreach ($customerDB->getAll() as $profileCustomer) {
                    $profileCustomerOptions[$profileCustomer->getCustomerId()] =
                        $profileCustomer->getFullName()
                        . ' — ' . $profileCustomer->getEmail();
                }

                $requestedProfileId = $_GET['customer_id'] ?? '';

                if (is_string($requestedProfileId)) {
                    $profileCustomerId = $requestedProfileId;
                }

                $allowedProfileIds = array_map(
                    'strval',
                    array_keys($profileCustomerOptions)
                );

                if ($profileCustomerId !== '') {
                    if (!in_array(
                        $profileCustomerId,
                        $allowedProfileIds,
                        true
                    )) {
                        $profilePageUnavailable = true;
                        $errors[] = 'Select an existing customer.';
                    } else {
                        $profileCustomerRecord =
                            $customerDB->getById($profileCustomerId);

                        if ($profileCustomerRecord === null) {
                            $profilePageUnavailable = true;
                            $errors[] = 'The selected customer was not found.';
                        } elseif (!$isPost) {
                            $values = $profileCustomerRecord->toFormArray();
                        }
                    }
                } elseif (
                    $isPost ||
                    (isset($_GET['customer_id']) &&
                        !is_string($_GET['customer_id']))
                ) {
                    $profilePageUnavailable = true;
                    $errors[] = 'Select a customer before editing a profile.';
                }

                if (
                    $isPost &&
                    !$errors &&
                    !$profilePageUnavailable &&
                    $profileCustomerRecord !== null
                ) {

                    $profileCustomerRecord->setFirstName($values['first_name']);
                    $profileCustomerRecord->setLastName($values['last_name']);
                    $profileCustomerRecord->setEmail($values['email']);
                    $profileCustomerRecord->setStreetAddress(
                        $values['street_address']
                    );
                    $profileCustomerRecord->setCity($values['city']);
                    $profileCustomerRecord->setState($values['state']);
                    $profileCustomerRecord->setZipCode($values['zip_code']);
                    $profileCustomerRecord->setPhone($values['phone']);

                    if ($customerDB->emailExists(
                        $profileCustomerRecord->getEmail(),
                        $profileCustomerRecord->getCustomerId()
                    )) {
                        $errors[] = 'That email address is already registered.';
                    } else {
                        $customerDB->update($profileCustomerRecord);

                        header(
                            'Location: ?page=profile&customer_id='
                                . rawurlencode($profileCustomerId)
                                . '&saved=1'
                        );
                        exit;
                    }
                }
            } catch (PDOException $exception) {
                error_log($exception->getMessage());

                if (
                    $exception->getCode() === '23000' &&
                    (int) ($exception->errorInfo[1] ?? 0) === 1062
                ) {
                    $errors[] = 'That email address is already registered.';
                } else {
                    $profilePageUnavailable = true;
                    $errors[] =
                        'Customer profile information could not be loaded or saved. '
                        . 'Check the database connection and PHP error log.';
                }
            }
        }

        $viewFiles = [
            'login' => 'login.php',
            'register' => 'register.php',
            'reset-password' => 'reset-password.php',

            'customer-dashboard' => 'customer-dashboard.php',
            'profile' => 'profile.php',
            'new-complaint' => 'new-complaint.php',
            'my-complaints' => 'my-complaints.php',
            'complaint-details' => 'complaint-details.php',

            'technician-dashboard' => 'technician-dashboard.php',
            'technician' => 'assigned-complaints.php',
            'update-complaint' => 'update-complaint.php',
            'change-password' => 'change-password.php',

            'admin-dashboard' => 'admin-dashboard.php',
            'customers' => 'customers.php',
            'customer-edit' => 'customer-edit.php',
            'employees' => 'employees.php',
            'employee-form' => 'employee-form.php',
            'open-complaints' => 'open-complaints.php',
            'unassigned' => 'unassigned-complaints.php',
            'workload' => 'workload.php',
            'assign' => 'assign.php'
        ];

        $viewFile = __DIR__ . '/../views/' . $viewFiles[$page];

        require __DIR__ . '/../views/page.php';
    }
}
