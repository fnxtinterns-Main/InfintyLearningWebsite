-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 28, 2026 at 07:07 AM
-- Server version: 11.8.9-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u999011396_inflearning`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_assessments`
--

CREATE TABLE `academic_assessments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `teacher_user_id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `class_level` varchar(50) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(220) NOT NULL,
  `assessment_type` varchar(50) NOT NULL DEFAULT 'unit_test',
  `total_marks` decimal(5,1) NOT NULL DEFAULT 20.0,
  `passing_marks` decimal(5,1) NOT NULL DEFAULT 8.0,
  `duration_minutes` int(10) UNSIGNED DEFAULT 0,
  `instructions` text DEFAULT NULL,
  `questions_json` longtext NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `status` enum('draft','published','closed') NOT NULL DEFAULT 'published',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `academic_assessments`
--

INSERT INTO `academic_assessments` (`id`, `uuid`, `teacher_user_id`, `school_id`, `batch_id`, `class_level`, `subject_id`, `title`, `assessment_type`, `total_marks`, `passing_marks`, `duration_minutes`, `instructions`, `questions_json`, `start_date`, `due_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'ac8652af-7bb9-48fe-923c-36d47f22ec98', 5, 3, NULL, '', 3, 'TEST', 'homework', 4.0, 8.0, 45, '', '[{\"id\":19,\"uuid\":\"296ba8e7-a52c-48c0-9d0f-7f90a5a2f61f\",\"teacher_user_id\":5,\"school_id\":3,\"subject_id\":3,\"chapter_id\":null,\"topic_id\":null,\"class_level\":\"Class X\",\"question_text\":\"According to Newton\'s Second Law of Motion, the formula for force F is given by:\",\"question_type\":\"mcq\",\"options_json\":\"[\\\"(a) F = m \\\\u00d7 a\\\",\\\"(b) F = m \\\\\\/ a\\\",\\\"(c) F = 1\\\\\\/2 m v\\\\u00b2\\\",\\\"(d) F = m \\\\u00d7 g \\\\u00d7 h\\\"]\",\"image_url\":null,\"correct_answer\":\"(a) F = m \\u00d7 a\",\"explanation\":\"Force is equal to mass multiplied by acceleration (rate of change of momentum).\",\"marks\":\"1.0\",\"difficulty\":\"medium\",\"created_at\":\"2026-10-09 06:23:57\",\"updated_at\":\"2026-10-09 06:23:57\",\"subject_name\":\"Chemistry\",\"chapter_name\":null,\"topic_name\":null,\"teacher_name\":\"TEST techaer 1\"},{\"id\":20,\"uuid\":\"af7a7084-faec-455e-bdc8-5e067e18c0cc\",\"teacher_user_id\":5,\"school_id\":3,\"subject_id\":3,\"chapter_id\":null,\"topic_id\":null,\"class_level\":\"Class X\",\"question_text\":\"What is the equivalent resistance when two resistors of 4 \\u03a9 and 4 \\u03a9 are connected in parallel?\",\"question_type\":\"mcq\",\"options_json\":\"[\\\"(a) 2 \\\\u03a9\\\",\\\"(b) 8 \\\\u03a9\\\",\\\"(c) 4 \\\\u03a9\\\",\\\"(d) 1 \\\\u03a9\\\"]\",\"image_url\":null,\"correct_answer\":\"(a) 2 \\u03a9\",\"explanation\":\"1\\/Rp = 1\\/4 + 1\\/4 = 2\\/4 = 1\\/2 => Rp = 2 \\u03a9.\",\"marks\":\"1.0\",\"difficulty\":\"medium\",\"created_at\":\"2026-10-09 06:23:57\",\"updated_at\":\"2026-10-09 06:23:57\",\"subject_name\":\"Chemistry\",\"chapter_name\":null,\"topic_name\":null,\"teacher_name\":\"TEST techaer 1\"},{\"id\":21,\"uuid\":\"8d37842e-dbc0-409b-86eb-df5ffa0a4f58\",\"teacher_user_id\":5,\"school_id\":3,\"subject_id\":3,\"chapter_id\":null,\"topic_id\":null,\"class_level\":\"Class X\",\"question_text\":\"According to Newton\'s Second Law of Motion, the formula for force F is given by:\",\"question_type\":\"mcq\",\"options_json\":\"[\\\"(a) F = m \\\\u00d7 a\\\",\\\"(b) F = m \\\\\\/ a\\\",\\\"(c) F = 1\\\\\\/2 m v\\\\u00b2\\\",\\\"(d) F = m \\\\u00d7 g \\\\u00d7 h\\\"]\",\"image_url\":null,\"correct_answer\":\"(a) F = m \\u00d7 a\",\"explanation\":\"Force is equal to mass multiplied by acceleration (rate of change of momentum).\",\"marks\":\"1.0\",\"difficulty\":\"medium\",\"created_at\":\"2026-10-09 06:23:57\",\"updated_at\":\"2026-10-09 06:23:57\",\"subject_name\":\"Chemistry\",\"chapter_name\":null,\"topic_name\":null,\"teacher_name\":\"TEST techaer 1\"},{\"id\":22,\"uuid\":\"95017085-5365-43b3-aeb9-35592398a13c\",\"teacher_user_id\":5,\"school_id\":3,\"subject_id\":3,\"chapter_id\":null,\"topic_id\":null,\"class_level\":\"Class X\",\"question_text\":\"What is the equivalent resistance when two resistors of 4 \\u03a9 and 4 \\u03a9 are connected in parallel?\",\"question_type\":\"mcq\",\"options_json\":\"[\\\"(a) 2 \\\\u03a9\\\",\\\"(b) 8 \\\\u03a9\\\",\\\"(c) 4 \\\\u03a9\\\",\\\"(d) 1 \\\\u03a9\\\"]\",\"image_url\":null,\"correct_answer\":\"(a) 2 \\u03a9\",\"explanation\":\"1\\/Rp = 1\\/4 + 1\\/4 = 2\\/4 = 1\\/2 => Rp = 2 \\u03a9.\",\"marks\":\"1.0\",\"difficulty\":\"medium\",\"created_at\":\"2026-10-09 06:23:57\",\"updated_at\":\"2026-10-09 06:23:57\",\"subject_name\":\"Chemistry\",\"chapter_name\":null,\"topic_name\":null,\"teacher_name\":\"TEST techaer 1\"}]', '2026-10-09 13:19:00', '2026-10-16 13:19:00', 'published', '2026-10-09 07:50:08', '2026-10-09 07:50:08'),
(2, '310ef3f4-6795-4d63-815b-41d72c2b852a', 5, 3, 1, '6', 10, 'Unit Test (UT) — 23 Oct 2026', 'unit_test', 20.0, 8.0, 60, 'Direct marks recorded by instructor.', '[]', NULL, '2026-10-23 23:59:59', 'published', '2026-10-23 11:30:50', '2026-10-23 11:30:50');

-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

CREATE TABLE `achievements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category` enum('academic','sports','creative','communication','problem_solving','competition','other') DEFAULT 'other',
  `badge_icon` varchar(255) DEFAULT NULL,
  `earned_at` datetime DEFAULT current_timestamp(),
  `awarded_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `category` enum('creative','academic','sports','cultural','technology','communication','competition') NOT NULL,
  `short_description` varchar(300) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `gallery` longtext DEFAULT NULL CHECK (json_valid(`gallery`)),
  `event_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `venue` varchar(200) DEFAULT NULL,
  `organizer` varchar(150) DEFAULT NULL,
  `eligibility` varchar(200) DEFAULT NULL,
  `eligible_classes` varchar(300) DEFAULT NULL,
  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `registration_deadline` date DEFAULT NULL,
  `registration_url` varchar(500) DEFAULT NULL,
  `fee` decimal(10,2) DEFAULT 0.00,
  `max_participants` int(10) UNSIGNED DEFAULT NULL,
  `contact_email` varchar(150) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `tags` varchar(300) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `status` enum('draft','published','closed','archived') DEFAULT 'published',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `uuid`, `title`, `slug`, `category`, `short_description`, `description`, `cover_image`, `gallery`, `event_date`, `start_time`, `end_time`, `venue`, `organizer`, `eligibility`, `eligible_classes`, `batch_id`, `registration_deadline`, `registration_url`, `fee`, `max_participants`, `contact_email`, `contact_phone`, `tags`, `featured`, `status`, `created_by`, `school_id`, `created_at`, `updated_at`) VALUES
(1, '573fee8d-d922-4be2-8f10-449fc111fa81', 'QUIZWIZ', 'quizwiz', 'competition', 'Quiz competition', 'There is a quiz competition for class 6-12', '/uploads/activities/27484c36c25127a1_1790330150.jpg', NULL, '2026-09-27', '08:00:00', '00:00:00', 'SCHOOL AUDITORIUM', 'QUIZWIZ', 'Ages 6-18', NULL, NULL, '2026-09-26', '', 0.00, 100, '', '', '', 1, 'published', 1, NULL, '2026-09-25 15:25:50', '2026-09-25 15:25:50');

-- --------------------------------------------------------

--
-- Table structure for table `activity_registrations`
--

CREATE TABLE `activity_registrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `activity_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('registered','approved','rejected','attended','absent') DEFAULT 'registered',
  `registered_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_registrations`
--

INSERT INTO `activity_registrations` (`id`, `activity_id`, `user_id`, `notes`, `status`, `registered_at`) VALUES
(1, 1, 6, NULL, 'attended', '2026-09-25 19:11:03'),
(2, 1, 1, NULL, 'registered', '2026-09-25 19:12:09');

-- --------------------------------------------------------

--
-- Table structure for table `activity_results`
--

CREATE TABLE `activity_results` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `activity_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `score` decimal(7,2) DEFAULT NULL,
  `position` int(10) UNSIGNED DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `recorded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `recorded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_results`
--

INSERT INTO `activity_results` (`id`, `activity_id`, `student_user_id`, `score`, `position`, `remarks`, `recorded_by`, `recorded_at`) VALUES
(1, 1, 6, 100.00, 1, NULL, 5, '2026-10-23 11:31:21');

-- --------------------------------------------------------

--
-- Table structure for table `aiverse_banners`
--

CREATE TABLE `aiverse_banners` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` text DEFAULT NULL,
  `badge` varchar(100) DEFAULT 'Special Offer',
  `cta_text` varchar(100) DEFAULT 'Explore Bundles',
  `cta_link` varchar(255) DEFAULT '#bundles',
  `bg_gradient` varchar(255) DEFAULT 'indigo-purple',
  `image_url` varchar(500) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `aiverse_banners`
--

INSERT INTO `aiverse_banners` (`id`, `title`, `subtitle`, `badge`, `cta_text`, `cta_link`, `bg_gradient`, `image_url`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '🎁 Mega Launch: Buy GenAI Track, Get Python for AI 100% FREE!', 'Kickstart your AI engineering career. Enroll in Generative AI & LLM Engineering today and receive the complete Python for AI Foundation course at zero cost (Save ₹3,999)!', '⚡ LIMITED TIME BOGO LAUNCH', 'Claim Free Course Offer →', '#bundles', 'linear-gradient(135deg, #1e1b4b 0%, #31104b 50%, #0f172a 100%)', NULL, 1, 1, '2026-10-21 09:07:09', '2026-10-21 09:07:09'),
(2, '📦 Complete Agentic AI Master Bundle — Flat 10% Discount!', 'Master Model Context Protocol (MCP), CrewAI agent swarms, Azure AI Foundry, and LLMOps in one comprehensive combo package with verified credentials.', '🔥 MASSIVE COMBO SAVINGS', 'Explore Bundles & Save 10% →', '#bundles', 'linear-gradient(135deg, #064e3b 0%, #0f172a 60%, #1e1b4b 100%)', NULL, 2, 1, '2026-10-21 09:07:09', '2026-10-21 09:07:09');

-- --------------------------------------------------------

--
-- Table structure for table `aiverse_bundles`
--

CREATE TABLE `aiverse_bundles` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `tagline` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `badge` varchar(100) DEFAULT 'Save 10%',
  `course_ids` text NOT NULL,
  `original_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bundle_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_pct` decimal(5,2) NOT NULL DEFAULT 10.00,
  `is_bogo` tinyint(1) NOT NULL DEFAULT 0,
  `bogo_primary_course_id` int(11) UNSIGNED DEFAULT NULL,
  `bogo_free_course_id` int(11) UNSIGNED DEFAULT NULL,
  `status` enum('published','draft','archived') NOT NULL DEFAULT 'published',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `aiverse_bundles`
--

INSERT INTO `aiverse_bundles` (`id`, `title`, `slug`, `tagline`, `description`, `badge`, `course_ids`, `original_price`, `bundle_price`, `discount_pct`, `is_bogo`, `bogo_primary_course_id`, `bogo_free_course_id`, `status`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'Complete Generative AI & Agentic AI Master Bundle', 'complete-genai-agentic-master-bundle', 'All 4 Core Specializations: Prompting, Multi-Agent Swarms, MCP & Azure AI Foundry', 'The complete package for mastering generative and agentic systems. Includes Model Context Protocol, CrewAI/LangGraph, and Azure AI Foundry with 10% massive combo savings.', 'Save 10% · Combo', '[1,3,6,7]', 37996.00, 34196.00, 10.00, 0, NULL, NULL, 'published', 1, '2026-10-21 09:07:09', '2026-10-21 09:32:27'),
(2, 'BOGO Special: Buy GenAI Track, Get Python for AI 100% FREE', 'bogo-genai-plus-free-python', 'Enroll in Generative AI & LLM Engineering and receive Python for AI at zero extra cost!', 'Perfect launching pack! Enroll in our flagship GenAI engineering track and get the complete Python for AI Foundation course completely free (Save ₹3,999)!', '🎁 BUY 1 GET 1 FREE', '[1,2]', 20998.00, 16999.00, 19.00, 1, 1, 2, 'published', 2, '2026-10-21 09:07:09', '2026-10-21 09:07:09'),
(3, 'Agent Engineering & MCP Super-Pack', 'agent-engineering-mcp-superpack', 'Autonomous Swarms (CrewAI, LangGraph) + Model Context Protocol (MCP) Tooling', 'Targeted combo for developers who want to specialize exclusively in building and orchestrating next-generation autonomous AI agents and standard MCP servers.', '🔥 8% Off Combo', '[3,6]', 13998.00, 12878.16, 8.00, 0, NULL, NULL, 'published', 3, '2026-10-21 09:07:09', '2026-10-21 09:07:09');

-- --------------------------------------------------------

--
-- Table structure for table `aiverse_courses`
--

CREATE TABLE `aiverse_courses` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `tagline` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'Artificial Intelligence',
  `level` varchar(50) NOT NULL DEFAULT 'Beginner to Intermediate',
  `duration` varchar(100) NOT NULL DEFAULT '8 Weeks',
  `fee` decimal(10,2) NOT NULL DEFAULT 4999.00,
  `badge` varchar(50) DEFAULT 'Featured Track',
  `overview` text DEFAULT NULL,
  `curriculum` text DEFAULT NULL,
  `outcomes` text DEFAULT NULL,
  `prerequisites` text DEFAULT NULL,
  `includes` text DEFAULT NULL,
  `status` enum('published','draft','archived') NOT NULL DEFAULT 'published',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `aiverse_courses`
--

INSERT INTO `aiverse_courses` (`id`, `title`, `slug`, `tagline`, `category`, `level`, `duration`, `fee`, `badge`, `overview`, `curriculum`, `outcomes`, `prerequisites`, `includes`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Generative AI & LLM Engineering', 'generative-ai-llm-engineering', 'Master Prompt Engineering, LangChain, RAG architectures & Vector DBs', 'Generative AI', 'Beginner to Intermediate', '8 Weeks · 32 Live Hours', 16999.00, 'Featured Track', 'An end-to-end immersion into modern Generative AI. Learn to design, evaluate, and deploy enterprise Large Language Model (LLM) applications with hands-on labs.', '• Module 1: Foundations of Foundation Models & Attention Mechanism\r\n• Module 2: Advanced Prompt Engineering & Structured JSON Outputs\r\n• Module 3: Vector Embeddings, Chunking & Semantic Search\r\n• Module 4: Building RAG with LangChain & LlamaIndex\r\n• Module 5: Open-Source Models & Local Inference (Ollama/vLLM)\r\n• Module 6: Model Fine-Tuning & Parameter-Efficient Tuning\r\n• Module 7: LLM Evaluation, Guardrails & Responsible AI\r\n• Module 8: Capstone Project & Enterprise Deployment', '• Master prompt crafting, system prompting & few-shot reasoning\r\n• Build production RAG (Retrieval-Augmented Generation) pipelines\r\n• Integrate Vector databases (Pinecone, ChromaDB, Qdrant)\r\n• Fine-tune open-weights models (Llama 3, Mistral) with PEFT / LoRA\r\n• Deploy production-ready GenAI REST APIs with FastAPI & Docker', 'Basic knowledge of Python programming and web fundamentals.', '• 32 Hours of Live Interactive Sessions & Code-Alongs• 4 Real-world Portfolio Projects & Capstone• Verified Industry Certificate from AIVERSE & Infinity Learning• Lifetime Access to Course Notebooks & GitHub Repositories• 1-on-1 Code Review & Mentor Guidance', 'published', '2026-10-21 08:05:35', '2026-10-21 08:07:06'),
(2, 'Python for Artificial Intelligence', 'python-for-artificial-intelligence', 'From programming fundamentals to NumPy, Pandas, PyTorch & Scikit-Learn', 'Programming & Data Science', 'Complete Beginner', '6 Weeks · 24 Live Hours', 3999.00, 'Popular', 'The essential launching pad for your AI journey. Build rock-solid Python programming skills tailored explicitly for Machine Learning, data manipulation, and mathematical modeling.', '• Module 1: Python Fundamentals, Data Structures & Control Flow\n• Module 2: Functions, Lambda Expressions & Object-Oriented Python\n• Module 3: Numerical Python (NumPy) & Matrix Algebra\n• Module 4: Data Wrangling & Analysis with Pandas\n• Module 5: Data Visualization with Matplotlib & Seaborn\n• Module 6: Introduction to Machine Learning Pipelines with Scikit-Learn', '• Write idiomatic, clean Python 3.12+ code and object-oriented architectures\n• Vectorized numerical computing with NumPy\n• Fast tabular data manipulation, cleaning & analysis using Pandas\n• Exploratory Data Analysis & visual storytelling with Matplotlib & Seaborn\n• Training and evaluating classical machine learning models with Scikit-Learn', 'No prior coding experience needed. Ideal for high school students, college learners & beginners.', '• 24 Hours of Hands-on Live Coding Classes\n• 20+ Practice Notebooks & Interactive Problem Sets\n• Course Completion Certificate\n• 24/7 AI Coding Assistant inside Infinity Learning', 'published', '2026-10-21 08:05:35', '2026-10-21 08:05:35'),
(3, 'Autonomous AI Agents & Multi-Agent Systems', 'autonomous-ai-agents-multi-agent', 'Build goal-directed agents with AutoGen, CrewAI, LangGraph & Tool Calling', 'Agentic Systems', 'Intermediate to Advanced', '8 Weeks · 32 Live Hours', 7999.00, 'Trending', 'Step into the next frontier of AI: Autonomous Agents that reason, plan, use tools, browse the web, write code, and collaborate in multi-agent swarms to accomplish complex objectives.', '• Module 1: Architecture of Autonomous Agents (Perception, Memory, Planning, Action)\n• Module 2: Tool Use & Function Calling with Gemini & OpenAI APIs\n• Module 3: Memory Architectures & Hierarchical Task Decomposition\n• Module 4: Building Workflows with LangGraph State Machines\n• Module 5: Multi-Agent Teams with CrewAI & AutoGen\n• Module 6: Human-in-the-Loop, Sandboxing & Security Controls\n• Module 7: Real-world Agent Projects (Researcher, Developer, Analyst)\n• Module 8: Multi-Agent Capstone Deployment', '• Understand ReAct reasoning loops, Plan-and-Solve & Reflection patterns\n• Equip agents with custom tool calling, shell access & web search\n• Architect multi-agent collaboration with CrewAI and LangGraph\n• Persist agent memory across sessions (Short-term, Long-term, Episodic)\n• Build automated agents for market research, code auditing, and customer support', 'Comfortable with Python and basic API / LLM prompting concepts.', '• 32 Hours of Live Interactive Workshops\n• Complete Source Code for 5 Production AI Agents\n• Official AIVERSE Agentic AI Specialist Certificate\n• Direct access to AI Practitioners community', 'published', '2026-10-21 08:05:35', '2026-10-21 08:05:35'),
(4, 'Deep Learning & Computer Vision', 'deep-learning-computer-vision', 'Convolutional Neural Networks, Vision Transformers & Object Detection', 'Computer Vision', 'Intermediate', '8 Weeks · 30 Live Hours', 5999.00, 'Comprehensive', 'Discover how computers understand visual data. Train neural networks from scratch and build vision systems for face recognition, medical imaging, self-driving cars, and object tracking.', '• Module 1: Artificial Neural Networks & PyTorch Fundamentals\n• Module 2: Image Processing with OpenCV\n• Module 3: Convolutional Neural Networks (CNNs) & Feature Extraction\n• Module 4: Transfer Learning with ResNet, EfficientNet & MobileNet\n• Module 5: Object Detection & Tracking with YOLO\n• Module 6: Image Segmentation (Mask R-CNN, SAM)\n• Module 7: Vision Transformers (ViT) & Diffusion Models Intro\n• Module 8: Edge AI Deployment & Capstone', '• Deep neural network architectures, backpropagation & gradient descent\n• Image processing with OpenCV and PIL\n• Convolutional Neural Networks (CNNs), ResNet, and transfer learning in PyTorch\n• Real-time Object Detection with YOLOv8 & YOLOv10\n• Vision Transformers (ViT) and modern multimodal vision models', 'Python proficiency and foundational algebra / calculus.', '• 30 Hours of Interactive PyTorch Live Sessions\n• Cloud GPU environment guidance for model training\n• Certificate of Accomplishment\n• CV Portfolio Capstone project', 'published', '2026-10-21 08:05:35', '2026-10-21 08:05:35'),
(5, 'Full-Stack AI Application Development', 'full-stack-ai-application-development', 'Create web applications powered by AI APIs, streaming, and modern UI', 'Full-Stack AI', 'All Levels', '8 Weeks · 32 Live Hours', 6499.00, 'Hands-on', 'Transform AI models into polished, production-ready web products. Learn to build reactive web apps with real-time token streaming, multimodal input, and secure authentication.', '• Module 1: Modern Full-Stack AI Architecture Overview\n• Module 2: Building Backends for AI with Python / PHP\n• Module 3: Streaming Responses & Server-Sent Events (SSE)\n• Module 4: Multimodal Inputs (Audio, PDF, Images)\n• Module 5: Database Persistence & User State Management\n• Module 6: Rate Limiting, API Security & Cost Optimization\n• Module 7: Production CI/CD & Cloud Hosting\n• Module 8: End-to-End AI SaaS Capstone Launch', '• Design interactive AI web interfaces with streaming responses\n• Secure backend API integration with rate limiting and key management\n• Multimodal UI (audio recording, image uploads, document parsing)\n• User authentication, subscription billing, and usage tracking\n• Cloud deployment on production infrastructure', 'Basic HTML/CSS, JavaScript or Python knowledge.', '• 32 Live Instructional Hours\n• Full SaaS Boilerplate Code Template\n• Verified Full-Stack AI Developer Certificate\n• Lifetime Updates & Community Access', 'published', '2026-10-21 08:05:35', '2026-10-21 08:05:35'),
(6, 'Model Context Protocol (MCP) & AI Tooling Systems', 'mcp-ai-tooling-frameworks', 'Build standardized MCP servers, client tools & dynamic context injection', 'Model Context Protocols', 'Intermediate', '6 Weeks · 24 Live Hours', 5999.00, 'New Standard', 'Dive deep into the revolutionary Model Context Protocol (MCP). Learn how to expose local databases, filesystem context, secure enterprise tools, and cloud APIs to modern LLM agents seamlessly.', '• Module 1: Introduction to MCP & The Universal AI Tooling Standard\n• Module 2: Building Your First MCP Server in Python (FastMCP)\n• Module 3: Tools, Resources & Prompts Primitive deep dive\n• Module 4: Connecting SQL Databases & Internal APIs via MCP\n• Module 5: Transport Layers: Stdio vs SSE (Server-Sent Events)\n• Module 6: Enterprise Security, RBAC & Production MCP Deployment', '• Understand the core architecture of MCP (Host, Client, Server)\n• Build high-performance MCP servers in Python and TypeScript\n• Expose SQL databases, document stores & terminal tools via standard protocols\n• Orchestrate multi-server contexts for Claude, Gemini, and local models\n• Implement enterprise token security, authentication & sandboxing for MCP', 'Basic knowledge of Python or JavaScript.', '• 24 Live Hours & Interactive Hands-on Labs\n• Production-ready MCP Server Templates\n• Official MCP Developer Credential\n• Dedicated Mentor Code Audits', 'published', '2026-10-21 09:07:09', '2026-10-21 09:07:09'),
(7, 'Enterprise GenAI on Azure & Microsoft Foundry', 'azure-genai-microsoft-foundry', 'Azure OpenAI, Prompt Flow, Azure AI Foundry SDK & Enterprise Security', 'Cloud AI & Enterprise', 'Intermediate to Advanced', '8 Weeks · 32 Live Hours', 6999.00, 'Enterprise Ready', 'Architect scalable, compliant Generative AI applications on Microsoft Azure. Leverage Azure OpenAI Service, Azure AI Search, Prompt Flow orchestration, and Foundry IQ knowledge agents.', '• Module 1: Azure AI Ecosystem & Azure OpenAI Provisioning\n• Module 2: Enterprise RAG Architecture on Azure AI Search\n• Module 3: Prompt Flow: Authoring, Testing & Variant Evaluation\n• Module 4: Azure AI Foundry SDK & Agentic Integration\n• Module 5: Foundry IQ & Multi-Source Knowledge Connectors\n• Module 6: Enterprise Security, VNet Integration & Managed Identities\n• Module 7: Cost Management, Provisioned Throughput & Quotas\n• Module 8: Enterprise Capstone Project & Azure Architecture Review', '• Deploy Azure OpenAI models (GPT-4o, embeddings) with private endpoints\n• Build hybrid vector search with Azure AI Search and semantic ranking\n• Design and test evaluation pipelines using Prompt Flow\n• Build knowledge-enhanced reasoning agents using Foundry IQ\n• Configure enterprise RBAC, network isolation & data residency', 'Familiarity with cloud concepts and Python programming.', '• 32 Hours of Instructor-Led Labs\n• Azure Cloud Lab Exercise Guides\n• Verified Enterprise AI Specialist Certificate\n• Architecture Review from Microsoft Certified Experts', 'published', '2026-10-21 09:07:09', '2026-10-21 09:07:09'),
(8, 'LLMOps, Production Deployment & AI Governance', 'llmops-deployment-governance', 'CI/CD for LLMs, evaluation benchmarks, guardrails, ethics & regulatory compliance', 'LLMOps & Governance', 'Intermediate', '6 Weeks · 24 Live Hours', 5499.00, 'Crucial Skill', 'Operationalize foundation models reliably in production. Automate evals, monitor token drift and hallucination rates, enforce safety guardrails, and comply with global AI safety regulations.', '• Module 1: The LLMOps Lifecycle vs Traditional MLOps\n• Module 2: Automated Evals (Ragas, TruLens, DeepEval)\n• Module 3: Guardrail Engineering & Safety Policy Enforcement\n• Module 4: Production Observability, Tracing & Cost Monitoring\n• Module 5: Red Teaming & Adversarial Attack Defenses\n• Module 6: AI Governance, Copyright, Ethics & Compliance', '• Build automated evaluation pipelines for prompt and model changes\n• Implement LLM guardrails (NeMo Guardrails, Llama Guard)\n• Monitor production latency, token costs, and drift with OpenTelemetry\n• Implement red-teaming, prompt injection defenses & jailbreak mitigations\n• Navigate EU AI Act, NIST AI RMF & enterprise governance frameworks', 'Basic understanding of LLMs and web services.', '• 24 Live Hours of Real-world Case Studies\n• Complete CI/CD Evaluation Boilerplate\n• Certificate in LLMOps & AI Governance\n• 1-on-1 Portfolio Feedback', 'published', '2026-10-21 09:07:09', '2026-10-21 09:07:09'),
(9, 'Professional Certificate in Generative AI & Agentic AI', 'generative-agentic-ai-master-program', 'Complete 5-Phase Immersion: Foundations, MCP, Multi-Agent Swarms & Foundry Capstone', 'Flagship Master Track', 'Comprehensive (Zero to Hero)', '16 Weeks · 64 Live Hours', 14999.00, 'Flagship Master Track', 'The ultimate industry credential for future AI Architects and Engineers. Covers the complete 5-Phase curriculum: Python for AI, Prompt Engineering & RAG, MCP Tooling, Multi-Agent Engineering (CrewAI/LangGraph), Azure AI Foundry, LLMOps, and a real-world enterprise capstone.', '• Phase 1: Python for AI & Foundations of Agentic Intelligence\n• Phase 2: Context-Driven RAG, Multimodal AI & Model Context Protocol (MCP)\n• Phase 3: Agent Engineering, Multi-Agent Swarms & LLMOps Pipeline\n• Phase 4: Azure AI Foundry, Knowledge Retrieval & AI Governance\n• Phase 5: Production Capstone Project & Faculty Masterclasses', '• Complete end-to-end mastery of Generative AI, RAG, and Autonomous Agents\n• Build production MCP servers and custom AI agent tools\n• Architect multi-agent collaborative workflows with CrewAI and LangGraph\n• Deploy enterprise-grade knowledge agents on Microsoft Azure Foundry\n• Ship an industry-ready Capstone evaluated by AI practitioners', 'No prior experience required; starts from Python fundamentals and scales to enterprise AI engineering.', '• 64 Hours of Interactive Live Sessions & Code Reviews\n• 5 Full Portfolio Projects + Industry Capstone\n• Dual Certification from AIVERSE & Infinity Learning\n• Priority Placement Support & Practitioner Community Access', 'published', '2026-10-21 09:07:09', '2026-10-21 09:07:09');

-- --------------------------------------------------------

--
-- Table structure for table `aiverse_enrollments`
--

CREATE TABLE `aiverse_enrollments` (
  `id` int(11) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` int(11) UNSIGNED NOT NULL,
  `fee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending','active','completed','cancelled') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_answers`
--

CREATE TABLE `ai_answers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ai_question_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `answer_text` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `ai_feedback` text DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `answered_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_questions`
--

CREATE TABLE `ai_questions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ai_session_id` bigint(20) UNSIGNED NOT NULL,
  `question_text` text NOT NULL,
  `expected_answer` text DEFAULT NULL,
  `difficulty` enum('easy','medium','hard') DEFAULT 'medium',
  `generated_by` enum('ai','teacher','imported') DEFAULT 'ai',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_sessions`
--

CREATE TABLE `ai_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `topic_id` bigint(20) UNSIGNED DEFAULT NULL,
  `mode` enum('explain','practice','evaluate','recommend','chat') DEFAULT 'practice',
  `context` longtext DEFAULT NULL CHECK (json_valid(`context`)),
  `started_at` datetime DEFAULT current_timestamp(),
  `ended_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `audience` enum('all','students','teachers','admins') DEFAULT 'all',
  `pinned` tinyint(1) DEFAULT 0,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `body`, `audience`, `pinned`, `created_by`, `school_id`, `created_at`) VALUES
(1, 'Quizwiz', 'There is going to be a quiz competation please participate.', 'all', 0, 1, 3, '2026-09-25 19:13:21'),
(2, 'Hello', 'Sta', 'all', 0, 4, 3, '2026-10-10 11:14:01'),
(3, 'importent', 'Nothing', 'teachers', 1, 4, 3, '2026-10-24 10:10:50'),
(4, 'hello', 'hello', 'all', 1, 4, 3, '2026-10-24 10:44:29');

-- --------------------------------------------------------

--
-- Table structure for table `assessment_submissions`
--

CREATE TABLE `assessment_submissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `assessment_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `answers_json` longtext DEFAULT NULL,
  `score_obtained` decimal(5,1) DEFAULT NULL,
  `is_evaluated` tinyint(1) NOT NULL DEFAULT 0,
  `teacher_feedback` text DEFAULT NULL,
  `status` enum('submitted','evaluated','late') NOT NULL DEFAULT 'submitted',
  `submitted_at` datetime DEFAULT current_timestamp(),
  `evaluated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assessment_submissions`
--

INSERT INTO `assessment_submissions` (`id`, `uuid`, `assessment_id`, `student_user_id`, `answers_json`, `score_obtained`, `is_evaluated`, `teacher_feedback`, `status`, `submitted_at`, `evaluated_at`) VALUES
(1, '243793bf-7895-4eaa-acea-961f55712af2', 1, 6, '{\"19\":\"(a) F = m \\u00d7 a\",\"20\":\"(b) 8 \\u03a9\",\"21\":\"(b) F = m \\/ a\",\"22\":\"(b) 8 \\u03a9\"}', 1.0, 1, NULL, 'submitted', '2026-10-09 07:50:35', NULL),
(2, '7b09541f-d6a7-4a85-b91b-071134394b94', 2, 6, NULL, 20.0, 1, NULL, 'evaluated', '2026-10-23 11:30:50', '2026-10-24 09:20:19');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `batch_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('present','absent','late','excused') NOT NULL,
  `marked_by` bigint(20) UNSIGNED DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `batch_id`, `student_user_id`, `attendance_date`, `status`, `marked_by`, `remarks`, `created_at`) VALUES
(1, 1, 6, '2026-09-25', 'present', 5, NULL, '2026-09-25 15:21:32'),
(2, 1, 6, '2026-09-24', 'absent', 5, NULL, '2026-09-25 19:28:59'),
(3, 1, 6, '2026-09-30', 'present', 5, NULL, '2026-09-30 09:11:50'),
(4, 4, 6, '2026-10-24', 'present', 17, NULL, '2026-10-24 10:27:18'),
(5, 1, 23, '2026-10-26', 'present', 5, NULL, '2026-10-26 14:58:55'),
(6, 1, 6, '2026-10-26', 'present', 5, NULL, '2026-10-26 14:58:55');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_sessions`
--

CREATE TABLE `attendance_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `batch_id` bigint(20) UNSIGNED NOT NULL,
  `session_date` date NOT NULL,
  `session_label` varchar(100) DEFAULT NULL,
  `marked_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `metadata` longtext DEFAULT NULL CHECK (json_valid(`metadata`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `metadata`, `ip_address`, `created_at`) VALUES
(1, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-24 12:28:03'),
(2, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 14:58:31'),
(3, 1, 'school.created', 'school', 1, '{\"name\":\"TEST SCHOOL\",\"code\":\"TS-6183\"}', '::1', '2026-09-25 14:59:33'),
(4, 2, 'user.login', 'user', 2, NULL, '::1', '2026-09-25 15:00:01'),
(5, 2, 'user.logout', 'user', 2, NULL, '::1', '2026-09-25 15:05:08'),
(6, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 15:05:26'),
(7, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-25 15:06:14'),
(8, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 15:07:11'),
(9, 1, 'school.created', 'school', 3, '{\"name\":\"Test SCHOOL\",\"code\":\"TS-F59C\"}', '::1', '2026-09-25 15:10:43'),
(10, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-25 15:11:25'),
(11, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"testteacher@gmail.com\",\"role\":\"teacher\",\"school_id\":3}', '::1', '2026-09-25 15:12:24'),
(12, NULL, 'registration.completed', 'user', 5, '{\"role\":\"teacher\",\"school_id\":3}', '::1', '2026-09-25 15:12:35'),
(13, 4, 'user.login', 'user', 4, NULL, '::1', '2026-09-25 15:12:51'),
(14, 4, 'school.user_approved', 'user', 5, '{\"school_id\":3}', '::1', '2026-09-25 15:12:57'),
(15, 4, 'user.logout', 'user', 4, NULL, '::1', '2026-09-25 15:13:30'),
(16, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 15:13:45'),
(17, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-25 15:14:54'),
(18, 4, 'user.login', 'user', 4, NULL, '::1', '2026-09-25 15:15:05'),
(19, 4, 'user.logout', 'user', 4, NULL, '::1', '2026-09-25 15:15:20'),
(20, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"stu@gmail.com\",\"role\":\"student\",\"school_id\":3}', '::1', '2026-09-25 15:16:36'),
(21, NULL, 'registration.completed', 'user', 6, '{\"role\":\"student\",\"school_id\":3}', '::1', '2026-09-25 15:17:04'),
(22, 4, 'user.login', 'user', 4, NULL, '::1', '2026-09-25 15:17:22'),
(23, 4, 'school.user_approved', 'user', 6, '{\"school_id\":3}', '::1', '2026-09-25 15:17:51'),
(24, 4, 'user.logout', 'user', 4, NULL, '::1', '2026-09-25 15:19:32'),
(25, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 15:20:18'),
(26, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-25 15:20:28'),
(27, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-25 15:20:34'),
(28, 5, 'batch.created', 'batch', 1, '{\"name\":\"science class 6\"}', '::1', '2026-09-25 15:20:56'),
(29, 5, 'batch.student_added', 'batch', 1, '{\"student\":6}', '::1', '2026-09-25 15:21:14'),
(30, 5, 'attendance.marked', 'batch', 1, '{\"date\":\"2026-09-25\",\"count\":1}', '::1', '2026-09-25 15:21:32'),
(31, 5, 'user.logout', 'user', 5, NULL, '::1', '2026-09-25 15:23:16'),
(32, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 15:23:29'),
(33, 1, 'activity.created', 'activity', 1, '{\"title\":\"QUIZWIZ\"}', '::1', '2026-09-25 15:25:50'),
(34, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-25 15:26:09'),
(35, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 15:40:17'),
(36, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-25 17:29:43'),
(37, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 17:30:20'),
(38, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-25 17:30:44'),
(39, 6, 'user.login', 'user', 6, NULL, '::1', '2026-09-25 17:30:53'),
(40, 6, 'password.changed', 'user', 6, NULL, '::1', '2026-09-25 18:39:45'),
(41, 6, 'password.change_failed', 'user', 6, '{\"reason\":\"wrong_current\"}', '::1', '2026-09-25 18:42:50'),
(42, 6, 'password.changed', 'user', 6, NULL, '::1', '2026-09-25 18:43:12'),
(43, 6, 'activity.registered', 'activity', 1, NULL, '::1', '2026-09-25 19:11:03'),
(44, 6, 'profile.updated', 'user', 6, NULL, '::1', '2026-09-25 19:11:21'),
(45, 6, 'user.logout', 'user', 6, NULL, '::1', '2026-09-25 19:11:35'),
(46, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 19:11:53'),
(47, 1, 'activity.registered', 'activity', 1, NULL, '::1', '2026-09-25 19:12:09'),
(48, 1, 'announcement.created', 'announcement', 1, '{\"audience\":\"all\",\"school_id\":3}', '::1', '2026-09-25 19:13:21'),
(49, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-25 19:13:26'),
(50, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 19:14:28'),
(51, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-25 19:14:37'),
(52, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-25 19:14:44'),
(53, 5, 'attendance.marked', 'batch', 1, '{\"date\":\"2026-09-24\",\"count\":1}', '::1', '2026-09-25 19:28:59'),
(54, 5, 'user.logout', 'user', 5, NULL, '::1', '2026-09-25 19:29:13'),
(55, 6, 'user.login', 'user', 6, NULL, '::1', '2026-09-25 19:29:30'),
(56, 6, 'user.logout', 'user', 6, NULL, '::1', '2026-09-25 19:29:58'),
(57, 4, 'user.login', 'user', 4, NULL, '::1', '2026-09-25 19:30:16'),
(58, 4, 'user.logout', 'user', 4, NULL, '::1', '2026-09-25 19:31:56'),
(59, 6, 'user.login', 'user', 6, NULL, '::1', '2026-09-25 19:32:06'),
(60, 6, 'user.logout', 'user', 6, NULL, '::1', '2026-09-25 19:32:42'),
(61, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-25 19:33:34'),
(62, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-25 19:33:44'),
(63, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-25 19:33:49'),
(64, 5, 'activity.assigned', 'batch', 1, '{\"activity_id\":1,\"assignment_id\":1}', '::1', '2026-09-25 19:34:46'),
(65, 5, 'user.logout', 'user', 5, NULL, '::1', '2026-09-25 19:46:33'),
(66, 4, 'user.login', 'user', 4, NULL, '::1', '2026-09-25 19:46:47'),
(67, 4, 'lesson.created', 'lesson', 2, '{\"title\":\"qqqqqqq\",\"school_id\":3,\"batch_id\":null,\"attachments\":1}', '::1', '2026-09-25 19:54:14'),
(68, 4, 'user.logout', 'user', 4, NULL, '::1', '2026-09-25 20:10:25'),
(69, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-25 20:10:47'),
(70, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-26 13:39:06'),
(71, 1, 'school.created', 'school', 4, '{\"name\":\"ISL Dhanbad\",\"code\":\"ID-E799\"}', '::1', '2026-09-26 13:41:12'),
(72, 1, 'school.created', 'school', 5, '{\"name\":\"DAV Ranchi\",\"code\":\"DR-03CA\"}', '::1', '2026-09-26 13:42:38'),
(73, 1, 'school.created', 'school', 6, '{\"name\":\"DAV KDPS\",\"code\":\"DK-95BB\"}', '::1', '2026-09-26 13:44:31'),
(74, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-26 13:45:59'),
(75, 9, 'user.login', 'user', 9, NULL, '::1', '2026-09-26 13:51:53'),
(76, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-26 13:52:58'),
(77, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-26 13:53:20'),
(78, 9, 'user.login', 'user', 9, NULL, '::1', '2026-09-26 13:53:40'),
(79, 9, 'user.logout', 'user', 9, NULL, '::1', '2026-09-26 13:53:47'),
(80, 8, 'user.login', 'user', 8, NULL, '::1', '2026-09-26 13:54:10'),
(81, 8, 'user.logout', 'user', 8, NULL, '::1', '2026-09-26 13:54:35'),
(82, 7, 'user.login', 'user', 7, NULL, '::1', '2026-09-26 13:54:43'),
(83, 9, 'user.login', 'user', 9, NULL, '::1', '2026-09-26 13:54:50'),
(84, 7, 'user.logout', 'user', 7, NULL, '::1', '2026-09-26 13:54:50'),
(85, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"testteacher@dav.com\",\"role\":\"teacher\",\"school_id\":5}', '::1', '2026-09-26 13:56:04'),
(86, NULL, 'registration.completed', 'user', 10, '{\"role\":\"teacher\",\"school_id\":5}', '::1', '2026-09-26 13:56:13'),
(87, 8, 'user.login', 'user', 8, NULL, '::1', '2026-09-26 13:57:13'),
(88, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"nishant012543@gmail.com\",\"role\":\"teacher\",\"school_id\":6}', '::1', '2026-09-26 13:57:17'),
(89, 8, 'school.user_approved', 'user', 10, '{\"school_id\":5}', '::1', '2026-09-26 13:57:22'),
(90, 8, 'user.logout', 'user', 8, NULL, '::1', '2026-09-26 13:57:25'),
(91, 10, 'user.login', 'user', 10, NULL, '::1', '2026-09-26 13:57:37'),
(92, 10, 'user.logout', 'user', 10, NULL, '::1', '2026-09-26 13:57:51'),
(93, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"nishant012543@gmail.com\",\"role\":\"teacher\",\"school_id\":6}', '::1', '2026-09-26 13:58:11'),
(94, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-26 14:12:25'),
(95, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"nishant012543@gmail.com\",\"role\":\"teacher\",\"school_id\":6}', '127.0.0.1', '2026-09-26 14:25:43'),
(96, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"nishant012543@gmail.com\",\"role\":\"teacher\",\"school_id\":6}', '127.0.0.1', '2026-09-26 14:33:01'),
(97, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-26 21:53:35'),
(98, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-27 20:00:40'),
(99, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-27 20:00:45'),
(100, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"kumaraviraj682@gmail.com\",\"role\":\"student\",\"school_id\":6}', '::1', '2026-09-28 11:52:57'),
(101, NULL, 'registration.completed', 'user', 11, '{\"role\":\"student\",\"school_id\":6}', '::1', '2026-09-28 11:53:23'),
(102, 9, 'user.login', 'user', 9, NULL, '::1', '2026-09-28 11:54:04'),
(103, 9, 'school.user_approved', 'user', 11, '{\"school_id\":6}', '::1', '2026-09-28 11:54:15'),
(104, 9, 'user.logout', 'user', 9, NULL, '::1', '2026-09-28 11:54:48'),
(105, 11, 'user.login', 'user', 11, NULL, '::1', '2026-09-28 11:55:05'),
(106, 11, 'user.logout', 'user', 11, NULL, '::1', '2026-09-28 11:55:31'),
(107, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-28 11:59:02'),
(108, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-28 12:20:14'),
(109, 1, 'user.login', 'user', 1, NULL, '::1', '2026-09-28 12:21:08'),
(110, 1, 'user.logout', 'user', 1, NULL, '::1', '2026-09-28 12:21:27'),
(111, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-28 12:21:50'),
(112, 5, 'certificate.generated', 'certificate', 1, '{\"student_id\":6,\"type\":\"winner\"}', '::1', '2026-09-28 12:22:13'),
(113, 5, 'user.logout', 'user', 5, NULL, '::1', '2026-09-28 12:22:37'),
(114, 6, 'user.login', 'user', 6, NULL, '::1', '2026-09-28 12:22:49'),
(115, 6, 'user.logout', 'user', 6, NULL, '::1', '2026-09-28 12:26:52'),
(116, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-28 12:27:10'),
(117, 5, 'certificate.generated', 'certificate', 2, '{\"student_id\":6,\"type\":\"winner\"}', '::1', '2026-09-28 12:27:28'),
(118, 5, 'user.logout', 'user', 5, NULL, '::1', '2026-09-28 12:27:31'),
(119, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-28 12:28:13'),
(120, 5, 'user.logout', 'user', 5, NULL, '::1', '2026-09-28 12:28:22'),
(121, 6, 'user.login', 'user', 6, NULL, '::1', '2026-09-28 12:28:33'),
(122, 6, 'user.logout', 'user', 6, NULL, '::1', '2026-09-28 12:32:16'),
(123, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"test@test.com\",\"role\":\"student\",\"school_id\":3}', '::1', '2026-09-28 12:34:09'),
(124, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-28 12:35:12'),
(125, 5, 'certificate.generated', 'certificate', 3, '{\"student_id\":6,\"type\":\"runner_up\"}', '::1', '2026-09-28 12:35:38'),
(126, 5, 'user.logout', 'user', 5, NULL, '::1', '2026-09-28 12:35:40'),
(127, 6, 'user.login', 'user', 6, NULL, '::1', '2026-09-28 12:35:49'),
(128, 6, 'user.logout', 'user', 6, NULL, '::1', '2026-09-28 12:39:36'),
(129, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-28 12:39:47'),
(130, 5, 'certificate.generated', 'certificate', 4, '{\"student_id\":6,\"type\":\"runner_up\"}', '::1', '2026-09-28 12:40:02'),
(131, 5, 'user.logout', 'user', 5, NULL, '::1', '2026-09-28 12:40:04'),
(132, 6, 'user.login', 'user', 6, NULL, '::1', '2026-09-28 12:40:14'),
(133, 6, 'user.logout', 'user', 6, NULL, '::1', '2026-09-28 12:44:16'),
(134, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-28 12:44:29'),
(135, 5, 'certificate.generated', 'certificate', 5, '{\"student_id\":6,\"type\":\"runner_up\"}', '::1', '2026-09-28 12:44:45'),
(136, 5, 'user.logout', 'user', 5, NULL, '::1', '2026-09-28 12:44:48'),
(137, 6, 'user.login', 'user', 6, NULL, '::1', '2026-09-28 12:44:57'),
(138, 6, 'user.logout', 'user', 6, NULL, '::1', '2026-09-28 12:53:10'),
(139, 5, 'user.login', 'user', 5, NULL, '::1', '2026-09-28 12:53:28'),
(140, 5, 'certificate.generated', 'certificate', 6, '{\"student_id\":6,\"type\":\"winner\"}', '::1', '2026-09-28 12:53:41'),
(141, 5, 'user.logout', 'user', 5, NULL, '::1', '2026-09-28 12:53:47'),
(142, 6, 'user.login', 'user', 6, NULL, '::1', '2026-09-28 12:53:56'),
(143, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:d9ec:9d5c:bf10:d26f', '2026-09-28 11:17:39'),
(144, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:d9ec:9d5c:bf10:d26f', '2026-09-28 11:20:25'),
(145, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:d9ec:9d5c:bf10:d26f', '2026-09-28 11:31:06'),
(146, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:d9ec:9d5c:bf10:d26f', '2026-09-28 11:31:15'),
(147, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:d9ec:9d5c:bf10:d26f', '2026-09-28 11:31:27'),
(148, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:d9ec:9d5c:bf10:d26f', '2026-09-28 11:32:12'),
(149, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:d9ec:9d5c:bf10:d26f', '2026-09-28 11:32:41'),
(150, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:d9ec:9d5c:bf10:d26f', '2026-09-28 11:34:52'),
(151, 8, 'user.login', 'user', 8, NULL, '2405:201:a41a:c882:d9ec:9d5c:bf10:d26f', '2026-09-28 11:36:00'),
(152, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:51cd:58f0:1870:3b9d', '2026-09-29 06:16:51'),
(153, 7, 'user.login', 'user', 7, NULL, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:30:10'),
(154, 7, 'user.logout', 'user', 7, NULL, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:32:33'),
(155, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"mamtapathak051@gmail.com\",\"role\":\"teacher\",\"school_id\":4}', '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:35:06'),
(156, NULL, 'registration.completed', 'user', 12, '{\"role\":\"teacher\",\"school_id\":4}', '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:35:43'),
(157, 7, 'user.login', 'user', 7, NULL, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:37:06'),
(158, 7, 'school.user_approved', 'user', 12, '{\"school_id\":4}', '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:37:26'),
(159, 7, 'user.logout', 'user', 7, NULL, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:38:17'),
(160, 12, 'user.login', 'user', 12, NULL, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:38:31'),
(161, 12, 'user.logout', 'user', 12, NULL, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:42:51'),
(162, 8, 'user.login', 'user', 8, NULL, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:43:39'),
(163, 8, 'user.logout', 'user', 8, NULL, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:43:57'),
(164, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:51cd:58f0:1870:3b9d', '2026-09-29 07:00:25'),
(165, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:51cd:58f0:1870:3b9d', '2026-09-29 07:02:14'),
(166, 1, 'user.login', 'user', 1, NULL, '103.149.126.42', '2026-09-29 07:14:48'),
(167, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 07:22:42'),
(168, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 07:24:36'),
(169, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:764c:4c7f:196c:cbff', '2026-09-29 08:03:05'),
(170, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:764c:4c7f:196c:cbff', '2026-09-29 08:10:16'),
(171, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:764c:4c7f:196c:cbff', '2026-09-29 08:10:35'),
(172, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:764c:4c7f:196c:cbff', '2026-09-29 08:11:08'),
(173, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:764c:4c7f:196c:cbff', '2026-09-29 08:11:43'),
(174, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 08:56:17'),
(175, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:9e56:dbaa:c7db:7762', '2026-09-29 10:12:35'),
(176, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:9e56:dbaa:c7db:7762', '2026-09-29 10:13:25'),
(177, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:30:01'),
(178, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:33:37'),
(179, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:33:47'),
(180, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:34:11'),
(181, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:34:23'),
(182, 5, 'certificate.generated', 'certificate', 7, '{\"student_id\":6,\"type\":\"winner\"}', '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:35:50'),
(183, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:36:10'),
(184, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:36:18'),
(185, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:40:51'),
(186, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:41:11'),
(187, 1, 'school.created', 'school', 8, '{\"name\":\"AJ\",\"code\":\"A-5A24\"}', '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:50:06'),
(188, 1, 'school.created', 'school', 9, '{\"name\":\"AIVERSE\",\"code\":\"A-176B\"}', '2405:201:a41a:c882:39d4:7d49:a94d:b265', '2026-09-29 10:58:45'),
(189, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:16:48'),
(190, 1, 'school.archived', 'school', 8, '{\"users_suspended\":1}', '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:17:06'),
(191, 1, 'school.restored', 'school', 8, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:18:34'),
(192, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:37:42'),
(193, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:38:19'),
(194, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:39:48'),
(195, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:48:25'),
(196, 6, 'counseling.created', 'counseling', 1, '{\"type\":\"career\",\"school\":3}', '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:49:16'),
(197, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:49:19'),
(198, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:49:50'),
(199, 4, 'counseling.assigned', 'counseling', 1, '{\"teacher\":5}', '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 06:59:35'),
(200, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 07:02:06'),
(201, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 07:02:19'),
(202, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 07:11:18'),
(203, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 07:11:27'),
(204, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 08:51:23'),
(205, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:10:52'),
(206, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:11:28'),
(207, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:11:38'),
(208, 5, 'attendance.marked', 'batch', 1, '{\"date\":\"2026-09-30\",\"count\":1}', '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:11:50'),
(209, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:12:07'),
(210, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:12:16'),
(211, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:12:47'),
(212, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:13:00'),
(213, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:1c47:73a5:e68f:4064', '2026-09-30 09:14:41'),
(214, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:1c47:73a5:e68f:4064', '2026-09-30 09:16:26'),
(215, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:22:39'),
(216, 14, 'user.login', 'user', 14, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:23:17'),
(217, 14, 'lesson.created', 'lesson', 3, '{\"title\":\"REAL NUMBERS\",\"school_id\":9,\"batch_id\":null,\"attachments\":1}', '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:24:44'),
(218, 14, 'user.logout', 'user', 14, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:25:03'),
(219, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:39:41'),
(220, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 09:39:59'),
(221, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 10:32:05'),
(222, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-09-30 11:07:19'),
(223, 14, 'user.login', 'user', 14, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-10-01 04:42:50'),
(224, 14, 'password.reset_requested', 'user', 14, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-10-01 04:43:36'),
(225, 14, 'password.reset_requested', 'user', 14, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-10-01 04:51:26'),
(226, 14, 'password.reset_completed', 'user', 14, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-10-01 04:52:02'),
(227, 14, 'user.login', 'user', 14, NULL, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', '2026-10-01 04:52:23'),
(228, 1, 'user.login', 'user', 1, NULL, '2001:67c:2628:647:32:604:0:8e', '2026-10-01 07:25:50'),
(229, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:51be:dafd:9277:9db4', '2026-10-03 06:10:11'),
(230, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 07:11:57'),
(231, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 07:12:03'),
(232, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 07:12:18'),
(233, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:ad7b:fdc:1cf:3c33', '2026-10-05 07:25:19'),
(234, 6, 'chess.calibration_completed', 'user', 6, '{\"final_rating\":1729,\"tier\":\"master\"}', '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 08:03:18'),
(235, 6, 'chess.calibration_completed', 'user', 6, '{\"final_rating\":1915,\"tier\":\"master\"}', '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 08:04:31'),
(236, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c015:ad61:e99a:893c:82d5', '2026-10-05 08:18:59'),
(237, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 09:13:03'),
(238, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:ad7b:fdc:1cf:3c33', '2026-10-05 09:27:53'),
(239, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 10:23:31'),
(240, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 10:24:13'),
(241, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 10:25:09'),
(242, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 10:26:55'),
(243, 4, 'password.changed', 'user', 4, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 10:27:48'),
(244, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 10:27:55'),
(245, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 10:28:06'),
(246, 5, 'chess.live_session_started', 'chess_session', 1, '{\"type\":\"1-on-1\",\"student\":null,\"batch\":null}', '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 10:28:26'),
(247, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 10:30:08'),
(248, 5, 'chess.live_session_ended', 'chess_session', 1, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 10:31:46'),
(249, 5, 'chess.live_session_started', 'chess_session', 2, '{\"type\":\"1-on-1\",\"student\":null,\"batch\":null}', '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 11:00:01'),
(250, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 11:00:26'),
(251, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 11:00:29'),
(252, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 11:00:39'),
(253, 5, 'chess.live_session_started', 'chess_session', 3, '{\"type\":\"1-on-1\",\"student\":6,\"batch\":null}', '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 11:01:04'),
(254, 5, 'chess.live_session_ended', 'chess_session', 3, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 11:01:55'),
(255, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 11:02:05'),
(256, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:f8b4:35c2:78a0:ceea', '2026-10-05 11:02:09'),
(257, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:8695:9db9:dec3:d773', '2026-10-06 05:51:48'),
(258, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:a4cf:acae:67d6:746c', '2026-10-06 06:02:05'),
(259, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:a4cf:acae:67d6:746c', '2026-10-06 06:08:25'),
(260, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"swiftcompass@mailinator.com\",\"role\":\"student\",\"school_id\":3}', '2405:201:a41a:c882:a4cf:acae:67d6:746c', '2026-10-06 06:09:43'),
(261, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:a4cf:acae:67d6:746c', '2026-10-06 06:12:20'),
(262, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:8695:9db9:dec3:d773', '2026-10-06 07:57:16'),
(263, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:8695:9db9:dec3:d773', '2026-10-06 08:00:46'),
(264, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:8695:9db9:dec3:d773', '2026-10-06 08:01:13'),
(265, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:8695:9db9:dec3:d773', '2026-10-06 08:02:47'),
(266, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c882:8695:9db9:dec3:d773', '2026-10-06 08:03:34'),
(267, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c882:8695:9db9:dec3:d773', '2026-10-06 08:06:24'),
(268, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:4deb:cc5c:8143:11f0', '2026-10-07 07:23:51'),
(269, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:4deb:cc5c:8143:11f0', '2026-10-07 07:24:21'),
(270, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c882:4deb:cc5c:8143:11f0', '2026-10-07 07:24:32'),
(271, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c882:4deb:cc5c:8143:11f0', '2026-10-07 07:51:50'),
(272, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:4deb:cc5c:8143:11f0', '2026-10-07 07:52:01'),
(273, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:3709:a7ac:5cfa:78db', '2026-10-07 07:54:37'),
(274, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:4deb:cc5c:8143:11f0', '2026-10-07 07:59:34'),
(275, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c882:4deb:cc5c:8143:11f0', '2026-10-07 07:59:44'),
(276, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:cd84:7c2c:172d:6660', '2026-10-08 06:32:27'),
(277, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:cd84:7c2c:172d:6660', '2026-10-08 06:33:18'),
(278, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c882:cd84:7c2c:172d:6660', '2026-10-08 06:33:38'),
(279, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c882:cd84:7c2c:172d:6660', '2026-10-08 06:34:45'),
(280, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:cd84:7c2c:172d:6660', '2026-10-08 06:35:00'),
(281, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:cd84:7c2c:172d:6660', '2026-10-08 06:36:00'),
(282, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c882:2dfe:19c2:21:67ca', '2026-10-08 06:38:17'),
(283, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c882:2dfe:19c2:21:67ca', '2026-10-08 06:42:16'),
(284, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:2dfe:19c2:21:67ca', '2026-10-08 06:42:26'),
(285, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:cd84:7c2c:172d:6660', '2026-10-08 07:34:31'),
(286, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:cd84:7c2c:172d:6660', '2026-10-08 07:35:54'),
(287, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:cd84:7c2c:172d:6660', '2026-10-08 07:36:16'),
(288, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:cd84:7c2c:172d:6660', '2026-10-08 07:38:25'),
(289, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c882:cd84:7c2c:172d:6660', '2026-10-08 07:38:49'),
(290, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c882:cd84:7c2c:172d:6660', '2026-10-08 08:28:13'),
(291, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:cd84:7c2c:172d:6660', '2026-10-08 08:28:31'),
(292, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:2dfe:19c2:21:67ca', '2026-10-08 09:15:39'),
(293, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:8070:4bf7:d29b:f0a9', '2026-10-08 11:15:31'),
(294, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c882:8070:4bf7:d29b:f0a9', '2026-10-08 11:15:49'),
(295, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c882:8070:4bf7:d29b:f0a9', '2026-10-08 11:17:58'),
(296, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:8070:4bf7:d29b:f0a9', '2026-10-08 11:18:09'),
(297, 5, 'user.login', 'user', 5, NULL, '2405:201:a405:20b3:f422:7f05:ea02:d1b1', '2026-10-09 05:55:56'),
(298, 5, 'user.logout', 'user', 5, NULL, '2405:201:a405:20b3:48d5:22f2:8629:b062', '2026-10-09 06:40:17'),
(299, 6, 'user.login', 'user', 6, NULL, '2405:201:a405:20b3:48d5:22f2:8629:b062', '2026-10-09 06:40:28'),
(300, 6, 'profile.updated', 'user', 6, NULL, '2405:201:a405:20b3:48d5:22f2:8629:b062', '2026-10-09 07:48:35'),
(301, 6, 'user.logout', 'user', 6, NULL, '2405:201:a405:20b3:48d5:22f2:8629:b062', '2026-10-09 07:49:04'),
(302, 5, 'user.login', 'user', 5, NULL, '2405:201:a405:20b3:48d5:22f2:8629:b062', '2026-10-09 07:49:16'),
(303, 5, 'user.logout', 'user', 5, NULL, '2405:201:a405:20b3:48d5:22f2:8629:b062', '2026-10-09 07:50:13'),
(304, 6, 'user.login', 'user', 6, NULL, '2405:201:a405:20b3:48d5:22f2:8629:b062', '2026-10-09 07:50:22'),
(305, 6, 'user.logout', 'user', 6, NULL, '2405:201:a405:20b3:1dfe:6962:6c9b:d6d4', '2026-10-09 09:06:48'),
(306, 5, 'user.login', 'user', 5, NULL, '2405:201:a405:20b3:1dfe:6962:6c9b:d6d4', '2026-10-09 09:06:57'),
(307, 5, 'user.logout', 'user', 5, NULL, '2405:201:a405:20b3:1dfe:6962:6c9b:d6d4', '2026-10-09 09:07:08'),
(308, 6, 'user.login', 'user', 6, NULL, '2405:201:a405:20b3:1dfe:6962:6c9b:d6d4', '2026-10-09 09:07:16'),
(309, 5, 'user.login', 'user', 5, NULL, '2405:201:a405:20b3:1dfe:6962:6c9b:d6d4', '2026-10-09 09:08:30'),
(310, 1, 'user.login', 'user', 1, NULL, '2401:4900:b452:8cb2:bd5c:5808:71d9:cff8', '2026-10-09 16:31:47'),
(311, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:a0ed:cb72:8943:4129', '2026-10-10 10:32:09'),
(312, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:439:64f8:a81a:ef72', '2026-10-10 11:07:41'),
(313, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c882:439:64f8:a81a:ef72', '2026-10-10 11:10:35'),
(314, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c882:439:64f8:a81a:ef72', '2026-10-10 11:11:04'),
(315, 4, 'announcement.created', 'announcement', 2, '{\"audience\":\"all\",\"school_id\":3}', '2405:201:a41a:c882:439:64f8:a81a:ef72', '2026-10-10 11:14:01'),
(316, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c882:439:64f8:a81a:ef72', '2026-10-10 11:14:51'),
(317, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:439:64f8:a81a:ef72', '2026-10-10 11:15:19'),
(318, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:439:64f8:a81a:ef72', '2026-10-10 11:24:17'),
(319, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:439:64f8:a81a:ef72', '2026-10-10 11:24:34'),
(320, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c882:98d4:32b5:856e:3b6f', '2026-10-11 11:02:09'),
(321, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c882:98d4:32b5:856e:3b6f', '2026-10-11 11:06:01'),
(322, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:98d4:32b5:856e:3b6f', '2026-10-11 11:06:32'),
(323, 1, 'school.archived', 'school', 8, '{\"users_suspended\":1}', '2405:201:a41a:c882:98d4:32b5:856e:3b6f', '2026-10-11 11:06:54'),
(324, 1, 'school.archived', 'school', 6, '{\"users_suspended\":2}', '2405:201:a41a:c882:98d4:32b5:856e:3b6f', '2026-10-11 11:06:59'),
(325, 1, 'school.archived', 'school', 5, '{\"users_suspended\":2}', '2405:201:a41a:c882:98d4:32b5:856e:3b6f', '2026-10-11 11:07:04'),
(326, 1, 'school.archived', 'school', 4, '{\"users_suspended\":2}', '2405:201:a41a:c882:98d4:32b5:856e:3b6f', '2026-10-11 11:07:10'),
(327, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:f0e1:9b24:d3d:4f56', '2026-10-12 06:07:56'),
(328, 1, 'school.deleted', 'school', 8, NULL, '2405:201:a41a:c882:f0e1:9b24:d3d:4f56', '2026-10-12 06:08:06'),
(329, 1, 'school.deleted', 'school', 6, NULL, '2405:201:a41a:c882:f0e1:9b24:d3d:4f56', '2026-10-12 06:08:12'),
(330, 1, 'school.deleted', 'school', 5, NULL, '2405:201:a41a:c882:f0e1:9b24:d3d:4f56', '2026-10-12 06:08:18'),
(331, 1, 'school.deleted', 'school', 4, NULL, '2405:201:a41a:c882:f0e1:9b24:d3d:4f56', '2026-10-12 06:08:24'),
(332, 1, 'school.updated', 'school', 3, NULL, '2405:201:a41a:c882:f0e1:9b24:d3d:4f56', '2026-10-12 06:20:21'),
(333, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:257d:8916:37e7:4f6d', '2026-10-12 07:29:44'),
(334, 14, 'user.login', 'user', 14, NULL, '2405:201:a41a:c882:257d:8916:37e7:4f6d', '2026-10-12 07:35:12'),
(335, 14, 'user.logout', 'user', 14, NULL, '2405:201:a41a:c882:257d:8916:37e7:4f6d', '2026-10-12 07:36:19'),
(336, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:b86f:bbbf:219c:a149', '2026-10-12 08:35:24'),
(337, 1, 'school.created', 'school', 10, '{\"name\":\"Institute Of Digital Training\",\"code\":\"IODT-A3CC\"}', '2405:201:a41a:c882:b86f:bbbf:219c:a149', '2026-10-12 08:40:23'),
(338, 15, 'user.login', 'user', 15, NULL, '2405:201:a41a:c882:b86f:bbbf:219c:a149', '2026-10-12 08:41:15'),
(339, 15, 'user.login', 'user', 15, NULL, '2405:201:a41a:c882:b86f:bbbf:219c:a149', '2026-10-12 09:42:04'),
(340, 15, 'password.changed', 'user', 15, NULL, '2405:201:a41a:c882:b86f:bbbf:219c:a149', '2026-10-12 09:43:18'),
(341, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:b86f:bbbf:219c:a149', '2026-10-12 09:59:06'),
(342, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c882:b86f:bbbf:219c:a149', '2026-10-12 09:59:37'),
(343, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c882:b86f:bbbf:219c:a149', '2026-10-12 09:59:56'),
(344, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c882:b86f:bbbf:219c:a149', '2026-10-12 10:00:10'),
(345, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:b86f:bbbf:219c:a149', '2026-10-12 10:00:19'),
(346, 15, 'user.login', 'user', 15, NULL, '2405:201:a41a:c882:349e:bd7d:e7f5:1795', '2026-10-12 10:28:10'),
(347, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c882:349e:bd7d:e7f5:1795', '2026-10-12 10:30:53'),
(348, 1, 'school.updated', 'school', 10, NULL, '2405:201:a41a:c882:349e:bd7d:e7f5:1795', '2026-10-12 10:31:22'),
(349, 1, 'user.login', 'user', 1, NULL, '2405:201:a405:20b3:2836:9eb0:7e3e:60f4', '2026-10-15 06:43:30'),
(350, 1, 'school.updated', 'school', 10, NULL, '2405:201:a405:20b3:2836:9eb0:7e3e:60f4', '2026-10-15 06:44:05'),
(351, 1, 'user.logout', 'user', 1, NULL, '2405:201:a405:20b3:2836:9eb0:7e3e:60f4', '2026-10-15 07:07:58'),
(352, 15, 'user.login', 'user', 15, NULL, '2405:201:a405:20b3:2836:9eb0:7e3e:60f4', '2026-10-15 07:08:52'),
(353, 15, 'user.logout', 'user', 15, NULL, '2405:201:a405:20b3:2836:9eb0:7e3e:60f4', '2026-10-15 07:14:27'),
(354, 15, 'user.login', 'user', 15, NULL, '2405:201:a41a:c015:1ffb:a13d:778c:395c', '2026-10-15 07:15:51'),
(355, 15, 'user.login', 'user', 15, NULL, '2405:201:a405:20b3:2836:9eb0:7e3e:60f4', '2026-10-15 07:49:19'),
(356, 15, 'user.login', 'user', 15, NULL, '2405:201:a41a:c015:3a8f:1944:a48a:935e', '2026-10-15 10:52:04'),
(357, 14, 'user.login', 'user', 14, NULL, '2405:201:a41a:c882:809f:9fda:31d6:dfda', '2026-10-16 06:18:25'),
(358, 14, 'user.login', 'user', 14, NULL, '2405:201:a41a:c882:b5c:5b07:9b0f:710b', '2026-10-16 09:59:54'),
(359, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c882:b5c:5b07:9b0f:710b', '2026-10-16 10:01:42'),
(360, NULL, 'user.logout', 'user', NULL, NULL, '2405:201:a41a:c882:b5c:5b07:9b0f:710b', '2026-10-16 11:11:14'),
(361, 14, 'user.login', 'user', 14, NULL, '2405:201:a405:20b3:e5db:2653:fbb5:4d06', '2026-10-18 13:00:43'),
(362, 14, 'user.logout', 'user', 14, NULL, '2405:201:a405:20b3:e5db:2653:fbb5:4d06', '2026-10-18 13:00:50'),
(363, 1, 'user.login', 'user', 1, NULL, '2405:201:a405:20b3:e5db:2653:fbb5:4d06', '2026-10-18 13:01:08'),
(364, 1, 'user.logout', 'user', 1, NULL, '2405:201:a405:20b3:e5db:2653:fbb5:4d06', '2026-10-18 13:01:30'),
(365, 15, 'user.login', 'user', 15, NULL, '2405:201:a405:20b3:e5db:2653:fbb5:4d06', '2026-10-18 13:01:41'),
(366, 15, 'user.logout', 'user', 15, NULL, '2405:201:a405:20b3:e5db:2653:fbb5:4d06', '2026-10-18 13:01:57'),
(367, 1, 'user.login', 'user', 1, NULL, '2405:201:a405:20b3:e5db:2653:fbb5:4d06', '2026-10-18 13:05:36'),
(368, 1, 'user.logout', 'user', 1, NULL, '2405:201:a405:20b3:e5db:2653:fbb5:4d06', '2026-10-18 13:05:59'),
(369, NULL, 'user.logout', 'user', NULL, NULL, '2405:201:a405:20b3:e5db:2653:fbb5:4d06', '2026-10-18 13:06:03'),
(370, 4, 'user.login', 'user', 4, NULL, '2405:201:a405:20b3:e5db:2653:fbb5:4d06', '2026-10-18 13:06:22'),
(371, 4, 'user.logout', 'user', 4, NULL, '2405:201:a405:20b3:e5db:2653:fbb5:4d06', '2026-10-18 13:07:45'),
(372, 1, 'user.login', 'user', 1, NULL, '2405:201:a405:20b3:e5db:2653:fbb5:4d06', '2026-10-18 13:07:53'),
(373, 1, 'user.logout', 'user', 1, NULL, '2405:201:a405:20b3:e5db:2653:fbb5:4d06', '2026-10-18 13:09:20'),
(374, 5, 'user.login', 'user', 5, NULL, '2405:201:a405:20b3:e5db:2653:fbb5:4d06', '2026-10-18 13:09:34'),
(375, 5, 'user.logout', 'user', 5, NULL, '2405:201:a405:20b3:e5db:2653:fbb5:4d06', '2026-10-18 13:17:35'),
(376, 6, 'user.login', 'user', 6, NULL, '2405:201:a405:20b3:e5db:2653:fbb5:4d06', '2026-10-18 13:17:50'),
(377, 6, 'user.logout', 'user', 6, NULL, '2405:201:a405:20b3:e5db:2653:fbb5:4d06', '2026-10-18 13:18:50'),
(378, 1, 'user.login', 'user', 1, NULL, '2405:201:a405:20b3:e5db:2653:fbb5:4d06', '2026-10-18 13:18:59'),
(379, 1, 'user.logout', 'user', 1, NULL, '2405:201:a405:20b3:e5db:2653:fbb5:4d06', '2026-10-18 13:26:00'),
(380, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:7835:cf02:66d7:802b', '2026-10-21 05:40:44'),
(381, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"avirajkumar853@gmail.com\",\"role\":\"student\",\"school_id\":9}', '2405:201:a41a:c11b:7835:cf02:66d7:802b', '2026-10-21 07:06:36'),
(382, 16, 'user.login', 'user', 16, NULL, '2405:201:a41a:c11b:7835:cf02:66d7:802b', '2026-10-21 07:09:28'),
(383, 16, 'user.logout', 'user', 16, NULL, '2405:201:a41a:c11b:7835:cf02:66d7:802b', '2026-10-21 07:26:10'),
(384, 16, 'user.login', 'user', 16, NULL, '2405:201:a41a:c11b:7835:cf02:66d7:802b', '2026-10-21 07:35:19'),
(385, 16, 'user.login', 'user', 16, NULL, '2405:201:a41a:c11b:7835:cf02:66d7:802b', '2026-10-21 08:05:34'),
(386, 16, 'user.logout', 'user', 16, NULL, '2405:201:a41a:c11b:7835:cf02:66d7:802b', '2026-10-21 08:06:06'),
(387, 14, 'user.login', 'user', 14, NULL, '2405:201:a41a:c11b:7835:cf02:66d7:802b', '2026-10-21 08:06:12'),
(388, 14, 'user.logout', 'user', 14, NULL, '2405:201:a41a:c11b:7835:cf02:66d7:802b', '2026-10-21 08:23:00'),
(389, 16, 'user.login', 'user', 16, NULL, '2405:201:a41a:c11b:7835:cf02:66d7:802b', '2026-10-21 08:23:07'),
(390, 16, 'user.logout', 'user', 16, NULL, '2405:201:a41a:c11b:7835:cf02:66d7:802b', '2026-10-21 08:23:26'),
(391, 16, 'user.login', 'user', 16, NULL, '2405:201:a41a:c11b:7835:cf02:66d7:802b', '2026-10-21 08:24:11'),
(392, 16, 'user.login', 'user', 16, NULL, '2405:201:a41a:c11b:7835:cf02:66d7:802b', '2026-10-21 09:07:08'),
(393, 16, 'user.logout', 'user', 16, NULL, '2405:201:a41a:c11b:7835:cf02:66d7:802b', '2026-10-21 09:07:59'),
(394, 14, 'user.login', 'user', 14, NULL, '2405:201:a41a:c11b:7835:cf02:66d7:802b', '2026-10-21 09:08:04'),
(395, 14, 'user.logout', 'user', 14, NULL, '2405:201:a41a:c11b:7835:cf02:66d7:802b', '2026-10-21 09:31:46'),
(396, 14, 'user.login', 'user', 14, NULL, '2405:201:a41a:c11b:7835:cf02:66d7:802b', '2026-10-21 09:31:51'),
(397, 14, 'user.logout', 'user', 14, NULL, '2405:201:a41a:c11b:7835:cf02:66d7:802b', '2026-10-21 09:32:53'),
(398, 14, 'user.login', 'user', 14, NULL, '2405:201:a41a:c015:ad61:e99a:893c:82d5', '2026-10-21 16:32:20'),
(399, 14, 'user.login', 'user', 14, NULL, '2405:201:a41a:c11b:496a:d2da:88aa:6a29', '2026-10-23 06:45:17'),
(400, 14, 'user.logout', 'user', 14, NULL, '2405:201:a41a:c11b:496a:d2da:88aa:6a29', '2026-10-23 06:49:40'),
(401, 16, 'user.login', 'user', 16, NULL, '2405:201:a41a:c11b:496a:d2da:88aa:6a29', '2026-10-23 06:49:48'),
(402, 16, 'user.login', 'user', 16, NULL, '49.37.27.98', '2026-10-23 06:52:24'),
(403, 16, 'user.logout', 'user', 16, NULL, '49.37.27.98', '2026-10-23 06:53:20'),
(404, 16, 'user.logout', 'user', 16, NULL, '2405:201:a41a:c11b:496a:d2da:88aa:6a29', '2026-10-23 07:01:29'),
(405, 14, 'user.login', 'user', 14, NULL, '2405:201:a41a:c11b:496a:d2da:88aa:6a29', '2026-10-23 07:01:39'),
(406, 14, 'user.logout', 'user', 14, NULL, '2405:201:a41a:c11b:496a:d2da:88aa:6a29', '2026-10-23 07:12:59'),
(407, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c11b:496a:d2da:88aa:6a29', '2026-10-23 07:13:14'),
(408, 6, 'user.login', 'user', 6, NULL, '49.37.27.98', '2026-10-23 07:24:03'),
(409, 6, 'user.logout', 'user', 6, NULL, '49.37.27.98', '2026-10-23 07:28:55'),
(410, 5, 'user.login', 'user', 5, NULL, '49.37.27.98', '2026-10-23 07:29:12'),
(411, NULL, 'user.logout', 'user', NULL, NULL, '49.37.27.98', '2026-10-23 08:22:31'),
(412, 4, 'user.login', 'user', 4, NULL, '49.37.27.98', '2026-10-23 08:22:44'),
(413, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:496a:d2da:88aa:6a29', '2026-10-23 09:03:33'),
(414, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:496a:d2da:88aa:6a29', '2026-10-23 09:35:29'),
(415, NULL, 'user.logout', 'user', NULL, NULL, '49.37.27.98', '2026-10-23 09:43:00'),
(416, 6, 'user.login', 'user', 6, NULL, '49.37.27.98', '2026-10-23 09:43:22'),
(417, 6, 'user.logout', 'user', 6, NULL, '49.37.27.98', '2026-10-23 09:49:36'),
(418, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:f1dd:8c31:ec20:3c46', '2026-10-23 11:23:31'),
(419, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:496a:d2da:88aa:6a29', '2026-10-23 11:28:16'),
(420, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c11b:496a:d2da:88aa:6a29', '2026-10-23 11:30:17'),
(421, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c11b:496a:d2da:88aa:6a29', '2026-10-23 11:30:26'),
(422, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c11b:496a:d2da:88aa:6a29', '2026-10-23 11:31:26'),
(423, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:496a:d2da:88aa:6a29', '2026-10-23 11:31:44'),
(424, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c11b:93da:1869:fa2:67f1', '2026-10-23 11:37:34'),
(425, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 06:34:20'),
(426, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 07:16:17'),
(427, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 08:32:09'),
(428, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 08:57:52'),
(429, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 08:58:22'),
(430, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 09:00:00'),
(431, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 09:00:07'),
(432, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 09:03:09'),
(433, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 09:03:53'),
(434, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 09:06:46'),
(435, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 09:07:37'),
(436, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 09:12:21'),
(437, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"abc@gmail.com\",\"role\":\"teacher\",\"school_id\":3}', '2405:201:a41a:c11b:f9fe:597:6b25:3f35', '2026-10-24 09:13:28'),
(438, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"nishant012543@gmail.com\",\"role\":\"teacher\",\"school_id\":3}', '2405:201:a41a:c11b:f9fe:597:6b25:3f35', '2026-10-24 09:14:07'),
(439, NULL, 'password.reset_requested', 'user', 12, NULL, '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 09:16:20'),
(440, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 09:16:24'),
(441, 1, 'user.reactivated', 'user', 12, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 09:16:47'),
(442, NULL, 'password.reset_completed', 'user', 12, NULL, '2405:201:a41a:c11b:877d:d90c:435:2c22', '2026-10-24 09:17:07'),
(443, 1, 'user.reactivated', 'user', 11, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 09:17:50'),
(444, 11, 'user.login', 'user', 11, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 09:18:37'),
(445, 11, 'profile.updated', 'user', 11, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 09:19:15'),
(446, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 09:19:39'),
(447, 11, 'user.logout', 'user', 11, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 09:20:00'),
(448, 16, 'user.login', 'user', 16, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 09:20:09'),
(449, 16, 'user.logout', 'user', 16, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 09:20:19'),
(450, 15, 'user.login', 'user', 15, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 09:20:23'),
(451, 15, 'user.logout', 'user', 15, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 09:20:28'),
(452, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 09:20:32'),
(453, 14, 'user.login', 'user', 14, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 09:20:32'),
(454, 14, 'user.logout', 'user', 14, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 09:20:40'),
(455, NULL, 'password.reset_requested', 'user', 12, NULL, '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 09:21:14'),
(456, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c11b:f9fe:597:6b25:3f35', '2026-10-24 09:21:39'),
(457, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c11b:f9fe:597:6b25:3f35', '2026-10-24 09:22:05'),
(458, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"tiwarisachin9886@gmail.com\",\"role\":\"teacher\",\"school_id\":3}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 09:23:36'),
(459, NULL, 'registration.completed', 'user', 17, '{\"role\":\"teacher\",\"school_id\":3,\"is_aiverse\":false}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 09:24:19'),
(460, 17, 'user.login', 'user', 17, NULL, '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 09:24:40'),
(461, 4, 'school.user_approved', 'user', 17, '{\"school_id\":3}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 09:25:05'),
(462, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"kedojad423@moblha.com\",\"role\":\"teacher\",\"school_id\":3}', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 09:30:39'),
(463, 17, 'user.logout', 'user', 17, NULL, '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 09:30:53'),
(464, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"nishant012543@gmail.com\",\"role\":\"teacher\",\"school_id\":3}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 09:33:17'),
(465, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"nishant012543@gmail.com\",\"role\":\"teacher\",\"school_id\":3}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 09:35:25'),
(466, NULL, 'registration.completed', 'user', 18, '{\"role\":\"teacher\",\"school_id\":3,\"is_aiverse\":false}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 09:35:58'),
(467, 4, 'school.user_approved', 'user', 18, '{\"school_id\":3}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 09:36:10'),
(468, 18, 'user.login', 'user', 18, NULL, '2405:201:a41a:c11b:f9fe:597:6b25:3f35', '2026-10-24 09:36:23'),
(469, 18, 'batch.created', 'batch', 3, '{\"name\":\"class 8 - English\"}', '2405:201:a41a:c11b:f9fe:597:6b25:3f35', '2026-10-24 09:39:23'),
(470, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 09:45:20'),
(471, 18, 'user.login', 'user', 18, NULL, '2405:201:a41a:c11b:f9fe:597:6b25:3f35', '2026-10-24 09:45:27'),
(472, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 09:45:47'),
(473, 1, 'user.login', 'user', 1, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 09:46:58'),
(474, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"uwypdc@mailto.plus\",\"role\":\"teacher\",\"school_id\":3}', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 09:46:59'),
(475, 1, 'user.logout', 'user', 1, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 09:47:05'),
(476, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 09:47:13'),
(477, NULL, 'registration.completed', 'user', 19, '{\"role\":\"teacher\",\"school_id\":3,\"is_aiverse\":false}', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 09:47:28'),
(478, 4, 'school.user_approved', 'user', 19, '{\"school_id\":3}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 09:48:07'),
(479, 4, 'school.user_approved', 'user', 19, '{\"school_id\":3}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 09:48:09'),
(480, 17, 'user.login', 'user', 17, NULL, '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 09:51:03'),
(481, 18, 'user.logout', 'user', 18, NULL, '2405:201:a41a:c11b:f9fe:597:6b25:3f35', '2026-10-24 09:54:39'),
(482, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c11b:f9fe:597:6b25:3f35', '2026-10-24 09:55:09'),
(483, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 10:05:37'),
(484, 17, 'user.logout', 'user', 17, NULL, '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 10:06:55');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `metadata`, `ip_address`, `created_at`) VALUES
(485, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"ns336759@gmail.com\",\"role\":\"teacher\",\"school_id\":3}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 10:08:40'),
(486, NULL, 'registration.completed', 'user', 20, '{\"role\":\"teacher\",\"school_id\":3,\"is_aiverse\":false}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 10:08:58'),
(487, 4, 'school.user_approved', 'user', 20, '{\"school_id\":3}', '2405:201:a41a:c11b:f9fe:597:6b25:3f35', '2026-10-24 10:09:08'),
(488, 4, 'school.user_approved', 'user', 20, '{\"school_id\":3}', '2405:201:a41a:c11b:d74:c974:17cc:414f', '2026-10-24 10:09:37'),
(489, 4, 'announcement.created', 'announcement', 3, '{\"audience\":\"teachers\",\"school_id\":3}', '2405:201:a41a:c11b:f9fe:597:6b25:3f35', '2026-10-24 10:10:50'),
(490, 17, 'user.login', 'user', 17, NULL, '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 10:11:17'),
(491, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"uwypdc@fexpost.com\",\"role\":\"student\",\"school_id\":3}', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:13:48'),
(492, NULL, 'registration.completed', 'user', 21, '{\"role\":\"student\",\"school_id\":3,\"is_aiverse\":false}', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:14:32'),
(493, 4, 'school.user_approved', 'user', 21, '{\"school_id\":3}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 10:14:47'),
(494, 4, 'school.user_approved', 'user', 21, '{\"school_id\":3}', '2405:201:a41a:c11b:f9fe:597:6b25:3f35', '2026-10-24 10:14:50'),
(495, 21, 'user.login', 'user', 21, NULL, '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:16:07'),
(496, 17, 'batch.created', 'batch', 4, '{\"name\":\"6A\"}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 10:18:43'),
(497, 17, 'activity.assigned', 'batch', 4, '{\"activity_id\":1,\"assignment_id\":2}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 10:20:13'),
(498, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c11b:f9fe:597:6b25:3f35', '2026-10-24 10:20:19'),
(499, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:f9fe:597:6b25:3f35', '2026-10-24 10:20:38'),
(500, 21, 'registration.initiated', 'user', NULL, '{\"email\":\"uwypdc@fexbox.org\",\"role\":\"student\",\"school_id\":3}', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:22:35'),
(501, 21, 'registration.completed', 'user', 22, '{\"role\":\"student\",\"school_id\":3,\"is_aiverse\":false}', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:24:36'),
(502, 17, 'batch.student_added', 'batch', 4, '{\"student\":6}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 10:24:41'),
(503, 4, 'school.user_approved', 'user', 22, '{\"school_id\":3}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 10:25:16'),
(504, 22, 'user.login', 'user', 22, NULL, '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:26:02'),
(505, 22, 'user.logout', 'user', 22, NULL, '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:26:48'),
(506, 17, 'attendance.marked', 'batch', 4, '{\"date\":\"2026-10-24\",\"count\":1}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 10:27:18'),
(507, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"uwypdc@mailbox.in.ua\",\"role\":\"student\",\"school_id\":3}', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:29:34'),
(508, NULL, 'registration.completed', 'user', 23, '{\"role\":\"student\",\"school_id\":3,\"is_aiverse\":false}', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:30:10'),
(509, 4, 'school.user_approved', 'user', 23, '{\"school_id\":3}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 10:34:00'),
(510, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"uwypdc@any.pink\",\"role\":\"student\",\"school_id\":3}', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:34:18'),
(511, NULL, 'registration.completed', 'user', 24, '{\"role\":\"student\",\"school_id\":3,\"is_aiverse\":false}', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:34:58'),
(512, 4, 'school.user_approved', 'user', 24, '{\"school_id\":3}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 10:36:19'),
(513, 24, 'user.login', 'user', 24, NULL, '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:37:30'),
(514, 24, 'user.logout', 'user', 24, NULL, '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:38:19'),
(515, 21, 'user.login', 'user', 21, NULL, '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:39:00'),
(516, 4, 'lesson.created', 'lesson', 4, '{\"title\":\"nx vj\",\"school_id\":3,\"batch_id\":null,\"attachments\":0}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 10:39:54'),
(517, 21, 'user.logout', 'user', 21, NULL, '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:41:21'),
(518, NULL, 'user.logout', 'user', NULL, NULL, '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:41:23'),
(519, 4, 'announcement.created', 'announcement', 4, '{\"audience\":\"all\",\"school_id\":3}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 10:44:29'),
(520, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"uwypdc@merepost.com\",\"role\":\"student\",\"school_id\":3}', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:44:54'),
(521, NULL, 'registration.completed', 'user', 25, '{\"role\":\"student\",\"school_id\":3,\"is_aiverse\":false}', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:45:39'),
(522, 4, 'school.user_rejected', 'user', 25, '{\"school_id\":3}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 10:46:15'),
(523, 4, 'school.user_approved', 'user', 25, '{\"school_id\":3}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 10:46:22'),
(524, 4, 'school.user_approved', 'user', 20, '{\"school_id\":3}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 10:47:30'),
(525, 4, 'school.user_rejected', 'user', 20, '{\"school_id\":3}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 10:47:38'),
(526, 4, 'school.user_approved', 'user', 20, '{\"school_id\":3}', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 10:47:45'),
(527, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c11b:c5c6:9482:d91a:d9cb', '2026-10-24 10:48:34'),
(528, NULL, 'registration.initiated', 'user', NULL, '{\"email\":\"uwypdc@rover.info\",\"role\":\"student\",\"school_id\":3}', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:50:10'),
(529, NULL, 'registration.completed', 'user', 26, '{\"role\":\"student\",\"school_id\":3,\"is_aiverse\":false}', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:50:35'),
(530, 4, 'school.user_approved', 'user', 26, '{\"school_id\":3}', '2405:201:a41a:c11b:c5c6:9482:d91a:d9cb', '2026-10-24 10:50:41'),
(531, 17, 'user.login', 'user', 17, NULL, '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 11:02:00'),
(532, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 13:44:12'),
(533, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 13:45:15'),
(534, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 13:45:25'),
(535, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 14:23:43'),
(536, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 14:23:54'),
(537, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 14:24:00'),
(538, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 14:29:31'),
(539, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 14:29:39'),
(540, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 14:30:12'),
(541, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 14:30:20'),
(542, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 14:33:09'),
(543, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 14:35:05'),
(544, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 14:56:42'),
(545, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 14:56:50'),
(546, 5, 'batch.student_added', 'batch', 1, '{\"student\":23}', '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 14:58:43'),
(547, 5, 'attendance.marked', 'batch', 1, '{\"date\":\"2026-10-26\",\"count\":2}', '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 14:58:55'),
(548, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 15:04:29'),
(549, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 15:04:35'),
(550, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 16:42:17'),
(551, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 16:42:39'),
(552, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 16:42:49'),
(553, 5, 'user.logout', 'user', 5, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 16:43:02'),
(554, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 16:43:11'),
(555, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 16:44:43'),
(556, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:14f5:31e8:d7f6:135b', '2026-10-26 16:44:49'),
(557, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:9cba:70cb:18b0:f079', '2026-10-27 11:35:16'),
(558, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c11b:9cba:70cb:18b0:f079', '2026-10-27 11:37:03'),
(559, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c11b:9cba:70cb:18b0:f079', '2026-10-27 11:37:16'),
(560, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c11b:9cba:70cb:18b0:f079', '2026-10-27 11:42:05'),
(561, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:9cba:70cb:18b0:f079', '2026-10-27 11:42:11'),
(562, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:9cba:70cb:18b0:f079', '2026-10-27 12:14:19'),
(563, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:9cba:70cb:18b0:f079', '2026-10-27 12:40:46'),
(564, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:98e2:80e9:2475:3ab6', '2026-10-27 12:50:12'),
(565, 4, 'user.login', 'user', 4, NULL, '2405:201:a41a:c11b:98e2:80e9:2475:3ab6', '2026-10-27 12:56:39'),
(566, 4, 'user.logout', 'user', 4, NULL, '2405:201:a41a:c11b:98e2:80e9:2475:3ab6', '2026-10-27 13:01:54'),
(567, 5, 'user.login', 'user', 5, NULL, '2405:201:a41a:c11b:98e2:80e9:2475:3ab6', '2026-10-27 13:02:10'),
(568, NULL, 'user.logout', 'user', NULL, NULL, '2405:201:a41a:c11b:9cba:70cb:18b0:f079', '2026-10-27 13:06:18'),
(569, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:4057:997b:1bad:2fb8', '2026-10-27 13:40:00'),
(570, NULL, 'user.logout', 'user', NULL, NULL, '2405:201:a41a:c11b:5841:5f35:bc10:9574', '2026-10-27 14:05:25'),
(571, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:5841:5f35:bc10:9574', '2026-10-27 14:27:44'),
(572, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c11b:5841:5f35:bc10:9574', '2026-10-27 14:51:43'),
(573, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:5841:5f35:bc10:9574', '2026-10-27 14:51:48'),
(574, NULL, 'user.logout', 'user', NULL, NULL, '2405:201:a41a:c11b:5841:5f35:bc10:9574', '2026-10-27 15:49:11'),
(575, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:5841:5f35:bc10:9574', '2026-10-27 16:13:34'),
(576, NULL, 'user.logout', 'user', NULL, NULL, '2405:201:a41a:c11b:98e2:80e9:2475:3ab6', '2026-10-27 16:15:19'),
(577, 6, 'user.login', 'user', 6, NULL, '2405:201:a41a:c11b:98e2:80e9:2475:3ab6', '2026-10-27 16:15:40'),
(578, 6, 'user.logout', 'user', 6, NULL, '2405:201:a41a:c11b:98e2:80e9:2475:3ab6', '2026-10-27 16:17:26'),
(579, NULL, 'user.logout', 'user', NULL, NULL, '2405:201:a41a:c11b:5841:5f35:bc10:9574', '2026-10-27 16:56:03'),
(580, NULL, 'user.logout', 'user', NULL, NULL, '2405:201:a41a:c11b:98e2:80e9:2475:3ab6', '2026-10-27 17:07:08'),
(581, NULL, 'user.logout', 'user', NULL, NULL, '2401:4900:8908:ad60:28f1:5bad:26f3:4d74', '2026-10-28 02:20:29');

-- --------------------------------------------------------

--
-- Table structure for table `batches`
--

CREATE TABLE `batches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `class_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `class_level` varchar(50) DEFAULT NULL,
  `teacher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `capacity` int(10) UNSIGNED DEFAULT 50,
  `status` enum('active','archived') DEFAULT 'active',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `batches`
--

INSERT INTO `batches` (`id`, `uuid`, `school_id`, `class_id`, `name`, `description`, `subject`, `class_level`, `teacher_id`, `cover_image`, `capacity`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '7972d24f-2fd6-44c5-b8e6-b07e40f517a9', NULL, NULL, 'science class 6', NULL, 'science', '6', 5, NULL, 50, 'active', 5, '2026-09-25 15:20:56', '2026-09-25 15:20:56'),
(2, 'a5df7e98-d36b-4b9d-9863-fcfdf06aaae3', 3, NULL, 'Chess - Elite Rooks (Master)', 'Rising Rooks Chess Academy batch for Master tier (1501-2000 Elo).', 'Chess', 'Master', NULL, NULL, 50, 'active', NULL, '2026-10-05 08:03:18', '2026-10-05 08:03:18'),
(3, 'a5fa62b3-9bfa-48fa-8bfe-06e483b4fa2c', NULL, NULL, 'class 8 - English', 'no', 'English', '8', 18, NULL, 50, 'active', 18, '2026-10-24 09:39:23', '2026-10-24 09:39:23'),
(4, 'dc121441-1d48-4aa0-8d70-942397f2316b', NULL, NULL, '6A', 'nd jhasbvjadv', 'Math', 'ghjkl', 17, NULL, 50, 'active', 17, '2026-10-24 10:18:43', '2026-10-24 10:18:43');

-- --------------------------------------------------------

--
-- Table structure for table `batch_activity_assignments`
--

CREATE TABLE `batch_activity_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `batch_id` bigint(20) UNSIGNED NOT NULL,
  `activity_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_by` bigint(20) UNSIGNED NOT NULL,
  `scheduled_date` date DEFAULT NULL,
  `scheduled_time` time DEFAULT NULL,
  `duration_minutes` int(10) UNSIGNED DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `batch_activity_assignments`
--

INSERT INTO `batch_activity_assignments` (`id`, `batch_id`, `activity_id`, `assigned_by`, `scheduled_date`, `scheduled_time`, `duration_minutes`, `instructions`, `due_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 5, '2026-09-26', '13:30:00', 30, NULL, '2026-09-27', 'scheduled', '2026-09-25 19:34:46', '2026-09-25 19:34:46'),
(2, 4, 1, 17, '2026-10-25', '10:30:00', 60, 'sdmbsjgyf', '2026-10-25', 'scheduled', '2026-10-24 10:20:13', '2026-10-24 10:20:13');

-- --------------------------------------------------------

--
-- Table structure for table `batch_assignment_acknowledgments`
--

CREATE TABLE `batch_assignment_acknowledgments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `assignment_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `acknowledged_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `batch_students`
--

CREATE TABLE `batch_students` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `batch_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `joined_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `batch_students`
--

INSERT INTO `batch_students` (`id`, `batch_id`, `student_user_id`, `joined_at`) VALUES
(1, 1, 6, '2026-09-25 15:21:14'),
(2, 2, 6, '2026-10-05 08:03:18'),
(4, 4, 6, '2026-10-24 10:24:41'),
(5, 1, 23, '2026-10-26 14:58:43');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `type` enum('participation','achievement','completion','winner','merit') DEFAULT 'participation',
  `certificate_type` enum('winner','runner_up','participant') DEFAULT 'participant',
  `issued_for` enum('activity','competition','course','lesson','other') DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `certificate_url` varchar(500) DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `metadata` longtext DEFAULT NULL CHECK (json_valid(`metadata`)),
  `issued_by` bigint(20) UNSIGNED DEFAULT NULL,
  `issued_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `uuid`, `student_user_id`, `title`, `type`, `certificate_type`, `issued_for`, `reference_id`, `certificate_url`, `file_path`, `metadata`, `issued_by`, `issued_at`) VALUES
(1, 'f286af20-c3cf-4fc9-ae3a-54485cc4af07', 6, 'Certificate of Achievement', 'winner', 'winner', 'activity', NULL, '/uploads/certificates/cert_winner_6_20260928_122213_4e3686.pdf', '/uploads/certificates/cert_winner_6_20260928_122213_4e3686.pdf', '{\"event_title\":\"QUIZWIZ\",\"rank\":\"1st Place\",\"date_completed\":\"28 Sep 2026\",\"school_name\":\"Test SCHOOL\"}', 5, '2026-09-28 12:22:13'),
(2, 'c094a034-f911-4edb-b83b-a1c278cad8b8', 6, 'Certificate of Achievement', 'winner', 'winner', 'activity', NULL, '/uploads/certificates/cert_winner_6_20260928_122728_528795.pdf', '/uploads/certificates/cert_winner_6_20260928_122728_528795.pdf', '{\"event_title\":\"QUIZWIZ\",\"rank\":\"1st Place\",\"date_completed\":\"28 Sep 2026\",\"school_name\":\"Test SCHOOL\"}', 5, '2026-09-28 12:27:28'),
(3, '13dc8cd4-f61a-4236-a3d8-ff34e1feb5db', 6, 'Certificate of Runner-up', 'winner', 'runner_up', 'activity', NULL, '/uploads/certificates/cert_runner_up_6_20260928_123538_0b1f4b.pdf', '/uploads/certificates/cert_runner_up_6_20260928_123538_0b1f4b.pdf', '{\"event_title\":\"QUIZWIZ\",\"rank\":\"2nd Place (Runner-up)\",\"date_completed\":\"28 Sep 2026\",\"school_name\":\"Test SCHOOL\"}', 5, '2026-09-28 12:35:38'),
(4, 'ea59593c-eea7-46de-8969-a6eb68a84a44', 6, 'Certificate of Runner-up', 'winner', 'runner_up', 'activity', NULL, '/uploads/certificates/cert_runner_up_6_20260928_124002_d1f121.pdf', '/uploads/certificates/cert_runner_up_6_20260928_124002_d1f121.pdf', '{\"event_title\":\"QUIZWIZ\",\"rank\":\"3th Place\",\"date_completed\":\"28 Sep 2026\",\"school_name\":\"Test SCHOOL\"}', 5, '2026-09-28 12:40:02'),
(5, 'c813160c-c7e4-4344-967b-815c69dc2650', 6, 'Certificate of Runner-up', 'winner', 'runner_up', 'activity', NULL, '/uploads/certificates/cert_runner_up_6_20260928_124445_44b06b.pdf', '/uploads/certificates/cert_runner_up_6_20260928_124445_44b06b.pdf', '{\"event_title\":\"QUIZWIZ\",\"rank\":\"3th Place\",\"date_completed\":\"28 Sep 2026\",\"school_name\":\"Test SCHOOL\"}', 5, '2026-09-28 12:44:45'),
(6, '266132c6-41a8-46bb-938f-7733770b07dd', 6, 'Certificate of Achievement', 'winner', 'winner', 'activity', NULL, '/uploads/certificates/cert_winner_6_20260928_125341_eb4364.pdf', '/uploads/certificates/cert_winner_6_20260928_125341_eb4364.pdf', '{\"event_title\":\"QUIZWIZ\",\"rank\":\"1st Place\",\"date_completed\":\"28 Sep 2026\",\"school_name\":\"Test SCHOOL\"}', 5, '2026-09-28 12:53:41'),
(7, '347a4d3c-3d71-43ba-8430-f5009361c30e', 6, 'Certificate of Achievement', 'winner', 'winner', 'activity', NULL, '/uploads/certificates/cert_winner_6_20260929_103550_255bbe.pdf', '/uploads/certificates/cert_winner_6_20260929_103550_255bbe.pdf', '{\"event_title\":\"QUIZWIZ\",\"rank\":\"1st Place\",\"date_completed\":\"29 Sep 2026\",\"school_name\":\"Test SCHOOL\"}', 5, '2026-09-29 10:35:50');

-- --------------------------------------------------------

--
-- Table structure for table `chapters`
--

CREATE TABLE `chapters` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `class_level` varchar(50) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(10) UNSIGNED DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chess_challenges`
--

CREATE TABLE `chess_challenges` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `challenger_user_id` bigint(20) UNSIGNED NOT NULL,
  `challenged_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `challenger_color` enum('white','black','random') NOT NULL DEFAULT 'random',
  `time_control_minutes` tinyint(3) UNSIGNED NOT NULL DEFAULT 10,
  `increment_seconds` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `rated` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('pending','accepted','declined','cancelled','expired') NOT NULL DEFAULT 'pending',
  `game_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chess_coach_assignments`
--

CREATE TABLE `chess_coach_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_user_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_tier` enum('learner','rookie','intermediate','master','grandmaster','all') DEFAULT 'all',
  `notes` text DEFAULT NULL,
  `assigned_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chess_coach_assignments`
--

INSERT INTO `chess_coach_assignments` (`id`, `school_id`, `teacher_user_id`, `student_user_id`, `assigned_tier`, `notes`, `assigned_by`, `created_at`) VALUES
(2, 3, 18, 6, 'all', 'sndbjh', 4, '2026-10-24 10:03:59'),
(3, 3, 19, 21, 'all', 'sndbjh', 4, '2026-10-24 10:37:51');

-- --------------------------------------------------------

--
-- Table structure for table `chess_curriculum`
--

CREATE TABLE `chess_curriculum` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tier` enum('learner','rookie','intermediate','master','grandmaster') NOT NULL,
  `category` enum('opening','middlegame','endgame','tactics') NOT NULL,
  `title` varchar(200) NOT NULL,
  `subtitle` varchar(300) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `initial_fen` varchar(150) NOT NULL DEFAULT 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1',
  `solution_pgn` text DEFAULT NULL,
  `key_concepts` longtext DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chess_games`
--

CREATE TABLE `chess_games` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `white_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `black_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `game_type` enum('calibration','bot','pvp','promotion','relegation','live_coaching','tournament') NOT NULL DEFAULT 'bot',
  `bot_difficulty` tinyint(3) UNSIGNED DEFAULT NULL,
  `bot_rating_target` int(10) UNSIGNED DEFAULT NULL,
  `initial_fen` varchar(150) NOT NULL DEFAULT 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1',
  `current_fen` varchar(150) NOT NULL DEFAULT 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1',
  `pgn` longtext DEFAULT NULL,
  `moves_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `result` enum('1-0','0-1','1/2-1/2','*') NOT NULL DEFAULT '*',
  `winner_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `termination` varchar(100) DEFAULT NULL,
  `white_rating_before` int(10) UNSIGNED DEFAULT NULL,
  `black_rating_before` int(10) UNSIGNED DEFAULT NULL,
  `white_rating_change` int(11) DEFAULT 0,
  `black_rating_change` int(11) DEFAULT 0,
  `white_accuracy` decimal(5,2) DEFAULT NULL,
  `black_accuracy` decimal(5,2) DEFAULT NULL,
  `coach_notes` text DEFAULT NULL,
  `status` enum('in_progress','completed','aborted') NOT NULL DEFAULT 'in_progress',
  `created_at` datetime DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chess_games`
--

INSERT INTO `chess_games` (`id`, `uuid`, `white_user_id`, `black_user_id`, `school_id`, `batch_id`, `game_type`, `bot_difficulty`, `bot_rating_target`, `initial_fen`, `current_fen`, `pgn`, `moves_count`, `result`, `winner_user_id`, `termination`, `white_rating_before`, `black_rating_before`, `white_rating_change`, `black_rating_change`, `white_accuracy`, `black_accuracy`, `coach_notes`, `status`, `created_at`, `completed_at`) VALUES
(1, '4c2c6846-d222-49b4-9d4e-313b90c1c743', 6, NULL, 3, NULL, 'calibration', 1, 400, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '6k1/2p5/p1p2R1Q/7p/1P6/2BP4/1Pb3PP/6K1 w - - 5 27', '1. e4 e5 2. Nf3 a6 3. Bc4 Bb4 4. a3 Nf6 5. axb4 Nxe4 6. Nc3 O-O 7. O-O g5 8. d3 Nxf2 9. Rxf2 Qf6 10. Bxg5 e4 11. Bxf6 exf3 12. Rxf3 d5 13. Bxd5 h5 14. Qf1 Nc6 15. Bxc6 bxc6 16. Ne4 Be6 17. Ng5 Bb3 18. Bc3 Bxc2 19. Nxf7 Rxf7 20. Rxf7 Rc8 21. Qf2 Bb3 22. Rf1 Ra8 23. Rf8+ Rxf8 24. Qxf8+ Kh7 25. Rf6 Bc2 26. Qh6+ Kg8', 52, '1-0', 6, 'White wins by Checkmate!', 400, 400, 0, 0, NULL, NULL, NULL, 'completed', '2026-10-05 07:39:36', '2026-10-05 07:41:08'),
(2, '1f0df15d-2798-4a3a-bf5c-d4d1b2445633', 6, NULL, 3, NULL, 'calibration', 3, 750, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'k5R1/3Q4/1p6/4P3/8/5N2/1PP2PK1/5R2 b - - 4 27', '1. e4 d5 2. exd5 Qxd5 3. Nc3 Qxa2 4. Rxa2 a5 5. Bb5+ Kd8 6. d4 Ra6 7. Bxa6 Nxa6 8. Nf3 h5 9. O-O e5 10. dxe5+ Ke8 11. h3 Bxh3 12. Bg5 Bxg2 13. Kxg2 Be7 14. Bxe7 Kxe7 15. Rxa5 Kf8 16. Qd3 Nf6 17. Nb5 Ne4 18. Qxe4 b6 19. Rxa6 f5 20. Qxf5+ Ke8 21. Nxc7+ Kd8 22. Ra8+ Kxc7 23. Rxh8 g5 24. Rxh5 Kc6 25. Rxg5 Kb7 26. Qd7+ Ka8 27. Rg8#', 53, '1-0', 6, 'White wins by Checkmate!', 500, 750, 0, 0, NULL, NULL, NULL, 'completed', '2026-10-05 07:41:17', '2026-10-05 07:42:39'),
(3, '8617bb0f-30f7-4f69-a8f9-e154531a04f1', 6, NULL, 3, NULL, 'calibration', 5, 1100, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '4r3/2p3p1/4B2k/pp2R3/6Q1/3P1N2/PPP2PPP/2K4R w - - 2 27', '1. Nc3 e5 2. e4 d5 3. exd5 Qxd5 4. Nxd5 e4 5. Bc4 Na6 6. Qe2 Kd8 7. Qxe4 Nb8 8. d3 Bb4+ 9. Nxb4 Kd7 10. Qd4+ Ke8 11. Qd5 Ne7 12. Qxf7+ Kd8 13. Bg5 a6 14. Bxe7+ Kd7 15. Qe6+ Ke8 16. Qxc8+ Kxe7 17. Qxh8 Kf6 18. Qxh7 Nc6 19. Qh4+ Kg6 20. Be6 Nxb4 21. Qxb4 Rc8 22. Qg4+ Kh6 23. Nf3 b6 24. O-O-O b5 25. Rde1 a5 26. Re5 Re8', 52, '1-0', 6, 'White wins by Checkmate!', 725, 1100, 0, 0, NULL, NULL, NULL, 'completed', '2026-10-05 07:43:26', '2026-10-05 07:47:12'),
(4, '5cb06c18-4e6d-483f-b8d1-6f77becf5cf9', 6, NULL, 3, NULL, 'calibration', 7, 1500, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 1013, 1500, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 07:47:24', NULL),
(5, '9800c6b3-645d-4b9d-8821-cce6abb9d0a9', 6, NULL, 3, NULL, 'calibration', 7, 1500, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '2k5/R3R3/8/4N2P/1p6/1P6/7P/2B3K1 w - - 1 31', '1. e4 d5 2. exd5 Qxd5 3. Nc3 Qxd2+ 4. Qxd2 h6 5. Bc4 g6 6. Nf3 e5 7. Nxe5 a6 8. O-O Be7 9. Re1 Bf5 10. a3 Bxc2 11. Qxc2 Bxa3 12. Rxa3 f5 13. Nxg6+ Ne7 14. Nxh8 f4 15. g3 fxg3 16. fxg3 h5 17. Ng6 a5 18. Rxe7+ Kd8 19. Qd3+ Kc8 20. Qd7+ Nxd7 21. Nd5 h4 22. gxh4 Ne5 23. Nxe5 c6 24. Nb6+ Kb8 25. Nxa8 Kxa8 26. h5 b5 27. Bxb5 cxb5 28. Rxa5+ Kb8 29. Raa7 b4 30. b3 Kc8', 60, '1-0', 6, 'White wins by Checkmate!', 1013, 1500, 0, 0, NULL, NULL, NULL, 'completed', '2026-10-05 08:00:18', '2026-10-05 08:01:41'),
(6, 'dc612449-8b2e-4a8e-8f41-b521734ef713', 6, NULL, 3, NULL, 'calibration', 9, 1900, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rn2BR1k/p7/1p4Q1/2p5/8/2N1B3/PPP2PP1/2KR4 b - - 11 25', '1. e4 d5 2. exd5 Qxd5 3. Nc3 Qxd2+ 4. Qxd2 e5 5. Nf3 e4 6. Nd4 f5 7. Nxf5 Bxf5 8. Qf4 Bg4 9. Qxg4 e3 10. Bb5+ Kf7 11. Bxe3 Nh6 12. Qg5 Bd6 13. O-O-O Bxh2 14. Rxh2 b6 15. Bd4 Ke6 16. Qxg7 Re8 17. Qxh6+ Ke7 18. Qxh7+ Kd6 19. Bxe8 c5 20. Be3+ Ke5 21. Qh5+ Ke6 22. Rh4 Kf6 23. Rf4+ Kg7 24. Qg6+ Kh8 25. Rf8#', 49, '1-0', 6, 'White wins by Checkmate!', 1357, 1900, 0, 0, NULL, NULL, NULL, 'completed', '2026-10-05 08:01:45', '2026-10-05 08:03:18'),
(7, '12844d83-39ea-488e-a7fc-ce370594e686', 6, NULL, 3, NULL, 'calibration', 9, 1900, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '8/8/p3R3/3Q4/2p2K1k/7P/PPP2P2/R7 w - - 5 31', '1. e4 d5 2. exd5 Qxd5 3. Nc3 Qxd2+ 4. Qxd2 e5 5. Bc4 e4 6. Nxe4 b5 7. Nf3 bxc4 8. O-O Bg4 9. h3 Bxf3 10. gxf3 a6 11. Qd4 f5 12. Ng3 Bd6 13. Nxf5 Bh2+ 14. Kxh2 g6 15. Qxh8 gxf5 16. Qxg8+ Ke7 17. Qxh7+ Kf6 18. Qxc7 Nd7 19. Qxd7 Ke5 20. Re1+ Kf6 21. Bg5+ Kxg5 22. f4+ Kxf4 23. Kg2 Rg8+ 24. Kh2 Rg2+ 25. Kxg2 Kg5 26. Kg3 f4+ 27. Kf3 Kh6 28. Kxf4 Kg6 29. Re6+ Kh5 30. Qd5+ Kh4', 60, '1-0', 6, 'White wins by Checkmate!', 1729, 1900, 0, 0, NULL, NULL, NULL, 'completed', '2026-10-05 08:03:20', '2026-10-05 08:04:31'),
(8, '08a8401e-7e9d-4cbc-a116-82e6e4d2b0d0', 6, NULL, 3, NULL, 'calibration', 9, 1900, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 1915, 1900, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 08:04:33', NULL),
(9, 'dba8a094-2434-4cd9-a3f5-2ebf40d11692', 6, NULL, 3, NULL, 'bot', 3, 800, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppp1ppp/8/4p3/4P3/8/PPPP1PPP/RNBQKBNR w KQkq e6 0 2', '1. e4 e5', 2, '*', NULL, NULL, 1915, 800, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 08:10:28', NULL),
(10, '5725158b-eb64-46cc-87dc-000473e1d7a7', 6, NULL, 3, NULL, 'bot', 3, 800, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '4Kqk1/pp3ppp/8/8/3r4/8/8/8 w - - 4 41', '1. e4 c5 2. f3 Nc6 3. c4 Nf6 4. d3 e5 5. Bg5 Be7 6. Bxf6 Bxf6 7. Ne2 Qb6 8. g4 Qxb2 9. Nd2 Bh4+ 10. Ng3 O-O 11. Kf2 d6 12. Kg2 Bxg3 13. Kxg3 Nd4 14. h4 Re8 15. g5 Be6 16. f4 exf4+ 17. Kxf4 Rad8 18. Bh3 Nc2 19. Rb1 Qe5+ 20. Kf3 Nd4+ 21. Ke3 Qg3+ 22. Nf3 Bxh3 23. Rb2 Bc8 24. Qe2 Nxe2 25. Rxe2 Bg4 26. Rf2 d5 27. cxd5 Rxd5 28. Rg1 Rxd3+ 29. Kxd3 Qxf2 30. Rxg4 Qxf3+ 31. Kc4 Qxg4 32. Kxc5 Rxe4 33. a3 Qxh4 34. g6 Qg5+ 35. Kd6 Qxg6+ 36. Kc7 Qb6+ 37. Kd7 Qc5 38. a4 Rxa4 39. Kd8 Rd4+ 40. Ke8 Qf8#', 80, '0-1', NULL, 'Black wins by Checkmate!', 1915, 800, -32, 0, NULL, NULL, NULL, 'completed', '2026-10-05 08:19:07', '2026-10-05 08:28:07'),
(11, 'f15d9fe6-60e2-472a-854d-f356a96f9f8b', 6, NULL, 3, NULL, 'bot', 3, 800, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 1883, 800, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 08:28:18', NULL),
(12, 'dd927007-a561-4cfd-afb3-16be18b253d3', 6, NULL, 3, NULL, 'bot', 10, 2700, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '1Qn2k1r/pp3ppp/8/1B6/8/R7/1PP2PPP/2B1R1K1 w - - 1 18', '1. e4 d5 2. exd5 Qxd5 3. Nc3 Qxd2+ 4. Qxd2 e5 5. Nf3 e4 6. Nxe4 Nd7 7. Bb5 Rb8 8. O-O Ra8 9. Re1 Rb8 10. a3 Bxa3 11. Rxa3 Ra8 12. Ne5 Rb8 13. Nxd7 Bxd7 14. Qxd7+ Kf8 15. Nd6 cxd6 16. Qxd6+ Ne7 17. Qxb8+ Nc8', 34, '1-0', 6, 'White wins by Checkmate!', 1883, 2700, 32, 0, NULL, NULL, NULL, 'completed', '2026-10-05 08:56:19', '2026-10-05 08:57:16'),
(13, 'd62e4d35-9538-4d08-afe3-107c74e5320a', 6, NULL, 3, NULL, 'bot', 8, 2000, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 1915, 2000, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 09:02:40', NULL),
(14, '37b7e5e3-4058-4890-9466-587a327c88ff', 6, NULL, 3, NULL, 'bot', 8, 2000, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '6n1/2Q5/p5pk/2p3N1/2B4p/P6P/P4PP1/R1B1R1K1 w - - 3 25', '1. e4 d5 2. exd5 Qxd5 3. Nc3 Qxd2+ 4. Qxd2 g6 5. Nf3 e5 6. Nxe5 c6 7. Bc4 Ba3 8. bxa3 Ne7 9. O-O Ng8 10. Qg5 Bf5 11. Re1 Bxc2 12. h3 Be4 13. Nxe4 h5 14. Nd6+ Kf8 15. Qd8+ Kg7 16. Nexf7 c5 17. Nxh8 Kxh8 18. Nf7+ Kh7 19. Ng5+ Kg7 20. Qc7+ Kh6 21. Qxb7 a6 22. Qxa8 h4 23. Qxb8 Kg7 24. Qc7+ Kh6', 48, '0-1', NULL, 'Flag Fall — You ran out of time!', 1915, 2000, -12, 0, NULL, NULL, NULL, 'completed', '2026-10-05 09:02:48', '2026-10-05 09:06:02'),
(15, 'e9144171-dffe-4611-b520-d8f303a702f1', 6, NULL, 3, NULL, 'bot', 10, 2700, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '2r1k2r/ppp1Q1pp/8/8/8/2N2N2/PPP2PPP/2KRR3 b - - 0 16', '1. e4 d5 2. exd5 Qxd5 3. Nc3 Qxd2+ 4. Qxd2 e5 5. Bc4 e4 6. Qe3 Nd7 7. Qxe4+ Ne5 8. Qxe5+ Be6 9. Bxe6 fxe6 10. Qxe6+ Kd8 11. Bg5+ Be7 12. O-O-O+ Ke8 13. Nf3 Rb8 14. Rhe1 Rc8 15. Bxe7 Nxe7 16. Qxe7#', 31, '1-0', 6, 'White wins by Checkmate!', 1903, 2700, 32, 0, NULL, NULL, NULL, 'completed', '2026-10-05 09:06:53', '2026-10-05 09:07:32'),
(16, '50abb893-5f82-4cb0-89e0-aac2e9a6799e', 6, NULL, 3, NULL, 'bot', 3, 750, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 1935, 750, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 09:10:31', NULL),
(17, 'aaa95c4d-bb5a-4b12-bf0f-dd3a4b1f5a5e', 6, NULL, 3, NULL, 'bot', 8, 2000, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 1935, 2000, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 09:28:15', NULL),
(18, '4bf9aa7f-3aaa-4d84-a251-f7285b673381', 6, NULL, 3, NULL, 'bot', 10, 2700, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 1935, 2700, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 09:43:15', NULL),
(19, 'c1524e3c-f508-455c-bd8c-e04fd06f6afc', 6, NULL, 3, NULL, 'bot', 10, 2700, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '2k5/Q7/8/5B2/8/5N2/RBP2PPP/3R2K1 b - - 10 32', '1. d4 d5 2. Nf3 e5 3. dxe5 Bb4+ 4. Nc3 Bxc3+ 5. bxc3 d4 6. cxd4 Qxd4 7. Qxd4 Nd7 8. e4 Nxe5 9. Qxe5+ Be6 10. Bb5+ Kf8 11. O-O Bxa2 12. Rxa2 Rb8 13. Qxc7 Rc8 14. Qxc8+ Ke7 15. Ba3+ Kf6 16. Qd8+ Ne7 17. Qxh8 Nd5 18. exd5 a6 19. Bd3 b6 20. Qxh7 g6 21. Bb2+ Ke7 22. d6+ Kxd6 23. Re1 Kd5 24. Qxf7+ Kc6 25. Qxg6+ Kd5 26. Qxb6 a5 27. Qxa5+ Kc6 28. Be4+ Kd7 29. Rd1+ Kc8 30. Qc5+ Kb8 31. Qa7+ Kc8 32. Bf5#', 63, '1-0', 6, 'White wins by Checkmate!', 1935, 2700, 32, 0, NULL, NULL, NULL, 'completed', '2026-10-05 09:43:21', '2026-10-05 09:45:01'),
(20, '51ae1f4c-d995-44f6-8e70-cd54868deb12', NULL, 6, 3, NULL, 'bot', 10, 2700, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 2700, 1967, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 09:45:18', NULL),
(21, '88dd10f7-6358-43a3-abec-ca27ed2bb14f', NULL, 6, 3, NULL, 'bot', 10, 2700, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 2700, 1967, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 09:45:21', NULL),
(22, 'cc83272a-b415-412a-af70-1540b28be5b4', NULL, 6, 3, NULL, 'bot', 10, 2700, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'r2q3k/bR3Q2/8/4P1pp/3P4/7P/5PP1/2BR2K1 w - - 1 27', '1. c4 e5 2. Nc3 Nc6 3. Nf3 Nf6 4. a3 d5 5. cxd5 Nxd5 6. e4 Nxc3 7. bxc3 Bg4 8. Rb1 Bc5 9. h3 Bxf3 10. Qxf3 O-O 11. Bc4 Qe7 12. a4 a6 13. O-O b5 14. Bd5 Na5 15. Bxa8 Rxa8 16. axb5 axb5 17. Rxb5 c6 18. Rb1 Nc4 19. Rd1 Nd6 20. d4 exd4 21. cxd4 Ba7 22. e5 Nc4 23. Qxc6 Qd8 24. Qxc4 h5 25. Rb7 g5 26. Qxf7+ Kh8', 52, '1-0', NULL, 'White wins by Checkmate!', 2700, 1967, 0, 0, NULL, NULL, NULL, 'completed', '2026-10-05 10:09:28', '2026-10-05 10:12:50'),
(23, '3d019082-01c4-4ed3-802d-cbe9085c82c4', NULL, 6, 3, NULL, 'bot', 1, 100, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'r2qkb1r/pp3ppp/4p3/np1pNb1Q/3P1B2/P1N1P2P/1PP2PP1/R3K2R w KQkq - 0 12', '1. d4 d5 2. Bf4 Nc6 3. Nf3 Nf6 4. a3 Bf5 5. Ne5 Na5 6. e3 Qd7 7. h3 Qd8 8. Nc3 e6 9. Be2 Nh5 10. Bb5+ c6 11. Qxh5 cxb5', 22, '1-0', NULL, 'White wins by Checkmate!', 100, 1967, 0, -32, NULL, NULL, NULL, 'completed', '2026-10-05 10:13:59', '2026-10-05 10:16:18'),
(24, '1638daa6-d479-4baa-8ee1-6067811d3919', 5, NULL, 3, NULL, 'calibration', 1, 100, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 400, 100, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-05 10:59:42', NULL),
(25, '8576ec36-9990-42e9-b1f1-49438d66636c', 6, NULL, 3, NULL, 'bot', 1, 100, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 0, '*', NULL, NULL, 1935, 100, 0, 0, NULL, NULL, NULL, 'in_progress', '2026-10-06 07:49:09', NULL),
(26, 'e9e26018-4084-4447-94e7-8194cf104f3f', 6, NULL, 3, NULL, 'bot', 1, 100, 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '5QQ1/k1B4N/p7/1p1P4/3PN2P/8/PPP5/2KR3R w - - 1 33', '1. f4 f5 2. g3 Nh6 3. h4 Nc6 4. Nf3 d5 5. Nc3 a6 6. d4 Be6 7. Ng5 Rc8 8. Nxe6 g5 9. Nxd8 gxf4 10. Nxc6 Kd7 11. Ne5+ Ke6 12. gxf4 Nf7 13. Nxf7 Rg8 14. Ng5+ Kd6 15. Nxh7 Bg7 16. Ng5 e5 17. fxe5+ Kd7 18. Bh3 Rge8 19. Bxf5+ Kc6 20. e4 Rh8 21. Qg4 Rcd8 22. Bf4 Bf6 23. exf6 Rh7 24. Nxh7 Kb6 25. exd5 Re8+ 26. Be4 Rxe4+ 27. Nxe4 Ka7 28. O-O-O Kb8 29. Qg8+ Ka7 30. Bxc7 b5 31. f7 Kb7 32. f8=Q Ka7', 64, '1-0', 6, 'White wins by Checkmate!', 1935, 100, 0, 0, NULL, NULL, NULL, 'completed', '2026-10-10 11:27:26', '2026-10-10 11:34:04');

-- --------------------------------------------------------

--
-- Table structure for table `chess_live_sessions`
--

CREATE TABLE `chess_live_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_user_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `session_type` enum('1-on-1','batch_class') NOT NULL DEFAULT '1-on-1',
  `current_fen` varchar(150) NOT NULL DEFAULT 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1',
  `pgn` longtext DEFAULT NULL,
  `orientation` enum('white','black') NOT NULL DEFAULT 'white',
  `annotations_json` longtext DEFAULT NULL CHECK (json_valid(`annotations_json`)),
  `teacher_turn` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('scheduled','live','ended') NOT NULL DEFAULT 'scheduled',
  `scheduled_at` datetime DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chess_live_sessions`
--

INSERT INTO `chess_live_sessions` (`id`, `uuid`, `school_id`, `teacher_user_id`, `student_user_id`, `batch_id`, `title`, `session_type`, `current_fen`, `pgn`, `orientation`, `annotations_json`, `teacher_turn`, `status`, `scheduled_at`, `started_at`, `ended_at`, `created_at`) VALUES
(1, 'd33434ce-7abe-4555-aea4-64b40e803dd9', 3, 5, NULL, NULL, 'Endgame', '1-on-1', 'rnbqkbnr/pppp1ppp/8/4p3/4P3/8/PPPP1PPP/RNBQKBNR w KQkq e6 0 2', '[SetUp \"1\"]\n[FEN \"rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq e3 0 1\"]\n\n1. ... e5', 'white', NULL, 1, 'ended', NULL, '2026-10-05 10:28:26', '2026-10-05 10:31:46', '2026-10-05 10:28:26'),
(2, 'e37f4e57-90d8-46c9-83cd-c4baf910611f', 3, 5, NULL, NULL, 'Endgame', '1-on-1', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NULL, 'white', NULL, 1, 'live', NULL, '2026-10-05 11:00:01', NULL, '2026-10-05 11:00:01'),
(3, '6dd58c77-ffae-41c1-8635-4ce6f9150bf4', 3, 5, 6, NULL, '1-on-1 Coaching with STUTEST1', '1-on-1', 'r1bq1rk1/pppp1ppp/2n2n2/1B2p3/1b2P3/2N2N2/PPPP1PPP/R1BQ1RK1 w - - 8 6', '[SetUp \"1\"]\n[FEN \"r1bqk2r/pppp1ppp/2n2n2/1B2p3/1b2P3/2N2N2/PPPP1PPP/R1BQ1RK1 b kq - 7 5\"]\n\n5. ... O-O', 'white', NULL, 1, 'ended', NULL, '2026-10-05 11:01:04', '2026-10-05 11:01:55', '2026-10-05 11:01:04');

-- --------------------------------------------------------

--
-- Table structure for table `chess_ratings`
--

CREATE TABLE `chess_ratings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `current_rating` int(10) UNSIGNED NOT NULL DEFAULT 400,
  `tier` enum('learner','rookie','intermediate','master','grandmaster') NOT NULL DEFAULT 'learner',
  `games_played` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `wins` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `draws` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `losses` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `peak_rating` int(10) UNSIGNED NOT NULL DEFAULT 400,
  `calibration_completed` tinyint(1) NOT NULL DEFAULT 0,
  `calibration_matches_played` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `in_promotion_battle` tinyint(1) NOT NULL DEFAULT 0,
  `promotion_matches_played` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `promotion_wins` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `promotion_draws` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `in_relegation_defense` tinyint(1) NOT NULL DEFAULT 0,
  `relegation_matches_played` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `relegation_wins` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `relegation_draws` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `season_number` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `season_reset_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chess_ratings`
--

INSERT INTO `chess_ratings` (`id`, `student_user_id`, `school_id`, `current_rating`, `tier`, `games_played`, `wins`, `draws`, `losses`, `peak_rating`, `calibration_completed`, `calibration_matches_played`, `in_promotion_battle`, `promotion_matches_played`, `promotion_wins`, `promotion_draws`, `in_relegation_defense`, `relegation_matches_played`, `relegation_wins`, `relegation_draws`, `season_number`, `season_reset_at`, `created_at`, `updated_at`) VALUES
(1, 6, 3, 1935, 'master', 14, 10, 0, 4, 1967, 1, 6, 0, 0, 0, 0, 0, 0, 0, 0, 1, NULL, '2026-10-05 07:18:07', '2026-10-10 11:34:04'),
(2, 1, NULL, 400, 'learner', 0, 0, 0, 0, 400, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, NULL, '2026-10-05 10:24:29', '2026-10-05 10:24:29'),
(3, 5, 3, 400, 'learner', 0, 0, 0, 0, 400, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, NULL, '2026-10-05 10:30:27', '2026-10-05 10:30:27'),
(4, 11, NULL, 400, 'learner', 0, 0, 0, 0, 400, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, NULL, '2026-10-24 09:18:38', '2026-10-24 09:18:38'),
(5, 21, 3, 400, 'learner', 0, 0, 0, 0, 400, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, NULL, '2026-10-24 10:16:07', '2026-10-24 10:16:07'),
(6, 22, 3, 400, 'learner', 0, 0, 0, 0, 400, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, NULL, '2026-10-24 10:26:03', '2026-10-24 10:26:03'),
(7, 24, 3, 400, 'learner', 0, 0, 0, 0, 400, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, NULL, '2026-10-24 10:37:31', '2026-10-24 10:37:31');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `section` varchar(10) DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `competitions`
--

CREATE TABLE `competitions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `level` enum('school','district','state','national','international','online') DEFAULT 'school',
  `description` longtext DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `venue` varchar(200) DEFAULT NULL,
  `registration_deadline` date DEFAULT NULL,
  `fee` decimal(10,2) DEFAULT 0.00,
  `cover_image` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','closed','archived') DEFAULT 'published',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `competition_participants`
--

CREATE TABLE `competition_participants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `competition_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('registered','participated','qualified','disqualified') DEFAULT 'registered',
  `registered_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `competition_results`
--

CREATE TABLE `competition_results` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `competition_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `rank_position` int(10) UNSIGNED DEFAULT NULL,
  `score` decimal(7,2) DEFAULT NULL,
  `certificate_url` varchar(500) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `recorded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `recorded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `counseling_logs`
--

CREATE TABLE `counseling_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(50) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `counseling_logs`
--

INSERT INTO `counseling_logs` (`id`, `request_id`, `user_id`, `action`, `note`, `created_at`) VALUES
(1, 1, 6, 'submitted', 'Request created', '2026-09-30 06:49:16'),
(2, 1, 4, 'assigned', 'Teacher #5 assigned', '2026-09-30 06:59:35'),
(3, 1, 5, 'in_progress', NULL, '2026-09-30 07:07:41'),
(4, 1, 5, 'completed', 'Hello', '2026-09-30 07:07:51');

-- --------------------------------------------------------

--
-- Table structure for table `counseling_requests`
--

CREATE TABLE `counseling_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `counseling_type` enum('academics','career','admission','psychometric') NOT NULL,
  `rating_concentration` tinyint(3) UNSIGNED NOT NULL,
  `rating_cognitive` tinyint(3) UNSIGNED NOT NULL,
  `rating_comprehension` tinyint(3) UNSIGNED NOT NULL,
  `rating_creativity` tinyint(3) UNSIGNED NOT NULL,
  `rating_confidence` tinyint(3) UNSIGNED NOT NULL,
  `rating_communication` tinyint(3) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `current_class` varchar(50) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `preferred_time` varchar(100) DEFAULT NULL,
  `status` enum('pending','assigned','in_progress','completed','cancelled') DEFAULT 'pending',
  `assigned_teacher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `teacher_notes` text DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `rating_by_student` tinyint(3) UNSIGNED DEFAULT NULL,
  `student_feedback` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `counseling_requests`
--

INSERT INTO `counseling_requests` (`id`, `uuid`, `student_user_id`, `school_id`, `counseling_type`, `rating_concentration`, `rating_cognitive`, `rating_comprehension`, `rating_creativity`, `rating_confidence`, `rating_communication`, `full_name`, `email`, `phone`, `current_class`, `city`, `state`, `reason`, `preferred_time`, `status`, `assigned_teacher_id`, `assigned_at`, `assigned_by`, `teacher_notes`, `completed_at`, `rating_by_student`, `student_feedback`, `created_at`, `updated_at`) VALUES
(1, '894bf296-41fc-4816-9bf2-6204219d28ac', 6, 3, 'career', 2, 5, 3, 5, 1, 1, 'STUTEST1', 'stu@gmail.com', '852369745', '6', 'qw', 'qw', 'Hi, I cannot decide what to do after 10th.', 'AT LUNCH', 'completed', 5, '2026-09-30 06:59:35', 4, 'Hello', '2026-09-30 07:07:51', NULL, NULL, '2026-09-30 06:49:16', '2026-09-30 07:07:51');

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `owner_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_path` varchar(500) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `size_bytes` bigint(20) UNSIGNED DEFAULT NULL,
  `purpose` enum('profile_image','lesson_attachment','activity_cover','certificate','submission','other') DEFAULT 'other',
  `related_table` varchar(50) DEFAULT NULL,
  `related_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `chapter_id` bigint(20) UNSIGNED DEFAULT NULL,
  `topic_id` bigint(20) UNSIGNED DEFAULT NULL,
  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `summary` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `attachments` longtext DEFAULT NULL CHECK (json_valid(`attachments`)),
  `is_ai_generated` tinyint(1) DEFAULT 0,
  `difficulty` enum('easy','medium','hard') DEFAULT 'medium',
  `estimated_minutes` int(10) UNSIGNED DEFAULT 15,
  `published` tinyint(1) DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `subject_id`, `chapter_id`, `topic_id`, `batch_id`, `title`, `slug`, `summary`, `content`, `video_url`, `attachment`, `attachments`, `is_ai_generated`, `difficulty`, `estimated_minutes`, `published`, `created_by`, `school_id`, `created_at`, `updated_at`) VALUES
(1, 4, NULL, NULL, NULL, 'environment', 'environment-404f', '', '', '', '/uploads/lessons/7bde61a535282de2_1790344898.jpg', NULL, 0, 'medium', 15, 1, 4, 3, '2026-09-25 19:31:38', '2026-09-25 19:31:38'),
(2, 1, NULL, NULL, NULL, 'qqqqqqq', 'qqqqqqq-0e5d', NULL, NULL, NULL, NULL, '[{\"path\":\"\\/uploads\\/lessons\\/dcb9c6042f626aa3_1790346254.pdf\",\"mime\":\"application\\/pdf\",\"ext\":\"pdf\",\"name\":\"Nishant_kumar_singh_Internship_offer_letter.pdf\",\"size\":715935}]', 0, 'easy', 15, 1, 4, 3, '2026-09-25 19:54:14', '2026-09-25 19:54:14'),
(3, 1, NULL, NULL, NULL, 'REAL NUMBERS', 'real-numbers-d776', NULL, NULL, NULL, NULL, '[{\"path\":\"\\/uploads\\/lessons\\/a1244f70d9729fe2_1790760284.docx\",\"mime\":\"application\\/vnd.openxmlformats-officedocument.wordprocessingml.document\",\"ext\":\"docx\",\"name\":\"Real Number set_1 MCQ.docx\",\"size\":29314}]', 0, 'medium', 15, 1, 14, 9, '2026-09-30 09:24:44', '2026-09-30 09:24:44'),
(4, 5, NULL, NULL, NULL, 'nx vj', 'nx-vj-2840', 'sdv sd', 'nmsdv jsd b', 'https://infintylearning.net/school/lessons/new', NULL, NULL, 0, 'hard', 1, 1, 4, 3, '2026-10-24 10:39:54', '2026-10-24 10:39:54');

-- --------------------------------------------------------

--
-- Table structure for table `lesson_doubts`
--

CREATE TABLE `lesson_doubts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `lesson_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `attachment_url` varchar(500) DEFAULT NULL,
  `status` enum('open','answered','resolved') NOT NULL DEFAULT 'open',
  `replies_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lesson_doubts`
--

INSERT INTO `lesson_doubts` (`id`, `uuid`, `lesson_id`, `student_user_id`, `school_id`, `batch_id`, `title`, `body`, `attachment_url`, `status`, `replies_count`, `created_at`, `updated_at`) VALUES
(1, '61c08e07-1889-4149-824f-6a82e1e7118a', 1, 6, 3, NULL, 'WHAT', 'Test Doubt', NULL, 'answered', 1, '2026-10-09 09:06:44', '2026-10-10 11:20:12');

-- --------------------------------------------------------

--
-- Table structure for table `lesson_doubt_replies`
--

CREATE TABLE `lesson_doubt_replies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `doubt_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `user_role` enum('student','teacher','admin') NOT NULL,
  `reply_text` text NOT NULL,
  `is_teacher_endorsed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lesson_doubt_replies`
--

INSERT INTO `lesson_doubt_replies` (`id`, `doubt_id`, `user_id`, `user_role`, `reply_text`, `is_teacher_endorsed`, `created_at`) VALUES
(1, 1, 5, 'teacher', 'Tri', 1, '2026-10-10 11:20:12');

-- --------------------------------------------------------

--
-- Table structure for table `lesson_progress`
--

CREATE TABLE `lesson_progress` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lesson_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('not_started','in_progress','completed') DEFAULT 'not_started',
  `progress_percent` tinyint(3) UNSIGNED DEFAULT 0,
  `last_viewed_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lesson_progress`
--

INSERT INTO `lesson_progress` (`id`, `lesson_id`, `student_user_id`, `status`, `progress_percent`, `last_viewed_at`, `completed_at`) VALUES
(1, 1, 6, 'in_progress', 10, '2026-09-25 19:32:10', NULL),
(2, 2, 4, 'in_progress', 10, '2026-10-18 13:06:45', NULL),
(3, 1, 4, 'in_progress', 10, '2026-10-18 13:07:30', NULL),
(9, 1, 1, 'in_progress', 10, '2026-09-29 08:09:45', NULL),
(10, 2, 5, 'in_progress', 10, '2026-10-18 13:09:57', NULL),
(12, 2, 1, 'in_progress', 10, '2026-09-29 08:10:00', NULL),
(13, 3, 14, 'in_progress', 10, '2026-09-30 09:24:49', NULL),
(14, 3, 1, 'in_progress', 10, '2026-10-18 13:08:54', NULL),
(15, 2, 6, 'in_progress', 10, '2026-10-10 11:35:21', NULL),
(21, 4, 6, 'in_progress', 10, '2026-10-26 14:26:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lesson_quizzes`
--

CREATE TABLE `lesson_quizzes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lesson_id` bigint(20) UNSIGNED NOT NULL,
  `questions_json` longtext NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lesson_quizzes`
--

INSERT INTO `lesson_quizzes` (`id`, `lesson_id`, `questions_json`, `created_at`, `updated_at`) VALUES
(1, 2, '[{\"question\":\"What is the primary core objective taught in \\\"qqqqqqq\\\"?\",\"options\":[\"To understand foundational mechanisms and practical applications\",\"To memorize historical dates without context\",\"To ignore theoretical frameworks completely\",\"To bypass standard analytical reasoning\"],\"correct_index\":0,\"explanation\":\"The primary focus of this lesson is mastering foundational principles and their practical applications.\"},{\"question\":\"In Mathematics, which methodology is most appropriate for analyzing qqqqqqq?\",\"options\":[\"Unchecked assumption\",\"Structured empirical reasoning and step-by-step verification\",\"Arbitrary guesswork\",\"Skipping preliminary checks\"],\"correct_index\":1,\"explanation\":\"Mathematics relies upon structured empirical evaluation and verifiable analytical deduction.\"},{\"question\":\"What is a common misconception when studying \\\"qqqqqqq\\\"?\",\"options\":[\"That variables can be defined with precision\",\"That complex problems can be simplified into basic components\",\"That intuition alone replaces rigorous problem solving\",\"That formulas require units of measurement\"],\"correct_index\":2,\"explanation\":\"A frequent trap for students is relying solely on superficial intuition rather than systematic proof.\"},{\"question\":\"How does \\\"qqqqqqq\\\" directly relate to the broader chapter on \\\"Fundamental Principles\\\"?\",\"options\":[\"It serves as a critical stepping stone linking basic theory to advanced problem-solving\",\"It has zero connection to the chapter curriculum\",\"It completely contradicts established rules\",\"It only applies to theoretical simulations and never real scenarios\"],\"correct_index\":0,\"explanation\":\"This concept forms the cornerstone connecting fundamental definitions with advanced problem sets.\"},{\"question\":\"When solving problems in this lesson, what is the recommended first step?\",\"options\":[\"Jump straight to guessing the answer\",\"Identify the given parameters, constraints, and target unknowns\",\"Ignore units and notation\",\"Assume all constants are equal to zero\"],\"correct_index\":1,\"explanation\":\"The cardinal rule of analytical problem solving is clearly noting knowns, constraints, and target values first.\"},{\"question\":\"Which of the following conditions would invalidate a solution in \\\"qqqqqqq\\\"?\",\"options\":[\"Matching dimensional consistency\",\"Violating fundamental conservation laws or constraint limits\",\"Verifying calculations with an alternative method\",\"Documenting intermediate steps\"],\"correct_index\":1,\"explanation\":\"Any outcome that breaches boundary constraints or foundational conservation laws is physically or mathematically invalid.\"},{\"question\":\"What key skill is reinforced through regular practice of \\\"qqqqqqq\\\"?\",\"options\":[\"Passive observation\",\"Critical analysis, pattern recognition, and problem decomposition\",\"Rote memorization without understanding\",\"Trial and error without reflection\"],\"correct_index\":1,\"explanation\":\"Working through variations of this topic sharpens logical decomposition and pattern recognition.\"},{\"question\":\"In real-world applications of Mathematics, where is \\\"qqqqqqq\\\" most frequently utilized?\",\"options\":[\"Predictive modeling, engineering systems, and decision analysis\",\"Random number generation only\",\"Nowhere outside classroom exams\",\"Exclusively in historical trivia\"],\"correct_index\":0,\"explanation\":\"This concept is heavily applied in practical industry models, systems engineering, and strategic decisions.\"},{\"question\":\"If an edge case produces unexpected outputs in this topic, what should you verify first?\",\"options\":[\"Boundary conditions, zero denominators, and domain limits\",\"Conclude the entire theory is flawed\",\"Discard the test data immediately\",\"Increase calculation speed without checking inputs\"],\"correct_index\":0,\"explanation\":\"Edge cases almost always trace back to extreme boundaries, domain restrictions, or division by zero.\"},{\"question\":\"What is the best way to demonstrate true mastery of \\\"qqqqqqq\\\"?\",\"options\":[\"Being able to explain the core concept simply to a peer and solve non-routine challenge problems\",\"Memorizing one specific textbook question word-for-word\",\"Avoiding practice exercises\",\"Depending on answer keys without independent attempts\"],\"correct_index\":0,\"explanation\":\"True mastery is reflected in the Feynman technique: the ability to explain the principle lucidly and tackle novel challenges.\"}]', '2026-10-10 11:35:21', '2026-10-10 11:35:21'),
(2, 1, '[{\"question\":\"What is the primary core objective taught in \\\"environment\\\"?\",\"options\":[\"To understand foundational mechanisms and practical applications\",\"To memorize historical dates without context\",\"To ignore theoretical frameworks completely\",\"To bypass standard analytical reasoning\"],\"correct_index\":0,\"explanation\":\"The primary focus of this lesson is mastering foundational principles and their practical applications.\"},{\"question\":\"In Biology, which methodology is most appropriate for analyzing environment?\",\"options\":[\"Unchecked assumption\",\"Structured empirical reasoning and step-by-step verification\",\"Arbitrary guesswork\",\"Skipping preliminary checks\"],\"correct_index\":1,\"explanation\":\"Biology relies upon structured empirical evaluation and verifiable analytical deduction.\"},{\"question\":\"What is a common misconception when studying \\\"environment\\\"?\",\"options\":[\"That variables can be defined with precision\",\"That complex problems can be simplified into basic components\",\"That intuition alone replaces rigorous problem solving\",\"That formulas require units of measurement\"],\"correct_index\":2,\"explanation\":\"A frequent trap for students is relying solely on superficial intuition rather than systematic proof.\"},{\"question\":\"How does \\\"environment\\\" directly relate to the broader chapter on \\\"Fundamental Principles\\\"?\",\"options\":[\"It serves as a critical stepping stone linking basic theory to advanced problem-solving\",\"It has zero connection to the chapter curriculum\",\"It completely contradicts established rules\",\"It only applies to theoretical simulations and never real scenarios\"],\"correct_index\":0,\"explanation\":\"This concept forms the cornerstone connecting fundamental definitions with advanced problem sets.\"},{\"question\":\"When solving problems in this lesson, what is the recommended first step?\",\"options\":[\"Jump straight to guessing the answer\",\"Identify the given parameters, constraints, and target unknowns\",\"Ignore units and notation\",\"Assume all constants are equal to zero\"],\"correct_index\":1,\"explanation\":\"The cardinal rule of analytical problem solving is clearly noting knowns, constraints, and target values first.\"},{\"question\":\"Which of the following conditions would invalidate a solution in \\\"environment\\\"?\",\"options\":[\"Matching dimensional consistency\",\"Violating fundamental conservation laws or constraint limits\",\"Verifying calculations with an alternative method\",\"Documenting intermediate steps\"],\"correct_index\":1,\"explanation\":\"Any outcome that breaches boundary constraints or foundational conservation laws is physically or mathematically invalid.\"},{\"question\":\"What key skill is reinforced through regular practice of \\\"environment\\\"?\",\"options\":[\"Passive observation\",\"Critical analysis, pattern recognition, and problem decomposition\",\"Rote memorization without understanding\",\"Trial and error without reflection\"],\"correct_index\":1,\"explanation\":\"Working through variations of this topic sharpens logical decomposition and pattern recognition.\"},{\"question\":\"In real-world applications of Biology, where is \\\"environment\\\" most frequently utilized?\",\"options\":[\"Predictive modeling, engineering systems, and decision analysis\",\"Random number generation only\",\"Nowhere outside classroom exams\",\"Exclusively in historical trivia\"],\"correct_index\":0,\"explanation\":\"This concept is heavily applied in practical industry models, systems engineering, and strategic decisions.\"},{\"question\":\"If an edge case produces unexpected outputs in this topic, what should you verify first?\",\"options\":[\"Boundary conditions, zero denominators, and domain limits\",\"Conclude the entire theory is flawed\",\"Discard the test data immediately\",\"Increase calculation speed without checking inputs\"],\"correct_index\":0,\"explanation\":\"Edge cases almost always trace back to extreme boundaries, domain restrictions, or division by zero.\"},{\"question\":\"What is the best way to demonstrate true mastery of \\\"environment\\\"?\",\"options\":[\"Being able to explain the core concept simply to a peer and solve non-routine challenge problems\",\"Memorizing one specific textbook question word-for-word\",\"Avoiding practice exercises\",\"Depending on answer keys without independent attempts\"],\"correct_index\":0,\"explanation\":\"True mastery is reflected in the Feynman technique: the ability to explain the principle lucidly and tackle novel challenges.\"}]', '2026-10-18 13:07:30', '2026-10-18 13:07:30'),
(3, 3, '[{\"question\":\"What is the primary core objective taught in \\\"REAL NUMBERS\\\"?\",\"options\":[\"To understand foundational mechanisms and practical applications\",\"To memorize historical dates without context\",\"To ignore theoretical frameworks completely\",\"To bypass standard analytical reasoning\"],\"correct_index\":0,\"explanation\":\"The primary focus of this lesson is mastering foundational principles and their practical applications.\"},{\"question\":\"In Mathematics, which methodology is most appropriate for analyzing REAL NUMBERS?\",\"options\":[\"Unchecked assumption\",\"Structured empirical reasoning and step-by-step verification\",\"Arbitrary guesswork\",\"Skipping preliminary checks\"],\"correct_index\":1,\"explanation\":\"Mathematics relies upon structured empirical evaluation and verifiable analytical deduction.\"},{\"question\":\"What is a common misconception when studying \\\"REAL NUMBERS\\\"?\",\"options\":[\"That variables can be defined with precision\",\"That complex problems can be simplified into basic components\",\"That intuition alone replaces rigorous problem solving\",\"That formulas require units of measurement\"],\"correct_index\":2,\"explanation\":\"A frequent trap for students is relying solely on superficial intuition rather than systematic proof.\"},{\"question\":\"How does \\\"REAL NUMBERS\\\" directly relate to the broader chapter on \\\"Fundamental Principles\\\"?\",\"options\":[\"It serves as a critical stepping stone linking basic theory to advanced problem-solving\",\"It has zero connection to the chapter curriculum\",\"It completely contradicts established rules\",\"It only applies to theoretical simulations and never real scenarios\"],\"correct_index\":0,\"explanation\":\"This concept forms the cornerstone connecting fundamental definitions with advanced problem sets.\"},{\"question\":\"When solving problems in this lesson, what is the recommended first step?\",\"options\":[\"Jump straight to guessing the answer\",\"Identify the given parameters, constraints, and target unknowns\",\"Ignore units and notation\",\"Assume all constants are equal to zero\"],\"correct_index\":1,\"explanation\":\"The cardinal rule of analytical problem solving is clearly noting knowns, constraints, and target values first.\"},{\"question\":\"Which of the following conditions would invalidate a solution in \\\"REAL NUMBERS\\\"?\",\"options\":[\"Matching dimensional consistency\",\"Violating fundamental conservation laws or constraint limits\",\"Verifying calculations with an alternative method\",\"Documenting intermediate steps\"],\"correct_index\":1,\"explanation\":\"Any outcome that breaches boundary constraints or foundational conservation laws is physically or mathematically invalid.\"},{\"question\":\"What key skill is reinforced through regular practice of \\\"REAL NUMBERS\\\"?\",\"options\":[\"Passive observation\",\"Critical analysis, pattern recognition, and problem decomposition\",\"Rote memorization without understanding\",\"Trial and error without reflection\"],\"correct_index\":1,\"explanation\":\"Working through variations of this topic sharpens logical decomposition and pattern recognition.\"},{\"question\":\"In real-world applications of Mathematics, where is \\\"REAL NUMBERS\\\" most frequently utilized?\",\"options\":[\"Predictive modeling, engineering systems, and decision analysis\",\"Random number generation only\",\"Nowhere outside classroom exams\",\"Exclusively in historical trivia\"],\"correct_index\":0,\"explanation\":\"This concept is heavily applied in practical industry models, systems engineering, and strategic decisions.\"},{\"question\":\"If an edge case produces unexpected outputs in this topic, what should you verify first?\",\"options\":[\"Boundary conditions, zero denominators, and domain limits\",\"Conclude the entire theory is flawed\",\"Discard the test data immediately\",\"Increase calculation speed without checking inputs\"],\"correct_index\":0,\"explanation\":\"Edge cases almost always trace back to extreme boundaries, domain restrictions, or division by zero.\"},{\"question\":\"What is the best way to demonstrate true mastery of \\\"REAL NUMBERS\\\"?\",\"options\":[\"Being able to explain the core concept simply to a peer and solve non-routine challenge problems\",\"Memorizing one specific textbook question word-for-word\",\"Avoiding practice exercises\",\"Depending on answer keys without independent attempts\"],\"correct_index\":0,\"explanation\":\"True mastery is reflected in the Feynman technique: the ability to explain the principle lucidly and tackle novel challenges.\"}]', '2026-10-18 13:08:03', '2026-10-18 13:08:03'),
(4, 4, '[{\"question\":\"What is the primary core objective taught in \\\"nx vj\\\"?\",\"options\":[\"To understand foundational mechanisms and practical applications\",\"To memorize historical dates without context\",\"To ignore theoretical frameworks completely\",\"To bypass standard analytical reasoning\"],\"correct_index\":0,\"explanation\":\"The primary focus of this lesson is mastering foundational principles and their practical applications.\"},{\"question\":\"In English, which methodology is most appropriate for analyzing nx vj?\",\"options\":[\"Unchecked assumption\",\"Structured empirical reasoning and step-by-step verification\",\"Arbitrary guesswork\",\"Skipping preliminary checks\"],\"correct_index\":1,\"explanation\":\"English relies upon structured empirical evaluation and verifiable analytical deduction.\"},{\"question\":\"What is a common misconception when studying \\\"nx vj\\\"?\",\"options\":[\"That variables can be defined with precision\",\"That complex problems can be simplified into basic components\",\"That intuition alone replaces rigorous problem solving\",\"That formulas require units of measurement\"],\"correct_index\":2,\"explanation\":\"A frequent trap for students is relying solely on superficial intuition rather than systematic proof.\"},{\"question\":\"How does \\\"nx vj\\\" directly relate to the broader chapter on \\\"Fundamental Principles\\\"?\",\"options\":[\"It serves as a critical stepping stone linking basic theory to advanced problem-solving\",\"It has zero connection to the chapter curriculum\",\"It completely contradicts established rules\",\"It only applies to theoretical simulations and never real scenarios\"],\"correct_index\":0,\"explanation\":\"This concept forms the cornerstone connecting fundamental definitions with advanced problem sets.\"},{\"question\":\"When solving problems in this lesson, what is the recommended first step?\",\"options\":[\"Jump straight to guessing the answer\",\"Identify the given parameters, constraints, and target unknowns\",\"Ignore units and notation\",\"Assume all constants are equal to zero\"],\"correct_index\":1,\"explanation\":\"The cardinal rule of analytical problem solving is clearly noting knowns, constraints, and target values first.\"},{\"question\":\"Which of the following conditions would invalidate a solution in \\\"nx vj\\\"?\",\"options\":[\"Matching dimensional consistency\",\"Violating fundamental conservation laws or constraint limits\",\"Verifying calculations with an alternative method\",\"Documenting intermediate steps\"],\"correct_index\":1,\"explanation\":\"Any outcome that breaches boundary constraints or foundational conservation laws is physically or mathematically invalid.\"},{\"question\":\"What key skill is reinforced through regular practice of \\\"nx vj\\\"?\",\"options\":[\"Passive observation\",\"Critical analysis, pattern recognition, and problem decomposition\",\"Rote memorization without understanding\",\"Trial and error without reflection\"],\"correct_index\":1,\"explanation\":\"Working through variations of this topic sharpens logical decomposition and pattern recognition.\"},{\"question\":\"In real-world applications of English, where is \\\"nx vj\\\" most frequently utilized?\",\"options\":[\"Predictive modeling, engineering systems, and decision analysis\",\"Random number generation only\",\"Nowhere outside classroom exams\",\"Exclusively in historical trivia\"],\"correct_index\":0,\"explanation\":\"This concept is heavily applied in practical industry models, systems engineering, and strategic decisions.\"},{\"question\":\"If an edge case produces unexpected outputs in this topic, what should you verify first?\",\"options\":[\"Boundary conditions, zero denominators, and domain limits\",\"Conclude the entire theory is flawed\",\"Discard the test data immediately\",\"Increase calculation speed without checking inputs\"],\"correct_index\":0,\"explanation\":\"Edge cases almost always trace back to extreme boundaries, domain restrictions, or division by zero.\"},{\"question\":\"What is the best way to demonstrate true mastery of \\\"nx vj\\\"?\",\"options\":[\"Being able to explain the core concept simply to a peer and solve non-routine challenge problems\",\"Memorizing one specific textbook question word-for-word\",\"Avoiding practice exercises\",\"Depending on answer keys without independent attempts\"],\"correct_index\":0,\"explanation\":\"True mastery is reflected in the Feynman technique: the ability to explain the principle lucidly and tackle novel challenges.\"}]', '2026-10-26 14:26:35', '2026-10-26 14:26:35');

-- --------------------------------------------------------

--
-- Table structure for table `lesson_quiz_attempts`
--

CREATE TABLE `lesson_quiz_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lesson_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `score` tinyint(3) UNSIGNED NOT NULL,
  `total_questions` tinyint(3) UNSIGNED NOT NULL DEFAULT 10,
  `passed` tinyint(1) NOT NULL DEFAULT 0,
  `xp_awarded` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `answers_json` longtext DEFAULT NULL,
  `attempted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(60) NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` text DEFAULT NULL,
  `link` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `body`, `link`, `is_read`, `created_at`) VALUES
(1, 5, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now access your dashboard.', '/dashboard', 1, '2026-09-25 15:12:57'),
(2, 6, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now access your dashboard.', '/dashboard', 1, '2026-09-25 15:17:51'),
(3, 6, 'password.changed', 'Your password was changed', 'If this wasn\'t you, reset it immediately and contact support.', '/profile', 1, '2026-09-25 18:39:45'),
(4, 6, 'password.changed', 'Your password was changed', 'If this wasn\'t you, reset it immediately and contact support.', '/profile', 1, '2026-09-25 18:43:12'),
(5, 4, 'announcement', 'Quizwiz', 'There is going to be a quiz competation please participate.', '/dashboard', 1, '2026-09-25 19:13:21'),
(6, 5, 'announcement', 'Quizwiz', 'There is going to be a quiz competation please participate.', '/dashboard', 1, '2026-09-25 19:13:21'),
(7, 6, 'announcement', 'Quizwiz', 'There is going to be a quiz competation please participate.', '/dashboard', 1, '2026-09-25 19:13:21'),
(8, 10, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now access your dashboard.', '/dashboard', 1, '2026-09-26 13:57:22'),
(9, 11, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now access your dashboard.', '/dashboard', 1, '2026-09-28 11:54:15'),
(10, 6, 'certificate.issued', 'Your certificate is ready! 🏅', 'You received a certificate for: QUIZWIZ', '/my-certificates', 1, '2026-09-28 12:22:13'),
(11, 6, 'certificate.issued', 'Your certificate is ready! 🏅', 'You received a certificate for: QUIZWIZ', '/my-certificates', 1, '2026-09-28 12:27:28'),
(12, 6, 'certificate.issued', 'Your certificate is ready! 🏅', 'You received a certificate for: QUIZWIZ', '/my-certificates', 1, '2026-09-28 12:35:38'),
(13, 6, 'certificate.issued', 'Your certificate is ready! 🏅', 'You received a certificate for: QUIZWIZ', '/my-certificates', 1, '2026-09-28 12:40:02'),
(14, 6, 'certificate.issued', 'Your certificate is ready! 🏅', 'You received a certificate for: QUIZWIZ', '/my-certificates', 1, '2026-09-28 12:44:45'),
(15, 6, 'certificate.issued', 'Your certificate is ready! 🏅', 'You received a certificate for: QUIZWIZ', '/my-certificates', 1, '2026-09-28 12:53:41'),
(16, 12, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now access your dashboard.', '/dashboard', 0, '2026-09-29 06:37:26'),
(17, 6, 'certificate.issued', 'Your certificate is ready! 🏅', 'You received a certificate for: QUIZWIZ', '/my-certificates', 1, '2026-09-29 10:35:50'),
(18, 4, 'counseling.request', '🧭 New counseling request', 'STUTEST1 requested Career counseling.', '/school/counseling', 1, '2026-09-30 06:49:16'),
(19, 5, 'counseling.assigned', '🧭 New counseling request assigned to you', 'A Career counseling request has been assigned to you.', '/teacher/counseling?id=1', 1, '2026-09-30 06:59:35'),
(20, 6, 'counseling.completed', '✅ Your counseling session is complete', 'Your counselor has shared notes from your session.', '/counseling', 1, '2026-09-30 07:07:51'),
(21, 4, 'counseling.completed', 'Counseling session completed', 'STUTEST1 — Career session finished.', '/school/counseling-detail?id=1', 1, '2026-09-30 07:07:51'),
(22, 5, 'chess.student_assigned', '♟️ New Chess Mentee Assigned', 'A student has been assigned to you for chess coaching and 1-on-1 sessions.', '/teacher/chess', 1, '2026-10-05 10:27:29'),
(23, 6, 'chess.coach_assigned', '♟️ Your Chess Coach Has Been Assigned!', 'Your school assigned a mentor to help you advance in Rising Rooks Chess Academy.', '/chess', 1, '2026-10-05 10:27:29'),
(24, 4, 'password.changed', 'Your password was changed', 'If this wasn\'t you, reset it immediately and contact support.', '/profile', 1, '2026-10-05 10:27:48'),
(25, 4, 'announcement', 'Hello', 'Sta', '/dashboard', 1, '2026-10-10 11:14:01'),
(26, 5, 'announcement', 'Hello', 'Sta', '/dashboard', 1, '2026-10-10 11:14:01'),
(27, 6, 'announcement', 'Hello', 'Sta', '/dashboard', 1, '2026-10-10 11:14:01'),
(28, 15, 'password.changed', 'Your password was changed', 'If this wasn\'t you, reset it immediately and contact support.', '/profile', 1, '2026-10-12 09:43:18'),
(29, 6, 'certificate.issued', 'Your certificate is ready! 🏅', 'You received a certificate for: QUIZWIZ', '/my-certificates', 1, '2026-10-23 11:31:21'),
(30, 17, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now log in and access your dashboard.', '/dashboard', 1, '2026-10-24 09:25:05'),
(31, 18, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now log in and access your dashboard.', '/dashboard', 0, '2026-10-24 09:36:10'),
(32, 19, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now log in and access your dashboard.', '/dashboard', 0, '2026-10-24 09:48:07'),
(33, 19, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now log in and access your dashboard.', '/dashboard', 0, '2026-10-24 09:48:09'),
(34, 18, 'chess.student_assigned', '♟️ New Chess Mentee Assigned', 'A student has been assigned to you for chess coaching and 1-on-1 sessions.', '/teacher/chess', 0, '2026-10-24 10:03:59'),
(35, 6, 'chess.coach_assigned', '♟️ Your Chess Coach Has Been Assigned!', 'Your school assigned a mentor to help you advance in Rising Rooks Chess Academy.', '/chess', 1, '2026-10-24 10:03:59'),
(36, 20, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now log in and access your dashboard.', '/dashboard', 0, '2026-10-24 10:09:08'),
(37, 20, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now log in and access your dashboard.', '/dashboard', 0, '2026-10-24 10:09:37'),
(38, 5, 'announcement', 'importent', 'Nothing', '/dashboard', 1, '2026-10-24 10:10:50'),
(39, 17, 'announcement', 'importent', 'Nothing', '/dashboard', 1, '2026-10-24 10:10:50'),
(40, 18, 'announcement', 'importent', 'Nothing', '/dashboard', 0, '2026-10-24 10:10:50'),
(41, 19, 'announcement', 'importent', 'Nothing', '/dashboard', 0, '2026-10-24 10:10:50'),
(42, 20, 'announcement', 'importent', 'Nothing', '/dashboard', 0, '2026-10-24 10:10:50'),
(43, 21, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now log in and access your dashboard.', '/dashboard', 0, '2026-10-24 10:14:47'),
(44, 21, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now log in and access your dashboard.', '/dashboard', 0, '2026-10-24 10:14:50'),
(45, 22, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now log in and access your dashboard.', '/dashboard', 0, '2026-10-24 10:25:16'),
(46, 23, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now log in and access your dashboard.', '/dashboard', 0, '2026-10-24 10:34:00'),
(47, 24, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now log in and access your dashboard.', '/dashboard', 0, '2026-10-24 10:36:19'),
(48, 19, 'chess.student_assigned', '♟️ New Chess Mentee Assigned', 'A student has been assigned to you for chess coaching and 1-on-1 sessions.', '/teacher/chess', 0, '2026-10-24 10:37:51'),
(49, 21, 'chess.coach_assigned', '♟️ Your Chess Coach Has Been Assigned!', 'Your school assigned a mentor to help you advance in Rising Rooks Chess Academy.', '/chess', 0, '2026-10-24 10:37:51'),
(50, 4, 'announcement', 'hello', 'hello', '/dashboard', 1, '2026-10-24 10:44:29'),
(51, 5, 'announcement', 'hello', 'hello', '/dashboard', 1, '2026-10-24 10:44:29'),
(52, 6, 'announcement', 'hello', 'hello', '/dashboard', 1, '2026-10-24 10:44:29'),
(53, 17, 'announcement', 'hello', 'hello', '/dashboard', 0, '2026-10-24 10:44:29'),
(54, 18, 'announcement', 'hello', 'hello', '/dashboard', 0, '2026-10-24 10:44:29'),
(55, 19, 'announcement', 'hello', 'hello', '/dashboard', 0, '2026-10-24 10:44:29'),
(56, 20, 'announcement', 'hello', 'hello', '/dashboard', 0, '2026-10-24 10:44:29'),
(57, 21, 'announcement', 'hello', 'hello', '/dashboard', 0, '2026-10-24 10:44:29'),
(58, 22, 'announcement', 'hello', 'hello', '/dashboard', 0, '2026-10-24 10:44:29'),
(59, 23, 'announcement', 'hello', 'hello', '/dashboard', 0, '2026-10-24 10:44:29'),
(60, 24, 'announcement', 'hello', 'hello', '/dashboard', 0, '2026-10-24 10:44:29'),
(61, 25, 'account.rejected', 'Your account was not approved', 'Please contact your school admin for more information.', NULL, 0, '2026-10-24 10:46:15'),
(62, 25, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now log in and access your dashboard.', '/dashboard', 0, '2026-10-24 10:46:22'),
(63, 20, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now log in and access your dashboard.', '/dashboard', 0, '2026-10-24 10:47:30'),
(64, 20, 'account.rejected', 'Your account was not approved', 'Please contact your school admin for more information.', NULL, 0, '2026-10-24 10:47:38'),
(65, 20, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now log in and access your dashboard.', '/dashboard', 0, '2026-10-24 10:47:45'),
(66, 26, 'account.approved', 'Your account was approved 🎉', 'Welcome to Infinity Learning! You can now log in and access your dashboard.', '/dashboard', 0, '2026-10-24 10:50:41');

-- --------------------------------------------------------

--
-- Table structure for table `otps`
--

CREATE TABLE `otps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `phone` varchar(20) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `purpose` enum('registration','password_reset','phone_change') NOT NULL,
  `attempts` tinyint(3) UNSIGNED DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `consumed_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otps`
--

INSERT INTO `otps` (`id`, `phone`, `otp_hash`, `purpose`, `attempts`, `expires_at`, `consumed_at`, `ip_address`, `created_at`) VALUES
(1, '7774441110', '$argon2id$v=19$m=65536,t=4,p=1$WXJTMTJoL2dJWC56NWpwcw$IDotjr19P7synIQTwsTtIPZzmZzK61iUOROfeleo1Zs', 'registration', 0, '2026-09-25 15:22:24', '2026-09-25 15:12:34', '::1', '2026-09-25 15:12:24'),
(2, '852369745', '$argon2id$v=19$m=65536,t=4,p=1$VHluZnZ6NnZ1N3Z4ZHY5NA$3Ntxw8kmC1bQLA3AHiyPt4D5+kwxUorB6S7Udt3Brik', 'registration', 0, '2026-09-25 15:26:36', '2026-09-25 15:17:03', '::1', '2026-09-25 15:16:36'),
(3, '+919876625417', '$argon2id$v=19$m=65536,t=4,p=1$LzZSbU1DVDM2ZC91dkdkVQ$URc39NR1jjmnLAeM1no+nePeIO50qiwRm1ToqW+FCO4', 'registration', 0, '2026-09-26 14:06:04', '2026-09-26 13:56:13', '::1', '2026-09-26 13:56:04'),
(4, '+917488485464', '$argon2id$v=19$m=65536,t=4,p=1$SWlMbGk3b2RoTHNEdzJFeQ$oU4sLMsn+0be06cy0vcVz+KOImysA0sbsb9NJoXgrBo', 'registration', 0, '2026-09-26 14:07:17', '2026-09-26 13:58:10', '::1', '2026-09-26 13:57:17'),
(5, '+917488485464', '$argon2id$v=19$m=65536,t=4,p=1$SVBtc1VROUpzSnBGMXlrRA$NfD2+rtZFGhpIedq6olIkn+wbMiaIx7WfhdAL8I7mdw', 'registration', 0, '2026-09-26 14:08:11', '2026-09-26 14:25:43', '::1', '2026-09-26 13:58:11'),
(6, '+917488485464', '$argon2id$v=19$m=65536,t=4,p=1$SGNHSXlDeHMxR2pOWk1tbQ$+KeEVUos4PIcLM2Juv/PZdbd8fMc24Y5LHErTaHwxjg', 'registration', 0, '2026-09-26 14:35:43', '2026-09-26 14:33:01', '127.0.0.1', '2026-09-26 14:25:43'),
(7, '+917488485464', '$argon2id$v=19$m=65536,t=4,p=1$aDFibkUzZU9LenpUQXhIMA$vJqEzWIf1cUr4Unatly1P94Qb0KDHiZLEiey0aV1DMs', 'registration', 0, '2026-09-26 14:43:01', '2026-10-24 09:13:27', '127.0.0.1', '2026-09-26 14:33:01'),
(8, '+919905893514', '$argon2id$v=19$m=65536,t=4,p=1$U0l5YlVRZDZhM2RUTnROYg$H0M43dwae3LKUymTE4lVppFTF8ViHPWSHnC3JhXZqyo', 'registration', 0, '2026-09-28 12:02:51', '2026-09-28 11:53:23', '::1', '2026-09-28 11:52:51'),
(9, '+917418525502', '$argon2id$v=19$m=65536,t=4,p=1$dXhhOFJnTnRzWExGdVR3Sg$0Ts5cdoLH6+7gA11Q8Jr1q/NP/qFxpONmM6OULgfFSg', 'registration', 0, '2026-09-28 12:43:55', NULL, '::1', '2026-09-28 12:33:55'),
(10, '+917061509292', '$argon2id$v=19$m=65536,t=4,p=1$UHhuUUswMnFaUkRzeGhDOA$UfTkZr1R/7kqszN3hOhdSCtH7sttL7b1wkHuOF7x34E', 'registration', 0, '2026-09-29 06:45:03', '2026-09-29 06:35:43', '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', '2026-09-29 06:35:03'),
(11, '+917899987878', '$argon2id$v=19$m=65536,t=4,p=1$WWkwTW5UMzJ2VlVsOXJqcQ$HnA6QXl+IsD1Z7U0wwXsCJ2v67qnv3g+rAQEmEMWT6Y', 'registration', 1, '2026-10-06 06:19:40', NULL, '2405:201:a41a:c882:a4cf:acae:67d6:746c', '2026-10-06 06:09:40'),
(12, '+919905839514', '$argon2id$v=19$m=65536,t=4,p=1$MFZVZHVtbFpzbzF1ekdXUw$oZtnLYYj6mcNDyAustvq7WY6xed47CCXie8RLhTskSM', 'registration', 0, '2026-10-21 07:16:36', '2026-10-21 07:07:21', '2405:201:a41a:c11b:7835:cf02:66d7:802b', '2026-10-21 07:06:36'),
(13, '+917488485464', '$argon2id$v=19$m=65536,t=4,p=1$dXdzUms0bEV3SDVpYXJDaQ$nHZhWAY9liWL9NDS13b3JOQbSW7ddHYkiglStYO5swc', 'registration', 0, '2026-10-24 09:23:28', '2026-10-24 09:14:07', '2405:201:a41a:c11b:f9fe:597:6b25:3f35', '2026-10-24 09:13:28'),
(14, '+917488485464', '$argon2id$v=19$m=65536,t=4,p=1$ckdRSmkycjVvNm1HenpqOA$oMp1BiapiilFS9gXIBqca2lihkdEXjXYKfxsEkxOnwM', 'registration', 0, '2026-10-24 09:24:07', NULL, '2405:201:a41a:c11b:f9fe:597:6b25:3f35', '2026-10-24 09:14:07'),
(15, '+919142682791', '$argon2id$v=19$m=65536,t=4,p=1$dVVXUVJQdnVLOU5nWlNPRw$hweMd55zSf8+bWLQTwX2Tgpxi/+fZtdPIRWZ3r6QqQg', 'registration', 0, '2026-10-24 09:33:35', '2026-10-24 09:24:19', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 09:23:35'),
(16, '+918765456789', '$argon2id$v=19$m=65536,t=4,p=1$a1JqVU9oYXhKWmpBQlM1ZQ$tvUpo36Vgop/pH7wn0WDoPVdWsnsEVNndQFJEx7nbQw', 'registration', 0, '2026-10-24 09:40:39', NULL, '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 09:30:39'),
(17, '+918363546985', '$argon2id$v=19$m=65536,t=4,p=1$bDZnNVZwT0g3dkFUbUNnSg$q5CzTVuitOLGGiOYMNURNxdCC/S4WUUwON2FYLgir8w', 'registration', 2, '2026-10-24 09:43:17', '2026-10-24 09:35:25', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 09:33:17'),
(18, '+918363546985', '$argon2id$v=19$m=65536,t=4,p=1$cENnNkY1Y1lJeHNPNW5SSw$XXamn14Fm+SATwvehcpVwiMPjfE3/Qq9cg7Xonysb+U', 'registration', 0, '2026-10-24 09:45:25', '2026-10-24 09:35:58', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 09:35:25'),
(19, '+919876543211', '$argon2id$v=19$m=65536,t=4,p=1$OUI0Z3NudTlvZDZkUndJMw$tsL4LihbeW/NgLbHP6ARMYf4heOFUAIrY4nv1yb2+GI', 'registration', 0, '2026-10-24 09:56:59', '2026-10-24 09:47:28', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 09:46:59'),
(20, '+919875635485', '$argon2id$v=19$m=65536,t=4,p=1$Vm1aYXYvSDIxbWh4Lk1IZQ$23TSFTT+B9Sxmu4eeUYTKk+NPbaHu4EMZpDj9G0zFFY', 'registration', 0, '2026-10-24 10:18:40', '2026-10-24 10:08:58', '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', '2026-10-24 10:08:40'),
(21, '+919876543230', '$argon2id$v=19$m=65536,t=4,p=1$Ui81OTk3aU80S0FJc2Y5MA$4cihbjcSc8PK2ybSqpAAMJxq/Fnq/j8YPdChWALghi0', 'registration', 0, '2026-10-24 10:23:48', '2026-10-24 10:14:32', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:13:48'),
(22, '+919876543234', '$argon2id$v=19$m=65536,t=4,p=1$Vm5vUU80RVAuNlgveVVOcg$B4Z2dyOWRQAQE1mUKnSyhzLnpBG/Jc4/AUryJg+iCnQ', 'registration', 0, '2026-10-24 10:32:35', '2026-10-24 10:24:01', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:22:35'),
(23, '+919876543234', '$argon2id$v=19$m=65536,t=4,p=1$YVlXbVVBanh5NXZEMWNLOA$1bkuVyqb9EypH0XkZDP9gjAFtGHJn1YiwmkguYbY7eA', 'registration', 0, '2026-10-24 10:34:02', '2026-10-24 10:24:36', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:24:02'),
(24, '+918765678904', '$argon2id$v=19$m=65536,t=4,p=1$WUZjSS9STEdZZjFicWZwYQ$qZo3IKFMqNEolcrEsdQb5QTpkt33YXL4QyQ0RMhDhdw', 'registration', 0, '2026-10-24 10:39:34', '2026-10-24 10:30:10', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:29:34'),
(25, '+917654323456', '$argon2id$v=19$m=65536,t=4,p=1$Vy9kVFdsS0Q4c0JRRnFlNg$pYtTZue5nYaj/g2UqWP+hsn/FdMWGL+NQI+qGgyC7pE', 'registration', 0, '2026-10-24 10:44:18', '2026-10-24 10:34:58', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:34:18'),
(26, '+919876567890', '$argon2id$v=19$m=65536,t=4,p=1$RnBHLmIxc3BWazE0Mkw5TA$W2D8+1M/s5M/UYCpFmZmkkG9vlKK9XTyHFGbe5gqZxg', 'registration', 0, '2026-10-24 10:54:54', '2026-10-24 10:45:39', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:44:54'),
(27, '+919087654321', '$argon2id$v=19$m=65536,t=4,p=1$eVBWck5SNWdFQmpJR002UQ$pfx0ff++GaMk3kevvFLgzashxqu1NwUQNLKIwNKzaXU', 'registration', 0, '2026-10-24 11:00:10', '2026-10-24 10:50:35', '2405:201:a41a:c11b:7569:447d:a50b:c0d4', '2026-10-24 10:50:10');

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `relationship` enum('father','mother','guardian','other') DEFAULT 'guardian',
  `occupation` varchar(150) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `parents`
--

INSERT INTO `parents` (`id`, `user_id`, `full_name`, `email`, `phone`, `relationship`, `occupation`, `city`, `state`, `created_at`, `updated_at`) VALUES
(1, NULL, 'asdf', 'as@test.com', '+917459823143', 'mother', NULL, NULL, 'qw', '2026-09-25 15:17:04', '2026-10-09 07:48:35'),
(2, NULL, 'Madhu Kumari', 'mk.raregeo@gmail.com', '+919431733088', 'mother', NULL, NULL, 'Jharkhand', '2026-09-28 11:53:23', '2026-10-24 09:19:15'),
(3, NULL, 'SIMA', 'qwe123@gmail.com', '+919876654340', 'mother', 'GHGHERYHRE', 'DHANBAD', 'jhrkhand', '2026-10-24 10:14:32', '2026-10-24 10:14:32'),
(4, NULL, 'rohan', 'dshg@gmail.com', '+919876789545', 'father', 'GHGHERYHRE', 'bokaro', 'jhrkhand', '2026-10-24 10:24:36', '2026-10-24 10:24:36'),
(5, NULL, 'sohan', 'sdfs@gmail.com', '+919876654340', 'father', 'sghjsdghjsg', 'bokaro', 'jhrkhand', '2026-10-24 10:30:10', '2026-10-24 10:30:10'),
(6, NULL, 'rohan', 'fsdg@gmail.com', '+918765456789', 'father', 'jhgeddjhghy', 'bokaro', 'jhrkhand', '2026-10-24 10:34:58', '2026-10-24 10:34:58'),
(7, NULL, 'rani', 'dgfj@gmail.com', '+919087665432', 'mother', 'duhgfjgd', 'DHANBAD', 'jhrkhand', '2026-10-24 10:45:39', '2026-10-24 10:45:39'),
(8, NULL, 'rohan', 'ghdc@gmail.com', '+919876789545', 'father', 'dcshgbfjkjdkj', 'bokaro', 'jhrkhand', '2026-10-24 10:50:35', '2026-10-24 10:50:35');

-- --------------------------------------------------------

--
-- Table structure for table `parent_email_log`
--

CREATE TABLE `parent_email_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `week_start` date NOT NULL,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('sent','failed') NOT NULL DEFAULT 'sent',
  `error_message` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `consumed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token_hash`, `expires_at`, `consumed_at`, `created_at`) VALUES
(2, 14, '2d122d5b118f12432bfea89c008314b21eff5fd826382f816b78ff672ab7fc8b', '2026-10-01 05:51:23', '2026-10-01 04:52:02', '2026-10-01 04:51:23'),
(4, 12, '80611d0b2efb5091b6bde1d5aa6627102c8ffd8a375b503e56607fae7bd10623', '2026-10-24 10:21:14', NULL, '2026-10-24 09:21:14');

-- --------------------------------------------------------

--
-- Table structure for table `question_bank`
--

CREATE TABLE `question_bank` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `teacher_user_id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `chapter_id` bigint(20) UNSIGNED DEFAULT NULL,
  `topic_id` bigint(20) UNSIGNED DEFAULT NULL,
  `class_level` varchar(50) DEFAULT NULL,
  `question_text` longtext NOT NULL,
  `question_type` enum('mcq','short_answer','descriptive','numerical','true_false') NOT NULL DEFAULT 'mcq',
  `options_json` longtext DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `correct_answer` text DEFAULT NULL,
  `explanation` text DEFAULT NULL,
  `marks` decimal(4,1) NOT NULL DEFAULT 1.0,
  `difficulty` enum('easy','medium','hard') NOT NULL DEFAULT 'medium',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `question_bank`
--

INSERT INTO `question_bank` (`id`, `uuid`, `teacher_user_id`, `school_id`, `subject_id`, `chapter_id`, `topic_id`, `class_level`, `question_text`, `question_type`, `options_json`, `image_url`, `correct_answer`, `explanation`, `marks`, `difficulty`, `created_at`, `updated_at`) VALUES
(1, 'a30c9d6b-1c00-41f1-b3f4-9dd5ee4b8dfc', 5, 3, 1, NULL, NULL, 'Class X', 'What is the primary formula or relationship governing Trignometric in Class X?', 'mcq', '[\"Direct linear proportionality\",\"Inverse square principle\",\"Equilibrium condition: Sum equals Zero\",\"Exponential progression\"]', NULL, 'Equilibrium condition: Sum equals Zero', 'Fundamental algebraic and geometric laws in Trignometric follow balanced conditions across the domain.', 1.0, 'medium', '2026-10-08 08:27:33', '2026-10-08 08:27:33'),
(2, '726a89f7-a832-4764-9e0a-f1f9162374cd', 5, 3, 1, NULL, NULL, 'Class X', 'If the core parameter of Trignometric is doubled, what happens to the resultant outcome?', 'mcq', '[\"Remains unchanged\",\"Doubles proportionally\",\"Quadruples (increases 4x)\",\"Reduces by half\"]', NULL, 'Quadruples (increases 4x)', 'Second-degree factors in Trignometric result in quadratic scaling when base values increase.', 1.0, 'medium', '2026-10-08 08:27:33', '2026-10-08 08:27:33'),
(3, '561164ac-c973-401a-9045-77febe4368bd', 5, 3, 1, NULL, NULL, 'Class X', 'Which of the following conditions is necessary for Trignometric to have a valid real solution?', 'mcq', '[\"Discriminant \\/ determinant \\u2265 0\",\"Denominator equals zero\",\"Value must be purely imaginary\",\"Constant term equals -1\"]', NULL, 'Discriminant / determinant ≥ 0', 'Non-negative discriminant ensures real, verified outputs in mathematical problem solving.', 1.0, 'medium', '2026-10-08 08:27:33', '2026-10-08 08:27:33'),
(6, '9c005add-b077-4102-9d1c-a6a7906dae7e', 5, 3, 2, NULL, NULL, 'Class VIII', 'Which of the following fundamental principles best describes the phenomenon of Force and pressure?', 'mcq', '[\"Conservation of Energy and Matter\",\"Spontaneous entropy destruction\",\"Infinite speed propagation\",\"Zero reaction momentum\"]', NULL, 'Conservation of Energy and Matter', 'Natural biological and physical processes in Force and pressure strictly observe standard conservation laws.', 1.0, 'medium', '2026-10-08 08:29:40', '2026-10-08 08:29:40'),
(7, '269a0040-6d11-4d52-96d9-256437cdea0f', 5, 3, 2, NULL, NULL, 'Class VIII', 'In an experimental observation of Force and pressure, which variable is typically kept constant as the control?', 'mcq', '[\"Temperature and ambient pressure\",\"Total elapsed time\",\"Dependent outcome variable\",\"Catalyst concentration\"]', NULL, 'Temperature and ambient pressure', 'Environmental controls ensure that measured changes in Force and pressure are strictly attributable to the independent variable.', 1.0, 'medium', '2026-10-08 08:29:40', '2026-10-08 08:29:40'),
(8, '5af300b9-7211-46b8-85b1-8bb64915b8eb', 5, 3, 2, NULL, NULL, 'Class VIII', 'What is the primary biological or physical unit directly involved in Force and pressure?', 'mcq', '[\"Cellular organelle \\/ molecular carrier\",\"Inert elemental vacuum\",\"Non-reactive boundary layer\",\"Unbounded electromagnetic pulse\"]', NULL, 'Cellular organelle / molecular carrier', 'Active physiological and physical mechanisms in Force and pressure rely on specialized carrier units.', 1.0, 'medium', '2026-10-08 08:29:40', '2026-10-08 08:29:40'),
(11, 'ec0bcb53-949e-4f05-8060-40909c9b3320', 5, 3, 1, NULL, NULL, 'Class X', 'What is the primary formula or relationship governing Real numbers in Class X?', 'mcq', '[\"Direct linear proportionality\",\"Inverse square principle\",\"Equilibrium condition: Sum equals Zero\",\"Exponential progression\"]', NULL, 'Equilibrium condition: Sum equals Zero', 'Fundamental algebraic and geometric laws in Real numbers follow balanced conditions across the domain.', 1.0, 'hard', '2026-10-08 08:31:54', '2026-10-08 08:31:54'),
(12, '0309c92b-32a3-42f6-ae77-c707b5672171', 5, 3, 1, NULL, NULL, 'Class X', 'If the core parameter of Real numbers is doubled, what happens to the resultant outcome?', 'mcq', '[\"Remains unchanged\",\"Doubles proportionally\",\"Quadruples (increases 4x)\",\"Reduces by half\"]', NULL, 'Quadruples (increases 4x)', 'Second-degree factors in Real numbers result in quadratic scaling when base values increase.', 1.0, 'hard', '2026-10-08 08:31:54', '2026-10-08 08:31:54'),
(13, 'faea9f68-4136-47a4-8b88-8ff6ab7df149', 5, 3, 1, NULL, NULL, 'Class X', 'Which of the following conditions is necessary for Real numbers to have a valid real solution?', 'mcq', '[\"Discriminant \\/ determinant \\u2265 0\",\"Denominator equals zero\",\"Value must be purely imaginary\",\"Constant term equals -1\"]', NULL, 'Discriminant / determinant ≥ 0', 'Non-negative discriminant ensures real, verified outputs in mathematical problem solving.', 1.0, 'hard', '2026-10-08 08:31:54', '2026-10-08 08:31:54'),
(15, '6205c433-9d1a-4993-b5aa-33e1bb9d8551', 5, 3, 1, NULL, NULL, 'Class X', 'Explain the working principles and significance of Real Numbers with neat illustrative reasoning. Provide relevant examples appropriate for Class X.', 'descriptive', NULL, NULL, 'Structured answer detailing: 1) Definition & Context (1M), 2) Working Mechanism & Diagram (2M), 3) Key Examples & Importance (2M).', 'Comprehensive answer covering conceptual understanding, step-by-step logic, and accurate examples.', 5.0, 'medium', '2026-10-08 09:15:56', '2026-10-08 09:15:56'),
(19, '296ba8e7-a52c-48c0-9d0f-7f90a5a2f61f', 5, 3, 3, NULL, NULL, 'Class X', 'According to Newton\'s Second Law of Motion, the formula for force F is given by:', 'mcq', '[\"(a) F = m \\u00d7 a\",\"(b) F = m \\/ a\",\"(c) F = 1\\/2 m v\\u00b2\",\"(d) F = m \\u00d7 g \\u00d7 h\"]', NULL, '(a) F = m × a', 'Force is equal to mass multiplied by acceleration (rate of change of momentum).', 1.0, 'medium', '2026-10-09 06:23:57', '2026-10-09 06:23:57'),
(23, '0d972e36-4c0b-4dca-b1a5-20eaeb5a8ae6', 5, 3, 3, NULL, NULL, 'Class X', 'A car of mass 1200 kg accelerates uniformly from an initial velocity of 10 m/s to 30 m/s in 8 seconds. Using equations of motion:\n(a) Calculate acceleration (a = [v - u] / t).\n(b) Calculate total distance traveled (s = ut + 1/2 at²).\n(c) Calculate the net accelerating force (F = m × a).', 'numerical', NULL, NULL, '(a) a = 2.5 m/s², (b) s = 160 m, (c) F = 3000 N', '(a) a = (30 - 10)/8 = 20/8 = 2.5 m/s². (b) s = 10(8) + 0.5(2.5)(64) = 80 + 80 = 160 m. (c) F = 1200 × 2.5 = 3000 N.', 4.0, 'medium', '2026-10-09 06:23:57', '2026-10-09 06:23:57'),
(24, 'b28845cc-d5a0-44ad-9b22-00f0bca1281c', 5, 3, 3, NULL, NULL, 'Class X', 'Balance the following chemical reaction equations and identify the reaction type:\n(i) Fe + H₂O → Fe₃O₄ + H₂\n(ii) Al + HCl → AlCl₃ + H₂\n(iii) C₃H₈ + O₂ → CO₂ + H₂O', 'numerical', NULL, NULL, '(i) 3Fe + 4H₂O → Fe₃O₄ + 4H₂, (ii) 2Al + 6HCl → 2AlCl₃ + 3H₂, (iii) C₃H₈ + 5O₂ → 3CO₂ + 4H₂O', 'Conservation of atoms on both sides balances stoichiometry.', 3.0, 'medium', '2026-10-09 06:23:57', '2026-10-09 06:23:57'),
(27, '056e2ae1-e41f-4e02-94fe-eba84891f2bd', 5, 3, 3, NULL, NULL, 'Class X', '[Diagram: Electric Circuit schematic showing a 12V DC Battery connected in series with a Switch and Ammeter (A), and two Resistors R1 = 6 Ω and R2 = 3 Ω connected in parallel with each other. A Voltmeter (V) is connected across the parallel combination.]\n\nBased on the circuit diagram:\n(a) Calculate the equivalent resistance (1/Rp = 1/R1 + 1/R2).\n(b) Find the total current measured by the Ammeter (I = V / Rp).\n(c) What is the reading on the Voltmeter?', 'descriptive', NULL, NULL, '(a) Rp = 2 Ω, (b) Current I = 6 A, (c) Voltmeter = 12 V', '(a) 1/Rp = 1/6 + 1/3 = 3/6 = 1/2 => Rp = 2 Ω. (b) I = V/Rp = 12/2 = 6 A. (c) Voltage across parallel branch is full battery potential = 12 V.', 5.0, 'medium', '2026-10-09 06:23:57', '2026-10-09 06:23:57'),
(28, '038de5a6-4136-45bb-a812-3121909563a7', 5, 3, 3, NULL, NULL, 'Class X', '[Diagram: Ray Diagram of a Concave Mirror with focal length f = 15 cm. An object AB of height 4 cm is placed perpendicularly on the principal axis at a distance u = 25 cm in front of the mirror.]\n\nUsing the mirror equation (1/f = 1/v + 1/u) and the ray diagram:\n(a) Calculate the position (v) of the formed image.\n(b) Calculate the magnification (m = -v/u) and the height of the image.\n(c) State whether the image is real or virtual, inverted or erect.', 'descriptive', NULL, NULL, '(a) v = -37.5 cm, (b) m = -1.5, height = -6 cm, (c) Real, inverted, and magnified', '1/v = 1/f - 1/u = -1/15 - (-1/25) = -1/15 + 1/25 = -2/75 => v = -37.5 cm. m = -(-37.5)/(-25) = -1.5. Height = -1.5 × 4 = -6 cm.', 5.0, 'medium', '2026-10-09 06:23:57', '2026-10-09 06:23:57'),
(29, 'e6c74696-14bd-421b-b6d2-96544fa28c26', 18, 3, 5, NULL, NULL, 'Class VIII', 'What is the fundamental governing principle of anything in Class VIII?', 'mcq', '[\"(a) Core Axiomatic Principle\",\"(b) Auxiliary Secondary Variable\",\"(c) Random Factor\",\"(d) Non-standard Outlier\"]', NULL, '(a) Core Axiomatic Principle', 'Curriculum frameworks ground anything in core foundational principles.', 1.0, 'hard', '2026-10-24 09:41:09', '2026-10-24 09:41:09'),
(30, '2ee17f49-9e9d-470f-88b7-83647047d251', 18, 3, 5, NULL, NULL, 'Class VIII', 'What is the fundamental governing principle of anything in Class VIII?', 'mcq', '[\"(a) Core Axiomatic Principle\",\"(b) Auxiliary Secondary Variable\",\"(c) Random Factor\",\"(d) Non-standard Outlier\"]', NULL, '(a) Core Axiomatic Principle', 'Curriculum frameworks ground anything in core foundational principles.', 1.0, 'hard', '2026-10-24 09:41:09', '2026-10-24 09:41:09'),
(31, 'a19a81d6-4740-4edc-912f-1f6bef25ee93', 18, 3, 5, NULL, NULL, 'Class VIII', 'What is the fundamental governing principle of anything in Class VIII?', 'mcq', '[\"(a) Core Axiomatic Principle\",\"(b) Auxiliary Secondary Variable\",\"(c) Random Factor\",\"(d) Non-standard Outlier\"]', NULL, '(a) Core Axiomatic Principle', 'Curriculum frameworks ground anything in core foundational principles.', 1.0, 'hard', '2026-10-24 09:41:09', '2026-10-24 09:41:09'),
(32, '1ca8604f-e1f8-4c50-bb80-168757005bf4', 18, 3, 5, NULL, NULL, 'Class VIII', 'What is the fundamental governing principle of anything in Class VIII?', 'mcq', '[\"(a) Core Axiomatic Principle\",\"(b) Auxiliary Secondary Variable\",\"(c) Random Factor\",\"(d) Non-standard Outlier\"]', NULL, '(a) Core Axiomatic Principle', 'Curriculum frameworks ground anything in core foundational principles.', 1.0, 'hard', '2026-10-24 09:41:09', '2026-10-24 09:41:09'),
(33, '0bdac8be-3096-4968-8002-96b4ea5fa2d1', 18, 3, 5, NULL, NULL, 'Class VIII', 'What is the fundamental governing principle of anything in Class VIII?', 'mcq', '[\"(a) Core Axiomatic Principle\",\"(b) Auxiliary Secondary Variable\",\"(c) Random Factor\",\"(d) Non-standard Outlier\"]', NULL, '(a) Core Axiomatic Principle', 'Curriculum frameworks ground anything in core foundational principles.', 1.0, 'hard', '2026-10-24 09:41:09', '2026-10-24 09:41:09'),
(34, 'c3d14f7d-8993-479e-b786-cec9c9759812', 18, 3, 5, NULL, NULL, 'Class VIII', 'What is the fundamental governing principle of anything in Class VIII?', 'mcq', '[\"(a) Core Axiomatic Principle\",\"(b) Auxiliary Secondary Variable\",\"(c) Random Factor\",\"(d) Non-standard Outlier\"]', NULL, '(a) Core Axiomatic Principle', 'Curriculum frameworks ground anything in core foundational principles.', 1.0, 'hard', '2026-10-24 09:41:09', '2026-10-24 09:41:09'),
(35, 'd5ade414-8154-4078-9db0-48e0d409b96d', 18, 3, 5, NULL, NULL, 'Class VIII', 'What is the fundamental governing principle of anything in Class VIII?', 'mcq', '[\"(a) Core Axiomatic Principle\",\"(b) Auxiliary Secondary Variable\",\"(c) Random Factor\",\"(d) Non-standard Outlier\"]', NULL, '(a) Core Axiomatic Principle', 'Curriculum frameworks ground anything in core foundational principles.', 1.0, 'hard', '2026-10-24 09:41:09', '2026-10-24 09:41:09'),
(36, '1ec48b41-df19-43d9-a030-ab3ed5ad9f9e', 18, 3, 5, NULL, NULL, 'Class VIII', 'What is the fundamental governing principle of anything in Class VIII?', 'mcq', '[\"(a) Core Axiomatic Principle\",\"(b) Auxiliary Secondary Variable\",\"(c) Random Factor\",\"(d) Non-standard Outlier\"]', NULL, '(a) Core Axiomatic Principle', 'Curriculum frameworks ground anything in core foundational principles.', 1.0, 'hard', '2026-10-24 09:41:09', '2026-10-24 09:41:09'),
(37, 'e37f24fc-86d2-409c-b134-de9c0d94d17c', 18, 3, 5, NULL, NULL, 'Class VIII', 'What is the fundamental governing principle of anything in Class VIII?', 'mcq', '[\"(a) Core Axiomatic Principle\",\"(b) Auxiliary Secondary Variable\",\"(c) Random Factor\",\"(d) Non-standard Outlier\"]', NULL, '(a) Core Axiomatic Principle', 'Curriculum frameworks ground anything in core foundational principles.', 1.0, 'hard', '2026-10-24 09:41:09', '2026-10-24 09:41:09'),
(38, 'b6524547-d890-4f1e-a7be-6e98014428fd', 18, 3, 5, NULL, NULL, 'Class VIII', 'What is the fundamental governing principle of anything in Class VIII?', 'mcq', '[\"(a) Core Axiomatic Principle\",\"(b) Auxiliary Secondary Variable\",\"(c) Random Factor\",\"(d) Non-standard Outlier\"]', NULL, '(a) Core Axiomatic Principle', 'Curriculum frameworks ground anything in core foundational principles.', 1.0, 'hard', '2026-10-24 09:41:09', '2026-10-24 09:41:09'),
(39, 'd5f43b30-ef89-4436-b34d-4fc4d9825342', 18, 3, 5, NULL, NULL, 'Class VIII', 'What is the fundamental governing principle of Adverb in Class VIII?', 'mcq', '[\"(a) Core Axiomatic Principle\",\"(b) Auxiliary Secondary Variable\",\"(c) Random Factor\",\"(d) Non-standard Outlier\"]', NULL, '(a) Core Axiomatic Principle', 'Curriculum frameworks ground Adverb in core foundational principles.', 1.0, 'hard', '2026-10-24 09:47:21', '2026-10-24 09:47:21'),
(40, 'aedd4970-7661-4dd7-8cf6-67ef5a9d34fc', 18, 3, 5, NULL, NULL, 'Class VIII', 'What is the fundamental governing principle of Adverb in Class VIII?', 'mcq', '[\"(a) Core Axiomatic Principle\",\"(b) Auxiliary Secondary Variable\",\"(c) Random Factor\",\"(d) Non-standard Outlier\"]', NULL, '(a) Core Axiomatic Principle', 'Curriculum frameworks ground Adverb in core foundational principles.', 1.0, 'hard', '2026-10-24 09:47:21', '2026-10-24 09:47:21'),
(41, '28ad9c18-2b3a-478a-b6ad-749955f4d601', 18, 3, 5, NULL, NULL, 'Class VIII', 'What is the fundamental governing principle of Adverb in Class VIII?', 'mcq', '[\"(a) Core Axiomatic Principle\",\"(b) Auxiliary Secondary Variable\",\"(c) Random Factor\",\"(d) Non-standard Outlier\"]', NULL, '(a) Core Axiomatic Principle', 'Curriculum frameworks ground Adverb in core foundational principles.', 1.0, 'hard', '2026-10-24 09:47:21', '2026-10-24 09:47:21'),
(42, '3f75367d-9d3f-4b90-b379-29924f08e699', 18, 3, 5, NULL, NULL, 'Class VIII', 'What is the fundamental governing principle of Adverb in Class VIII?', 'mcq', '[\"(a) Core Axiomatic Principle\",\"(b) Auxiliary Secondary Variable\",\"(c) Random Factor\",\"(d) Non-standard Outlier\"]', NULL, '(a) Core Axiomatic Principle', 'Curriculum frameworks ground Adverb in core foundational principles.', 1.0, 'hard', '2026-10-24 09:47:21', '2026-10-24 09:47:21'),
(43, 'b6a2f6b3-75d9-4e6c-87bb-b52658bda600', 18, 3, 5, NULL, NULL, 'Class VIII', 'What is the fundamental governing principle of Adverb in Class VIII?', 'mcq', '[\"(a) Core Axiomatic Principle\",\"(b) Auxiliary Secondary Variable\",\"(c) Random Factor\",\"(d) Non-standard Outlier\"]', NULL, '(a) Core Axiomatic Principle', 'Curriculum frameworks ground Adverb in core foundational principles.', 1.0, 'hard', '2026-10-24 09:47:21', '2026-10-24 09:47:21'),
(44, 'abad3e45-e3c8-4c2d-921a-e45cbd403555', 18, 3, 5, NULL, NULL, 'Class VIII', 'What is the fundamental governing principle of Adverb in Class VIII?', 'mcq', '[\"(a) Core Axiomatic Principle\",\"(b) Auxiliary Secondary Variable\",\"(c) Random Factor\",\"(d) Non-standard Outlier\"]', NULL, '(a) Core Axiomatic Principle', 'Curriculum frameworks ground Adverb in core foundational principles.', 1.0, 'hard', '2026-10-24 09:47:21', '2026-10-24 09:47:21'),
(45, 'bdb3dd72-c0c0-43b9-861a-ce731de44497', 18, 3, 5, NULL, NULL, 'Class VIII', 'What is the fundamental governing principle of Adverb in Class VIII?', 'mcq', '[\"(a) Core Axiomatic Principle\",\"(b) Auxiliary Secondary Variable\",\"(c) Random Factor\",\"(d) Non-standard Outlier\"]', NULL, '(a) Core Axiomatic Principle', 'Curriculum frameworks ground Adverb in core foundational principles.', 1.0, 'hard', '2026-10-24 09:47:21', '2026-10-24 09:47:21'),
(46, 'a6c0fabf-8db6-495b-8853-1d8259f52c86', 18, 3, 5, NULL, NULL, 'Class VIII', 'What is the fundamental governing principle of Adverb in Class VIII?', 'mcq', '[\"(a) Core Axiomatic Principle\",\"(b) Auxiliary Secondary Variable\",\"(c) Random Factor\",\"(d) Non-standard Outlier\"]', NULL, '(a) Core Axiomatic Principle', 'Curriculum frameworks ground Adverb in core foundational principles.', 1.0, 'hard', '2026-10-24 09:47:21', '2026-10-24 09:47:21'),
(47, '2616b533-61cc-470f-ac4b-7408ce62fff3', 18, 3, 5, NULL, NULL, 'Class VIII', 'What is the fundamental governing principle of Adverb in Class VIII?', 'mcq', '[\"(a) Core Axiomatic Principle\",\"(b) Auxiliary Secondary Variable\",\"(c) Random Factor\",\"(d) Non-standard Outlier\"]', NULL, '(a) Core Axiomatic Principle', 'Curriculum frameworks ground Adverb in core foundational principles.', 1.0, 'hard', '2026-10-24 09:47:21', '2026-10-24 09:47:21'),
(48, 'e3dafd8d-9e50-45a4-b9e0-cb9fb68549fd', 18, 3, 5, NULL, NULL, 'Class VIII', 'What is the fundamental governing principle of Adverb in Class VIII?', 'mcq', '[\"(a) Core Axiomatic Principle\",\"(b) Auxiliary Secondary Variable\",\"(c) Random Factor\",\"(d) Non-standard Outlier\"]', NULL, '(a) Core Axiomatic Principle', 'Curriculum frameworks ground Adverb in core foundational principles.', 1.0, 'hard', '2026-10-24 09:47:21', '2026-10-24 09:47:21');

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `identifier` varchar(150) NOT NULL,
  `action` varchar(50) NOT NULL,
  `attempts` int(10) UNSIGNED DEFAULT 0,
  `window_start` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `identifier`, `action`, `attempts`, `window_start`) VALUES
(6, '::1', 'register', 4, '2026-09-28 11:52:29'),
(7, '7774441110', 'otp_verify', 1, '2026-09-25 15:12:34'),
(11, '852369745', 'otp_verify', 1, '2026-09-25 15:17:03'),
(17, 'teststu@gmail.com|::1', 'login', 1, '2026-09-25 17:29:58'),
(23, 'admin@infinityearning.net|::1', 'login', 3, '2026-09-25 19:13:51'),
(29, 'testteac@gmail.com|::1', 'login', 1, '2026-09-25 19:33:12'),
(33, 'testteacher@test.com|::1', 'login', 1, '2026-09-25 20:10:38'),
(35, 'admin@infinitlearning.net|::1', 'login', 1, '2026-09-26 13:38:54'),
(43, '+919876625417', 'otp_verify', 1, '2026-09-26 13:56:13'),
(44, 'testteacher1@dav.com|::1', 'login', 1, '2026-09-26 13:56:28'),
(48, '127.0.0.1', 'register', 2, '2026-09-26 14:25:43'),
(52, '+919905893514', 'otp_verify', 1, '2026-09-28 11:53:22'),
(60, 'stutest@gmail.com|::1', 'login', 2, '2026-09-28 12:27:46'),
(69, 'testteacher@gmai.com|::1', 'login', 1, '2026-09-28 12:53:20'),
(77, 'testschool1@test.com|2405:201:a41a:c882:d9ec:9d5c:bf10:d26f', 'login', 5, '2026-09-28 11:33:33'),
(81, '2405:201:a41a:c882:f9e1:1c76:2a25:87f9', 'register', 5, '2026-09-29 06:35:03'),
(82, '+917061509292', 'otp_verify', 1, '2026-09-29 06:35:43'),
(89, 'admin@infinitylearning.ner|2405:201:a41a:c882:764c:4c7f:196c:cbff', 'login', 1, '2026-09-29 08:02:14'),
(93, 'testteacheerr@gmail.com|2405:201:a41a:c882:9e56:dbaa:c7db:7762', 'login', 1, '2026-09-29 10:12:09'),
(94, 'testteacheer@gmail.com|2405:201:a41a:c882:9e56:dbaa:c7db:7762', 'login', 1, '2026-09-29 10:12:22'),
(116, '2405:201:a41a:c882:2116:7ccc:8c66:e7b3', 'forgot', 2, '2026-10-01 04:43:32'),
(134, 'testschool@gmail.com|2405:201:a41a:c882:a4cf:acae:67d6:746c', 'login', 1, '2026-10-06 06:01:43'),
(135, 'testschool1@gmail.com|2405:201:a41a:c882:a4cf:acae:67d6:746c', 'login', 1, '2026-10-06 06:01:49'),
(137, '2405:201:a41a:c882:a4cf:acae:67d6:746c', 'register', 1, '2026-10-06 06:09:40'),
(138, '+917899987878', 'otp_verify', 1, '2026-10-06 06:11:46'),
(140, 'admin@infintylearning.net|2405:201:a41a:c882:8695:9db9:dec3:d773', 'login', 1, '2026-10-06 07:56:58'),
(193, 'gdorozio@hotmail.com|2001:569:7d71:1c00:f8d8:943:3e24:2e94', 'login', 3, '2026-10-15 19:32:24'),
(207, '2405:201:a41a:c11b:7835:cf02:66d7:802b', 'register', 1, '2026-10-21 07:06:35'),
(208, '+919905839514', 'otp_verify', 1, '2026-10-21 07:07:21'),
(243, '2405:201:a41a:c11b:f9fe:597:6b25:3f35', 'register', 5, '2026-10-24 09:11:51'),
(244, '2405:201:a41a:c11b:7569:447d:a50b:c0d4', 'register', 5, '2026-10-24 10:22:35'),
(245, '2405:201:a41a:c11b:7569:447d:a50b:c0d4', 'forgot', 2, '2026-10-24 09:16:20'),
(248, 'mamtapathak051@gmail.com|2405:201:a41a:c11b:7569:447d:a50b:c0d4', 'login', 2, '2026-10-24 09:18:54'),
(254, '2405:201:a41a:c11b:78ab:55ad:8c6:7daa', 'register', 1, '2026-10-24 11:05:25'),
(255, '+919142682791', 'otp_verify', 1, '2026-10-24 09:24:19'),
(257, '+918363546985', 'otp_verify', 3, '2026-10-24 09:33:42'),
(258, 'nishant012543@gamil.com|2405:201:a41a:c11b:78ab:55ad:8c6:7daa', 'login', 1, '2026-10-24 09:34:46'),
(265, '+919876543211', 'otp_verify', 1, '2026-10-24 09:47:28'),
(269, '+919875635485', 'otp_verify', 1, '2026-10-24 10:08:58'),
(271, '+919876543230', 'otp_verify', 1, '2026-10-24 10:14:31'),
(274, '+919876543234', 'otp_resend', 1, '2026-10-24 10:24:01'),
(275, '+919876543234', 'otp_verify', 1, '2026-10-24 10:24:36'),
(277, '+918765678904', 'otp_verify', 1, '2026-10-24 10:30:10'),
(278, '+917654323456', 'otp_verify', 1, '2026-10-24 10:34:58'),
(281, '+919876567890', 'otp_verify', 1, '2026-10-24 10:45:39'),
(283, '+919087654321', 'otp_verify', 1, '2026-10-24 10:50:35');

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `principal_name` varchar(150) DEFAULT NULL,
  `board` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `status` enum('active','archived') DEFAULT 'active',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `admin_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `student_capacity` int(10) UNSIGNED DEFAULT 500,
  `teacher_capacity` int(10) UNSIGNED DEFAULT 100,
  `approved_at` datetime DEFAULT NULL,
  `brand_primary_color` varchar(20) DEFAULT '#0A192F',
  `brand_secondary_color` varchar(20) DEFAULT '#BE9420',
  `brand_tagline` varchar(255) DEFAULT NULL,
  `crest_logo` varchar(500) DEFAULT NULL,
  `principal_signature` varchar(500) DEFAULT NULL,
  `official_seal` varchar(500) DEFAULT NULL,
  `bell_schedule_json` longtext DEFAULT NULL,
  `custom_classes_json` longtext DEFAULT NULL,
  `custom_sections_json` longtext DEFAULT NULL,
  `allow_weekly_summary` tinyint(1) NOT NULL DEFAULT 0,
  `weekly_summary_enabled` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`id`, `uuid`, `code`, `name`, `principal_name`, `board`, `city`, `state`, `address`, `contact_email`, `contact_phone`, `phone`, `website`, `logo`, `status`, `created_by`, `admin_user_id`, `created_at`, `updated_at`, `student_capacity`, `teacher_capacity`, `approved_at`, `brand_primary_color`, `brand_secondary_color`, `brand_tagline`, `crest_logo`, `principal_signature`, `official_seal`, `bell_schedule_json`, `custom_classes_json`, `custom_sections_json`, `allow_weekly_summary`, `weekly_summary_enabled`) VALUES
(3, '33c27435-d519-4644-b2ef-ecf0d2a2f8c7', 'TS-F59C', 'Onboarding School', 'TEST#', 'CBSE', 'RANCHI', 'Jharhand', '505, Mahabir Tower', 'test@school.com', '749863520', '741852960', NULL, NULL, 'active', 1, 4, '2026-09-25 15:10:42', '2026-10-26 14:43:55', 500, 100, '2026-09-25 15:10:43', '#2a2323', '#091b20', NULL, '/uploads/branding/1ea547f6ccdf6051_1793006035.webp', '/uploads/branding/adc6d3757ed68f59_1793006035.webp', '/uploads/branding/dcef0c8e6ce28ab3_1793006035.webp', '[{\"period_number\":1,\"label\":\"Period 1\",\"start\":\"08:30:00\",\"end\":\"09:15:00\",\"is_break\":0},{\"period_number\":2,\"label\":\"Period 2\",\"start\":\"09:15:00\",\"end\":\"10:00:00\",\"is_break\":0},{\"period_number\":3,\"label\":\"Period 3\",\"start\":\"10:15:00\",\"end\":\"11:00:00\",\"is_break\":0},{\"period_number\":4,\"label\":\"Period 4\",\"start\":\"11:00:00\",\"end\":\"11:45:00\",\"is_break\":0},{\"period_number\":5,\"label\":\"Lunch Break\",\"start\":\"12:15:00\",\"end\":\"13:00:00\",\"is_break\":1},{\"period_number\":6,\"label\":\"Period 6\",\"start\":\"13:00:00\",\"end\":\"13:45:00\",\"is_break\":0},{\"period_number\":7,\"label\":\"Period 7\",\"start\":\"13:45:00\",\"end\":\"14:30:00\",\"is_break\":0},{\"period_number\":8,\"label\":\"Period 8\",\"start\":\"14:30:00\",\"end\":\"15:15:00\",\"is_break\":0}]', NULL, NULL, 0, 1),
(9, 'bbc410aa-4a29-4437-91e3-de61c282b5d5', 'A-176B', 'AIVERSE', 'RAVI SHARMA', 'CBSE', 'RANCHI', 'JHARKHAND', 'MAHABIR TOWER', 'india.futurenxt@gmail.com', '9801506511', '9099937500', NULL, NULL, 'active', 1, 14, '2026-09-29 10:58:45', '2026-10-23 06:47:10', 500, 100, '2026-09-29 10:58:45', '#0a192f', '#be9420', NULL, '/uploads/branding/094d04b8dd17512e_1792738030.webp', NULL, NULL, NULL, NULL, NULL, 0, 1),
(10, 'f7d84683-6f37-43f3-9c02-cc5fa840c51e', 'IODT-A3CC', 'Navjeevan Ambedker Mission', 'Rakshit Sharma', 'Other', 'Ranchi', 'Jharkhand', '2nd Floor, Above Urban Lights, SKY LINE Complex, Kadru, Ranchi Jharkhand 834002', 'info@instituteofdigitaltraining.com', '+91 62041 25297', '9801506511', NULL, NULL, 'active', 1, 15, '2026-10-12 08:40:22', '2026-10-15 07:13:06', 5000, 1000, '2026-10-12 08:40:23', '#0a192f', '#be9420', 'Future Skills • Digital Careers • Trainer Development Become a Future-Ready Digital Skills Trainer', '/uploads/branding/ed1f151441197542_1792048386.webp', NULL, NULL, NULL, NULL, NULL, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` char(64) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `last_activity` datetime NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') DEFAULT NULL,
  `age` tinyint(3) UNSIGNED DEFAULT NULL,
  `school_name` varchar(200) DEFAULT NULL,
  `school_board` varchar(100) DEFAULT NULL,
  `current_class` varchar(50) DEFAULT NULL,
  `registration_number` varchar(100) DEFAULT NULL,
  `roll_number` varchar(50) DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `interests` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `district` varchar(100) DEFAULT NULL,
  `pincode` varchar(20) DEFAULT NULL,
  `mentor_user_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `full_name`, `profile_image`, `gender`, `age`, `school_name`, `school_board`, `current_class`, `registration_number`, `roll_number`, `academic_year`, `city`, `state`, `interests`, `created_at`, `updated_at`, `district`, `pincode`, `mentor_user_id`) VALUES
(1, 6, 'STUTEST1', NULL, 'female', 10, NULL, 'CBSE', '6 A', NULL, NULL, '2026', 'qw', 'qw', 'maths', '2026-09-25 15:17:04', '2026-10-09 07:48:35', NULL, NULL, NULL),
(2, 11, 'Aviraj', NULL, 'male', 18, 'Onboarding School', 'ICSE', '12', NULL, NULL, '2025-2026', 'Ranchi', 'Jharkhand', 'Maths', '2026-09-28 11:53:23', '2026-10-24 09:19:15', NULL, NULL, NULL),
(3, 16, 'Aviraj', NULL, NULL, NULL, 'AIVERSE', 'AI Academy', 'AIverse Cohort 2026', NULL, NULL, '2026-2027', 'Ranchi', 'Jharkhand', 'Artificial Intelligence, Machine Learning', '2026-10-21 07:07:22', '2026-10-21 07:07:22', 'Ranchi', '834002', NULL),
(4, 21, 'anu', NULL, 'female', 16, NULL, 'CBSE', '1OTH', NULL, NULL, '2026', 'ranchi', 'jhrkhand', 'MATH', '2026-10-24 10:14:32', '2026-10-24 10:14:32', 'Ranchi', '56777', NULL),
(5, 22, 'simmi', NULL, 'female', 17, NULL, 'CBSE', '6th', NULL, NULL, '2026', 'ranchi', 'jhrkhand', 'Physics', '2026-10-24 10:24:36', '2026-10-24 10:24:36', 'Ranchi', '542712', NULL),
(6, 23, 'rohan', NULL, 'male', 15, NULL, 'CBSE', '6th', NULL, NULL, '2026', 'bokaro', 'jhrkhand', 'MATH', '2026-10-24 10:30:10', '2026-10-24 10:30:10', 'bokaro', '234002', NULL),
(7, 24, 'sohan', NULL, 'male', 15, NULL, 'ICSE', '6th', NULL, NULL, '2026', 'bokaro', 'jhrkhand', 'science', '2026-10-24 10:34:58', '2026-10-24 10:34:58', 'bokaro', '20034', NULL),
(8, 25, 'ranu mandle', NULL, 'female', 16, NULL, 'CBSE', '6th', NULL, NULL, '2026', 'dhanbad', 'jhrkhand', 'science', '2026-10-24 10:45:39', '2026-10-24 10:45:39', 'dhanbad', '826004', NULL),
(9, 26, 'komal', NULL, 'female', 15, NULL, 'CBSE', '6th', NULL, NULL, '2026', 'dhanbad', 'jhrkhand', 'science', '2026-10-24 10:50:35', '2026-10-24 10:50:35', 'dhanbad', '29902', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_fees`
--

CREATE TABLE `student_fees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `academic_year` varchar(20) NOT NULL DEFAULT '2026-2027',
  `fee_month` varchar(20) NOT NULL,
  `fee_type` enum('tuition','admission','transport','exam','chess_academy','activity') NOT NULL DEFAULT 'tuition',
  `amount_due` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `due_date` date DEFAULT NULL,
  `status` enum('pending','partial','paid','overdue','waived') NOT NULL DEFAULT 'pending',
  `payment_mode` enum('upi','cash','bank_transfer','card','cheque') DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `recorded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_fees`
--

INSERT INTO `student_fees` (`id`, `school_id`, `batch_id`, `student_user_id`, `academic_year`, `fee_month`, `fee_type`, `amount_due`, `amount_paid`, `discount`, `due_date`, `status`, `payment_mode`, `receipt_number`, `paid_at`, `recorded_by`, `notes`, `created_at`, `updated_at`) VALUES
(1, 3, NULL, 6, '2026-2027', 'October', 'tuition', 2500.00, 2500.00, 0.00, '2026-11-10', 'paid', 'upi', 'REC-A7D45B', '2026-10-07 08:02:18', 4, '\n', '2026-10-07 08:02:09', '2026-10-07 08:02:18'),
(2, 9, NULL, 16, '2026-2027', 'October 2026', 'activity', 12000.00, 6000.00, 0.00, '2026-11-20', 'partial', 'upi', 'AIV-REC-427360', '2026-10-21 07:07:22', 1, 'AIverse Program: Generative AI & Prompt Engineering Masterclass', '2026-10-21 07:07:22', '2026-10-21 07:07:22');

-- --------------------------------------------------------

--
-- Table structure for table `student_knowledge_profile`
--

CREATE TABLE `student_knowledge_profile` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `topic_id` bigint(20) UNSIGNED DEFAULT NULL,
  `mastery_percent` tinyint(3) UNSIGNED DEFAULT 0,
  `strength_level` enum('weak','developing','strong','mastered') DEFAULT 'developing',
  `last_evaluated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_parents`
--

CREATE TABLE `student_parents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_parents`
--

INSERT INTO `student_parents` (`id`, `student_user_id`, `parent_id`, `is_primary`, `created_at`) VALUES
(1, 6, 1, 1, '2026-09-25 15:17:04'),
(2, 11, 2, 1, '2026-09-28 11:53:23'),
(3, 21, 3, 1, '2026-10-24 10:14:32'),
(4, 22, 4, 1, '2026-10-24 10:24:36'),
(5, 23, 5, 1, '2026-10-24 10:30:10'),
(6, 24, 6, 1, '2026-10-24 10:34:58'),
(7, 25, 7, 1, '2026-10-24 10:45:39'),
(8, 26, 8, 1, '2026-10-24 10:50:35');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `icon` varchar(60) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `slug`, `icon`, `color`, `description`, `created_at`) VALUES
(1, 'Mathematics', 'mathematics', '➗', '#FF7A18', NULL, '2026-09-24 12:19:47'),
(2, 'Physics', 'physics', '⚛️', '#2563EB', NULL, '2026-09-24 12:19:47'),
(3, 'Chemistry', 'chemistry', '🧪', '#16A34A', NULL, '2026-09-24 12:19:47'),
(4, 'Biology', 'biology', '🧬', '#10B981', NULL, '2026-09-24 12:19:47'),
(5, 'English', 'english', '📚', '#3B82F6', NULL, '2026-09-24 12:19:47'),
(6, 'Hindi', 'hindi', '🕉️', '#F97316', NULL, '2026-09-24 12:19:47'),
(7, 'Sanskrit', 'sanskrit', '📜', '#D97706', NULL, '2026-09-24 12:19:47'),
(8, 'Computer Science', 'computer-science', '💻', '#EC4899', NULL, '2026-09-24 12:19:47'),
(9, 'Social Science', 'social-science', '🌍', '#A855F7', NULL, '2026-09-24 12:19:47'),
(10, 'AI & Innovation', 'ai-innovation', '🤖', '#F59E0B', NULL, '2026-09-24 12:19:47');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_code` varchar(50) DEFAULT NULL,
  `registration_number` varchar(100) DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') DEFAULT NULL,
  `school_name` varchar(200) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `expert_field` varchar(150) DEFAULT NULL,
  `years_experience` tinyint(3) UNSIGNED DEFAULT NULL,
  `additional_expertise` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `class_teacher_grade` varchar(50) DEFAULT NULL,
  `class_teacher_section` varchar(20) DEFAULT NULL,
  `signature_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `teacher_code`, `registration_number`, `full_name`, `profile_image`, `gender`, `school_name`, `city`, `state`, `expert_field`, `years_experience`, `additional_expertise`, `bio`, `approved_by`, `approved_at`, `created_at`, `updated_at`, `class_teacher_grade`, `class_teacher_section`, `signature_image`) VALUES
(1, 5, 'O62687', NULL, 'TEST techaer 1', NULL, 'male', NULL, 'ranchi', 'jharkhand', 'science', 2, NULL, NULL, 4, '2026-09-25 15:12:57', '2026-09-25 15:12:35', '2026-10-26 14:49:38', NULL, NULL, NULL),
(2, 10, NULL, NULL, 'Test Teacher 1', NULL, 'female', NULL, 'Ranchi', 'Jharkhand', 'History', 30, 'Civics', 'HI', 8, '2026-09-26 13:57:22', '2026-09-26 13:56:13', '2026-09-26 13:57:22', NULL, NULL, NULL),
(3, 12, NULL, NULL, 'Mamta kumari', NULL, 'female', NULL, 'Dhanbad', 'Jharkhand', 'Computer', 2, 'Science', NULL, 7, '2026-09-29 06:37:26', '2026-09-29 06:35:43', '2026-09-29 06:37:26', NULL, NULL, NULL),
(4, 17, 'O82683', NULL, 'Sachin Tiwari', NULL, NULL, NULL, 'amb', 'Bihar', 'MAth', 10, 'sdblj', 'dbliuas', 4, '2026-10-24 09:25:05', '2026-10-24 09:24:19', '2026-10-26 14:49:38', NULL, NULL, NULL),
(5, 18, 'O19796', NULL, 'Nishant', NULL, NULL, NULL, 'hbsd', 'bihar', 'English', 10, 'sst', 'jhsgcyuas', 4, '2026-10-24 09:36:10', '2026-10-24 09:35:58', '2026-10-26 14:49:38', NULL, NULL, NULL),
(6, 19, 'O37349', NULL, 'avi', NULL, NULL, NULL, 'ranchi', 'jhrkhand', 'Math', 2, 'hindi', NULL, 4, '2026-10-24 09:48:09', '2026-10-24 09:47:28', '2026-10-26 14:49:38', NULL, NULL, NULL),
(7, 20, 'O73209', NULL, 'Rahul Gandhi', NULL, NULL, NULL, 'jsd', 'Jharkhand', 'SST', 10, 'g', 'fm,nafjar', 4, '2026-10-24 10:47:45', '2026-10-24 10:08:58', '2026-10-26 14:49:38', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `timetables`
--

CREATE TABLE `timetables` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `class_level` varchar(50) NOT NULL,
  `section` varchar(10) DEFAULT 'A',
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday') NOT NULL,
  `period_number` tinyint(3) UNSIGNED NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_user_id` bigint(20) UNSIGNED NOT NULL,
  `room_number` varchar(30) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `timetables`
--

INSERT INTO `timetables` (`id`, `school_id`, `class_level`, `section`, `day_of_week`, `period_number`, `start_time`, `end_time`, `subject_id`, `teacher_user_id`, `room_number`, `created_at`) VALUES
(4, 3, '6', 'A', 'thursday', 6, '13:00:00', '13:45:00', 10, 5, '', '2026-10-08 06:34:01'),
(7, 3, '6 A', 'A', 'monday', 2, '09:15:00', '10:00:00', 10, 5, '', '2026-10-24 09:11:14'),
(8, 3, '6 A', 'A', 'wednesday', 4, '11:00:00', '11:45:00', 5, 5, '', '2026-10-24 09:12:38'),
(9, 3, '6 A', 'A', 'tuesday', 3, '10:15:00', '11:00:00', 10, 5, '', '2026-10-24 09:13:02'),
(10, 3, '6 A', 'A', 'friday', 7, '13:45:00', '14:30:00', 1, 5, '', '2026-10-24 09:17:15'),
(11, 3, '6 A', 'A', 'monday', 3, '10:15:00', '11:00:00', 1, 17, '', '2026-10-24 09:28:05'),
(16, 3, '6 A', 'A', 'monday', 1, '08:30:00', '09:15:00', 10, 19, '', '2026-10-24 09:52:29'),
(17, 3, '6 A', 'A', 'saturday', 8, '14:30:00', '15:15:00', 10, 18, '', '2026-10-24 09:52:47'),
(18, 3, 'Grade 12', 'E', 'monday', 4, '11:00:00', '11:45:00', 10, 19, '', '2026-10-24 09:55:23'),
(19, 3, '6 A', 'E', 'saturday', 1, '08:30:00', '09:15:00', 1, 19, '', '2026-10-24 10:00:08'),
(20, 3, '6 A', 'E', 'saturday', 3, '10:15:00', '11:00:00', 10, 18, '', '2026-10-24 10:00:22');

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

CREATE TABLE `topics` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `chapter_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(10) UNSIGNED DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('student','teacher','admin') NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending','active','suspended','rejected') DEFAULT 'pending',
  `phone_verified_at` datetime DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `uuid`, `email`, `phone`, `password_hash`, `role`, `school_id`, `status`, `phone_verified_at`, `email_verified_at`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, '4291aeef-6976-48dd-9440-c4737143e415', 'admin@infinitylearning.net', '+919523176706', '$argon2id$v=19$m=65536,t=4,p=1$MDhvMDNRLmFDeUdlQVhaQg$QRS1Ay3K59/ZIwR3Ta6InDM98hhDRDrqi7m+aJaaDaM', 'admin', NULL, 'active', '2026-09-24 12:27:47', '2026-09-24 12:27:47', '2026-10-24 09:46:58', '2026-09-24 12:27:47', '2026-10-24 09:46:58'),
(2, 'b3d1dcae-81c2-4789-b54b-1fdb7ec606b5', 'admin@test.com', '7418529630', '$argon2id$v=19$m=65536,t=4,p=1$djJVSnpxaldIcGtVeWEwZw$s95WBErWujg81WVJYf0Qju74eDHaiGS7l2AI2EaQgGY', 'admin', 1, 'active', '2026-09-25 14:59:33', '2026-09-25 14:59:33', '2026-09-25 15:00:01', '2026-09-25 14:59:33', '2026-09-25 15:00:01'),
(4, 'de215c01-7d3a-4298-b452-670bc6fdc355', 'testschool1@test.com', '7894623102', '$argon2id$v=19$m=65536,t=4,p=1$Z0RuUWFKUHZFU1h1UkZQZw$TSVW0NS6/gp25VlaWyaZXRFQKLBwgwCU5OMor6jsD2g', 'admin', 3, 'active', '2026-09-25 15:10:43', '2026-09-25 15:10:43', '2026-10-27 12:56:39', '2026-09-25 15:10:43', '2026-10-27 12:56:39'),
(5, '3ac142ea-234d-4669-a2a9-36d83c761ce5', 'testteacher@gmail.com', '7774441110', '$argon2id$v=19$m=65536,t=4,p=1$L1FsaXRzRm5FRnNKeGhJeA$nNEaMZSuG8tJ2YhcKLC5Av8yEiXxq21JfxBniJRgYi4', 'teacher', 3, 'active', '2026-09-25 15:12:35', NULL, '2026-10-27 13:02:10', '2026-09-25 15:12:35', '2026-10-27 13:02:10'),
(6, '33d536c8-ecf0-4f2f-a8a3-07436fe54ee1', 'stu@gmail.com', '852369745', '$argon2id$v=19$m=65536,t=4,p=1$QW5xZThTMC85RmR6VS5FNw$QQGDoOk4356q7Vy1ErStt1hdtVnZDACNQkvHSBr0vzE', 'student', 3, 'active', '2026-09-25 15:17:04', NULL, '2026-10-27 16:15:40', '2026-09-25 15:17:04', '2026-10-27 16:15:40'),
(7, '3c0f6f9a-e95b-4c12-a08b-fdfbd73ed9ee', 'adminisl@isl.com', '7988546123', '$argon2id$v=19$m=65536,t=4,p=1$ZG9VU1cyOUh2RExVYXNQLg$hwPtlzgehR2eP9T6YnFJg9yWdfXbQB/Ggwmi2OZ0z6k', 'admin', NULL, 'suspended', '2026-09-26 13:41:12', '2026-09-26 13:41:12', '2026-09-29 06:37:06', '2026-09-26 13:41:12', '2026-10-12 06:08:24'),
(8, '574d7a7f-a080-4cf4-b08d-c29b71cb33ea', 'admindavran@dav.com', '7894856125', '$argon2id$v=19$m=65536,t=4,p=1$YXFTeFZaeEd5L3NTd3Nzbg$ywMqDhRLl/IKms/v625bJCeEG6Z6cP0QFEf2cqzlZWQ', 'admin', NULL, 'suspended', '2026-09-26 13:42:38', '2026-09-26 13:42:38', '2026-09-29 06:43:39', '2026-09-26 13:42:38', '2026-10-12 06:08:18'),
(9, '3cba1927-da35-4286-8cfe-cf06d1557155', 'adminkdps@kdps.in', '7898852136', '$argon2id$v=19$m=65536,t=4,p=1$UkguNkgyQVVvamxEYkdMNQ$Zx9G3yQlu+JhmmwxLr01Dh9541Xo//2i6RarZOx+BvM', 'admin', NULL, 'suspended', '2026-09-26 13:44:31', '2026-09-26 13:44:31', '2026-09-28 11:54:04', '2026-09-26 13:44:31', '2026-10-12 06:08:12'),
(10, '4f52e97b-76ce-4715-b63b-ca34539a0bb0', 'testteacher@dav.com', '+919876625417', '$argon2id$v=19$m=65536,t=4,p=1$THZoZU94a3FJSmVEUk9USg$diHDzr/P2RUaHiT08XdRaIekI9E62Pn2sU4mcvaQcpc', 'teacher', NULL, 'suspended', '2026-09-26 13:56:13', NULL, '2026-09-26 13:57:37', '2026-09-26 13:56:13', '2026-10-12 06:08:18'),
(11, '213749eb-40ac-4e54-97c1-4d04132700a7', 'kumaraviraj682@gmail.com', '+919905893514', '$argon2id$v=19$m=65536,t=4,p=1$dnYyeG50ckN5ZkxXdlM3cg$cnWY+bshxuTvVuPx+FjJXVkGrDEcyR1qTfxw145y1iY', 'student', NULL, 'active', '2026-09-28 11:53:23', NULL, '2026-10-24 09:18:37', '2026-09-28 11:53:23', '2026-10-24 09:18:37'),
(12, 'f3c3917a-0368-4aa0-b424-e3f9a64ac250', 'mamtapathak051@gmail.com', '+917061509292', '$argon2id$v=19$m=65536,t=4,p=1$aDJrcXNWMTRVeXhHbldoRw$qbqBWpX1e4jC9GkvuT1LngdI8OEi1O6MjKa7XPvqUhs', 'teacher', NULL, 'active', '2026-09-29 06:35:43', NULL, '2026-09-29 06:38:31', '2026-09-29 06:35:43', '2026-10-24 09:17:07'),
(13, '349909fc-e319-4ccb-9966-a70731a0dc96', 'avirajk07@gmail.com', '9905893514', '$argon2id$v=19$m=65536,t=4,p=1$c21EMWFYOXdrcmd6V3I2Zg$+dUXiRMSfer5a2iwm2zd0bQljNTJ5U3ei7LvnB1UC1s', 'admin', NULL, 'suspended', '2026-09-29 10:50:06', '2026-09-29 10:50:06', NULL, '2026-09-29 10:50:06', '2026-10-12 06:08:06'),
(14, '1ad705dd-7fcd-4b26-97bb-d66f89f2dcb2', 'fnxt.interns@gmail.com', '9470181755', '$argon2id$v=19$m=65536,t=4,p=1$eDJwenRPTmxzTEg1QmdaaA$wY1meQDa0bOcSqlIE08J2KWixNs7V11obpU8HvLo1nk', 'admin', 9, 'active', '2026-09-29 10:58:45', '2026-09-29 10:58:45', '2026-10-24 09:20:32', '2026-09-29 10:58:45', '2026-10-24 09:20:32'),
(15, '8b8d4439-ccc5-4678-a745-7dc12834a744', 'ravifuturenxt@gmail.com', '9958335033', '$argon2id$v=19$m=65536,t=4,p=1$dHlRM3k5aWxrVUV0T0NZRw$4t9t1DJk5Ua89BAT91cCIOzkuCkNyT3DG9ffFJoi3zY', 'admin', 10, 'active', '2026-10-12 08:40:23', '2026-10-12 08:40:23', '2026-10-24 09:20:23', '2026-10-12 08:40:23', '2026-10-24 09:20:23'),
(16, '0acfbec7-8dd3-4207-bc82-ae553aaaa544', 'avirajkumar853@gmail.com', '+919905839514', '$argon2id$v=19$m=65536,t=4,p=1$UnZ6MC5ETWxiZFA4Wkdwag$7F+Q8Wo7HMjgtgyKhqMeyEXVK1ZcpheCM1VteV8lAv8', 'student', 9, 'active', '2026-10-21 07:07:22', NULL, '2026-10-24 09:20:09', '2026-10-21 07:07:22', '2026-10-24 09:20:09'),
(17, '9bce5771-c800-41d7-bdb9-5a354b9a6fce', 'tiwarisachin9886@gmail.com', '+919142682791', '$argon2id$v=19$m=65536,t=4,p=1$V0t6VFRtM2pEeFllM0l2Vg$5LyWahb1Xt06fKdQDRIFgRiLkEGBWunTsSMhi5VlDSw', 'teacher', 3, 'active', '2026-10-24 09:24:19', NULL, '2026-10-24 11:02:00', '2026-10-24 09:24:19', '2026-10-24 11:02:00'),
(18, '626fcc78-6c2c-4572-9a05-3fcdb9c3db0a', 'nishant012543@gmail.com', '+918363546985', '$argon2id$v=19$m=65536,t=4,p=1$R2MvWDJmWTU5dkdIeVM4bA$t9v9nlB8RsoHF2RXdOvH9+PV9ylCj6h3LMyAPUKXENo', 'teacher', 3, 'active', '2026-10-24 09:35:58', NULL, '2026-10-24 09:45:27', '2026-10-24 09:35:58', '2026-10-24 09:45:27'),
(19, '0d5d6966-00e8-4c60-a56f-b68e76529260', 'uwypdc@mailto.plus', '+919876543211', '$argon2id$v=19$m=65536,t=4,p=1$Q1YyeHRtUE9nckZmNjhMVg$fjZJCinmtQaIaB2TrQMPUYwd50UBI4wIztpfWkYfvbQ', 'teacher', 3, 'active', '2026-10-24 09:47:28', NULL, NULL, '2026-10-24 09:47:28', '2026-10-24 09:48:07'),
(20, '6599cbc2-6feb-48a6-a662-17d23f48a1a9', 'ns336759@gmail.com', '+919875635485', '$argon2id$v=19$m=65536,t=4,p=1$SG9HYUxWMlE5WGpDd1pGNw$V3ULEDCp7wXH/pH4SC+PFbXoT1w33VD+il5z3xbGAO0', 'teacher', 3, 'active', '2026-10-24 10:08:58', NULL, NULL, '2026-10-24 10:08:58', '2026-10-24 10:47:45'),
(21, '774d7c8e-ab80-4e23-aa8b-68ada042c20b', 'uwypdc@fexpost.com', '+919876543230', '$argon2id$v=19$m=65536,t=4,p=1$QUhKWHEzc3ZSdFJ4bzQ0SQ$OhvqbSTpS9znP1h6r3JLyPMNh8oUCj0foFoJnUdJ+GQ', 'student', 3, 'active', '2026-10-24 10:14:32', NULL, '2026-10-24 10:39:00', '2026-10-24 10:14:32', '2026-10-24 10:39:00'),
(22, '14b76362-e0cd-472b-a906-a21e5becd2a6', 'uwypdc@fexbox.org', '+919876543234', '$argon2id$v=19$m=65536,t=4,p=1$YlNaVlVUcnFaTmh6VmIxWg$CyE/Vox7hQl8rUJkYDKfxHI9hLib0UzNp3D3BpCdgXA', 'student', 3, 'active', '2026-10-24 10:24:36', NULL, '2026-10-24 10:26:02', '2026-10-24 10:24:36', '2026-10-24 10:26:02'),
(23, 'c3871470-cf1b-4069-80e9-d048728ba6d9', 'uwypdc@mailbox.in.ua', '+918765678904', '$argon2id$v=19$m=65536,t=4,p=1$MERsVnlDRTlsZ3BXN2Q4UA$beDOa9NH+SZOS8MwbDTV+rYCS1zpg3ooZLmbifeC87U', 'student', 3, 'active', '2026-10-24 10:30:10', NULL, NULL, '2026-10-24 10:30:10', '2026-10-24 10:34:00'),
(24, '8a33db61-e1a8-4be6-a92e-ffb59b97dd4e', 'uwypdc@any.pink', '+917654323456', '$argon2id$v=19$m=65536,t=4,p=1$NlNCWnJ4WWFKOGtHcE10TQ$R/uQqoPGsEvOtugcQ+sF4JjTBm5hsgnE2k9NB/eiw98', 'student', 3, 'active', '2026-10-24 10:34:58', NULL, '2026-10-24 10:37:30', '2026-10-24 10:34:58', '2026-10-24 10:37:30'),
(25, '3aa09f33-1318-4c71-b9ad-fac1216a3329', 'uwypdc@merepost.com', '+919876567890', '$argon2id$v=19$m=65536,t=4,p=1$azloUkhoZTZsQXE1QWR0Qw$KtPAP6nsc3iBDwjVDwogOHHQqXNfkfSHCunNSxY/xLA', 'student', 3, 'active', '2026-10-24 10:45:39', NULL, NULL, '2026-10-24 10:45:39', '2026-10-24 10:46:22'),
(26, '4c56604d-ab95-45e1-8cab-944ab8af5fcd', 'uwypdc@rover.info', '+919087654321', '$argon2id$v=19$m=65536,t=4,p=1$dk0uc3FwWkhCZEgwNjFFMA$IBM7to8EgF5bKTSAJbIiIem4ijEE+lDfBHv98wY4gCg', 'student', 3, 'active', '2026-10-24 10:50:35', NULL, NULL, '2026-10-24 10:50:35', '2026-10-24 10:50:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_assessments`
--
ALTER TABLE `academic_assessments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_aa_uuid` (`uuid`),
  ADD KEY `idx_aa_teacher` (`teacher_user_id`),
  ADD KEY `idx_aa_batch` (`batch_id`),
  ADD KEY `idx_aa_type` (`assessment_type`),
  ADD KEY `idx_aa_due` (`due_date`);

--
-- Indexes for table `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `awarded_by` (`awarded_by`),
  ADD KEY `idx_student` (`student_user_id`),
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `batch_id` (`batch_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_event_date` (`event_date`),
  ADD KEY `idx_school` (`school_id`);

--
-- Indexes for table `activity_registrations`
--
ALTER TABLE `activity_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_activity_user` (`activity_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `activity_results`
--
ALTER TABLE `activity_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_activity_student` (`activity_id`,`student_user_id`),
  ADD KEY `student_user_id` (`student_user_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `aiverse_banners`
--
ALTER TABLE `aiverse_banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bn_active` (`is_active`,`display_order`);

--
-- Indexes for table `aiverse_bundles`
--
ALTER TABLE `aiverse_bundles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_ab_slug` (`slug`),
  ADD KEY `idx_ab_status` (`status`);

--
-- Indexes for table `aiverse_courses`
--
ALTER TABLE `aiverse_courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_ac_slug` (`slug`),
  ADD KEY `idx_ac_status` (`status`);

--
-- Indexes for table `aiverse_enrollments`
--
ALTER TABLE `aiverse_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ae_student` (`student_user_id`),
  ADD KEY `idx_ae_course` (`course_id`),
  ADD KEY `idx_ae_fee` (`fee_id`);

--
-- Indexes for table `ai_answers`
--
ALTER TABLE `ai_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ai_question_id` (`ai_question_id`),
  ADD KEY `student_user_id` (`student_user_id`);

--
-- Indexes for table `ai_questions`
--
ALTER TABLE `ai_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ai_session_id` (`ai_session_id`);

--
-- Indexes for table `ai_sessions`
--
ALTER TABLE `ai_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `idx_student` (`student_user_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_school` (`school_id`);

--
-- Indexes for table `assessment_submissions`
--
ALTER TABLE `assessment_submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_as_uuid` (`uuid`),
  ADD UNIQUE KEY `uk_as_assessment_student` (`assessment_id`,`student_user_id`),
  ADD KEY `idx_as_student` (`student_user_id`),
  ADD KEY `idx_as_assessment` (`assessment_id`),
  ADD KEY `idx_sub_student_date` (`student_user_id`,`submitted_at`),
  ADD KEY `idx_sub_assessment_student` (`assessment_id`,`student_user_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_batch_student_date` (`batch_id`,`student_user_id`,`attendance_date`),
  ADD KEY `marked_by` (`marked_by`),
  ADD KEY `idx_date` (`attendance_date`),
  ADD KEY `idx_student` (`student_user_id`),
  ADD KEY `idx_att_student_date` (`student_user_id`,`attendance_date`),
  ADD KEY `idx_att_batch_date` (`batch_id`,`attendance_date`);

--
-- Indexes for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_batch_session` (`batch_id`,`session_date`,`session_label`),
  ADD KEY `marked_by` (`marked_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_audit_user_created` (`user_id`,`created_at`);

--
-- Indexes for table `batches`
--
ALTER TABLE `batches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_teacher` (`teacher_id`),
  ADD KEY `idx_class` (`class_id`);

--
-- Indexes for table `batch_activity_assignments`
--
ALTER TABLE `batch_activity_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_batch` (`batch_id`),
  ADD KEY `idx_activity` (`activity_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- Indexes for table `batch_assignment_acknowledgments`
--
ALTER TABLE `batch_assignment_acknowledgments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_assignment_student` (`assignment_id`,`student_user_id`),
  ADD KEY `student_user_id` (`student_user_id`);

--
-- Indexes for table `batch_students`
--
ALTER TABLE `batch_students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_batch_student` (`batch_id`,`student_user_id`),
  ADD KEY `student_user_id` (`student_user_id`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `issued_by` (`issued_by`),
  ADD KEY `idx_student` (`student_user_id`),
  ADD KEY `idx_student_type` (`student_user_id`,`certificate_type`);

--
-- Indexes for table `chapters`
--
ALTER TABLE `chapters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subject` (`subject_id`);

--
-- Indexes for table `chess_challenges`
--
ALTER TABLE `chess_challenges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_challenge_uuid` (`uuid`),
  ADD KEY `idx_challenger` (`challenger_user_id`),
  ADD KEY `idx_challenged` (`challenged_user_id`),
  ADD KEY `idx_challenge_school` (`school_id`),
  ADD KEY `fk_cc_game` (`game_id`);

--
-- Indexes for table `chess_coach_assignments`
--
ALTER TABLE `chess_coach_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cca_teacher` (`teacher_user_id`),
  ADD KEY `idx_cca_student` (`student_user_id`),
  ADD KEY `idx_cca_school` (`school_id`),
  ADD KEY `fk_cca_admin` (`assigned_by`);

--
-- Indexes for table `chess_curriculum`
--
ALTER TABLE `chess_curriculum`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_curr_tier_cat` (`tier`,`category`);

--
-- Indexes for table `chess_games`
--
ALTER TABLE `chess_games`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_chess_game_uuid` (`uuid`),
  ADD KEY `idx_white_user` (`white_user_id`),
  ADD KEY `idx_black_user` (`black_user_id`),
  ADD KEY `idx_game_type` (`game_type`),
  ADD KEY `idx_game_school` (`school_id`);

--
-- Indexes for table `chess_live_sessions`
--
ALTER TABLE `chess_live_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_live_session_uuid` (`uuid`),
  ADD KEY `idx_live_teacher` (`teacher_user_id`),
  ADD KEY `idx_live_student` (`student_user_id`),
  ADD KEY `idx_live_school` (`school_id`);

--
-- Indexes for table `chess_ratings`
--
ALTER TABLE `chess_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_student_chess` (`student_user_id`),
  ADD KEY `idx_tier` (`tier`),
  ADD KEY `idx_rating` (`current_rating`),
  ADD KEY `idx_school_chess` (`school_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_school` (`school_id`);

--
-- Indexes for table `competitions`
--
ALTER TABLE `competitions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `competition_participants`
--
ALTER TABLE `competition_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_comp_student` (`competition_id`,`student_user_id`),
  ADD KEY `student_user_id` (`student_user_id`);

--
-- Indexes for table `competition_results`
--
ALTER TABLE `competition_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_comp_student_result` (`competition_id`,`student_user_id`),
  ADD KEY `student_user_id` (`student_user_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `counseling_logs`
--
ALTER TABLE `counseling_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_request` (`request_id`),
  ADD KEY `fk_cl_user` (`user_id`);

--
-- Indexes for table `counseling_requests`
--
ALTER TABLE `counseling_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `idx_student` (`student_user_id`),
  ADD KEY `idx_school` (`school_id`),
  ADD KEY `idx_teacher` (`assigned_teacher_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_type` (`counseling_type`),
  ADD KEY `fk_cr_assigner` (`assigned_by`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `idx_owner` (`owner_user_id`),
  ADD KEY `idx_purpose` (`purpose`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chapter_id` (`chapter_id`),
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_subject` (`subject_id`),
  ADD KEY `idx_batch` (`batch_id`),
  ADD KEY `idx_school` (`school_id`),
  ADD KEY `idx_batch_school` (`batch_id`,`school_id`);

--
-- Indexes for table `lesson_doubts`
--
ALTER TABLE `lesson_doubts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_ld_uuid` (`uuid`),
  ADD KEY `idx_ld_lesson` (`lesson_id`),
  ADD KEY `idx_ld_student` (`student_user_id`),
  ADD KEY `idx_ld_status` (`status`);

--
-- Indexes for table `lesson_doubt_replies`
--
ALTER TABLE `lesson_doubt_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ldr_doubt` (`doubt_id`),
  ADD KEY `idx_ldr_user` (`user_id`);

--
-- Indexes for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_lesson_student` (`lesson_id`,`student_user_id`),
  ADD KEY `student_user_id` (`student_user_id`);

--
-- Indexes for table `lesson_quizzes`
--
ALTER TABLE `lesson_quizzes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_lq_lesson` (`lesson_id`);

--
-- Indexes for table `lesson_quiz_attempts`
--
ALTER TABLE `lesson_quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lqa_student` (`student_user_id`),
  ADD KEY `idx_lqa_lesson` (`lesson_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_unread` (`user_id`,`is_read`),
  ADD KEY `idx_notif_user_unread` (`user_id`,`is_read`,`created_at`);

--
-- Indexes for table `otps`
--
ALTER TABLE `otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phone_purpose` (`phone`,`purpose`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `parent_email_log`
--
ALTER TABLE `parent_email_log`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_parent_student_week` (`parent_id`,`student_user_id`,`week_start`),
  ADD KEY `idx_week` (`week_start`),
  ADD KEY `idx_parent` (`parent_id`),
  ADD KEY `pel_student` (`student_user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `question_bank`
--
ALTER TABLE `question_bank`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_qb_uuid` (`uuid`),
  ADD KEY `idx_qb_teacher` (`teacher_user_id`),
  ADD KEY `idx_qb_subject` (`subject_id`),
  ADD KEY `idx_qb_chapter` (`chapter_id`),
  ADD KEY `idx_qb_topic` (`topic_id`),
  ADD KEY `idx_qb_school` (`school_id`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_identifier_action` (`identifier`,`action`);

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_city` (`city`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_school` (`school_name`),
  ADD KEY `idx_city` (`city`);

--
-- Indexes for table `student_fees`
--
ALTER TABLE `student_fees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sf_student` (`student_user_id`),
  ADD KEY `idx_sf_school` (`school_id`),
  ADD KEY `idx_sf_batch` (`batch_id`),
  ADD KEY `idx_sf_month` (`fee_month`),
  ADD KEY `idx_sf_status` (`status`),
  ADD KEY `idx_fees_student_status` (`student_user_id`,`status`),
  ADD KEY `idx_fees_school_month` (`school_id`,`fee_month`,`academic_year`);

--
-- Indexes for table `student_knowledge_profile`
--
ALTER TABLE `student_knowledge_profile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_student_topic` (`student_user_id`,`subject_id`,`topic_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indexes for table `student_parents`
--
ALTER TABLE `student_parents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_student_parent` (`student_user_id`,`parent_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_school` (`school_name`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `timetables`
--
ALTER TABLE `timetables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_timetable_slot` (`school_id`,`class_level`,`section`,`day_of_week`,`period_number`),
  ADD KEY `idx_tt_teacher` (`teacher_user_id`),
  ADD KEY `idx_tt_school` (`school_id`),
  ADD KEY `idx_tt_day` (`day_of_week`),
  ADD KEY `idx_tt_school_class_sec` (`school_id`,`class_level`,`section`);

--
-- Indexes for table `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_chapter` (`chapter_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_school` (`school_id`),
  ADD KEY `idx_users_role_status` (`role`,`status`),
  ADD KEY `idx_users_school_role` (`school_id`,`role`,`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_assessments`
--
ALTER TABLE `academic_assessments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `achievements`
--
ALTER TABLE `achievements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `activity_registrations`
--
ALTER TABLE `activity_registrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `activity_results`
--
ALTER TABLE `activity_results`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `aiverse_banners`
--
ALTER TABLE `aiverse_banners`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `aiverse_bundles`
--
ALTER TABLE `aiverse_bundles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `aiverse_courses`
--
ALTER TABLE `aiverse_courses`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `aiverse_enrollments`
--
ALTER TABLE `aiverse_enrollments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ai_answers`
--
ALTER TABLE `ai_answers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_questions`
--
ALTER TABLE `ai_questions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_sessions`
--
ALTER TABLE `ai_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `assessment_submissions`
--
ALTER TABLE `assessment_submissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=582;

--
-- AUTO_INCREMENT for table `batches`
--
ALTER TABLE `batches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `batch_activity_assignments`
--
ALTER TABLE `batch_activity_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `batch_assignment_acknowledgments`
--
ALTER TABLE `batch_assignment_acknowledgments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `batch_students`
--
ALTER TABLE `batch_students`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `chapters`
--
ALTER TABLE `chapters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chess_challenges`
--
ALTER TABLE `chess_challenges`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chess_coach_assignments`
--
ALTER TABLE `chess_coach_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `chess_curriculum`
--
ALTER TABLE `chess_curriculum`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chess_games`
--
ALTER TABLE `chess_games`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `chess_live_sessions`
--
ALTER TABLE `chess_live_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `chess_ratings`
--
ALTER TABLE `chess_ratings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `competitions`
--
ALTER TABLE `competitions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `competition_participants`
--
ALTER TABLE `competition_participants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `competition_results`
--
ALTER TABLE `competition_results`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `counseling_logs`
--
ALTER TABLE `counseling_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `counseling_requests`
--
ALTER TABLE `counseling_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lesson_doubts`
--
ALTER TABLE `lesson_doubts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lesson_doubt_replies`
--
ALTER TABLE `lesson_doubt_replies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `lesson_quizzes`
--
ALTER TABLE `lesson_quizzes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lesson_quiz_attempts`
--
ALTER TABLE `lesson_quiz_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `otps`
--
ALTER TABLE `otps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `parent_email_log`
--
ALTER TABLE `parent_email_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `question_bank`
--
ALTER TABLE `question_bank`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=311;

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `student_fees`
--
ALTER TABLE `student_fees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student_knowledge_profile`
--
ALTER TABLE `student_knowledge_profile`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_parents`
--
ALTER TABLE `student_parents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `timetables`
--
ALTER TABLE `timetables`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `topics`
--
ALTER TABLE `topics`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `achievements`
--
ALTER TABLE `achievements`
  ADD CONSTRAINT `achievements_ibfk_1` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `achievements_ibfk_2` FOREIGN KEY (`awarded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `activities_ibfk_2` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `activity_registrations`
--
ALTER TABLE `activity_registrations`
  ADD CONSTRAINT `activity_registrations_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activity_registrations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `activity_results`
--
ALTER TABLE `activity_results`
  ADD CONSTRAINT `activity_results_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activity_results_ibfk_2` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activity_results_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ai_answers`
--
ALTER TABLE `ai_answers`
  ADD CONSTRAINT `ai_answers_ibfk_1` FOREIGN KEY (`ai_question_id`) REFERENCES `ai_questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_answers_ibfk_2` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ai_questions`
--
ALTER TABLE `ai_questions`
  ADD CONSTRAINT `ai_questions_ibfk_1` FOREIGN KEY (`ai_session_id`) REFERENCES `ai_sessions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ai_sessions`
--
ALTER TABLE `ai_sessions`
  ADD CONSTRAINT `ai_sessions_ibfk_1` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_sessions_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ai_sessions_ibfk_3` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  ADD CONSTRAINT `attendance_sessions_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_sessions_ibfk_2` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `batches`
--
ALTER TABLE `batches`
  ADD CONSTRAINT `batches_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `batches_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `batches_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `batches_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `batch_activity_assignments`
--
ALTER TABLE `batch_activity_assignments`
  ADD CONSTRAINT `batch_activity_assignments_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `batch_activity_assignments_ibfk_2` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `batch_activity_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `batch_assignment_acknowledgments`
--
ALTER TABLE `batch_assignment_acknowledgments`
  ADD CONSTRAINT `batch_assignment_acknowledgments_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `batch_activity_assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `batch_assignment_acknowledgments_ibfk_2` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `batch_students`
--
ALTER TABLE `batch_students`
  ADD CONSTRAINT `batch_students_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `batch_students_ibfk_2` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `chapters`
--
ALTER TABLE `chapters`
  ADD CONSTRAINT `chapters_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chess_challenges`
--
ALTER TABLE `chess_challenges`
  ADD CONSTRAINT `fk_cc_challenged` FOREIGN KEY (`challenged_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cc_challenger` FOREIGN KEY (`challenger_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cc_game` FOREIGN KEY (`game_id`) REFERENCES `chess_games` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `chess_coach_assignments`
--
ALTER TABLE `chess_coach_assignments`
  ADD CONSTRAINT `fk_cca_admin` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cca_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cca_student` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cca_teacher` FOREIGN KEY (`teacher_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chess_games`
--
ALTER TABLE `chess_games`
  ADD CONSTRAINT `fk_cg_black` FOREIGN KEY (`black_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cg_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cg_white` FOREIGN KEY (`white_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `chess_live_sessions`
--
ALTER TABLE `chess_live_sessions`
  ADD CONSTRAINT `fk_cls_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cls_student` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cls_teacher` FOREIGN KEY (`teacher_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chess_ratings`
--
ALTER TABLE `chess_ratings`
  ADD CONSTRAINT `fk_chess_rating_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_chess_rating_student` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `competitions`
--
ALTER TABLE `competitions`
  ADD CONSTRAINT `competitions_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `competition_participants`
--
ALTER TABLE `competition_participants`
  ADD CONSTRAINT `competition_participants_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `competition_participants_ibfk_2` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `competition_results`
--
ALTER TABLE `competition_results`
  ADD CONSTRAINT `competition_results_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `competition_results_ibfk_2` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `competition_results_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `counseling_logs`
--
ALTER TABLE `counseling_logs`
  ADD CONSTRAINT `fk_cl_request` FOREIGN KEY (`request_id`) REFERENCES `counseling_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cl_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `counseling_requests`
--
ALTER TABLE `counseling_requests`
  ADD CONSTRAINT `fk_cr_assigner` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cr_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cr_student` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cr_teacher` FOREIGN KEY (`assigned_teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lessons_ibfk_2` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lessons_ibfk_3` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lessons_ibfk_4` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lessons_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD CONSTRAINT `lesson_progress_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lesson_progress_ibfk_2` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `parents`
--
ALTER TABLE `parents`
  ADD CONSTRAINT `parents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `parent_email_log`
--
ALTER TABLE `parent_email_log`
  ADD CONSTRAINT `pel_parent` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pel_student` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schools`
--
ALTER TABLE `schools`
  ADD CONSTRAINT `schools_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_knowledge_profile`
--
ALTER TABLE `student_knowledge_profile`
  ADD CONSTRAINT `student_knowledge_profile_ibfk_1` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_knowledge_profile_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_knowledge_profile_ibfk_3` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_parents`
--
ALTER TABLE `student_parents`
  ADD CONSTRAINT `student_parents_ibfk_1` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_parents_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teachers_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `topics`
--
ALTER TABLE `topics`
  ADD CONSTRAINT `topics_ibfk_1` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
