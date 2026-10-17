<?php

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function inputField($values, $name, $label, $type = 'text', $maxLength = 150, $required = true, $readonly = false)
{
    // Never send submitted passwords back in the response HTML.
    $value = $type === 'password' ? '' : ($values[$name] ?? '');
    echo '<p><label for="' . e($name) . '">' . e($label) . '</label><br>';
    echo '<input id="' . e($name) . '" name="' . e($name)
        . '" type="' . e($type) . '" value="' . e($value) . '"';
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

function selectField($values, $name, $label, $options, $required = true)
{
    $selected = (string) ($values[$name] ?? '');
    echo '<p><label for="' . e($name) . '">' . e($label) . '</label><br>';
    echo '<select id="' . e($name) . '" name="' . e($name) . '"'
        . ($required ? ' required' : '') . '>';
    echo '<option value="">Select ' . e($label) . '</option>';
    foreach ($options as $value => $text) {
        echo '<option value="' . e($value) . '"'
            . ((string) $value === $selected ? ' selected' : '')
            . '>' . e($text) . '</option>';
    }
    echo '</select></p>';
}

function textAreaField($values, $name, $label, $required = false)
{
    echo '<p><label for="' . e($name) . '">' . e($label) . '</label><br>';
    echo '<textarea id="' . e($name) . '" name="' . e($name)
        . '" rows="6" cols="60" maxlength="5000"'
        . ($required ? ' required' : '') . '>'
        . e($values[$name] ?? '') . '</textarea></p>';
}

function formActions($label, $cancelPage)
{
    echo '<p><button type="submit">' . e($label) . '</button> '
        . '<a href="' . e('?page=' . rawurlencode($cancelPage)) . '">Cancel and Return</a></p>';
}

function complaintTable($complaints, $actionPage, $actionLabel)
{
    if (!$complaints) {
        echo '<p>No complaints found.</p>';
        return;
    }

    $columns = [
        'id' => 'Complaint ID',
        'customer' => 'Customer',
        'product' => 'Product / Service',
        'category' => 'Category',
        'status' => 'Status',
        'technician' => 'Technician'
    ];
    echo '<table><caption>Complaints</caption><thead><tr>';
    foreach ($columns as $label) {
        echo '<th scope="col">' . e($label) . '</th>';
    }
    echo '<th scope="col">Action</th></tr></thead><tbody>';
    foreach ($complaints as $complaint) {
        echo '<tr>';
        foreach ($columns as $field => $label) {
            echo '<td>' . e($complaint[$field] ?? '') . '</td>';
        }
        $url = '?page=' . rawurlencode($actionPage)
            . '&id=' . rawurlencode((string) $complaint['id']);
        echo '<td><a href="' . e($url) . '">' . e($actionLabel) . '</a></td></tr>';
    }
    echo '</tbody></table>';
}

function complaintSummary($complaint)
{
    $fields = [
        'id' => 'Complaint ID',
        'customer' => 'Customer',
        'product' => 'Product / Service',
        'category' => 'Category',
        'status' => 'Status',
        'technician' => 'Technician',
        'description' => 'Description'
    ];
    echo '<h2>Complaint Details</h2><dl>';
    foreach ($fields as $field => $label) {
        echo '<dt>' . e($label) . '</dt><dd>'
            . nl2br(e($complaint[$field] ?? '')) . '</dd>';
    }
    echo '</dl>';
}
