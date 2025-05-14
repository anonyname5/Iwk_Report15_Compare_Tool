-- Create user for IWK Finance application
ALTER SESSION SET CONTAINER = XEPDB1;

-- Create user
CREATE USER iwk_finance IDENTIFIED BY iwk_password
QUOTA UNLIMITED ON USERS;

-- Grant necessary privileges
GRANT CREATE SESSION TO iwk_finance;
GRANT CREATE TABLE TO iwk_finance;
GRANT CREATE VIEW TO iwk_finance;
GRANT CREATE SEQUENCE TO iwk_finance;
GRANT CREATE PROCEDURE TO iwk_finance;
GRANT CREATE TRIGGER TO iwk_finance;
GRANT CREATE TYPE TO iwk_finance;
GRANT CREATE SYNONYM TO iwk_finance;

-- Exit 