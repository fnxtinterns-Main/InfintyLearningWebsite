-- =========================================================
-- Infinity Learning - BPSC TRE 4.0 Sub-Portal
-- Development Database Schema & Seed Data
-- =========================================================

-- 1. Users Table (Admin, Teacher, Candidate)
CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'student',
    level_label VARCHAR(100) NOT NULL DEFAULT '2. Middle School (Classes 6–8)',
    district VARCHAR(100) NOT NULL DEFAULT 'Patna',
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    qualification VARCHAR(255) DEFAULT NULL,
    subject VARCHAR(150) DEFAULT NULL,
    overall_score REAL NOT NULL DEFAULT 0.0,
    best_score REAL NOT NULL DEFAULT 0.0,
    tests_taken INTEGER NOT NULL DEFAULT 0,
    avg_accuracy REAL NOT NULL DEFAULT 0.0,
    plan_name VARCHAR(150) NOT NULL DEFAULT 'BPSC TRE 4.0 Standard Pass',
    plan_status VARCHAR(50) NOT NULL DEFAULT 'Active (Enrolled)',
    plan_badge VARCHAR(50) NOT NULL DEFAULT 'Active Access',
    plan_amount VARCHAR(50) NOT NULL DEFAULT 'Trial Access',
    plan_expiry VARCHAR(50) NOT NULL DEFAULT 'December 31, 2026',
    rank_district VARCHAR(100) NOT NULL DEFAULT 'Pending First CBT Mock',
    earned_badges VARCHAR(255) NOT NULL DEFAULT 'Registered Aspirant,Admit Ready',
    approved_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL
);

-- 2. Question Papers Table (CBT Mock Tests)
CREATE TABLE IF NOT EXISTS question_papers (
    id VARCHAR(50) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    cadre VARCHAR(100) NOT NULL DEFAULT 'Classes 6–8 (Middle)',
    duration INTEGER NOT NULL DEFAULT 120,
    total_marks REAL NOT NULL DEFAULT 150.0,
    penalty VARCHAR(20) NOT NULL DEFAULT '-0.33',
    status VARCHAR(20) NOT NULL DEFAULT 'published',
    visibility VARCHAR(20) NOT NULL DEFAULT 'all',
    author VARCHAR(150) NOT NULL DEFAULT 'Academic Faculty',
    questions TEXT DEFAULT NULL,
    published_at DATETIME NOT NULL,
    formatted_date_time VARCHAR(50) NOT NULL,
    updated_at DATETIME DEFAULT NULL
);

-- 3. Notes Table (Study Materials & Documents)
CREATE TABLE IF NOT EXISTS notes (
    id VARCHAR(50) PRIMARY KEY,
    topic_id VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(150) NOT NULL,
    summary TEXT,
    body TEXT,
    tips TEXT,
    author VARCHAR(150) NOT NULL DEFAULT 'Faculty Terminal',
    file_name VARCHAR(255) DEFAULT NULL,
    file_url TEXT DEFAULT NULL,
    attached_file TEXT DEFAULT NULL,
    quiz_data TEXT DEFAULT NULL,
    quiz TEXT DEFAULT NULL,
    uploaded_at DATETIME NOT NULL,
    formatted_date_time VARCHAR(50) NOT NULL
);

-- 4. Test Results Table (Candidate Submissions & Leaderboard)
CREATE TABLE IF NOT EXISTS test_results (
    id VARCHAR(50) PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    user_name VARCHAR(150) NOT NULL,
    user_email VARCHAR(150) NOT NULL,
    district VARCHAR(100) NOT NULL,
    cadre VARCHAR(100) NOT NULL,
    paper_id VARCHAR(50) NOT NULL,
    paper_title VARCHAR(255) NOT NULL,
    score REAL NOT NULL,
    max_marks REAL NOT NULL,
    accuracy REAL NOT NULL,
    submitted_at DATETIME NOT NULL
);

-- 5. Master Question Bank Table (Shared Topic-Wise Repository for Teachers & Admins)
CREATE TABLE IF NOT EXISTS question_bank (
    id VARCHAR(50) PRIMARY KEY,
    topic VARCHAR(150) NOT NULL DEFAULT 'General Studies',
    question TEXT NOT NULL,
    options TEXT NOT NULL,
    correct_answer INTEGER NOT NULL DEFAULT 0,
    marks REAL NOT NULL DEFAULT 1.0,
    penalty VARCHAR(20) NOT NULL DEFAULT '-0.33',
    explanation TEXT DEFAULT NULL,
    diagram TEXT DEFAULT NULL,
    author VARCHAR(150) NOT NULL DEFAULT 'Academic Faculty',
    created_at DATETIME NOT NULL
);

