DROP TABLE IF EXISTS mailing;
CREATE TABLE mailing (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,

    mail_subject VARCHAR(255) NOT NULL,
    mail_message TEXT NOT NULL,

    mail_to VARCHAR(50) NOT NULL,
    mail_cc VARCHAR(50) DEFAULT NULL,
    mail_bcc VARCHAR(50) DEFAULT NULL,

    when_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    when_error DATETIME DEFAULT NULL,
    when_sent DATETIME DEFAULT NULL,

    error_message VARCHAR(255) DEFAULT NULL,

    PRIMARY KEY (id),
    KEY k_sent (when_sent)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
