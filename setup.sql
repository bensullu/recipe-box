-- Database setup for the "Recipe Box" project (Podstawy Technologii WWW)
-- Creates the database and all five related tables.
-- Run this once in phpMyAdmin (SQL tab) or: mysql -u root < setup.sql

CREATE DATABASE IF NOT EXISTS recipe_box
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE recipe_box;

-- 1) Categories: each recipe belongs to exactly one category (FK from recipes)
CREATE TABLE IF NOT EXISTS categories (
    category_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

INSERT IGNORE INTO categories (name) VALUES
('Breakfast'), ('Soups'), ('Main Courses'), ('Salads'),
('Desserts'), ('Drinks'), ('Vegan'), ('Baking');

-- 2) Users (is_admin = 1 means administrator)
CREATE TABLE IF NOT EXISTS users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3) Recipes (category_id is a foreign key to categories)
CREATE TABLE IF NOT EXISTS recipes (
    recipe_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    prep_time SMALLINT UNSIGNED NOT NULL,           -- preparation time in minutes
    servings TINYINT UNSIGNED NOT NULL,             -- number of servings
    difficulty ENUM('Easy','Medium','Hard') NOT NULL DEFAULT 'Easy',
    ingredients TEXT NOT NULL,                       -- one ingredient per line
    instructions TEXT NOT NULL,                      -- step by step
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_recipes_category
        FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

-- 4) Comments / ratings (username stores the login taken from the session)
CREATE TABLE IF NOT EXISTS comments (
    comment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT UNSIGNED NOT NULL,
    username VARCHAR(50) NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,               -- 1..5 stars
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comments_recipe
        FOREIGN KEY (recipe_id) REFERENCES recipes(recipe_id) ON DELETE CASCADE
);

-- 5) Favorites: links each user with the recipes they marked as favorite
CREATE TABLE IF NOT EXISTS favorites (
    favorite_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    UNIQUE KEY unique_recipe_user (recipe_id, user_id),
    CONSTRAINT fk_favorites_recipe
        FOREIGN KEY (recipe_id) REFERENCES recipes(recipe_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_favorites_user
        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- Sample recipes (optional, helpful for testing)
INSERT INTO recipes (title, category_id, prep_time, servings, difficulty, ingredients, instructions, image) VALUES
('Classic Pancakes',
 (SELECT category_id FROM categories WHERE name = 'Breakfast'),
 20, 4, 'Easy',
 '2 cups flour\n2 eggs\n1.5 cups milk\n2 tbsp sugar\n1 tsp baking powder\npinch of salt',
 'Mix the dry ingredients.\nWhisk in eggs and milk until smooth.\nFry small portions on a hot buttered pan until golden on both sides.\nServe with maple syrup or fruit.',
 NULL);

-- After registering an account named "admin" through the registration form,
-- promote it to administrator manually with:
-- UPDATE users SET is_admin = 1 WHERE login = 'admin';
