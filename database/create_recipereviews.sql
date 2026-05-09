CREATE TABLE recipereviews (
    reviewId INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    recipeId BIGINT NOT NULL,
    username VARCHAR(255),
    isPositive BOOLEAN NOT NULL,
    reviewDescription VARCHAR(500),

    FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE
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
    
    1) sudo mysql -u root -p testdb < database/create_recipereviews.sql 

    2) sudo mysql -u root -p testdb -e "SHOW TABLES;"

    3) sudo mysql -u root -p testdb -e "DESCRIBE recipereviews;"
*/