-- Performance Indexes
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_phone ON users(phone);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_users_status ON users(status);
CREATE INDEX IF NOT EXISTS idx_qp_status ON question_papers(status);
CREATE INDEX IF NOT EXISTS idx_notes_topic ON notes(topic_id);
CREATE INDEX IF NOT EXISTS idx_tr_user ON test_results(user_id);
CREATE INDEX IF NOT EXISTS idx_qb_topic ON question_bank(topic);

-- =========================================================
-- Development Seed Accounts (Only what is needed for testing)
-- =========================================================

-- 1. Master Administrator (Requested credentials: fnxt.admin@gmail.com / qweqweqwe1)
INSERT OR IGNORE INTO users (
    id, name, email, phone, password_hash, role, level_label, district, status,
    plan_name, plan_status, plan_badge, plan_amount, plan_expiry,
    rank_district, earned_badges, created_at
) VALUES (
    'USR_ADM_FNXT',
    'Master Administrator',
    'fnxt.admin@gmail.com',
    '+919876543211',
    '$2y$10$8zxhqauxqsAQrSJzQSfbGuagQKHJEyPery5AaasW22rD5ImPNUO/6',
    'admin',
    'Platform Administration',
    'Patna',
    'active',
    'BPSC TRE 4.0 Platform Administration Pass',
    'Active (Enrolled)',
    'Active Access',
    'Trial Access',
    'December 31, 2026',
    'Pending First CBT Mock',
    'Registered Aspirant,Admit Ready,Patna Aspirant',
    '2026-11-06 17:30:00'
);

-- 2. Development Faculty Account (teacher@infinitylearning.net / qweqwe1)
INSERT OR IGNORE INTO users (
    id, name, email, phone, password_hash, role, level_label, district, status,
    qualification, subject, plan_name, plan_status, plan_badge, plan_amount, plan_expiry,
    rank_district, earned_badges, created_at
) VALUES (
    'USR_FAC_DEV01',
    'Faculty Educator',
    'teacher@infinitylearning.net',
    '+919876543212',
    '$2y$10$CnCB7bY1zunNQIjJEzhoVujhWXRocGqwaRwDZNKAXRfMnooe/xKaW',
    'teacher',
    '2. Middle School (Classes 6–8)',
    'Patna',
    'active',
    'B.Ed, STET Qualified Educator',
    'Mathematics & Science',
    'BPSC TRE 4.0 Faculty Pass',
    'Active (Enrolled)',
    'Active Access',
    'Trial Access',
    'December 31, 2026',
    'Pending First CBT Mock',
    'Registered Aspirant,Admit Ready,Patna Aspirant',
    '2026-11-06 12:00:00'
);

-- 3. Development Candidate Account (student@infinitylearning.net / qweqwe1)
INSERT OR IGNORE INTO users (
    id, name, email, phone, password_hash, role, level_label, district, status,
    overall_score, best_score, tests_taken, avg_accuracy,
    plan_name, plan_status, plan_badge, plan_amount, plan_expiry,
    rank_district, earned_badges, created_at
) VALUES (
    'USR_STU_DEV01',
    'Test Aspirant',
    'student@infinitylearning.net',
    '+919876543213',
    '$2y$10$CnCB7bY1zunNQIjJEzhoVujhWXRocGqwaRwDZNKAXRfMnooe/xKaW',
    'student',
    '2. Middle School (Classes 6–8)',
    'Patna',
    'active',
    120.0,
    120.0,
    1,
    80.0,
    'BPSC TRE 4.0 2. Middle School (Classes 6–8) Pass',
    'Active (Enrolled)',
    'Active Access',
    'Trial Access',
    'December 31, 2026',
    '#1 in Patna',
    'Registered Aspirant,Admit Ready,Patna Aspirant',
    '2026-11-06 12:00:00'
);

-- =========================================================
-- Seed Topic-Wise Question Bank Repository
-- =========================================================

