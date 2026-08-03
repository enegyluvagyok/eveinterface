ALTER TABLE subcontractors
    DROP INDEX name,
    ADD COLUMN contact VARCHAR(191) NULL AFTER name,
    ADD COLUMN phone VARCHAR(30) NULL AFTER contact;
