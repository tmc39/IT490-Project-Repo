# IT490 Project Repo

Guilty Spark Team:
Joseph Boch
Musa Shaikh
Mushran Chowdhury
Nicolas Minervini
Timothy Conway

Guilty Spark is a dietary app that allows you to track dietary goals and the food in your fridge. It allows you to search through a database of food and recipe options, viewing their nutritional values and ingredient breakdowns, while giving you the option to edit the contents of each recipe as well.

Database Tables:

users:
username varchar(50), NOT NULL, PRIMARY KEY
password varchar(255), NOT NULL
email varchar(255), NOT NULL
firstname varchar(255), NOT NULL
lastname varchar(255), NOT NULL

sessions:
session_key varchar(255), NOT NULL, PRIMARY KEY
username varchar(255), NOT NULL, FOREIGN KEY REFERENCES users(username)
created_at timestamp, DEFAULT CURRENT_TIMESTAMP

