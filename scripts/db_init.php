<?php

/** 
 * Database Migration Script
 */


// paths
$db_directory = __DIR__ . '/../data';
$db_path = $db_directory . '/drugs_data.db';

// check if data dir exists or create it
if (!is_dir($db_directory)) {
    mkdir($db_directory, 0777, true);
}

try {
    // connect or creates the SQLite file
    $pdo = new PDO("sqlite:" . $db_path);

    // activate exceptions for error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    chmod($db_path, 0666); // gives write permissions for the db file

    echo "[INFO]: Started DB migration.<br>";

    // SQL commands for creating tables + indices
    $commands = [
        "PRAGMA foreign_keys = ON",

        "CREATE TABLE IF NOT EXISTS admins (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            username      TEXT    NOT NULL UNIQUE,
            password_hash TEXT    NOT NULL,
            created_at    TEXT    DEFAULT CURRENT_TIMESTAMP
        )",

        // DRUGS
        "CREATE TABLE IF NOT EXISTS drugs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE
        )",

        // (capturi)
        // DRUG SEIZURES
        "CREATE TABLE IF NOT EXISTS drug_seizures (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            year INTEGER NOT NULL,
            drug_id INTEGER NOT NULL REFERENCES drugs(id),
            grams REAL,
            tablets INTEGER,
            doses_units INTEGER,
            milliliters REAL,
            seizures_count INTEGER,
            UNIQUE (year, drug_id)
        )",

        // (infractionalitate)
        // CRIME: GENERAL
        "CREATE TABLE IF NOT EXISTS crimes_general (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            year INTEGER NOT NULL UNIQUE,
            investigated_persons INTEGER,
            indicted_persons INTEGER,
            convicted_persons INTEGER
        )",

        // CRIME: LEGAL ARTICLE
        "CREATE TABLE IF NOT EXISTS crimes_article (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            year INTEGER NOT NULL,
            legal_article TEXT NOT NULL,
            count INTEGER,
            UNIQUE (year, legal_article)
        )",

        // CRIME: SEX AND AGE (DEMOGRAPHICS)
        "CREATE TABLE IF NOT EXISTS crimes_demographic (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            year INTEGER NOT NULL,
            gender TEXT NOT NULL, -- 'Male' | 'Female'
            age_category TEXT NOT NULL, -- 'Adults' | 'Minors'
            count INTEGER,
            UNIQUE (year, gender, age_category)
        )",

        // CRIME: CRIMINAL GROUPS
        "CREATE TABLE IF NOT EXISTS crimes_group (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            year INTEGER NOT NULL UNIQUE,
            identified_groups INTEGER,
            involved_persons INTEGER
        )",

        // CRIME: SENTENCES
        "CREATE TABLE IF NOT EXISTS crimes_sentence (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            year INTEGER NOT NULL,
            sentence_type TEXT NOT NULL,
            law_reference TEXT NOT NULL, -- cod de lege/articol
            count INTEGER,
            UNIQUE (year, sentence_type, law_reference)
        )",

        // (proiecte si campanii)
        // PREVENTION: PROJECTS
        "CREATE TABLE IF NOT EXISTS prevention_projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            year INTEGER NOT NULL, 
            project_name TEXT NOT NULL,
            beneficiaries_count INTEGER,
            UNIQUE (year, project_name)
        )",

        // PREVENTION: NATIONAL CAMPAIGNS
        "CREATE TABLE IF NOT EXISTS prevention_campaigns (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            year INTEGER NOT NULL,
            campaign_name TEXT NOT NULL,
            beneficiaries_count INTEGER,
            UNIQUE (year, campaign_name)
        )",

        // PREVENTION: ACTIVITIES
        "CREATE TABLE IF NOT EXISTS prevention_activities (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            year INTEGER NOT NULL,
            setting TEXT NOT NULL, -- 'Preschool', 'University', 'Family' etc.
            activities_count INTEGER,
            beneficiaries_count INTEGER,
            beneficiary_type TEXT, -- 'students', 'parents' etc.
            UNIQUE (year, setting, beneficiary_type)
        )",

        // (urgente medicale)
        // EMERGENCIES: GENERAL
        "CREATE TABLE IF NOT EXISTS medical_emergencies (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            year INTEGER NOT NULL,
            drug_type TEXT NOT NULL,
            category TEXT NOT NULL, -- 'sex', 'varsta', 'cale de administrare' etc.
            value TEXT NOT NULL, -- 'Masculin/Feminin', '<25', 'Injectabil' etc.
            count INTEGER,
            UNIQUE (year, drug_type, category, value)
        )"
    ];

    foreach ($commands as $sql) {
        $pdo->exec($sql);
    }

    // indices
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_seizures_year ON drug_seizures(year)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_emergencies_year ON medical_emergencies(year)");

    echo "[INFO]: Migration completed successfully.\n";
} catch (PDOException $e) {
    http_response_code(500);
    echo "[ERROR]: Migration failed -> " . $e->getMessage();
}