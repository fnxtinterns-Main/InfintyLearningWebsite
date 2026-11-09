<?php
/**
 * Infinity Learning - Global Header & Navigation Template
 */
if (!defined('BPSC_PORTAL')) {
    exit('Direct access not permitted');
}

$currentUser = Auth::user();
$isLoggedIn = Auth::check();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars(AppConfig::APP_NAME); ?></title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="/assets/img/logo.png">
  <link rel="shortcut icon" type="image/png" href="/assets/img/logo.png">
  <link rel="apple-touch-icon" href="/assets/img/logo.png">
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Mammoth.js for Word (.docx) Parsing -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.6.0/mammoth.browser.min.js"></script>

  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              cream: '#FFFBF5',
              ivory: '#FFF8EE',
              peachBorder: '#FED7AA',
              peachSoft: '#FFEDD5',
              orange: '#EA580C',
              orangeHover: '#C2410C',
              orangeLight: '#FFF7ED',
              yellowPill: '#FEF08A',
              yellowText: '#854D0E',
              teal: '#0D9488',
              slateText: '#0F172A',
              muted: '#64748B'
            }
          },
          fontFamily: {
            sans: ['"Plus Jakarta Sans"', '"Inter"', 'system-ui', '-apple-system', 'sans-serif'],
            serif: ['"Plus Jakarta Sans"', '"Inter"', 'sans-serif'],
            mono: ['"Plus Jakarta Sans"', '"Inter"', 'sans-serif']
          }
        }
      }
    }
  </script>

  <style>
    body {
      background-color: #FFFBF5;
      color: #0F172A;
      font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .custom-scroll::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    .custom-scroll::-webkit-scrollbar-thumb {
      background: #FED7AA;
      border-radius: 4px;
    }
    .custom-scroll::-webkit-scrollbar-track {
      background: #FFF8EE;
    }
    .no-scrollbar::-webkit-scrollbar {
      display: none;
    }
    .no-scrollbar {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }
    /* Touch optimizations */
    @media (max-width: 640px) {
      input, select, textarea, button {
        font-size: 16px; /* Prevents auto-zoom on iOS */
      }
      .text-xs {
        font-size: 0.75rem;
      }
    }
    .card-3d-wrap {
      perspective: 1000px;
    }
    .card-3d-inner {
      transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
      transform-style: preserve-3d;
    }
    .card-3d-flipped {
      transform: rotateY(180deg);
    }
    .card-face {
      backface-visibility: hidden;
      -webkit-backface-visibility: hidden;
    }
    .card-back {
      transform: rotateY(180deg);
    }
    @keyframes marquee {
      0% { transform: translateX(100%); }
      100% { transform: translateX(-100%); }
    }
    .marquee-track {
      animation: marquee 30s linear infinite;
    }
    /* Word .docx Rendered Content Styles */
    .docx-rendered-content {
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      line-height: 1.8;
      color: #1e293b;
    }
    .docx-rendered-content table {
      width: 100%;
      border-collapse: collapse;
      margin: 1.5rem 0;
      font-size: 0.875rem;
      background: #ffffff;
      border-radius: 0.75rem;
      overflow: hidden;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .docx-rendered-content th, 
    .docx-rendered-content td {
      border: 1px solid #e2e8f0;
      padding: 0.75rem 1rem;
      text-align: left;
      vertical-align: top;
    }
    .docx-rendered-content th {
      background-color: #f8fafc;
      font-weight: 700;
      color: #0f172a;
    }
    .docx-rendered-content h1, 
    .docx-rendered-content h2, 
    .docx-rendered-content h3,
    .docx-rendered-content h4 {
      font-weight: 800;
      color: #0f172a;
      margin-top: 1.75rem;
      margin-bottom: 0.75rem;
      line-height: 1.3;
    }
    .docx-rendered-content h1 { font-size: 1.5rem; border-bottom: 2px solid #fed7aa; padding-bottom: 0.5rem; }
    .docx-rendered-content h2 { font-size: 1.25rem; color: #ea580c; }
    .docx-rendered-content h3 { font-size: 1.1rem; }
    .docx-rendered-content p {
      margin-bottom: 1rem;
    }
    .docx-rendered-content ul, 
    .docx-rendered-content ol {
      margin: 1rem 0;
      padding-left: 1.5rem;
    }
    .docx-rendered-content ul { list-style-type: disc; }
    .docx-rendered-content ol { list-style-type: decimal; }
    .docx-rendered-content li { margin-bottom: 0.5rem; }
    .docx-rendered-content img {
      max-width: 100%;
      height: auto;
      border-radius: 0.75rem;
      margin: 1rem auto;
      display: block;
    }
  </style>
</head>
<body class="min-h-screen flex flex-col bg-brand-cream selection:bg-brand-peachSoft selection:text-brand-orange">

  <!-- ========================================================= -->
  <!-- TOAST NOTIFICATION CONTAINER                              -->
  <!-- ========================================================= -->
  <div id="toastMessage" class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-6 sm:bottom-6 z-50 hidden transition-all duration-300">
    <div class="bg-slate-900 text-white px-4 py-3 sm:px-5 sm:py-4 rounded-2xl shadow-2xl border border-brand-orange/40 flex items-center gap-3 w-full sm:w-auto sm:min-w-[320px] max-w-md mx-auto">
      <div id="toastIcon" class="w-8 h-8 rounded-xl bg-brand-orange flex items-center justify-center text-white font-bold text-sm shrink-0">
        ✓
      </div>
      <div class="flex-1">
        <p class="text-[10px] uppercase font-sans font-bold text-amber-300" id="toastCategory">Notification</p>
        <p class="text-xs font-semibold leading-snug" id="toastContent">Action completed successfully.</p>
      </div>
      <button onclick="document.getElementById('toastMessage').classList.add('hidden')" class="text-slate-400 hover:text-white font-bold text-sm ml-2 cursor-pointer p-1">✕</button>
    </div>
  </div>

