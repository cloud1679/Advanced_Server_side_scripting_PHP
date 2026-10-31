ALTER TABLE customers
    ADD COLUMN must_change_password
        TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE employees
    ADD COLUMN must_change_password
        TINYINT(1) NOT NULL DEFAULT 0;