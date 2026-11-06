-- Run once on existing installations; existing values are preserved.
USE Customer_complaint_management;

ALTER TABLE employees
    CHANGE COLUMN phone_extension phone_number VARCHAR(20) NULL;
