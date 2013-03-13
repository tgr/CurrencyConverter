CREATE TABLE exchange_rates (
    -- er_id INT PRIMARY KEY AUTO_INCREMENT,
    er_from varchar(3),
    er_to varchar(3),
    er_rate NUMBER
);
COMMENT ON TABLE exchange_rates IS 'Currency exchange rates';