INSERT OR IGNORE INTO question_bank (id, topic, question, options, correct_answer, marks, penalty, explanation, author, created_at)
VALUES 
(
    'QB_001',
    'Bihar Special GK & History',
    'During the 1857 revolt in Bihar, which legendary leader led the resistance against the British East India Company in Jagdishpur (Bhojpur)?',
    '["Kunwar Singh", "Pir Ali Khan", "Amar Singh", "Hare Krishna Singh"]',
    0,
    1.0,
    '-0.33',
    'Babu Kunwar Singh of Jagdishpur was the foremost leader of the 1857 rebellion in Bihar.',
    'Academic Faculty',
    '2026-11-06 12:00:00'
),
(
    'QB_002',
    'Bihar Special GK & History',
    'Who invited Mahatma Gandhi to Champaran in 1917 to inspect the plight of indigo cultivators under the Tinkathia system?',
    '["Rajendra Prasad", "Raj Kumar Shukla", "Braja Kishore Prasad", "Anugrah Narayan Sinha"]',
    1,
    1.0,
    '-0.33',
    'Raj Kumar Shukla persistently requested Gandhi at the 1916 Lucknow Session to visit Champaran.',
    'Academic Faculty',
    '2026-11-06 12:00:00'
),
(
    'QB_003',
    'Bihar Special GK & History',
    'Which district of Bihar has the highest literacy rate according to the 2011 Census?',
    '["Patna", "Munger", "Rohtas", "Bhagalpur"]',
    2,
    1.0,
    '-0.33',
    'Rohtas recorded the highest literacy rate (73.37%) in Bihar as per the 2011 Census.',
    'Academic Faculty',
    '2026-11-06 12:00:00'
),
(
    'QB_004',
    'General Studies & National Movement',
    'In which year did the historic Quit India Movement commence following the Gowalia Tank Maidan resolution?',
    '["1940", "1942", "1945", "1939"]',
    1,
    1.0,
    '-0.33',
    'The Quit India Movement was officially launched on August 8, 1942 by Mahatma Gandhi.',
    'Academic Faculty',
    '2026-11-06 12:00:00'
),
(
    'QB_005',
    'General Studies & National Movement',
    'Under which Article of the Indian Constitution is the Right to Education established as a Fundamental Right for children aged 6 to 14?',
    '["Article 21A", "Article 45", "Article 19", "Article 51A"]',
    0,
    1.0,
    '-0.33',
    'The 86th Constitutional Amendment Act, 2002 inserted Article 21A making free and compulsory education a fundamental right.',
    'Academic Faculty',
    '2026-11-06 12:00:00'
),
(
    'QB_006',
    'General Studies & National Movement',
    'Who was the Viceroy of India when the Partition of Bengal took place in 1905?',
    '["Lord Curzon", "Lord Minto", "Lord Hardinge", "Lord Chelmsford"]',
    0,
    1.0,
    '-0.33',
    'Lord Curzon partitioned Bengal on October 16, 1905.',
    'Academic Faculty',
    '2026-11-06 12:00:00'
),
(
    'QB_007',
    'Science & Technology',
    'Which cell organelle is known as the powerhouse of the cell due to its role in cellular ATP synthesis?',
    '["Ribosome", "Mitochondria", "Golgi Apparatus", "Endoplasmic Reticulum"]',
    1,
    1.0,
    '-0.33',
    'Mitochondria generate most of the chemical energy needed to power the cell through ATP generation.',
    'Academic Faculty',
    '2026-11-06 12:00:00'
),
(
    'QB_008',
    'Science & Technology',
    'What is the chemical formula of Washing Soda?',
    '["NaHCO3", "Na2CO3·10H2O", "Ca(OH)2", "CaOCl2"]',
    1,
    1.0,
    '-0.33',
    'Washing Soda is sodium carbonate decahydrate (Na2CO3·10H2O).',
    'Academic Faculty',
    '2026-11-06 12:00:00'
),
(
    'QB_009',
    'Science & Technology',
    'Which phenomenon of light is primarily responsible for the twinkling of stars in the night sky?',
    '["Total Internal Reflection", "Atmospheric Refraction", "Dispersion", "Diffraction"]',
    1,
    1.0,
    '-0.33',
    'Twinkling of stars is caused by atmospheric refraction of starlight as it passes through varying atmospheric density layers.',
    'Academic Faculty',
    '2026-11-06 12:00:00'
),
(
    'QB_010',
    'Mathematics & Quantitative Aptitude',
    'If the ratio of two numbers is 3:4 and their HCF is 4, what is their LCM?',
    '["12", "24", "48", "36"]',
    2,
    1.0,
    '-0.33',
    'Numbers are 3*4=12 and 4*4=16. LCM of 12 and 16 is 48.',
    'Academic Faculty',
    '2026-11-06 12:00:00'
),
(
    'QB_011',
    'Mathematics & Quantitative Aptitude',
    'A train traveling at 72 km/h crosses a pole in 15 seconds. What is the length of the train in meters?',
    '["300 m", "250 m", "200 m", "360 m"]',
    0,
    1.0,
    '-0.33',
    'Speed = 72 * (5/18) = 20 m/s. Distance (length) = Speed * Time = 20 * 15 = 300 meters.',
    'Academic Faculty',
    '2026-11-06 12:00:00'
),
(
    'QB_012',
    'Language & Literature (Hindi & English)',
    'Choose the correctly punctuated sentence: "Where are you going asked Rohan"',
    '["\"Where are you going\", asked Rohan.", "\"Where are you going?\" asked Rohan.", "\"Where are you going\" asked Rohan?", "\"Where are you going?\", asked Rohan"]',
    1,
    1.0,
    '-0.33',
    'The question mark should be inside the quotation marks followed by the attribution tag.',
    'Academic Faculty',
    '2026-11-06 12:00:00'
),
(
    'QB_013',
    'Language & Literature (Hindi & English)',
    'हिंदी वर्णमाला में ''क्ष'' वर्ण किसके योग से बनता है?',
    '["क् + ष", "क् + च", "क् + छ", "क् + श"]',
    0,
    1.0,
    '-0.33',
    'संयुक्त व्यंजन ''क्ष'' क् और ष के योग से बनता है।',
    'Academic Faculty',
    '2026-11-06 12:00:00'
);

