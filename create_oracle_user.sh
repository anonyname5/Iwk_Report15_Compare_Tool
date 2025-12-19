#!/bin/bash
echo "Creating Oracle user for IWK Finance application..."

# Execute the SQL command to create the user
docker exec -i iwk_oracle bash -c "echo 'ALTER SESSION SET CONTAINER = XEPDB1;
CREATE USER iwk_finance IDENTIFIED BY iwk_password QUOTA UNLIMITED ON USERS;
GRANT CREATE SESSION TO iwk_finance;
GRANT CREATE TABLE TO iwk_finance;
GRANT CREATE VIEW TO iwk_finance;
GRANT CREATE SEQUENCE TO iwk_finance;
GRANT CREATE PROCEDURE TO iwk_finance;
GRANT CREATE TRIGGER TO iwk_finance;
GRANT CREATE TYPE TO iwk_finance;
GRANT CREATE SYNONYM TO iwk_finance;
ALTER USER iwk_finance PASSWORD EXPIRE;
ALTER USER iwk_finance IDENTIFIED BY iwk_password;
ALTER PROFILE DEFAULT LIMIT PASSWORD_LIFE_TIME UNLIMITED;
EXIT;' | sqlplus / as sysdba"

echo "User creation completed. Try connecting with SQL Developer now." 