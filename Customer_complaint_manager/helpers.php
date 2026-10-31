<?php

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function csrfField()
{
    $token = $_SESSION['csrf_token'] ?? '';

    if (!is_string($token) || $token === '') {
        throw new RuntimeException(
            'The form security token is unavailable. Reload the page.'
        );
    }

    echo '<input type="hidden" name="csrf_token" value="'
        . e($token) . '">';
}

function csrfTokenIsValid()
{
    $expected = $_SESSION['csrf_token'] ?? null;
    $submitted = $_POST['csrf_token'] ?? null;

    return is_string($expected)
        && $expected !== ''
        && is_string($submitted)
        && hash_equals($expected, $submitted);
}

function logoutForm()
{
    echo '<form method="post" action="?page=logout">';

    csrfField();

    echo '<p><button type="submit">Sign out</button></p>';
    echo '</form>';
}

function inputField(
    $values,
    $name,
    $label,
    $type = 'text',
    $maxLength = 150,
    $required = true,
    $readonly = false
) {
    // Never send submitted passwords back in the response HTML.
    $value = $type === 'password'
        ? ''
        : ($values[$name] ?? '');

    echo '<p><label for="' . e($name) . '">'
        . e($label) . '</label><br>';

    echo '<input id="' . e($name)
        . '" name="' . e($name)
        . '" type="' . e($type)
        . '" value="' . e($value) . '"';

    if ($type !== 'date') {
        echo ' maxlength="' . e($maxLength) . '"';
    }

    if ($required) {
        echo ' required';
    }

    if ($readonly) {
        echo ' readonly';
    }

    echo '></p>';
}

function optionsFromList($items)
{
    $options = [];

    foreach ($items as $item) {
        $options[$item] = $item;
    }

    return $options;
}

function selectField(
    $values,
    $name,
    $label,
    $options,
    $required = true
) {
    $selected = (string) ($values[$name] ?? '');

    echo '<p><label for="' . e($name) . '">'
        . e($label) . '</label><br>';

    echo '<select id="' . e($name)
        . '" name="' . e($name) . '"'
        . ($required ? ' required' : '') . '>';

    echo '<option value="">Select ' . e($label) . '</option>';

    foreach ($options as $value => $text) {
        echo '<option value="' . e($value) . '"'
            . ((string) $value === $selected ? ' selected' : '')
            . '>' . e($text) . '</option>';
    }

    echo '</select></p>';
}

function textAreaField(
    $values,
    $name,
    $label,
    $required = false
) {
    echo '<p><label for="' . e($name) . '">'
        . e($label) . '</label><br>';

    echo '<textarea id="' . e($name)
        . '" name="' . e($name)
        . '" rows="6" cols="60" maxlength="5000"'
        . ($required ? ' required' : '') . '>'
        . e($values[$name] ?? '')
        . '</textarea></p>';
}

function formActions($label, $cancelPage)
{
    echo '<p><button type="submit">'
        . e($label)
        . '</button> '
        . '<a href="'
        . e('?page=' . rawurlencode($cancelPage))
        . '">Cancel and Return</a></p>';
}

function complaintTable(
    $complaints,
    $actionPage,
    $actionLabel
) {
    if (!$complaints) {
        echo '<p>No complaints found.</p>';
        return;
    }

    $columns = [
        'getComplaintId' => 'Complaint ID',
        'getCustomerName' => 'Customer',
        'getProductName' => 'Product / Service',
        'getCategoryName' => 'Category',
        'getStatus' => 'Status',
        'getTechnicianName' => 'Technician'
    ];

    echo '<table>';
    echo '<caption>Complaints</caption>';
    echo '<thead><tr>';

    foreach ($columns as $label) {
        echo '<th scope="col">' . e($label) . '</th>';
    }

    echo '<th scope="col">Action</th>';
    echo '</tr></thead><tbody>';

    foreach ($complaints as $complaint) {
        echo '<tr>';

        foreach ($columns as $getter => $label) {
            echo '<td>'
                . e($complaint->$getter())
                . '</td>';
        }

        $url = '?page=' . rawurlencode($actionPage)
            . '&id='
            . rawurlencode(
                (string) $complaint->getComplaintId()
            );

        echo '<td><a href="' . e($url) . '">'
            . e($actionLabel)
            . '</a></td>';

        echo '</tr>';
    }

    echo '</tbody></table>';
}

function complaintSummary($complaint)
{
    $fields = [
        'getComplaintId' => 'Complaint ID',
        'getCustomerName' => 'Customer',
        'getProductName' => 'Product / Service',
        'getCategoryName' => 'Category',
        'getStatus' => 'Status',
        'getTechnicianName' => 'Technician',
        'getDescription' => 'Description'
    ];

    echo '<h2>Complaint Details</h2>';
    echo '<dl>';

    foreach ($fields as $getter => $label) {
        echo '<dt>' . e($label) . '</dt>';
        echo '<dd>'
            . nl2br(e($complaint->$getter()))
            . '</dd>';
    }

    echo '</dl>';
}

function complaintNotesText(array $notes)
{
    if (!$notes) {
        return 'No technician notes have been added.';
    }

    $lines = [];

    foreach ($notes as $note) {
        $lines[] = $note->getCreatedAt()
            . ' — '
            . $note->getAuthorName()
            . ': '
            . $note->getNoteText();
    }

    return implode("\n\n", $lines);
}
