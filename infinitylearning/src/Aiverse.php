<?php
declare(strict_types=1);

namespace App;

use PDO;

final class Aiverse
{
    private static bool $bootstrapped = false;

    public static function ensureSchema(): void
    {
        if (self::$bootstrapped) return;
        $db = Database::connect();

        // 1. Table for AI Courses
        $db->exec("
            CREATE TABLE IF NOT EXISTS `aiverse_courses` (
              `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
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
              `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `idx_ac_slug` (`slug`),
              KEY `idx_ac_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 2. Table for Course Enrollments
        $db->exec("
            CREATE TABLE IF NOT EXISTS `aiverse_enrollments` (
              `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `student_user_id` bigint(20) UNSIGNED NOT NULL,
              `course_id` int(11) UNSIGNED NOT NULL,
              `fee_id` bigint(20) UNSIGNED DEFAULT NULL,
              `status` enum('pending','active','completed','cancelled') NOT NULL DEFAULT 'active',
              `notes` text DEFAULT NULL,
              `created_at` datetime DEFAULT current_timestamp(),
              `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`),
              KEY `idx_ae_student` (`student_user_id`),
              KEY `idx_ae_course` (`course_id`),
              KEY `idx_ae_fee` (`fee_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 3. Table for Course Bundles & Promotions
        $db->exec("
            CREATE TABLE IF NOT EXISTS `aiverse_bundles` (
              `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
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
              `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `idx_ab_slug` (`slug`),
              KEY `idx_ab_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 4. Table for Promotional Banners
        $db->exec("
            CREATE TABLE IF NOT EXISTS `aiverse_banners` (
              `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
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
              `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`),
              KEY `idx_bn_active` (`is_active`, `display_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Sync and seed curriculum courses from trial PDF
        self::syncPdfCurriculumCourses($db);

        // Seed default bundles if empty
        $bCount = (int)$db->query("SELECT COUNT(*) FROM `aiverse_bundles`")->fetchColumn();
        if ($bCount === 0) {
            self::seedDefaultBundles($db);
        }

        // Seed default banners if empty
        $bnCount = (int)$db->query("SELECT COUNT(*) FROM `aiverse_banners`")->fetchColumn();
        if ($bnCount === 0) {
            self::seedDefaultBanners($db);
        }

        self::$bootstrapped = true;
    }

    private static function syncPdfCurriculumCourses(PDO $db): void
    {
        $allTracks = [
            [
                'title' => 'Generative AI & LLM Engineering',
                'slug' => 'generative-ai-llm-engineering',
                'tagline' => 'Master Prompt Engineering, LangChain, RAG architectures & Vector DBs',
                'category' => 'Generative AI',
                'level' => 'Beginner to Advanced',
                'duration' => '8 Weeks · 32 Live Hours',
                'fee' => 6999.00,
                'badge' => 'Featured Track',
                'overview' => 'An end-to-end immersion into modern Generative AI. Learn to design, evaluate, and deploy enterprise Large Language Model (LLM) applications with hands-on labs.',
                'outcomes' => "• Master prompt crafting, system prompting & few-shot reasoning\n• Build production RAG (Retrieval-Augmented Generation) pipelines\n• Integrate Vector databases (Pinecone, ChromaDB, Qdrant)\n• Fine-tune open-weights models (Llama 3, Mistral) with PEFT / LoRA\n• Deploy production-ready GenAI REST APIs with FastAPI & Docker",
                'curriculum' => "• Module 1: Foundations of Foundation Models & Attention Mechanism\n• Module 2: Advanced Prompt Engineering & Structured JSON Outputs\n• Module 3: Vector Embeddings, Chunking & Semantic Search\n• Module 4: Building RAG with LangChain & LlamaIndex\n• Module 5: Open-Source Models & Local Inference (Ollama/vLLM)\n• Module 6: Model Fine-Tuning & Parameter-Efficient Tuning\n• Module 7: LLM Evaluation, Guardrails & Responsible AI\n• Module 8: Capstone Project & Enterprise Deployment",
                'prerequisites' => 'Basic knowledge of Python programming and web fundamentals.',
                'includes' => "• 32 Hours of Live Interactive Sessions & Code-Alongs\n• 4 Real-world Portfolio Projects & Capstone\n• Verified Industry Certificate from AIVERSE & Infinity Learning\n• Lifetime Access to Course Notebooks & GitHub Repositories\n• 1-on-1 Code Review & Mentor Guidance"
            ],
            [
                'title' => 'Python for Generative & Agentic Workflows',
                'slug' => 'python-for-artificial-intelligence',
                'tagline' => 'From programming fundamentals to NumPy, Pandas, Asyncio & Tool Calling',
                'category' => 'Programming & Data Science',
                'level' => 'Complete Beginner',
                'duration' => '6 Weeks · 24 Live Hours',
                'fee' => 3999.00,
                'badge' => 'Popular',
                'overview' => 'The essential launching pad for your AI journey. Build rock-solid Python programming skills tailored explicitly for Machine Learning, data manipulation, async agent execution, and mathematical modeling.',
                'outcomes' => "• Write idiomatic, clean Python 3.12+ code and object-oriented architectures\n• Vectorized numerical computing with NumPy\n• Fast tabular data manipulation, cleaning & analysis using Pandas\n• Asyncio execution for parallel agent reasoning loops\n• Training and evaluating classical machine learning models with Scikit-Learn",
                'curriculum' => "• Module 1: Python Fundamentals, Data Structures & Control Flow\n• Module 2: Functions, Lambda Expressions & Object-Oriented Python\n• Module 3: Numerical Python (NumPy) & Matrix Algebra\n• Module 4: Data Wrangling & Analysis with Pandas\n• Module 5: Asynchronous Python & Network APIs for AI\n• Module 6: Introduction to Machine Learning Pipelines with Scikit-Learn",
                'prerequisites' => 'No prior coding experience needed. Ideal for high school students, college learners & beginners.',
                'includes' => "• 24 Hours of Hands-on Live Coding Classes\n• 20+ Practice Notebooks & Interactive Problem Sets\n• Course Completion Certificate\n• 24/7 AI Coding Assistant inside Infinity Learning"
            ],
            [
                'title' => 'Autonomous AI Agents & Multi-Agent Systems',
                'slug' => 'autonomous-ai-agents-multi-agent',
                'tagline' => 'Build goal-directed agents with AutoGen, CrewAI, LangGraph & Tool Calling',
                'category' => 'Agentic Systems',
                'level' => 'Intermediate to Advanced',
                'duration' => '8 Weeks · 32 Live Hours',
                'fee' => 7999.00,
                'badge' => 'Trending',
                'overview' => 'Step into the next frontier of AI: Autonomous Agents that reason, plan, use tools, browse the web, write code, and collaborate in multi-agent swarms to accomplish complex objectives.',
                'outcomes' => "• Understand ReAct reasoning loops, Plan-and-Solve & Reflection patterns\n• Equip agents with custom tool calling, shell access & web search\n• Architect multi-agent collaboration with CrewAI and LangGraph\n• Persist agent memory across sessions (Short-term, Long-term, Episodic)\n• Build automated agents for market research, code auditing, and customer support",
                'curriculum' => "• Module 1: Architecture of Autonomous Agents (Perception, Memory, Planning, Action)\n• Module 2: Tool Use & Function Calling with Gemini & OpenAI APIs\n• Module 3: Memory Architectures & Hierarchical Task Decomposition\n• Module 4: Building Workflows with LangGraph State Machines\n• Module 5: Multi-Agent Teams with CrewAI & AutoGen\n• Module 6: Human-in-the-Loop, Sandboxing & Security Controls\n• Module 7: Real-world Agent Projects (Researcher, Developer, Analyst)\n• Module 8: Multi-Agent Capstone Deployment",
                'prerequisites' => 'Comfortable with Python and basic API / LLM prompting concepts.',
                'includes' => "• 32 Hours of Live Interactive Workshops\n• Complete Source Code for 5 Production AI Agents\n• Official AIVERSE Agentic AI Specialist Certificate\n• Direct access to AI Practitioners community"
            ],
            [
                'title' => 'Model Context Protocol (MCP) & AI Tooling Systems',
                'slug' => 'mcp-ai-tooling-frameworks',
                'tagline' => 'Build standardized MCP servers, client tools & dynamic context injection',
                'category' => 'Model Context Protocols',
                'level' => 'Intermediate',
                'duration' => '6 Weeks · 24 Live Hours',
                'fee' => 5999.00,
                'badge' => 'New Standard',
                'overview' => 'Dive deep into the revolutionary Model Context Protocol (MCP). Learn how to expose local databases, filesystem context, secure enterprise tools, and cloud APIs to modern LLM agents seamlessly.',
                'outcomes' => "• Understand the core architecture of MCP (Host, Client, Server)\n• Build high-performance MCP servers in Python and TypeScript\n• Expose SQL databases, document stores & terminal tools via standard protocols\n• Orchestrate multi-server contexts for Claude, Gemini, and local models\n• Implement enterprise token security, authentication & sandboxing for MCP",
                'curriculum' => "• Module 1: Introduction to MCP & The Universal AI Tooling Standard\n• Module 2: Building Your First MCP Server in Python (FastMCP)\n• Module 3: Tools, Resources & Prompts Primitive deep dive\n• Module 4: Connecting SQL Databases & Internal APIs via MCP\n• Module 5: Transport Layers: Stdio vs SSE (Server-Sent Events)\n• Module 6: Enterprise Security, RBAC & Production MCP Deployment",
                'prerequisites' => 'Basic knowledge of Python or JavaScript.',
                'includes' => "• 24 Live Hours & Interactive Hands-on Labs\n• Production-ready MCP Server Templates\n• Official MCP Developer Credential\n• Dedicated Mentor Code Audits"
            ],
            [
                'title' => 'Enterprise GenAI on Azure & Microsoft Foundry',
                'slug' => 'azure-genai-microsoft-foundry',
                'tagline' => 'Azure OpenAI, Prompt Flow, Azure AI Foundry SDK & Enterprise Security',
                'category' => 'Cloud AI & Enterprise',
                'level' => 'Intermediate to Advanced',
                'duration' => '8 Weeks · 32 Live Hours',
                'fee' => 6999.00,
                'badge' => 'Enterprise Ready',
                'overview' => 'Architect scalable, compliant Generative AI applications on Microsoft Azure. Leverage Azure OpenAI Service, Azure AI Search, Prompt Flow orchestration, and Foundry IQ knowledge agents.',
                'outcomes' => "• Deploy Azure OpenAI models (GPT-4o, embeddings) with private endpoints\n• Build hybrid vector search with Azure AI Search and semantic ranking\n• Design and test evaluation pipelines using Prompt Flow\n• Build knowledge-enhanced reasoning agents using Foundry IQ\n• Configure enterprise RBAC, network isolation & data residency",
                'curriculum' => "• Module 1: Azure AI Ecosystem & Azure OpenAI Provisioning\n• Module 2: Enterprise RAG Architecture on Azure AI Search\n• Module 3: Prompt Flow: Authoring, Testing & Variant Evaluation\n• Module 4: Azure AI Foundry SDK & Agentic Integration\n• Module 5: Foundry IQ & Multi-Source Knowledge Connectors\n• Module 6: Enterprise Security, VNet Integration & Managed Identities\n• Module 7: Cost Management, Provisioned Throughput & Quotas\n• Module 8: Enterprise Capstone Project & Azure Architecture Review",
                'prerequisites' => 'Familiarity with cloud concepts and Python programming.',
                'includes' => "• 32 Hours of Instructor-Led Labs\n• Azure Cloud Lab Exercise Guides\n• Verified Enterprise AI Specialist Certificate\n• Architecture Review from Microsoft Certified Experts"
            ],
            [
                'title' => 'LLMOps, Production Deployment & AI Governance',
                'slug' => 'llmops-deployment-governance',
                'tagline' => 'CI/CD for LLMs, evaluation benchmarks, guardrails, ethics & regulatory compliance',
                'category' => 'LLMOps & Governance',
                'level' => 'Intermediate',
                'duration' => '6 Weeks · 24 Live Hours',
                'fee' => 5499.00,
                'badge' => 'Crucial Skill',
                'overview' => 'Operationalize foundation models reliably in production. Automate evals, monitor token drift and hallucination rates, enforce safety guardrails, and comply with global AI safety regulations.',
                'outcomes' => "• Build automated evaluation pipelines for prompt and model changes\n• Implement LLM guardrails (NeMo Guardrails, Llama Guard)\n• Monitor production latency, token costs, and drift with OpenTelemetry\n• Implement red-teaming, prompt injection defenses & jailbreak mitigations\n• Navigate EU AI Act, NIST AI RMF & enterprise governance frameworks",
                'curriculum' => "• Module 1: The LLMOps Lifecycle vs Traditional MLOps\n• Module 2: Automated Evals (Ragas, TruLens, DeepEval)\n• Module 3: Guardrail Engineering & Safety Policy Enforcement\n• Module 4: Production Observability, Tracing & Cost Monitoring\n• Module 5: Red Teaming & Adversarial Attack Defenses\n• Module 6: AI Governance, Copyright, Ethics & Compliance",
                'prerequisites' => 'Basic understanding of LLMs and web services.',
                'includes' => "• 24 Live Hours of Real-world Case Studies\n• Complete CI/CD Evaluation Boilerplate\n• Certificate in LLMOps & AI Governance\n• 1-on-1 Portfolio Feedback"
            ],
            [
                'title' => 'Deep Learning & Computer Vision',
                'slug' => 'deep-learning-computer-vision',
                'tagline' => 'Convolutional Neural Networks, Vision Transformers & Object Detection',
                'category' => 'Computer Vision',
                'level' => 'Intermediate',
                'duration' => '8 Weeks · 30 Live Hours',
                'fee' => 5999.00,
                'badge' => 'Comprehensive',
                'overview' => 'Discover how computers understand visual data. Train neural networks from scratch and build vision systems for face recognition, medical imaging, self-driving cars, and object tracking.',
                'outcomes' => "• Deep neural network architectures, backpropagation & gradient descent\n• Image processing with OpenCV and PIL\n• Convolutional Neural Networks (CNNs), ResNet, and transfer learning in PyTorch\n• Real-time Object Detection with YOLOv8 & YOLOv10\n• Vision Transformers (ViT) and modern multimodal vision models",
                'curriculum' => "• Module 1: Artificial Neural Networks & PyTorch Fundamentals\n• Module 2: Image Processing with OpenCV\n• Module 3: Convolutional Neural Networks (CNNs) & Feature Extraction\n• Module 4: Transfer Learning with ResNet, EfficientNet & MobileNet\n• Module 5: Object Detection & Tracking with YOLO\n• Module 6: Image Segmentation (Mask R-CNN, SAM)\n• Module 7: Vision Transformers (ViT) & Diffusion Models Intro\n• Module 8: Edge AI Deployment & Capstone",
                'prerequisites' => 'Python proficiency and foundational algebra / calculus.',
                'includes' => "• 30 Hours of Interactive PyTorch Live Sessions\n• Cloud GPU environment guidance for model training\n• Certificate of Accomplishment\n• CV Portfolio Capstone project"
            ],
            [
                'title' => 'Full-Stack AI Application Development',
                'slug' => 'full-stack-ai-application-development',
                'tagline' => 'Create web applications powered by AI APIs, streaming, and modern UI',
                'category' => 'Full-Stack AI',
                'level' => 'All Levels',
                'duration' => '8 Weeks · 32 Live Hours',
                'fee' => 6499.00,
                'badge' => 'Hands-on',
                'overview' => 'Transform AI models into polished, production-ready web products. Learn to build reactive web apps with real-time token streaming, multimodal input, and secure authentication.',
                'outcomes' => "• Design interactive AI web interfaces with streaming responses\n• Secure backend API integration with rate limiting and key management\n• Multimodal UI (audio recording, image uploads, document parsing)\n• User authentication, subscription billing, and usage tracking\n• Cloud deployment on production infrastructure",
                'curriculum' => "• Module 1: Modern Full-Stack AI Architecture Overview\n• Module 2: Building Backends for AI with Python / PHP\n• Module 3: Streaming Responses & Server-Sent Events (SSE)\n• Module 4: Multimodal Inputs (Audio, PDF, Images)\n• Module 5: Database Persistence & User State Management\n• Module 6: Rate Limiting, API Security & Cost Optimization\n• Module 7: Production CI/CD & Cloud Hosting\n• Module 8: End-to-End AI SaaS Capstone Launch",
                'prerequisites' => 'Basic HTML/CSS, JavaScript or Python knowledge.',
                'includes' => "• 32 Live Instructional Hours\n• Full SaaS Boilerplate Code Template\n• Verified Full-Stack AI Developer Certificate\n• Lifetime Updates & Community Access"
            ],
            [
                'title' => 'Professional Certificate in Generative AI & Agentic AI',
                'slug' => 'generative-agentic-ai-master-program',
                'tagline' => 'Complete 5-Phase Immersion: Foundations, MCP, Multi-Agent Swarms & Foundry Capstone',
                'category' => 'Flagship Master Track',
                'level' => 'Comprehensive (Zero to Hero)',
                'duration' => '16 Weeks · 64 Live Hours',
                'fee' => 14999.00,
                'badge' => 'Flagship Master Track',
                'overview' => 'The ultimate industry credential for future AI Architects and Engineers. Covers the complete 5-Phase curriculum: Python for AI, Prompt Engineering & RAG, MCP Tooling, Multi-Agent Engineering (CrewAI/LangGraph), Azure AI Foundry, LLMOps, and a real-world enterprise capstone.',
                'outcomes' => "• Complete end-to-end mastery of Generative AI, RAG, and Autonomous Agents\n• Build production MCP servers and custom AI agent tools\n• Architect multi-agent collaborative workflows with CrewAI and LangGraph\n• Deploy enterprise-grade knowledge agents on Microsoft Azure Foundry\n• Ship an industry-ready Capstone evaluated by AI practitioners",
                'curriculum' => "• Phase 1: Python for AI & Foundations of Agentic Intelligence\n• Phase 2: Context-Driven RAG, Multimodal AI & Model Context Protocol (MCP)\n• Phase 3: Agent Engineering, Multi-Agent Swarms & LLMOps Pipeline\n• Phase 4: Azure AI Foundry, Knowledge Retrieval & AI Governance\n• Phase 5: Production Capstone Project & Faculty Masterclasses",
                'prerequisites' => 'No prior experience required; starts from Python fundamentals and scales to enterprise AI engineering.',
                'includes' => "• 64 Hours of Interactive Live Sessions & Code Reviews\n• 5 Full Portfolio Projects + Industry Capstone\n• Dual Certification from AIVERSE & Infinity Learning\n• Priority Placement Support & Practitioner Community Access"
            ]
        ];

        $checkStmt = $db->prepare("SELECT id FROM `aiverse_courses` WHERE slug = ? LIMIT 1");
        $insStmt = $db->prepare("
            INSERT INTO `aiverse_courses` 
            (title, slug, tagline, category, level, duration, fee, badge, overview, outcomes, curriculum, prerequisites, includes, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'published')
        ");

        foreach ($allTracks as $c) {
            $checkStmt->execute([$c['slug']]);
            if (!$checkStmt->fetchColumn()) {
                $insStmt->execute([
                    $c['title'], $c['slug'], $c['tagline'], $c['category'],
                    $c['level'], $c['duration'], $c['fee'], $c['badge'],
                    $c['overview'], $c['outcomes'], $c['curriculum'],
                    $c['prerequisites'], $c['includes']
                ]);
            }
        }
    }

    private static function seedDefaultBundles(PDO $db): void
    {
        // Fetch course IDs by slug
        $cMap = [];
        $rows = $db->query("SELECT id, slug, fee FROM `aiverse_courses`")->fetchAll();
        foreach ($rows as $r) {
            $cMap[$r['slug']] = ['id' => (int)$r['id'], 'fee' => (float)$r['fee']];
        }

        $bundles = [];

        // Bundle 1: Complete GenAI & Agentic AI Master Bundle (10% Discount)
        $masterTracks = [
            'generative-ai-llm-engineering',
            'autonomous-ai-agents-multi-agent',
            'mcp-ai-tooling-frameworks',
            'azure-genai-microsoft-foundry'
        ];
        $b1Ids = [];
        $b1Orig = 0.0;
        foreach ($masterTracks as $slug) {
            if (isset($cMap[$slug])) {
                $b1Ids[] = $cMap[$slug]['id'];
                $b1Orig += $cMap[$slug]['fee'];
            }
        }
        if (!empty($b1Ids)) {
            $b1Disc = 10.0; // 10%
            $b1Price = round($b1Orig * (1.0 - ($b1Disc / 100.0)), 2);
            $bundles[] = [
                'title' => 'Complete Generative AI & Agentic AI Master Bundle',
                'slug' => 'complete-genai-agentic-master-bundle',
                'tagline' => 'All 4 Core Specializations: Prompting, Multi-Agent Swarms, MCP & Azure AI Foundry',
                'description' => 'The complete package for mastering generative and agentic systems. Includes Model Context Protocol, CrewAI/LangGraph, and Azure AI Foundry with 10% massive combo savings.',
                'badge' => '⚡ Massive 10% Discount',
                'course_ids' => json_encode($b1Ids),
                'original_price' => $b1Orig,
                'bundle_price' => $b1Price,
                'discount_pct' => $b1Disc,
                'is_bogo' => 0,
                'bogo_primary_course_id' => null,
                'bogo_free_course_id' => null,
                'display_order' => 1
            ];
        }

        // Bundle 2: BOGO Launch Offer - Buy GenAI Master Track, Get Python for AI 100% Free!
        $genAiId = $cMap['generative-ai-llm-engineering']['id'] ?? ($cMap['generative-agentic-ai-master-program']['id'] ?? null);
        $pythonId = $cMap['python-for-artificial-intelligence']['id'] ?? null;
        if ($genAiId && $pythonId) {
            $genAiFee = $cMap['generative-ai-llm-engineering']['fee'] ?? 6999.00;
            $pyFee = $cMap['python-for-artificial-intelligence']['fee'] ?? 3999.00;
            $bundles[] = [
                'title' => 'BOGO Special: Buy GenAI Track, Get Python for AI 100% FREE',
                'slug' => 'bogo-genai-plus-free-python',
                'tagline' => 'Enroll in Generative AI & LLM Engineering and receive Python for AI at zero extra cost!',
                'description' => 'Perfect launching pack! Enroll in our flagship GenAI engineering track and get the complete Python for AI Foundation course completely free (Save ₹' . number_format($pyFee, 0) . ')!',
                'badge' => '🎁 BUY 1 GET 1 FREE',
                'course_ids' => json_encode([$genAiId, $pythonId]),
                'original_price' => $genAiFee + $pyFee,
                'bundle_price' => $genAiFee,
                'discount_pct' => round(($pyFee / ($genAiFee + $pyFee)) * 100, 1),
                'is_bogo' => 1,
                'bogo_primary_course_id' => $genAiId,
                'bogo_free_course_id' => $pythonId,
                'display_order' => 2
            ];
        }

        // Bundle 3: Agent Engineering & MCP Super-Pack (8% Discount)
        $agentTracks = [
            'autonomous-ai-agents-multi-agent',
            'mcp-ai-tooling-frameworks'
        ];
        $b3Ids = [];
        $b3Orig = 0.0;
        foreach ($agentTracks as $slug) {
            if (isset($cMap[$slug])) {
                $b3Ids[] = $cMap[$slug]['id'];
                $b3Orig += $cMap[$slug]['fee'];
            }
        }
        if (!empty($b3Ids)) {
            $b3Disc = 8.0; // 8%
            $b3Price = round($b3Orig * (1.0 - ($b3Disc / 100.0)), 2);
            $bundles[] = [
                'title' => 'Agent Engineering & MCP Super-Pack',
                'slug' => 'agent-engineering-mcp-superpack',
                'tagline' => 'Autonomous Swarms (CrewAI, LangGraph) + Model Context Protocol (MCP) Tooling',
                'description' => 'Targeted combo for developers who want to specialize exclusively in building and orchestrating next-generation autonomous AI agents and standard MCP servers.',
                'badge' => '🔥 8% Off Combo',
                'course_ids' => json_encode($b3Ids),
                'original_price' => $b3Orig,
                'bundle_price' => $b3Price,
                'discount_pct' => $b3Disc,
                'is_bogo' => 0,
                'bogo_primary_course_id' => null,
                'bogo_free_course_id' => null,
                'display_order' => 3
            ];
        }

        $ins = $db->prepare("
            INSERT INTO `aiverse_bundles`
            (title, slug, tagline, description, badge, course_ids, original_price, bundle_price, discount_pct, is_bogo, bogo_primary_course_id, bogo_free_course_id, status, display_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'published', ?)
        ");

        foreach ($bundles as $b) {
            $ins->execute([
                $b['title'], $b['slug'], $b['tagline'], $b['description'],
                $b['badge'], $b['course_ids'], $b['original_price'], $b['bundle_price'],
                $b['discount_pct'], $b['is_bogo'], $b['bogo_primary_course_id'], $b['bogo_free_course_id'],
                $b['display_order']
            ]);
        }
    }

    private static function seedDefaultBanners(PDO $db): void
    {
        $banners = [
            [
                'title' => '🎁 Mega Launch: Buy GenAI Track, Get Python for AI 100% FREE!',
                'subtitle' => 'Kickstart your AI engineering career. Enroll in Generative AI & LLM Engineering today and receive the complete Python for AI Foundation course at zero cost (Save ₹3,999)!',
                'badge' => '⚡ LIMITED TIME BOGO LAUNCH',
                'cta_text' => 'Claim Free Course Offer →',
                'cta_link' => '#bundles',
                'bg_gradient' => 'linear-gradient(135deg, #1e1b4b 0%, #31104b 50%, #0f172a 100%)',
                'display_order' => 1
            ],
            [
                'title' => '📦 Complete Agentic AI Master Bundle — Flat 10% Discount!',
                'subtitle' => 'Master Model Context Protocol (MCP), CrewAI agent swarms, Azure AI Foundry, and LLMOps in one comprehensive combo package with verified credentials.',
                'badge' => '🔥 MASSIVE COMBO SAVINGS',
                'cta_text' => 'Explore Bundles & Save 10% →',
                'cta_link' => '#bundles',
                'bg_gradient' => 'linear-gradient(135deg, #064e3b 0%, #0f172a 60%, #1e1b4b 100%)',
                'display_order' => 2
            ]
        ];

        $ins = $db->prepare("
            INSERT INTO `aiverse_banners`
            (title, subtitle, badge, cta_text, cta_link, bg_gradient, display_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1)
        ");

        foreach ($banners as $bn) {
            $ins->execute([
                $bn['title'], $bn['subtitle'], $bn['badge'],
                $bn['cta_text'], $bn['cta_link'], $bn['bg_gradient'],
                $bn['display_order']
            ]);
        }
    }

    // ==========================================
    // COURSES API
    // ==========================================

    public static function getCourses(string $status = 'published'): array
    {
        self::ensureSchema();
        $db = Database::connect();
        if ($status === 'all') {
            return $db->query("SELECT * FROM `aiverse_courses` ORDER BY id ASC")->fetchAll();
        }
        $stmt = $db->prepare("SELECT * FROM `aiverse_courses` WHERE status = ? ORDER BY id ASC");
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }

    public static function getCourse(int $id): ?array
    {
        self::ensureSchema();
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM `aiverse_courses` WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function saveCourse(array $d, ?int $id = null): int
    {
        self::ensureSchema();
        $db = Database::connect();

        $title = trim($d['title'] ?? '');
        $slug = trim($d['slug'] ?? '');
        if (empty($slug)) {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
            $slug = trim($slug, '-');
        }
        $tagline = trim($d['tagline'] ?? '');
        $category = trim($d['category'] ?? 'Artificial Intelligence');
        $level = trim($d['level'] ?? 'Beginner to Intermediate');
        $duration = trim($d['duration'] ?? '8 Weeks');
        $fee = (float)($d['fee'] ?? 0.0);
        $badge = trim($d['badge'] ?? 'Featured Track');
        $overview = trim($d['overview'] ?? '');
        $curriculum = trim($d['curriculum'] ?? '');
        $outcomes = trim($d['outcomes'] ?? '');
        $prerequisites = trim($d['prerequisites'] ?? '');
        $includes = trim($d['includes'] ?? '');
        $status = in_array($d['status'] ?? '', ['published', 'draft', 'archived'], true) ? $d['status'] : 'published';

        if ($id && $id > 0) {
            $stmt = $db->prepare("
                UPDATE `aiverse_courses` SET
                    title = ?, slug = ?, tagline = ?, category = ?, level = ?,
                    duration = ?, fee = ?, badge = ?, overview = ?, curriculum = ?,
                    outcomes = ?, prerequisites = ?, includes = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $title, $slug, $tagline, $category, $level,
                $duration, $fee, $badge, $overview, $curriculum,
                $outcomes, $prerequisites, $includes, $status, $id
            ]);
            return $id;
        }

        $stmt = $db->prepare("
            INSERT INTO `aiverse_courses`
            (title, slug, tagline, category, level, duration, fee, badge, overview, curriculum, outcomes, prerequisites, includes, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $title, $slug, $tagline, $category, $level,
            $duration, $fee, $badge, $overview, $curriculum,
            $outcomes, $prerequisites, $includes, $status
        ]);
        return (int)$db->lastInsertId();
    }

    public static function deleteCourse(int $id): bool
    {
        self::ensureSchema();
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM `aiverse_courses` WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ==========================================
    // BUNDLES & PROMOTIONS API
    // ==========================================

    public static function getBundles(string $status = 'published'): array
    {
        self::ensureSchema();
        $db = Database::connect();
        if ($status === 'all') {
            $sql = "SELECT * FROM `aiverse_bundles` ORDER BY display_order ASC, id ASC";
            return $db->query($sql)->fetchAll();
        }
        $stmt = $db->prepare("SELECT * FROM `aiverse_bundles` WHERE status = ? ORDER BY display_order ASC, id ASC");
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }

    public static function getBundle(int $id): ?array
    {
        self::ensureSchema();
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM `aiverse_bundles` WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function saveBundle(array $d, ?int $id = null): int
    {
        self::ensureSchema();
        $db = Database::connect();

        $title = trim($d['title'] ?? '');
        $slug = trim($d['slug'] ?? '');
        if (empty($slug)) {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
            $slug = trim($slug, '-');
        }
        $tagline = trim($d['tagline'] ?? '');
        $description = trim($d['description'] ?? '');
        $badge = trim($d['badge'] ?? 'Save 10%');
        
        $courseIds = $d['course_ids'] ?? [];
        if (is_string($courseIds)) {
            $courseIds = json_decode($courseIds, true) ?: [];
        }
        $courseIds = array_values(array_filter(array_map('intval', (array)$courseIds)));

        $originalPrice = (float)($d['original_price'] ?? 0.0);
        $bundlePrice = (float)($d['bundle_price'] ?? 0.0);
        $discountPct = (float)($d['discount_pct'] ?? 10.0);
        $isBogo = !empty($d['is_bogo']) ? 1 : 0;
        $primaryCourseId = !empty($d['bogo_primary_course_id']) ? (int)$d['bogo_primary_course_id'] : null;
        $freeCourseId = !empty($d['bogo_free_course_id']) ? (int)$d['bogo_free_course_id'] : null;
        $status = in_array($d['status'] ?? '', ['published', 'draft', 'archived'], true) ? $d['status'] : 'published';
        $displayOrder = (int)($d['display_order'] ?? 0);

        // If BOGO, make sure course_ids includes both primary and free
        if ($isBogo && $primaryCourseId && $freeCourseId) {
            $courseIds = array_unique(array_merge($courseIds, [$primaryCourseId, $freeCourseId]));
        }

        $courseIdsJson = json_encode($courseIds);

        if ($id && $id > 0) {
            $stmt = $db->prepare("
                UPDATE `aiverse_bundles` SET
                    title = ?, slug = ?, tagline = ?, description = ?, badge = ?,
                    course_ids = ?, original_price = ?, bundle_price = ?, discount_pct = ?,
                    is_bogo = ?, bogo_primary_course_id = ?, bogo_free_course_id = ?,
                    status = ?, display_order = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $title, $slug, $tagline, $description, $badge,
                $courseIdsJson, $originalPrice, $bundlePrice, $discountPct,
                $isBogo, $primaryCourseId, $freeCourseId,
                $status, $displayOrder, $id
            ]);
            return $id;
        }

        $stmt = $db->prepare("
            INSERT INTO `aiverse_bundles`
            (title, slug, tagline, description, badge, course_ids, original_price, bundle_price, discount_pct, is_bogo, bogo_primary_course_id, bogo_free_course_id, status, display_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $title, $slug, $tagline, $description, $badge,
            $courseIdsJson, $originalPrice, $bundlePrice, $discountPct,
            $isBogo, $primaryCourseId, $freeCourseId,
            $status, $displayOrder
        ]);
        return (int)$db->lastInsertId();
    }

    public static function deleteBundle(int $id): bool
    {
        self::ensureSchema();
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM `aiverse_bundles` WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getActiveBogoForCourse(int $courseId): ?array
    {
        self::ensureSchema();
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT * FROM `aiverse_bundles`
            WHERE is_bogo = 1 AND bogo_primary_course_id = ? AND status = 'published'
            LIMIT 1
        ");
        $stmt->execute([$courseId]);
        return $stmt->fetch() ?: null;
    }

    // ==========================================
    // BANNERS API
    // ==========================================

    public static function getBanners(bool $activeOnly = true): array
    {
        self::ensureSchema();
        $db = Database::connect();
        if ($activeOnly) {
            return $db->query("SELECT * FROM `aiverse_banners` WHERE is_active = 1 ORDER BY display_order ASC, id ASC")->fetchAll();
        }
        return $db->query("SELECT * FROM `aiverse_banners` ORDER BY display_order ASC, id ASC")->fetchAll();
    }

    public static function getBanner(int $id): ?array
    {
        self::ensureSchema();
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM `aiverse_banners` WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function saveBanner(array $d, ?int $id = null): int
    {
        self::ensureSchema();
        $db = Database::connect();

        $title = trim($d['title'] ?? '');
        $subtitle = trim($d['subtitle'] ?? '');
        $badge = trim($d['badge'] ?? 'Special Offer');
        $ctaText = trim($d['cta_text'] ?? 'Explore Bundles');
        $ctaLink = trim($d['cta_link'] ?? '#bundles');
        $bgGradient = trim($d['bg_gradient'] ?? 'linear-gradient(135deg, #1e1b4b 0%, #31104b 50%, #0f172a 100%)');
        $imageUrl = !empty($d['image_url']) ? trim($d['image_url']) : null;
        $displayOrder = (int)($d['display_order'] ?? 0);
        $isActive = isset($d['is_active']) ? ((int)$d['is_active'] ? 1 : 0) : 1;

        if ($id && $id > 0) {
            $stmt = $db->prepare("
                UPDATE `aiverse_banners` SET
                    title = ?, subtitle = ?, badge = ?, cta_text = ?, cta_link = ?,
                    bg_gradient = ?, image_url = ?, display_order = ?, is_active = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $title, $subtitle, $badge, $ctaText, $ctaLink,
                $bgGradient, $imageUrl, $displayOrder, $isActive, $id
            ]);
            return $id;
        }

        $stmt = $db->prepare("
            INSERT INTO `aiverse_banners`
            (title, subtitle, badge, cta_text, cta_link, bg_gradient, image_url, display_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $title, $subtitle, $badge, $ctaText, $ctaLink,
            $bgGradient, $imageUrl, $displayOrder, $isActive
        ]);
        return (int)$db->lastInsertId();
    }

    public static function deleteBanner(int $id): bool
    {
        self::ensureSchema();
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM `aiverse_banners` WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function toggleBanner(int $id, bool $isActive): bool
    {
        self::ensureSchema();
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE `aiverse_banners` SET is_active = ? WHERE id = ?");
        return $stmt->execute([$isActive ? 1 : 0, $id]);
    }

    // ==========================================
    // ENROLLMENTS & FEE LOGIC
    // ==========================================

    public static function isStudentEnrolled(int $studentUserId, int $courseId): bool
    {
        self::ensureSchema();
        $db = Database::connect();
        $stmt = $db->prepare("SELECT 1 FROM `aiverse_enrollments` WHERE student_user_id = ? AND course_id = ? AND status != 'cancelled' LIMIT 1");
        $stmt->execute([$studentUserId, $courseId]);
        return (bool)$stmt->fetchColumn();
    }

    public static function enrollStudent(int $studentUserId, int $courseId, float $amountPaid = 0.0, string $paymentMode = 'upi', bool $triggerBogo = true): array
    {
        self::ensureSchema();
        $db = Database::connect();

        $course = self::getCourse($courseId);
        if (!$course) {
            throw new \RuntimeException('Course not found');
        }

        // Check if already enrolled
        $stmt = $db->prepare("SELECT * FROM `aiverse_enrollments` WHERE student_user_id = ? AND course_id = ? AND status != 'cancelled' LIMIT 1");
        $stmt->execute([$studentUserId, $courseId]);
        $existing = $stmt->fetch();
        if ($existing) {
            return $existing;
        }

        // Get Aiverse School ID
        $aiverseSchool = School::getOrCreateAiverse();
        $schoolId = (int)($aiverseSchool['id'] ?? 0);

        // Record course fee record in student_fees
        $feeId = FeeManagement::recordFee([
            'school_id'       => $schoolId,
            'student_user_id' => $studentUserId,
            'academic_year'   => date('Y') . '-' . (date('Y') + 1),
            'fee_month'       => date('F Y'),
            'fee_type'        => 'activity',
            'amount_due'      => (float)$course['fee'],
            'amount_paid'     => $amountPaid,
            'payment_mode'    => $amountPaid > 0 ? $paymentMode : null,
            'notes'           => 'Course Enrollment: ' . $course['title'],
            'due_date'        => date('Y-m-d', strtotime('+14 days')),
        ], $studentUserId);

        $ins = $db->prepare("
            INSERT INTO `aiverse_enrollments` (student_user_id, course_id, fee_id, status, notes)
            VALUES (?, ?, ?, 'active', ?)
        ");
        $ins->execute([$studentUserId, $courseId, $feeId, 'Enrolled in ' . $course['title']]);
        $enrollmentId = (int)$db->lastInsertId();

        $grantedBogo = null;
        // BOGO Guarantee: Check if this course triggers a free companion course!
        if ($triggerBogo) {
            $bogo = self::getActiveBogoForCourse($courseId);
            if ($bogo && !empty($bogo['bogo_free_course_id'])) {
                $freeCourseId = (int)$bogo['bogo_free_course_id'];
                if (!self::isStudentEnrolled($studentUserId, $freeCourseId)) {
                    $freeCourse = self::getCourse($freeCourseId);
                    if ($freeCourse) {
                        $freeFeeId = FeeManagement::recordFee([
                            'school_id'       => $schoolId,
                            'student_user_id' => $studentUserId,
                            'academic_year'   => date('Y') . '-' . (date('Y') + 1),
                            'fee_month'       => date('F Y'),
                            'fee_type'        => 'activity',
                            'amount_due'      => (float)$freeCourse['fee'],
                            'amount_paid'     => 0.00,
                            'discount'        => (float)$freeCourse['fee'], // 100% discount
                            'status'          => 'paid',
                            'notes'           => '🎁 Free BOGO Perk with: ' . $course['title'],
                            'due_date'        => date('Y-m-d'),
                        ], $studentUserId);

                        $insFree = $db->prepare("
                            INSERT INTO `aiverse_enrollments` (student_user_id, course_id, fee_id, status, notes)
                            VALUES (?, ?, ?, 'active', ?)
                        ");
                        $insFree->execute([
                            $studentUserId, $freeCourseId, $freeFeeId,
                            '🎁 Free BOGO Perk with: ' . $course['title']
                        ]);
                        $grantedBogo = [
                            'course_id' => $freeCourseId,
                            'course_title' => $freeCourse['title']
                        ];
                    }
                }
            }
        }

        return [
            'id' => $enrollmentId,
            'course_id' => $courseId,
            'fee_id' => $feeId,
            'status' => 'active',
            'bogo_granted' => $grantedBogo
        ];
    }

    public static function enrollBundle(int $studentUserId, int $bundleId, string $paymentMode = 'upi'): array
    {
        self::ensureSchema();
        $bundle = self::getBundle($bundleId);
        if (!$bundle) {
            throw new \RuntimeException('Bundle not found');
        }

        $courseIds = json_decode($bundle['course_ids'] ?? '[]', true) ?: [];
        if (empty($courseIds)) {
            throw new \RuntimeException('This bundle contains no courses.');
        }

        $aiverseSchool = School::getOrCreateAiverse();
        $schoolId = (int)($aiverseSchool['id'] ?? 0);
        $db = Database::connect();

        $enrolled = [];
        $totalSavings = 0.0;

        if (!empty($bundle['is_bogo']) && !empty($bundle['bogo_primary_course_id']) && !empty($bundle['bogo_free_course_id'])) {
            // BOGO Bundle Enrollment
            $primaryId = (int)$bundle['bogo_primary_course_id'];
            $freeId = (int)$bundle['bogo_free_course_id'];

            // 1. Primary Course
            $res1 = self::enrollStudent($studentUserId, $primaryId, 0.0, $paymentMode, false);
            $enrolled[] = $res1;

            // 2. Free Course with 100% discount
            if (!self::isStudentEnrolled($studentUserId, $freeId)) {
                $freeCourse = self::getCourse($freeId);
                if ($freeCourse) {
                    $freeFee = (float)$freeCourse['fee'];
                    $freeFeeId = FeeManagement::recordFee([
                        'school_id'       => $schoolId,
                        'student_user_id' => $studentUserId,
                        'academic_year'   => date('Y') . '-' . (date('Y') + 1),
                        'fee_month'       => date('F Y'),
                        'fee_type'        => 'activity',
                        'amount_due'      => $freeFee,
                        'amount_paid'     => 0.00,
                        'discount'        => $freeFee, // 100% Free
                        'status'          => 'paid',
                        'notes'           => '🎁 100% FREE BOGO Bundle: ' . $bundle['title'],
                        'due_date'        => date('Y-m-d'),
                    ], $studentUserId);

                    $ins = $db->prepare("
                        INSERT INTO `aiverse_enrollments` (student_user_id, course_id, fee_id, status, notes)
                        VALUES (?, ?, ?, 'active', ?)
                    ");
                    $ins->execute([
                        $studentUserId, $freeId, $freeFeeId,
                        '🎁 Free Course with BOGO Bundle: ' . $bundle['title']
                    ]);
                    $enrolled[] = ['id' => (int)$db->lastInsertId(), 'course_id' => $freeId, 'fee_id' => $freeFeeId, 'status' => 'active'];
                    $totalSavings += $freeFee;
                }
            }
        } else {
            // Combo Discount Bundle Enrollment (e.g. 5-10% off)
            $discountPct = (float)($bundle['discount_pct'] ?? 10.0);

            foreach ($courseIds as $cid) {
                $cid = (int)$cid;
                if (self::isStudentEnrolled($studentUserId, $cid)) {
                    continue; // Skip if already enrolled
                }
                $c = self::getCourse($cid);
                if (!$c) continue;

                $originalFee = (float)$c['fee'];
                $discountAmount = round($originalFee * ($discountPct / 100.0), 2);
                $netDue = max(0.0, $originalFee - $discountAmount);
                $totalSavings += $discountAmount;

                $feeId = FeeManagement::recordFee([
                    'school_id'       => $schoolId,
                    'student_user_id' => $studentUserId,
                    'academic_year'   => date('Y') . '-' . (date('Y') + 1),
                    'fee_month'       => date('F Y'),
                    'fee_type'        => 'activity',
                    'amount_due'      => $netDue,
                    'amount_paid'     => 0.00,
                    'discount'        => $discountAmount,
                    'status'          => 'pending',
                    'notes'           => 'Bundle: ' . $bundle['title'] . " ({$discountPct}% Combo Discount Applied)",
                    'due_date'        => date('Y-m-d', strtotime('+14 days')),
                ], $studentUserId);

                $ins = $db->prepare("
                    INSERT INTO `aiverse_enrollments` (student_user_id, course_id, fee_id, status, notes)
                    VALUES (?, ?, ?, 'active', ?)
                ");
                $ins->execute([
                    $studentUserId, $cid, $feeId,
                    'Enrolled via Bundle: ' . $bundle['title']
                ]);
                $enrolled[] = ['id' => (int)$db->lastInsertId(), 'course_id' => $cid, 'fee_id' => $feeId, 'status' => 'active'];
            }
        }

        return [
            'bundle_id' => $bundleId,
            'bundle_title' => $bundle['title'],
            'enrolled_courses' => $enrolled,
            'total_savings' => $totalSavings
        ];
    }

    public static function getStudentEnrollments(int $studentUserId): array
    {
        self::ensureSchema();
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT ae.*, ac.title AS course_title, ac.slug AS course_slug, ac.duration, ac.level, ac.fee AS course_fee,
                   sf.amount_due, sf.amount_paid, sf.discount, sf.status AS payment_status, sf.receipt_number, sf.paid_at
            FROM `aiverse_enrollments` ae
            JOIN `aiverse_courses` ac ON ac.id = ae.course_id
            LEFT JOIN `student_fees` sf ON sf.id = ae.fee_id
            WHERE ae.student_user_id = ? AND ae.status != 'cancelled'
            ORDER BY ae.created_at DESC
        ");
        $stmt->execute([$studentUserId]);
        return $stmt->fetchAll();
    }

    public static function getAllEnrollments(): array
    {
        self::ensureSchema();
        $db = Database::connect();
        $sql = "
            SELECT ae.*, ac.title AS course_title, ac.fee AS course_fee,
                   u.email AS student_email, u.phone AS student_phone,
                   st.full_name AS student_name, st.city, st.district, st.state,
                   sf.amount_due, sf.amount_paid, sf.discount, sf.status AS payment_status,
                   sf.receipt_number, sf.paid_at, sf.payment_mode
            FROM `aiverse_enrollments` ae
            JOIN `aiverse_courses` ac ON ac.id = ae.course_id
            JOIN `users` u ON u.id = ae.student_user_id
            LEFT JOIN `students` st ON st.user_id = ae.student_user_id
            LEFT JOIN `student_fees` sf ON sf.id = ae.fee_id
            ORDER BY ae.created_at DESC
        ";
        return $db->query($sql)->fetchAll();
    }

    public static function getStudentAiverseFeeSummary(int $studentUserId): array
    {
        self::ensureSchema();
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT
                COUNT(ae.id) AS total_courses,
                COALESCE(SUM(sf.amount_due), 0) AS total_due,
                COALESCE(SUM(sf.amount_paid), 0) AS total_paid,
                COALESCE(SUM(CASE WHEN sf.status IN ('pending','overdue','partial') THEN (sf.amount_due - sf.discount - sf.amount_paid) ELSE 0 END), 0) AS balance_pending
            FROM `aiverse_enrollments` ae
            JOIN `student_fees` sf ON sf.id = ae.fee_id
            WHERE ae.student_user_id = ? AND ae.status != 'cancelled'
        ");
        $stmt->execute([$studentUserId]);
        $row = $stmt->fetch();
        return $row ?: [
            'total_courses' => 0,
            'total_due' => 0.0,
            'total_paid' => 0.0,
            'balance_pending' => 0.0
        ];
    }

    public static function unenrollStudent(int $studentUserId, ?int $courseId = null, ?int $feeId = null): bool
    {
        self::ensureSchema();
        $db = Database::connect();

        $enrollment = null;
        if ($feeId && $feeId > 0) {
            $stmt = $db->prepare("SELECT * FROM `aiverse_enrollments` WHERE fee_id = ? AND status != 'cancelled' LIMIT 1");
            $stmt->execute([$feeId]);
            $enrollment = $stmt->fetch();
        } 
        if (!$enrollment && $courseId && $courseId > 0) {
            $stmt = $db->prepare("SELECT * FROM `aiverse_enrollments` WHERE student_user_id = ? AND course_id = ? AND status != 'cancelled' LIMIT 1");
            $stmt->execute([$studentUserId, $courseId]);
            $enrollment = $stmt->fetch();
        }

        if (!$enrollment) {
            if ($feeId && $feeId > 0) {
                $delFee = $db->prepare("DELETE FROM `student_fees` WHERE id = ?");
                return $delFee->execute([$feeId]);
            }
            return false;
        }

        // Handle associated fee in student_fees
        $targetFeeId = !empty($enrollment['fee_id']) ? (int)$enrollment['fee_id'] : $feeId;
        if ($targetFeeId) {
            $fStmt = $db->prepare("SELECT * FROM `student_fees` WHERE id = ? LIMIT 1");
            $fStmt->execute([$targetFeeId]);
            $fee = $fStmt->fetch();
            if ($fee) {
                if ((float)$fee['amount_paid'] <= 0) {
                    $delFee = $db->prepare("DELETE FROM `student_fees` WHERE id = ?");
                    $delFee->execute([$targetFeeId]);
                } else {
                    $updFee = $db->prepare("UPDATE `student_fees` SET status = 'waived', notes = CONCAT(IFNULL(notes,''), ' [UNENROLLED]') WHERE id = ?");
                    $updFee->execute([$targetFeeId]);
                }
            }
        }

        $del = $db->prepare("DELETE FROM `aiverse_enrollments` WHERE id = ?");
        return $del->execute([(int)$enrollment['id']]);
    }

    public static function getCourseScholars(int $courseId): array
    {
        self::ensureSchema();
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT ae.*, u.email, u.phone, st.full_name, st.city, st.district, st.state,
                   sf.amount_due, sf.amount_paid, sf.status as fee_status
            FROM `aiverse_enrollments` ae
            JOIN `users` u ON u.id = ae.student_user_id
            LEFT JOIN `students` st ON st.user_id = ae.student_user_id
            LEFT JOIN `student_fees` sf ON sf.id = ae.fee_id
            WHERE ae.course_id = ? AND ae.status != 'cancelled'
            ORDER BY ae.created_at DESC
        ");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }
}
