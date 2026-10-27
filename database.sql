-- إنشاء قاعدة البيانات المطلوبة في ملف المشروع
CREATE DATABASE IF NOT EXISTS student_registration_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE student_registration_system;

-- 1. جدول الطلاب (students) حسب المواصفات الحرفية للملف
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    address TEXT NOT NULL,
    profile_picture VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. جدول المستخدمين للمصادقة (users) حسب المواصفات الحرفية للملف
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);