-- =========================================================
-- Initial Published Question Paper (Available for CBT Mock)
-- =========================================================
INSERT OR IGNORE INTO question_papers (
    id, title, cadre, duration, total_marks, penalty, status, visibility, author, questions, published_at, formatted_date_time
) VALUES (
    'QP_TRE4_01',
    'BPSC TRE 4.0 Foundation Diagnostic Grand Mock',
    'Classes 6–8 (Middle)',
    120,
    5.0,
    '-0.33',
    'published',
    'all',
    'Faculty Academic Terminal',
    '[{"id":1,"section":"Bihar Special GK & History","topic":"Bihar Special GK & History","question":"During the 1857 revolt in Bihar, which legendary leader led the resistance against the British East India Company in Jagdishpur (Bhojpur)?","options":["Kunwar Singh","Pir Ali Khan","Amar Singh","Hare Krishna Singh"],"ans":0,"posMarks":1,"negMarks":0.33,"explanation":"Babu Kunwar Singh of Jagdishpur was the foremost leader of the 1857 rebellion in Bihar."},{"id":2,"section":"General Studies & National Movement","topic":"General Studies & National Movement","question":"In which year did the historic Quit India Movement commence following the Gowalia Tank Maidan resolution?","options":["1940","1942","1945","1939"],"ans":1,"posMarks":1,"negMarks":0.33,"explanation":"The Quit India Movement was officially launched on August 8, 1942 by Mahatma Gandhi."},{"id":3,"section":"Science & Technology","topic":"Science & Technology","question":"Which cell organelle is known as the powerhouse of the cell due to its role in cellular ATP synthesis?","options":["Ribosome","Mitochondria","Golgi Apparatus","Endoplasmic Reticulum"],"ans":1,"posMarks":1,"negMarks":0.33,"explanation":"Mitochondria generate most of the chemical energy needed to power the cell through ATP generation."},{"id":4,"section":"Mathematics & Quantitative Aptitude","topic":"Mathematics & Quantitative Aptitude","question":"If the ratio of two numbers is 3:4 and their HCF is 4, what is their LCM?","options":["12","24","48","36"],"ans":2,"posMarks":1,"negMarks":0.33,"explanation":"Numbers are 3*4=12 and 4*4=16. LCM of 12 and 16 is 48."},{"id":5,"section":"Language & Literature (Hindi & English)","topic":"Language & Literature (Hindi & English)","question":"Choose the correctly punctuated sentence: \"Where are you going asked Rohan\"","options":["\"Where are you going\", asked Rohan.","\"Where are you going?\" asked Rohan.","\"Where are you going\" asked Rohan?","\"Where are you going?\", asked Rohan"],"ans":1,"posMarks":1,"negMarks":0.33,"explanation":"The question mark should be inside the quotation marks followed by the attribution tag."}]',
    '2026-11-06 12:00:00',
    '06 Nov 2026, 12:00 PM'
);
