CREATE TABLE bundles (
    bundle_id INT AUTO_INCREMENT PRIMARY KEY,

    version_number VARCHAR(50) NOT NULL,
    machine VARCHAR(100) NOT NULL,

    bundle_name VARCHAR(255) NOT NULL,

    status ENUM('new', 'passed', 'failed') DEFAULT 'new',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

/*
-------------------------------------
Instructions for how to use this file
-------------------------------------
1) Run the following command inside your VM terminal:
    sudo mysql -u root -p [YOUR DB NAME] < [YOUR DIRECTORY PATH]/create_recipereviews.sql

2) Check if table added:
    sudo mysql -u root -p [YOUR DB NAME] -e "SHOW TABLES;"

3) Check table details
    sudo mysql -u root -p [YOUR DB NAME] -e "DESCRIBE [YOUR DB NAME];"

------------
For example:
------------
Based on my database name and directory, I would use:

    1) sudo mysql -u root -p testdb < database/create_bundles.sql

    2) sudo mysql -u root -p testdb -e "SHOW TABLES;"

    3) sudo mysql -u root -p testdb -e "DESCRIBE bundles;"
*/