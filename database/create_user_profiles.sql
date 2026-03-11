CREATE TABLE user_profiles (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    dietary_goal VARCHAR(50),
    calorie_target INT,
    kosher BOOLEAN DEFAULT FALSE,
    halal BOOLEAN DEFAULT FALSE,
    vegetarian BOOLEAN DEFAULT FALSE,
    vegan BOOLEAN DEFAULT FALSE,
    allergies TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE(username),
    FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE
);

/*
-------------------------------------
Instructions for how to use this file
-------------------------------------
1) Run the following command inside your VM terminal:
    sudo mysql -u root -p [YOUR DB NAME] < [YOUR DIRECTORY PATH]/create_user_profiles.sql

------------
For example:
------------
Based on my database name and directory, I would use: 
    sudo mysql -u root -p testdb < database/create_user_profiles.sql 
*/