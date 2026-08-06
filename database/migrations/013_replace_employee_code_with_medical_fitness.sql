ALTER TABLE employees
    DROP COLUMN employee_code,
    ADD COLUMN medical_fitness_until DATE NULL AFTER idcard;
