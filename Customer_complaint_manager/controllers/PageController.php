<?php

require_once __DIR__ . '/../models/CustomerDB.php';
require_once __DIR__ . '/../models/EmployeeDB.php';
require_once __DIR__ . '/../models/ProductDB.php';
require_once __DIR__ . '/../models/ComplaintCategoryDB.php';
require_once __DIR__ . '/../models/ComplaintDB.php';
require_once __DIR__ . '/../models/LoginAttemptDB.php';

class PageController
{
    private function redirect($page, array $parameters = [])
    {
        $query = http_build_query(
            array_merge(['page' => $page], $parameters)
        );

        header('Location: ?' . $query, true, 303);
        exit;
    }

    private function stopWithError($status, $message)
    {
        http_response_code($status);

        echo '<!DOCTYPE html>';
        echo '<html lang="en">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" ';
        echo 'content="width=device-width, initial-scale=1.0">';
        echo '<title>Customer Complaint Manager</title>';
        echo '</head><body><main>';
        echo '<h1>Customer Complaint Manager</h1>';
        echo '<p>' . e($message) . '</p>';
        echo '<p><a href="?page=login">Back to Login</a></p>';
        echo '</main></body></html>';

        exit;
    }

    private function dashboardFor($role)
    {
        $dashboards = [
            'Customer' => 'customer-dashboard',
            'Technician' => 'technician-dashboard',
            'Administrator' => 'admin-dashboard'
        ];

        return $dashboards[$role] ?? 'login';
    }

