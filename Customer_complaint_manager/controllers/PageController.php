<?php

class PageController
{
    public function handleRequest()
    {
        $pages = [
            'login' => 'Customer Complaint Manager',
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
            'reset-password' => 'Reset Password'
        ];

        // The default page is now the login screen.
        $page = $_GET['page'] ?? 'login';

        if (!is_string($page) || !isset($pages[$page])) {
            $page = 'login';
        }

        $title = $pages[$page];

        // Sample customer data.
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

        // Sample employee data.
        $employees = [
            'treed' => [
                'user_id' => 'treed',
                'first_name' => 'Taylor',
                'last_name' => 'Reed',
                'email' => 'taylor@example.com',
                'phone_extension' => '101',
                'employee_level' => 'Technician'
            ],
            'admin1' => [
                'user_id' => 'admin1',
                'first_name' => 'Casey',
                'last_name' => 'Brown',
                'email' => 'casey@example.com',
                'phone_extension' => '100',
                'employee_level' => 'Administrator'
            ]
        ];

        $editingEmployee = $page === 'employee-form'
            && ($_GET['mode'] ?? '') === 'edit';

        $requestedEmployee = $_GET['employee'] ?? 'treed';

        if (
            !is_string($requestedEmployee) ||
            !isset($employees[$requestedEmployee])
        ) {
            $requestedEmployee = 'treed';
        }

        $employee = $employees[$requestedEmployee];

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

        // Navigation for each role.
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

        if (in_array($page, ['profile', 'customer-edit'], true)) {
            $defaults = $customer;
        }

        if ($editingEmployee) {
            $defaults = $employee;
        }

        if ($page === 'login') {
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

        $isPost = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
        $errors = [];

        if ($isPost) {
            // The existing validator uses treed as its sample edit ID.
            // Normalize a COPY for that check, then verify the actual
            // selected employee ID separately.
            $validationData = $_POST;

            if ($editingEmployee) {
                $submittedId = $_POST['user_id'] ?? '';

                if (
                    !is_string($submittedId) ||
                    $submittedId !== $employee['user_id']
                ) {
                    $errors[] =
                        'An existing employee User ID cannot be changed.';
                }

                $validationData['user_id'] = 'treed';
            }

            $errors = array_merge(
                $errors,
                validateForm($page, $validationData, $editingEmployee)
            );
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

        // Always display the original ID when editing an employee.
        if ($editingEmployee) {
            $values['user_id'] = $employee['user_id'];
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
