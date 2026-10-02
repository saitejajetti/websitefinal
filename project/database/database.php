<?php

$db = new SQLite3(__DIR__ . "/grocery.db");


/* =========================
   PRODUCTS TABLE
========================= */

$db->exec("
    CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        category TEXT NOT NULL,
        purchase_price REAL NOT NULL,
        selling_price REAL NOT NULL,
        discount REAL DEFAULT 0,
        quantity INTEGER DEFAULT 0,
        description TEXT,
        image TEXT
    )
");


/* =========================
   ORDERS TABLE
========================= */

$db->exec("
    CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_number TEXT UNIQUE NOT NULL,
        customer TEXT NOT NULL,
        email TEXT,
        phone TEXT,
        address TEXT,
        items TEXT,
        amount REAL NOT NULL,
        payment TEXT,
        status TEXT DEFAULT 'Pending',
        date TEXT
    )
");


/* =========================
   USERS TABLE
========================= */

$db->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    )
");


/* =========================
   PAYMENT SETTINGS TABLE
========================= */

$db->exec("
    CREATE TABLE IF NOT EXISTS payment_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        upi_id TEXT NOT NULL,
        qr_image TEXT NOT NULL,
        instructions TEXT,
        updated_at TEXT DEFAULT CURRENT_TIMESTAMP
    )
");

?>