    private function positiveId($value)
    {
        if (!is_string($value)) {
            return false;
        }

        return filter_var(
            $value,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
    }

    private function clearAuthentication()
    {
        $_SESSION = [];

        session_regenerate_id(true);

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['last_activity'] = time();
    }

    private function signIn($account, $role)
    {
        $_SESSION = [];

        session_regenerate_id(true);

        $accountId = $role === 'Customer'
            ? $account->getCustomerId()
            : $account->getEmployeeId();

        $_SESSION['auth'] = [
            'account_id' => $accountId,
            'account_type' => $role,
            'password_fingerprint' => hash(
                'sha256',
                $account->getPasswordHash()
            )
        ];

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['last_activity'] = time();
    }

    private function signOut()
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $parameters = session_get_cookie_params();

            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $parameters['path'],
                'domain' => $parameters['domain'],
                'secure' => $parameters['secure'],
                'httponly' => $parameters['httponly'],
                'samesite' => $parameters['samesite'] ?? 'Lax'
            ]);
        }

        session_destroy();

        $this->redirect('login', ['logged_out' => 1]);
    }

    private function loadAuthenticatedAccount()
    {
        $auth = $_SESSION['auth'] ?? null;

        if ($auth === null) {
            return null;
        }

        if (
            !is_array($auth) ||
            !isset(
                $auth['account_id'],
                $auth['account_type'],
                $auth['password_fingerprint']
            ) ||
            !is_string($auth['account_type']) ||
            !is_string($auth['password_fingerprint']) ||
            !in_array(
                $auth['account_type'],
                ['Customer', 'Technician', 'Administrator'],
                true
            )
        ) {
            $this->clearAuthentication();
            return null;
        }

        $accountId = filter_var(
            $auth['account_id'],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($accountId === false) {
            $this->clearAuthentication();
            return null;
        }

        if ($auth['account_type'] === 'Customer') {
            $database = new CustomerDB();
            $account = $database->getById($accountId);
        } else {
            $database = new EmployeeDB();
            $account = $database->getById($accountId);

            if (
                $account !== null &&
                $account->getEmployeeLevel() !==
                $auth['account_type']
            ) {
                $account = null;
            }
        }

        // Check the current database record on every request.
        // Password or role changes invalidate existing authentication.
        if (
            $account === null ||
            !hash_equals(
                $auth['password_fingerprint'],
                hash('sha256', $account->getPasswordHash())
            )
        ) {
            $this->clearAuthentication();
            return null;
        }

        return $account;
    }

    private function fillCustomer(
        Customer $customer,
        array $values
    ) {
        $customer->setFirstName($values['first_name']);
        $customer->setLastName($values['last_name']);
        $customer->setEmail($values['email']);
        $customer->setStreetAddress($values['street_address']);
        $customer->setCity($values['city']);
        $customer->setState($values['state']);
        $customer->setZipCode($values['zip_code']);
        $customer->setPhone($values['phone']);
    }

    private function changeAccountPassword(
        $account,
        $role,
        $newPassword
    ) {
        $passwordHash = password_hash(
            $newPassword,
            PASSWORD_DEFAULT
        );

        if ($role === 'Customer') {
            $database = new CustomerDB();
            $accountId = $account->getCustomerId();
        } else {
            $database = new EmployeeDB();
            $accountId = $account->getEmployeeId();
        }

        // Save the new hash and clear the temporary-password flag.
        $database->updatePassword(
            $accountId,
            $passwordHash,
            false
        );

        $account->setPasswordHash($passwordHash);
        $account->setMustChangePassword(false);

        $this->signIn($account, $role);
    }

    private function generateTemporaryPassword()
    {
        // Random content plus the required character categories.
        return bin2hex(random_bytes(18)) . 'Aa1!';
    }

    public function handleRequest()
    {
        $pages = [
            'login' => 'Customer Complaint Manager',
            'logout' => 'Sign Out',
            'register' => 'Create Customer Account',

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
            'assign' => 'Assign Complaint',
            'admin-reset-password' => 'Reset Account Password'
        ];

        $page = $_GET['page'] ?? 'login';

        if (!is_string($page) || !isset($pages[$page])) {
            $page = 'login';
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (!in_array($method, ['GET', 'POST'], true)) {
            header('Allow: GET, POST');

            $this->stopWithError(
                405,
                'This request method is not supported.'
            );
        }

        $isPost = $method === 'POST';

        if ($isPost && !csrfTokenIsValid()) {
            $this->stopWithError(
                403,
                'The form expired or its security token was invalid. '
                    . 'Reload the page and try again.'
            );
        }

        if ($page === 'logout') {
            if (!$isPost) {
                header('Allow: POST');

                $this->stopWithError(
                    405,
                    'Use the Sign out button to end your session.'
                );
            }

            $this->signOut();
        }

        try {
            $currentUser = $this->loadAuthenticatedAccount();
        } catch (PDOException $exception) {
            error_log($exception->getMessage());

            $this->stopWithError(
                503,
                'The account service is temporarily unavailable.'
            );
        }

        $role = $currentUser !== null
            ? $_SESSION['auth']['account_type']
            : '';

        $currentUserName = $currentUser !== null
            ? $currentUser->getFullName()
            : '';

        $dashboardPage = $this->dashboardFor($role);

        $mustChangePassword = $currentUser !== null
            && $currentUser->getMustChangePassword();

        // Temporary-password users can only change their password
        // or sign out. Logout was handled above.
        if (
            $mustChangePassword &&
            $page !== 'change-password'
        ) {
            $this->redirect('change-password');
        }

        $permissions = [
            'customer-dashboard' => ['Customer'],
            'profile' => ['Customer'],
            'new-complaint' => ['Customer'],
            'my-complaints' => ['Customer'],
            'complaint-details' => ['Customer'],

            'technician-dashboard' => ['Technician'],
            'technician' => ['Technician'],
            'update-complaint' => ['Technician'],

            'admin-dashboard' => ['Administrator'],
            'customers' => ['Administrator'],
            'customer-edit' => ['Administrator'],
            'employees' => ['Administrator'],
            'employee-form' => ['Administrator'],
            'open-complaints' => ['Administrator'],
            'unassigned' => ['Administrator'],
            'workload' => ['Administrator'],
            'assign' => ['Administrator'],
            'admin-reset-password' => ['Administrator'],

            'change-password' => [
                'Customer',
                'Technician',
                'Administrator'
            ]
        ];

        if (isset($permissions[$page])) {
            if ($currentUser === null) {
                $this->redirect('login');
            }

            if (!in_array($role, $permissions[$page], true)) {
                $this->stopWithError(
                    403,
                    'Your account does not have permission '
                        . 'to view this page.'
                );
            }
        }

        if (
            $currentUser !== null &&
            in_array($page, ['login', 'register'], true)
        ) {
            $this->redirect($dashboardPage);
        }

        $postPages = [
            'login',
            'register',
            'profile',
            'new-complaint',
            'update-complaint',
            'change-password',
            'customer-edit',
            'employee-form',
            'assign',
            'admin-reset-password'
        ];

        if ($isPost && !in_array($page, $postPages, true)) {
            header('Allow: GET');

            $this->stopWithError(
                405,
                'This page does not accept form submissions.'
            );
        }

        $title = $pages[$page];

        $editingEmployee = $page === 'employee-form'
            && ($_GET['mode'] ?? '') === 'edit';

        $errors = [];
        $values = [];

        if ($page === 'login') {
            $values['account_type'] = 'Customer';
        }

        if ($page === 'employee-form' && !$editingEmployee) {
            $values['employee_level'] = 'Technician';
        }

        if ($isPost) {
            foreach ($_POST as $key => $value) {
                if (is_string($value)) {
                    $values[$key] = $value;
                }
            }
        }

        $navigation = [];

        if ($role === 'Customer') {
            $navigation = [
                'customer-dashboard' => 'Customer Dashboard',
                'profile' => 'My Profile',
                'new-complaint' => 'Submit Complaint',
                'my-complaints' => 'My Complaints',
                'change-password' => 'Change Password'
            ];
        } elseif ($role === 'Technician') {
            $navigation = [
                'technician-dashboard' => 'Technician Dashboard',
                'technician' => 'Assigned Complaints',
                'change-password' => 'Change Password'
            ];
        } elseif ($role === 'Administrator') {
            $navigation = [
                'admin-dashboard' => 'Administrator Dashboard',
                'customers' => 'Customers',
                'employees' => 'Employees',
                'open-complaints' => 'Open Complaints',
                'unassigned' => 'Unassigned Complaints',
                'workload' => 'Technician Workload',
                'assign' => 'Assign Complaint',
                'change-password' => 'Change Password'
            ];
        }

        if ($mustChangePassword) {
            $navigation = [];
        }

        $productOptions = [];
        $categoryOptions = [];
        $complaintChoicesAvailable = true;

        $customers = [];
        $customerRecord = null;
        $customerPageUnavailable = false;

        $employees = [];
        $employeeRecord = null;
        $employeePageUnavailable = false;

        $complaints = [];
        $customerComplaints = [];
        $assignedComplaints = [];
        $unassignedComplaints = [];
        $selectedComplaint = null;
        $complaintOptions = [];
        $technicians = [];
        $workloads = [];

        $complaintPageUnavailable = false;
        $adminComplaintPageUnavailable = false;
        $technicianComplaintPageUnavailable = false;

        $profilePageUnavailable = false;
        $profileCustomerRecord = null;

        $resetPageUnavailable = false;
        $resetAccountType = '';
        $resetAccountId = 0;
        $resetAccountName = '';
        $resetLoginId = '';
        $temporaryPassword = null;

        try {
            if ($page === 'new-complaint') {
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
                        'Products and complaint categories must be '
                        . 'added before entering a complaint.';
                }
            }

            if ($isPost && $page !== 'admin-reset-password') {
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

            // Login.
            if ($page === 'login' && $isPost && !$errors) {
                $accountType = $values['account_type'];
                $loginId = trim($values['login_id']);
                $attemptDB = new LoginAttemptDB();

                if ($attemptDB->isLocked($accountType, $loginId)) {
                    $errors[] =
                        'Login is temporarily unavailable for this '
                        . 'account. Please try again in 15 minutes.';
                } else {
                    $accountDB = $accountType === 'Customer'
                        ? new CustomerDB()
                        : new EmployeeDB();

                    $account = $accountDB->authenticate(
                        $loginId,
                        $values['password']
                    );

                    if (
                        $account !== null &&
                        $accountType !== 'Customer' &&
                        $account->getEmployeeLevel() !== $accountType
                    ) {
                        $account = null;
                    }

                    if ($account === null) {
                        $attemptDB->recordFailure(
                            $accountType,
                            $loginId
                        );

                        $errors[] =
                            'The account type, login ID, or password '
                            . 'is incorrect.';
                    } else {
                        $attemptDB->clear($accountType, $loginId);

                        $this->signIn($account, $accountType);

                        if ($account->getMustChangePassword()) {
                            $this->redirect('change-password');
                        }

                        $this->redirect(
                            $this->dashboardFor($accountType)
                        );
                    }
                }
            }

            // Customer registration and management.
            if (in_array($page, [
                'register',
                'customers',
                'customer-edit'
            ], true)) {
                $customerDB = new CustomerDB();

                if ($page === 'customers') {
                    $customers = $customerDB->getAll();
                }

                if ($page === 'customer-edit') {
                    $customerId = $this->positiveId(
                        $_GET['id'] ?? null
                    );

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
                            $values =
                                $customerRecord->toFormArray();
                        }
                    }
                }

                if (
                    $isPost &&
                    !$errors &&
                    !$customerPageUnavailable &&
                    in_array(
                        $page,
                        ['register', 'customer-edit'],
                        true
                    )
                ) {
                    $record = $page === 'register'
                        ? new Customer()
                        : $customerRecord;

                    $this->fillCustomer($record, $values);

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

                        $record->setMustChangePassword(false);

                        $customerDB->insert($record);

                        $this->redirect(
                            'login',
                            ['registered' => 1]
                        );
                    } else {
                        $customerDB->update($record);

                        $this->redirect(
                            'customers',
                            ['updated' => 1]
                        );
                    }
                }
            }

            // The authenticated customer's profile.
            if ($page === 'profile') {
                $customerDB = new CustomerDB();
                $profileCustomerRecord = $currentUser;

                if (!$isPost) {
                    $values =
                        $profileCustomerRecord->toFormArray();
                }

                if ($isPost && !$errors) {
                    $this->fillCustomer(
                        $profileCustomerRecord,
                        $values
                    );

                    if ($customerDB->emailExists(
                        $profileCustomerRecord->getEmail(),
                        $profileCustomerRecord->getCustomerId()
                    )) {
                        $errors[] =
                            'That email address is already registered.';
                    } else {
                        $customerDB->update($profileCustomerRecord);

                        $this->redirect(
                            'profile',
                            ['saved' => 1]
                        );
                    }
                }
            }

            // Employee management.
            if (in_array($page, [
                'employees',
                'employee-form'
            ], true)) {
                $employeeDB = new EmployeeDB();

                if ($page === 'employees') {
                    $employees = $employeeDB->getAll();
                }

                if (
                    $page === 'employee-form' &&
                    $editingEmployee
                ) {
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
                            $employeeDB->getByUserId(
                                $requestedUserId
                            );

                        if ($employeeRecord === null) {
                            $employeePageUnavailable = true;

                            $errors[] =
                                'The selected employee was not found.';
                        } else {
                            if (!$isPost) {
                                $values =
                                    $employeeRecord->toFormArray();
                            } elseif (
                                ($values['user_id'] ?? '') !==
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
                    $page === 'employee-form' &&
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

                            // Administrator-created initial passwords
                            // must also be changed on first login.
                            $record->setMustChangePassword(true);

                            $employeeDB->insert($record);
                        }

                        $this->redirect(
                            'employees',
                            ['saved' => 1]
                        );
                    }
                }
            }

            // Customer complaints.
            if (in_array($page, [
                'new-complaint',
                'my-complaints',
                'complaint-details'
            ], true)) {
                $complaintDB = new ComplaintDB();
                $customerId = $currentUser->getCustomerId();

                if (
                    $page === 'new-complaint' &&
                    $isPost &&
                    !$errors &&
                    $complaintChoicesAvailable
                ) {
                    $complaint = new Complaint();

                    $complaint->setCustomerId($customerId);
                    $complaint->setProductId(
                        $values['product_id']
                    );
                    $complaint->setCategoryId(
                        $values['category_id']
                    );
                    $complaint->setDescription(
                        $values['description']
                    );

                    $complaintDB->insert($complaint);

                    $this->redirect(
                        'my-complaints',
                        ['saved' => 1]
                    );
                }

                if ($page === 'my-complaints') {
                    $customerComplaints =
                        $complaintDB->getByCustomerId(
                            $customerId
                        );
                }

                if ($page === 'complaint-details') {
                    $complaintId = $this->positiveId(
                        $_GET['id'] ?? null
                    );

                    $record = $complaintId !== false
                        ? $complaintDB->getById($complaintId)
                        : null;

                    if (
                        $record === null ||
                        (int) $record->getCustomerId() !==
                        (int) $customerId
                    ) {
                        http_response_code(404);

                        $complaintPageUnavailable = true;

                        $errors[] =
                            'The requested complaint was not found.';
                    } else {
                        $selectedComplaint = $record;
                    }
                }
            }

            // Administrator complaint management.
            if (in_array($page, [
                'open-complaints',
                'unassigned',
                'assign',
                'workload'
            ], true)) {
                $complaintDB = new ComplaintDB();

                if ($page === 'open-complaints') {
                    $complaints = $complaintDB->getOpen();
                }

                if ($page === 'unassigned') {
                    $unassignedComplaints =
                        $complaintDB->getUnassigned();
                }

                if ($page === 'workload') {
                    $workloads =
                        $complaintDB->getTechnicianWorkload();
                }

                if ($page === 'assign') {
                    foreach ($complaintDB->getOpen() as $record) {
                        $complaintOptions[$record->getComplaintId()] =
                            '#' . $record->getComplaintId()
                            . ' — ' . $record->getCustomerName()
                            . ' — ' . $record->getProductName()
                            . ' — Assigned: '
                            . $record->getTechnicianName();
                    }

                    foreach (
                        $complaintDB->getTechnicians() as $record
                    ) {
                        $technicians[$record['employee_id']] =
                            $record['name']
                            . ' (' . $record['user_id'] . ')';
                    }

                    if (!$isPost) {
                        $requestedId = $_GET['id'] ?? '';

                        if (
                            is_string($requestedId) &&
                            array_key_exists(
                                $requestedId,
                                $complaintOptions
                            )
                        ) {
                            $values['complaint_id'] =
                                $requestedId;
                        } elseif ($requestedId !== '') {
                            $errors[] =
                                'That complaint is not available '
                                . 'for assignment.';
                        }
                    }

                    if ($isPost && !$errors) {
                        $complaintId =
                            $values['complaint_id'];

                        $technicianId =
                            $values['technician_id'];

                        if (!array_key_exists(
                            $complaintId,
                            $complaintOptions
                        )) {
                            $errors[] =
                                'Select an existing open complaint.';
                        }

                        if (!array_key_exists(
                            $technicianId,
                            $technicians
                        )) {
                            $errors[] =
                                'Select an existing technician.';
                        }

                        if (!$errors) {
                            $complaintDB->assignTechnician(
                                $complaintId,
                                $technicianId
                            );

                            $this->redirect(
                                'open-complaints',
                                ['assigned' => 1]
                            );
                        }
                    }
                }
            }

            // Technician assignments and updates.
            if (in_array($page, [
                'technician',
                'update-complaint'
            ], true)) {
                $complaintDB = new ComplaintDB();
                $technicianId = $currentUser->getEmployeeId();

                if ($page === 'technician') {
                    $assignedComplaints =
                        $complaintDB->getByTechnicianId(
                            $technicianId
                        );
                }

                if ($page === 'update-complaint') {
                    $complaintId = $this->positiveId(
                        $_GET['id'] ?? null
                    );

                    $record = $complaintId !== false
                        ? $complaintDB->getById($complaintId)
                        : null;

                    if (
                        $record === null ||
                        (int) $record->getTechnicianId() !==
                        (int) $technicianId ||
                        $record->getStatus() !== 'Open'
                    ) {
                        http_response_code(404);

                        $technicianComplaintPageUnavailable = true;

                        $errors[] =
                            'The complaint is unavailable or is not '
                            . 'an open complaint assigned to you.';
                    } else {
                        $selectedComplaint = $record;

                        if (!$isPost) {
                            $values = [
                                'status' => $record->getStatus(),
                                'technician_notes' => '',
                                'resolution_date' => '',
                                'resolution_notes' => ''
                            ];
                        }

                        if ($isPost && !$errors) {
                            $complaintDB->saveTechnicianUpdate(
                                $complaintId,
                                $technicianId,
                                $values['technician_notes'] ?? '',
                                $values['status'],
                                $values['resolution_date'] ?? '',
                                $values['resolution_notes'] ?? ''
                            );

                            $this->redirect(
                                'technician',
                                ['saved' => 1]
                            );
                        }
                    }
                }
            }

            // Normal and required password changes.
            if (
                $page === 'change-password' &&
                $isPost &&
                !$errors
            ) {
                if (!password_verify(
                    $values['current_password'],
                    $currentUser->getPasswordHash()
                )) {
                    $errors[] =
                        'The current or temporary password is incorrect.';
                } elseif (password_verify(
                    $values['new_password'],
                    $currentUser->getPasswordHash()
                )) {
                    $errors[] =
                        'Choose a password different from your '
                        . 'current or temporary password.';
                } else {
                    $wasRequired = $mustChangePassword;

                    $this->changeAccountPassword(
                        $currentUser,
                        $role,
                        $values['new_password']
                    );

                    if ($wasRequired) {
                        $this->redirect($dashboardPage);
                    }

                    $this->redirect(
                        'change-password',
                        ['saved' => 1]
                    );
                }
            }

            // Administrator-issued temporary passwords.
            if ($page === 'admin-reset-password') {
                $requestedType = $_GET['account_type'] ?? '';
                $requestedId = $this->positiveId(
                    $_GET['id'] ?? null
                );

                $validType = is_string($requestedType)
                    && in_array(
                        $requestedType,
                        ['Customer', 'Technician', 'Administrator'],
                        true
                    );

                if (!$validType || $requestedId === false) {
                    $resetPageUnavailable = true;

                    $errors[] =
                        'Select an account from the customer '
                        . 'or employee list.';
                } else {
                    $resetAccountType = $requestedType;
                    $resetAccountId = $requestedId;

                    if ($resetAccountType === 'Customer') {
                        $resetDB = new CustomerDB();

                        $resetAccount = $resetDB->getById(
                            $resetAccountId
                        );
                    } else {
                        $resetDB = new EmployeeDB();

                        $resetAccount = $resetDB->getById(
                            $resetAccountId
                        );

                        if (
                            $resetAccount !== null &&
                            $resetAccount->getEmployeeLevel() !==
                            $resetAccountType
                        ) {
                            $resetAccount = null;
                        }
                    }

                    if ($resetAccount === null) {
                        $resetPageUnavailable = true;

                        $errors[] =
                            'The selected account was not found.';
                    } elseif (
                        $resetAccountType !== 'Customer' &&
                        (int) $resetAccountId ===
                        (int) $currentUser->getEmployeeId()
                    ) {
                        $resetPageUnavailable = true;

                        $errors[] =
                            'Use Change Password to change your '
                            . 'own administrator password.';
                    } else {
                        $resetAccountName =
                            $resetAccount->getFullName();

                        $resetLoginId =
                            $resetAccountType === 'Customer'
                            ? $resetAccount->getEmail()
                            : $resetAccount->getUserId();

                        // Consume the one-time display after redirect.
                        $flash = $_SESSION['temporary_password_display'] ?? null;

                        unset(
                            $_SESSION['temporary_password_display']
                        );

                        if (
                            !$isPost &&
                            is_array($flash) &&
                            ($flash['account_type'] ?? '') ===
                            $resetAccountType &&
                            (int) ($flash['account_id'] ?? 0) ===
                            $resetAccountId &&
                            isset($flash['password']) &&
                            is_string($flash['password'])
                        ) {
                            $temporaryPassword =
                                $flash['password'];
                        }

                        if ($isPost) {
                            $confirmationPassword =
                                $values['admin_current_password'] ?? '';

                            $validConfirmation =
                                $confirmationPassword !== '' &&
                                strlen($confirmationPassword) <= 72 &&
                                strpos(
                                    $confirmationPassword,
                                    "\0"
                                ) === false;

                            $attemptDB = new LoginAttemptDB();

                            $administratorLogin =
                                $currentUser->getUserId();

                            if ($attemptDB->isLocked(
                                'Administrator',
                                $administratorLogin,
                                'admin-reset'
                            )) {
                                $errors[] =
                                    'Password resets are temporarily '
                                    . 'locked for your account. '
                                    . 'Try again in 15 minutes.';
                            } elseif (
                                !$validConfirmation ||
                                !password_verify(
                                    $confirmationPassword,
                                    $currentUser->getPasswordHash()
                                )
                            ) {
                                $attemptDB->recordFailure(
                                    'Administrator',
                                    $administratorLogin,
                                    'admin-reset'
                                );

                                $errors[] =
                                    'Your administrator password '
                                    . 'is incorrect.';
                            } else {
                                $generatedPassword =
                                    $this->generateTemporaryPassword();

                                $resetDB->setTemporaryPassword(
                                    $resetAccountId,
                                    password_hash(
                                        $generatedPassword,
                                        PASSWORD_DEFAULT
                                    )
                                );

                                $attemptDB->clear(
                                    'Administrator',
                                    $administratorLogin,
                                    'admin-reset'
                                );

                                // Allow immediate login after recovery.
                                $attemptDB->clear(
                                    $resetAccountType,
                                    $resetLoginId
                                );

                                // Briefly retain the plain password only
                                // for its one-time display after redirect.
                                $_SESSION['temporary_password_display'] = [
                                    'account_type' => $resetAccountType,
                                    'account_id' => $resetAccountId,
                                    'password' => $generatedPassword
                                ];

                                // Prevent replay of the submitted form.
                                $_SESSION['csrf_token'] =
                                    bin2hex(random_bytes(32));

                                unset($generatedPassword);

                                $this->redirect(
                                    'admin-reset-password',
                                    [
                                        'account_type' =>
                                        $resetAccountType,
                                        'id' => $resetAccountId
                                    ]
                                );
                            }

                            unset($confirmationPassword);
                        }
                    }
                }
            }
        } catch (InvalidArgumentException $exception) {
            $errors[] = $exception->getMessage();
        } catch (PDOException $exception) {
            error_log($exception->getMessage());

            if (
                $exception->getCode() === '23000' &&
                (int) ($exception->errorInfo[1] ?? 0) === 1062 &&
                in_array(
                    $page,
                    [
                        'register',
                        'profile',
                        'customer-edit',
                        'employee-form'
                    ],
                    true
                )
            ) {
                $errors[] =
                    'That email address or employee User ID '
                    . 'already exists.';
            } else {
                $customerPageUnavailable = true;
                $employeePageUnavailable = true;
                $complaintPageUnavailable = true;
                $adminComplaintPageUnavailable = true;
                $technicianComplaintPageUnavailable = true;
                $profilePageUnavailable = true;
                $complaintChoicesAvailable = false;
                $resetPageUnavailable = true;

                $errors[] =
                    'The requested information could not be '
                    . 'loaded or saved. Check the database '
                    . 'connection and PHP error log.';
            }
        }

        // Do not redisplay submitted passwords.
        unset(
            $values['password'],
            $values['current_password'],
            $values['new_password'],
            $values['confirm_password'],
            $values['admin_current_password'],
            $values['csrf_token']
        );

        $viewFiles = [
            'login' => 'login.php',
            'register' => 'register.php',

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
            'assign' => 'assign.php',
            'admin-reset-password' => 'admin-reset-password.php'
        ];

        $viewFile = __DIR__ . '/../views/' . $viewFiles[$page];

        require __DIR__ . '/../views/page.php';
    }
}
