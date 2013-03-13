CREATE TABLE exchange_rates (
    -- er_id INT PRIMARY KEY AUTO_INCREMENT,
    er_from varchar(3) COMMENT 'old currency as ISO 4217',
    er_to varchar(3) COMMENT 'new currency as ISO 4217',
    er_rate FLOAT COMMENT 'exchange rate'
) COMMENT = 'Currency exchange rates';
