ALTER TABLE employees
    ADD COLUMN card_color ENUM('red', 'green', 'blue') NULL AFTER medical_fitness_until;
