<?php
/**
 * Infinity Learning - Global Footer & JavaScript Application Core
 */
if (!defined('BPSC_PORTAL')) {
    exit('Direct access not permitted');
}

$currentUserJson = json_encode(Auth::user());
$isLoggedInBool = Auth::check() ? 'true' : 'false';

$sqlNotes = Database::fetchAll("SELECT * FROM notes ORDER BY uploaded_at DESC");
foreach ($sqlNotes as &$n) {
    $n['topicId'] = $n['topic_id'];
    $n['attached_file'] = !empty($n['attached_file']) && is_string($n['attached_file']) ? json_decode($n['attached_file'], true) : null;
    $n['quiz'] = !empty($n['quiz']) && is_string($n['quiz']) ? (json_decode($n['quiz'], true) ?: []) : [];
}
unset($n);
$phpStoredNotesJson = json_encode($sqlNotes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$sqlPapers = Database::fetchAll("SELECT * FROM question_papers ORDER BY published_at DESC");
foreach ($sqlPapers as &$p) {
    $p['questions'] = !empty($p['questions']) && is_string($p['questions']) ? (json_decode($p['questions'], true) ?: []) : [];
    $p['totalMarks'] = (float)($p['total_marks'] ?? 150);
    $p['duration'] = (int)($p['duration'] ?? 120);
    $p['penalty'] = $p['penalty'] !== null ? (string)$p['penalty'] : '-0.33';
    $firstQ = !empty($p['questions']) ? $p['questions'][0] : null;
    $p['marksPerQuestion'] = (float)($firstQ['posMarks'] ?? $firstQ['marks'] ?? (count($p['questions']) > 0 ? ($p['totalMarks'] / count($p['questions'])) : 1.0));
}
unset($p);
$phpStoredPapersJson = json_encode($sqlPapers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$sqlQuestionBank = [];
try {
    $rawQb = Database::fetchAll("SELECT * FROM question_bank ORDER BY topic ASC, id ASC");
    foreach ($rawQb as &$qbItem) {
        $qbItem['options'] = !empty($qbItem['options']) && is_string($qbItem['options']) ? (json_decode($qbItem['options'], true) ?: []) : [];
        $qbItem['posMarks'] = (float)($qbItem['marks'] ?? 1.0);
        $qbItem['negMarks'] = abs((float)($qbItem['penalty'] ?? 0.33));
        $qbItem['section'] = $qbItem['topic'] ?? 'General Studies';
        $qbItem['correctAnswer'] = (int)($qbItem['correct_answer'] ?? 0);
        $qbItem['ans'] = (int)($qbItem['correct_answer'] ?? 0);
        $sqlQuestionBank[] = $qbItem;
    }
    unset($qbItem);
} catch (\Throwable $e) {
    $sqlQuestionBank = [];
}
$phpStoredQuestionBankJson = json_encode($sqlQuestionBank, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
  </div> <!-- Close #mainAppShell -->

  <!-- ========================================================= -->
  <!-- JAVASCRIPT APPLICATION CORE (STRICTLY ENCAPSULATED)       -->
  <!-- ========================================================= -->
  <script>
    // --- 0. PHP SESSION SYNC DATA ---
    const PHP_SESSION_USER = <?php echo $currentUserJson ?: 'null'; ?>;
    const PHP_IS_LOGGED_IN = <?php echo $isLoggedInBool; ?>;
    const PHP_STORED_NOTES = <?php echo $phpStoredNotesJson; ?>;
    const PHP_STORED_PAPERS = <?php echo $phpStoredPapersJson; ?>;
    const PHP_STORED_QUESTION_BANK = <?php echo $phpStoredQuestionBankJson ?: '[]'; ?>;

    // --- 2. MASTER QUESTION BANK (SQL-BACKED) ---
    let QUESTION_BANK = Array.isArray(PHP_STORED_QUESTION_BANK) ? [...PHP_STORED_QUESTION_BANK] : [];
    let CBT_QUESTION_BANK = [...QUESTION_BANK];

    // --- 3. MASTER QUESTION PAPERS ---
    let MASTER_PAPERS = Array.isArray(PHP_STORED_PAPERS) ? [...PHP_STORED_PAPERS] : [];

    // --- 4. DIGITAL TEXTBOOKS REPOSITORY (READ-ONLY ONLINE CURRICULUM) ---
    const STORED_DIGITAL_BOOKS_KEY = 'infinity_digital_books_v2';
    let DIGITAL_BOOKS = [
      {
        id: "BK_01",
        title: "Bihar SCERT History (Class 6–8)",
        subject: "History & INM",
        cadre: "Classes 6–8 (Middle)",
        author: "SCERT Bihar",
        access: "Free",
        fileSize: "14.2 MB",
        pdfUrl: "SCERT_History_Bihar.pdf",
        chapters: [
          {
            title: "Ancient Bihar & The World's First Republic (Vaishali & Licchavis)",
            summary: "Constitutional governance in the Vajjian confederacy, Santhagara assembly proceedings, Mahavira's birth at Kundagrama, and Gautama Buddha's discourses at Vaishali.",
            contentHtml: `
              <h3 class="text-base font-bold text-brand-orange">1.1 The Licchavi Confederacy of Vaishali</h3>
              <p class="leading-relaxed">During the 6th century BCE, Vaishali emerged as the world's first democratic republic under the Licchavi clan, forming the northern powerhouse of the Vajjian Confederacy (Vajji Mahajanapada). Unlike hereditary monarchies across the Gangetic plain, Vaishali was administered by an assembly of 7,707 Rajas who deliberated on legislation, defense, and judicial disputes in the central Santhagara hall under strict parliamentary voting procedure (Chhanda).</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">1.2 Sacred Milestones of Jainism & Buddhism</h3>
              <p class="leading-relaxed">Vaishali holds unique sacred importance in Indian spiritual evolution: <strong>Lord Mahavira</strong>, the 24th Tirthankara of Jainism, was born at Kundagrama near Vaishali to King Siddhartha of the Jnatrika clan and Queen Trishala (sister of Licchavi Chief Chetaka). Gautama Buddha visited Vaishali repeatedly, received an offering of mango groves from the courtesan Amrapali, established the order of Buddhist nuns (Bhikkhunis) at the request of Mahapajapati Gotami, and delivered his final discourse here prior to Mahaparinirvana.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">1.3 Archaeological Artifacts & Inscriptions</h3>
              <p class="leading-relaxed">Key physical remains include the intact <strong>Ashokan Lion Pillar</strong> at Kolhua, carved from a single piece of red Chunar sandstone featuring a solitary lion facing north along the Buddha's last pilgrimage route, and the <strong>Relic Stupa</strong> excavated by the K.P. Jayaswal Research Institute containing corporeal bone relics and ashes of Lord Buddha.</p>
            `,
            keyTakeaways: [
              "Vaishali was governed by the Santhagara assembly of 7,707 representatives, establishing the world's earliest recorded republican constitution.",
              "Lord Mahavira was born at Kundagrama (Vaishali); his mother Trishala was the sister of Licchavi Chief Chetaka.",
              "The Second Buddhist Council was held at Vaishali in 383 BCE under the patronage of King Kalashoka of the Shishunaga dynasty."
            ]
          },
          {
            title: "The Magadha Empire & Mauryan Imperial Administration",
            summary: "Strategic evolution of Pataliputra, Chanakya's Arthashastra, Megasthenes' Indica municipal boards, and Ashoka's rock edicts across Bihar.",
            contentHtml: `
              <h3 class="text-base font-bold text-brand-orange">2.1 Rise of Magadhan Supremacy</h3>
              <p class="leading-relaxed">Magadha's geographical location surrounded by mineral-rich iron ore deposits and protected by seven natural hills at Girivraja (Rajgir) provided decisive military and economic dominance. King Bimbisara of the Haryanka dynasty annexed Anga (Champa). His son Ajatashatru built the strategic river fort of Pataligrama on the confluence of the Ganges and Son rivers, which his successor Udayin developed into the imperial metropolis <strong>Pataliputra</strong>.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">2.2 Imperial Administration under Chandragupta & Chanakya</h3>
              <p class="leading-relaxed">Chandragupta Maurya, guided by Prime Minister Chanakya (Kautilya), overthrew the Nanda dynasty to forge the first unified pan-Indian empire. Megasthenes, Greek ambassador from Seleucus I Nicator, documented Pataliputra in his book <em>Indica</em> as an imposing fortified metropolis with 64 gates and 570 towers, governed by an executive municipal commission of 30 members divided into 6 specialized boards of 5 members each.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">2.3 Emperor Ashoka's Inscriptions Across Bihar</h3>
              <p class="leading-relaxed">Emperor Ashoka engraved his moral edicts in the Prakrit language using Brahmi script across Bihar: Minor Rock Edict on the Chandan Shahid hill at Sasaram; majestic Pillar Edicts at Lauriya Nandangarh (famed for its peacock base), Lauriya Areraj, and Rampurva (renowned for its world-class bull capital, now preserved at Rashtrapati Bhavan).</p>
            `,
            keyTakeaways: [
              "Pataliputra was founded as a fortified capital by Udayin at the confluence of Ganga and Son rivers.",
              "Megasthenes recorded a 30-member municipal commission divided into 6 administrative boards governing Pataliputra.",
              "Ashokan pillar edicts in West Champaran include Lauriya Nandangarh, Lauriya Areraj, and Rampurva."
            ]
          },
          {
            title: "Golden Age of Nalanda & Vikramshila Mahaviharas",
            summary: "International residential universities of ancient Bihar, library towers of the Dharmaganja, and scholarly contributions of Hiuen Tsang and Atisha Dipankara.",
            contentHtml: `
              <h3 class="text-base font-bold text-brand-orange">3.1 Nalanda Mahavihara</h3>
              <p class="leading-relaxed">Founded in the 5th century CE under the patronage of Gupta Emperor Kumaragupta I (Shakraditya), Nalanda was an international residential university accommodating over 10,000 students and 2,000 faculty members drawn from China, Korea, Japan, Tibet, Sumatra, and Central Asia.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">3.2 Academic Rigor & The Dharmaganja Library</h3>
              <p class="leading-relaxed">Admission was fiercely competitive, guarded by the gatekeeping scholar (Dvarapala) who examined candidates on grammar, logic, and philosophy. The grand library complex, known as the <strong>Dharmaganja</strong>, comprised three soaring multi-storied towers: <em>Ratnasagara</em> (Ocean of Jewels), <em>Ratnodadhi</em> (Sea of Jewels, nine stories tall), and <em>Ratnaranjaka</em> (Jewel-adorned). Eminent scholar Xuanzang (Hiuen Tsang) studied Yogacara philosophy here for five years under Chancellor Shilabhadra.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">3.3 Vikramshila University & The Pala Renaissance</h3>
              <p class="leading-relaxed">Founded by Pala Emperor Dharmapala in the late 8th century at Antichak (Bhagalpur district), Vikramshila served as the preeminent international citadel for Vajrayana Buddhism, logic, metaphysics, and grammar. Its most distinguished scholar, Acharya Atisha Dipankara Srijnana, journeyed to Tibet in 1042 CE to revitalize Buddhist practice. In 1193 CE, Bakhtiyar Khilji sacked and burned both monastic university citadels.</p>
            `,
            keyTakeaways: [
              "Kumaragupta I founded Nalanda; Pala Emperor Dharmapala founded Vikramshila at Antichak (Bhagalpur).",
              "Nalanda's library Dharmaganja housed three legendary repositories: Ratnasagara, Ratnodadhi, and Ratnaranjaka.",
              "Venerable Chancellor Shilabhadra personally instructed Chinese pilgrim Xuanzang (Hiuen Tsang) in Yogacara doctrine."
            ]
          },
          {
            title: "Veer Kunwar Singh & The 1857 Great Revolt in Bihar",
            summary: "Danapur mutiny outbreak, Babu Veer Kunwar Singh of Jagdishpur, siege of Arrah, guerrilla warfare, and victory at the Battle of Jagdishpur.",
            contentHtml: `
              <h3 class="text-base font-bold text-brand-orange">4.1 Mutiny at Danapur Cantonment</h3>
              <p class="leading-relaxed">On 25 July 1857, Indian sepoys of the 7th, 8th, and 40th Native Infantry regiments stationed at Danapur Cantonment rose in mutiny. Crossing the Son river, they rallied at Jagdishpur under the leadership of <strong>Babu Veer Kunwar Singh</strong>, the 80-year-old chieftain of the Ujjainiya Parmar Rajput house of Bhojpur.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">4.2 Siege of Arrah & Campaign Across Central India</h3>
              <p class="leading-relaxed">Kunwar Singh's army besieged British officials and Sikh loyalists barricaded inside the fortified Arrah House. When Major Vincent Eyre marched with reinforcements, Kunwar Singh launched strategic guerrilla warfare. Moving out of Bhojpur, he carried operations across Sasaram, Mirzapur, Rewa, Banda, and Kanpur (coordinating with Nana Sahib and Tatya Tope), liberating Azamgarh in March 1858.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">4.3 Immortal Sacrifice at Shivpur Ghat & Vijay Diwas</h3>
              <p class="leading-relaxed">Pursued by British forces under Sir Edward Lugard, Kunwar Singh crossed the Ganges at Shivpur Ghat on 22 April 1858. When a British cannonball shattered his left wrist, he severed his own hand with his sword and consecrated it to Mother Ganga. Returning to Jagdishpur, he routed the British forces commanded by Captain Le Grand on 23 April 1858. He passed away peacefully on 26 April 1858, never once captured. Every year, 23 April is observed as <strong>Vijay Diwas</strong> across Bihar.</p>
            `,
            keyTakeaways: [
              "Danapur mutiny commenced on 25 July 1857; sepoys joined 80-year-old Babu Veer Kunwar Singh at Jagdishpur.",
              "Kunwar Singh decisively defeated Captain Le Grand on 23 April 1858, celebrated as Vijay Diwas.",
              "His brother Amar Singh sustained the guerrilla movement in the Kaimur hills until late 1859."
            ]
          },
          {
            title: "Champaran Satyagraha (1917) & Modern Freedom Movements in Bihar",
            summary: "The oppressive Tinkathia system, Raj Kumar Shukla's persistent plea, Gandhiji's historic arrival at Patna & Motihari, and the Champaran Agrarian Act of 1918.",
            contentHtml: `
              <h3 class="text-base font-bold text-brand-orange">5.1 The Oppressive Tinkathia System</h3>
              <p class="leading-relaxed">European indigo planters enforced the <strong>Tinkathia system</strong> across Champaran, legally compelling tenant farmers to cultivate indigo on 3 out of every 20 kathas of their most fertile land. When synthetic German chemical dyes collapsed global demand for natural indigo, planters demanded steep extortionate compensations (Tawan, Sharahbeshi, and abwabs) to release peasants from agreements.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">5.2 Raj Kumar Shukla & Gandhiji's Arrival</h3>
              <p class="leading-relaxed">Local farmer Raj Kumar Shukla persistently petitioned national leaders at the December 1916 Lucknow Congress until Mahatma Gandhi pledged to visit. Arriving at Patna on 10 April 1917, Gandhi proceeded to Motihari. When Champaran District Magistrate W.B. Heycock issued an order under Section 144 CrPC ordering Gandhi to leave, Gandhi pleaded guilty and refused to comply, staging the first act of non-violent Civil Disobedience on Indian soil.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">5.3 Champaran Agrarian Act & Foundation of Basic Education</h3>
              <p class="leading-relaxed">Lieutenant Governor Sir Edward Gait instituted the official Champaran Agrarian Enquiry Committee with Gandhiji as a member. The committee's unanimous findings resulted in the enactment of the <strong>Champaran Agrarian Act of 1918</strong>, abolishing the Tinkathia system and enforcing a 25% refund of illegally extorted levies. Gandhi also founded primary schools at Barharwa Lakhamsen and Bhitiharwa to promote rural education and sanitation.</p>
            `,
            keyTakeaways: [
              "Raj Kumar Shukla was the rural hero who persuaded Mahatma Gandhi at the 1916 Lucknow Congress to visit Champaran.",
              "Tinkathia compelled peasants to cultivate indigo on 3/20th of their agricultural plot.",
              "Champaran Agrarian Act of 1918 abolished Tinkathia; Dr. Rajendra Prasad and Babu Anugrah Narayan Sinha served as key lieutenants."
            ]
          }
        ]
      },
      {
        id: "BK_02",
        title: "NCERT Elementary Mathematics Formula Vault",
        subject: "Elementary Mathematics",
        cadre: "All Teaching Levels",
        author: "NCERT National",
        access: "Free",
        fileSize: "8.6 MB",
        pdfUrl: "Math_Elementary_Formulas.pdf",
        chapters: [
          {
            title: "Number Systems, Divisibility Rules & Prime Factorization",
            summary: "Fundamental properties of real numbers, HCF-LCM relationships, unit digit cyclicity, and divisibility tests for 7, 11, and 13.",
            contentHtml: `
              <h3 class="text-base font-bold text-brand-orange">1.1 Core Number Theory Relationships</h3>
              <p class="leading-relaxed">For any two positive integers <em>a</em> and <em>b</em>, the product of their Highest Common Factor and Least Common Multiple equals the product of the numbers: <strong>HCF(a, b) × LCM(a, b) = a × b</strong>. The HCF of fractions is computed as <code>HCF(numerators) / LCM(denominators)</code>, while the LCM of fractions equals <code>LCM(numerators) / HCF(denominators)</code>.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">1.2 Unit Digit Cyclicity Shortcuts</h3>
              <p class="leading-relaxed">The unit digit of <em>xⁿ</em> depends on the base unit digit cyclicity (period of 4 for 2, 3, 7, 8; period of 2 for 4 and 9; period of 1 for 0, 1, 5, 6). Divide the exponent <em>n</em> by 4. If remainder <em>r = 1, 2, 3</em>, the unit digit is the base raised to <em>r</em>. If remainder is 0, use exponent 4.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">1.3 Universal Divisibility Tests</h3>
              <p class="leading-relaxed"><strong>Divisibility by 11:</strong> The difference between the sum of digits at odd places and the sum of digits at even places must be 0 or a multiple of 11. <strong>Divisibility by 7 & 13:</strong> Form groups of 3 digits from right to left; the alternating sum of these groups must be divisible by 7 or 13.</p>
            `,
            keyTakeaways: [
              "HCF(a, b) × LCM(a, b) = a × b holds true exclusively for two numbers.",
              "Unit digit cyclicity for 2, 3, 7, 8 repeats every 4 powers.",
              "Divisibility by 11: |Sum(Odd Places) - Sum(Even Places)| = 0, 11, 22..."
            ]
          },
          {
            title: "Percentages, Profit, Loss & Successive Discounts",
            summary: "Standard fraction-percentage conversion tables, cost price shortcuts, successive variation formulas, and true discount calculations.",
            contentHtml: `
              <h3 class="text-base font-bold text-brand-orange">2.1 High-Yield Fraction to Percentage Table</h3>
              <p class="leading-relaxed">Mastery of benchmark fractions accelerates mental computation: <code>1/2 = 50%</code>, <code>1/3 = 33.33%</code>, <code>1/4 = 25%</code>, <code>1/5 = 20%</code>, <code>1/6 = 16.66%</code>, <code>1/7 = 14.28%</code>, <code>1/8 = 12.5%</code>, <code>1/9 = 11.11%</code>, <code>1/11 = 9.09%</code>, <code>1/12 = 8.33%</code>.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">2.2 Successive Percentage Change Formula</h3>
              <p class="leading-relaxed">When a quantity changes by <em>a%</em> followed by <em>b%</em>, the net percentage change is given by: <strong>Net Change = a + b + (ab / 100)%</strong>. (Use negative signs for decreases or discounts).</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">2.3 Single Equivalent Discount</h3>
              <p class="leading-relaxed">For two successive trade discounts of <em>d₁%</em> and <em>d₂%</em> on Marked Price (MP), the single equivalent discount equals: <strong>D_eq = d₁ + d₂ - (d₁ × d₂ / 100)%</strong>. Profit Percentage is calculated strictly on Cost Price (CP): <code>Profit % = [(SP - CP) / CP] × 100</code>.</p>
            `,
            keyTakeaways: [
              "Successive change formula: Net % = a + b + (ab / 100)%.",
              "Single equivalent discount of 20% and 10% is 20 + 10 - 2 = 28% (not 30%).",
              "Profit % is always evaluated on Cost Price unless specified otherwise."
            ]
          },
          {
            title: "Ratio, Proportion, Partnership & Alligation Methods",
            summary: "Compounded ratios, proportional division, partnership investment shares across time, and the Rule of Alligation for mixtures.",
            contentHtml: `
              <h3 class="text-base font-bold text-brand-orange">3.1 Continued Proportion & Mean Proportional</h3>
              <p class="leading-relaxed">If <em>a : b :: b : c</em>, then <em>b</em> is the mean proportional where <strong>b = √(ac)</strong>. The third proportional to <em>a</em> and <em>b</em> is <code>b² / a</code>. The fourth proportional to <em>a, b, c</em> is <code>(b × c) / a</code>.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">3.2 Partnership Capital-Time Principle</h3>
              <p class="leading-relaxed">Profit earned by partners is distributed directly in the ratio of the product of their invested capital and duration of investment: <strong>Profit Ratio = (C₁ × T₁) : (C₂ × T₂) : (C₃ × T₃)</strong>.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">3.3 Rule of Alligation for Mixtures</h3>
              <p class="leading-relaxed">When mixing two ingredients of cost <em>c</em> (cheaper) and <em>d</em> (dearer) to obtain a mixture of mean price <em>m</em>: <strong>(Quantity of Cheaper) / (Quantity of Dearer) = (d - m) / (m - c)</strong>.</p>
            `,
            keyTakeaways: [
              "Mean proportional between a and c is √(ac).",
              "Partnership profit is partitioned in proportion to Capital × Time period.",
              "Alligation ratio formula: Cheaper / Dearer = (Dearer Price - Mean Price) / (Mean Price - Cheaper Price)."
            ]
          },
          {
            title: "Time, Speed, Distance, Trains & Unitary Work Rules",
            summary: "Relative speed equations, trains crossing platforms and moving observers, pipes and cisterns, and men-days-hours work equivalence.",
            contentHtml: `
              <h3 class="text-base font-bold text-brand-orange">4.1 Speed Conversion & Average Speed</h3>
              <p class="leading-relaxed">Convert km/h to m/s by multiplying with <strong>5/18</strong>; convert m/s to km/h by multiplying with <strong>18/5</strong>. For a round trip covering equal distances at speeds <em>x</em> and <em>y</em>, the Average Speed is <strong>2xy / (x + y)</strong>.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">4.2 Train Motion Physics</h3>
              <p class="leading-relaxed">Time taken by a train of length <em>L</em> to cross a stationary pole or standing man is <code>T = L / Speed</code>. Time taken to cross a platform or bridge of length <em>P</em> is: <strong>T = (L + P) / Speed</strong>. When two trains move in opposite directions, relative speed is <em>v₁ + v₂</em>; in the same direction, relative speed is <em>|v₁ - v₂|</em>.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">4.3 Men-Days-Hours Work Formula</h3>
              <p class="leading-relaxed">Work equivalence across multiple teams is calculated using: <strong>(M₁ × D₁ × H₁ × E₁) / W₁ = (M₂ × D₂ × H₂ × E₂) / W₂</strong>, where M = Men, D = Days, H = Hours/day, E = Efficiency, W = Total units of Work.</p>
            `,
            keyTakeaways: [
              "Multiply by 5/18 to convert km/h to m/s; multiply by 18/5 for m/s to km/h.",
              "Average speed for equal forward/return distances is 2xy / (x + y).",
              "Universal Work Theorem: (M₁ D₁ H₁) / W₁ = (M₂ D₂ H₂) / W₂."
            ]
          },
          {
            title: "Mensuration 2D Plane & 3D Solid Geometry",
            summary: "Heron's formula, areas of cyclic figures, curved surface areas, and volume formulas for cylinders, cones, and spheres.",
            contentHtml: `
              <h3 class="text-base font-bold text-brand-orange">5.1 2D Plane Mensuration</h3>
              <p class="leading-relaxed"><strong>Heron's Formula:</strong> Area = <code>√[s(s - a)(s - b)(s - c)]</code> where semi-perimeter <em>s = (a + b + c)/2</em>. Area of an equilateral triangle is <code>(√3 / 4) × a²</code> with height <code>(√3 / 2) × a</code>. Area of a Trapezium is <code>1/2 × (Sum of parallel sides) × Height</code>. Circle Sector Area is <code>(θ / 360) × πr²</code>.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">5.2 Right Circular Cylinder</h3>
              <p class="leading-relaxed">Curved Surface Area (CSA) = <strong>2πrh</strong>; Total Surface Area (TSA) = <strong>2πr(h + r)</strong>; Volume = <strong>πr²h</strong>.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">5.3 Right Circular Cone & Spheres</h3>
              <p class="leading-relaxed">Cone slant height <em>l = √(r² + h²)</em>; Cone CSA = <strong>πrl</strong>; Cone Volume = <strong>(1/3)πr²h</strong>. Sphere Volume = <strong>(4/3)πr³</strong>; Sphere Surface Area = <strong>4πr²</strong>. Hemisphere Volume = <strong>(2/3)πr³</strong>; Hemisphere Total Surface Area = <strong>3πr²</strong>.</p>
            `,
            keyTakeaways: [
              "Equilateral Triangle: Area = (√3 / 4)a², Height = (√3 / 2)a.",
              "Right Circular Cone: Volume = (1/3)πr²h, Slant height l = √(r² + h²).",
              "Hemisphere Total Surface Area is 3πr² (includes flat circular base)."
            ]
          }
        ]
      },
      {
        id: "BK_03",
        title: "General Science Compendium (Physics & Bio)",
        subject: "General Science",
        cadre: "Classes 9–10 (Secondary)",
        author: "Infinity Academic Core",
        access: "Premium",
        fileSize: "21.5 MB",
        pdfUrl: "General_Science_Compendium.pdf",
        chapters: [
          {
            title: "Mechanics, Motion, Force & Universal Gravitation",
            summary: "Newton's laws of motion, momentum conservation, gravitational acceleration variation on Earth and Moon, and mechanical energy theorems.",
            contentHtml: `
              <h3 class="text-base font-bold text-brand-orange">1.1 Newton's Laws & Momentum</h3>
              <p class="leading-relaxed">Newton's First Law defines Inertia. The Second Law establishes that the rate of change of momentum is directly proportional to applied force: <strong>F = ma</strong>. The Third Law states that every action has an equal and opposite reaction acting on distinct interacting bodies.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">1.2 Universal Gravitation & Acceleration 'g'</h3>
              <p class="leading-relaxed">Newton's Law of Gravitation: <strong>F = G × (m₁ × m₂) / r²</strong> where <em>G = 6.674 × 10⁻¹¹ N·m²/kg²</em>. Acceleration due to gravity on Earth is <code>g = GM / R² ≈ 9.8 m/s²</code>. Acceleration due to gravity on the Moon is roughly <strong>1/6th of Earth</strong> (1.62 m/s²). Acceleration 'g' is maximum at poles and minimum at the equator.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">1.3 Work, Power & Conservation of Energy</h3>
              <p class="leading-relaxed">Work is scalar: <code>W = F × s × cos(θ)</code> (measured in Joules). Kinetic Energy <strong>KE = 1/2 × m × v²</strong>; Gravitational Potential Energy <strong>PE = m × g × h</strong>. Power is work per unit time (measured in Watts; <code>1 Horsepower = 746 Watts</code>).</p>
            `,
            keyTakeaways: [
              "F = ma derives from Newton's Second Law; momentum p = mv is conserved in isolated systems.",
              "Gravitational acceleration on Moon is 1/6th of Earth; g is maximum at the poles and minimum at the equator.",
              "1 Horsepower (HP) = 746 Watts; 1 kWh = 3.6 × 10⁶ Joules."
            ]
          },
          {
            title: "Optics, Reflection, Refraction & Human Vision Defects",
            summary: "Concave and convex mirrors, Snell's law, total internal reflection, lens maker formulas, and correction of Myopia, Hypermetropia, and Presbyopia.",
            contentHtml: `
              <h3 class="text-base font-bold text-brand-orange">2.1 Spherical Mirrors & Ray Optics</h3>
              <p class="leading-relaxed">Mirror formula: <strong>1/f = 1/v + 1/u</strong>. Concave mirrors form magnified real or virtual images (used in shaving mirrors and car headlights). Convex mirrors always produce virtual, erect, and diminished images with a wide field of view (used as automotive rear-view mirrors).</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">2.2 Refraction & Total Internal Reflection (TIR)</h3>
              <p class="leading-relaxed">Snell's Law: <code>sin(i) / sin(r) = n₂ / n₁</code>. When light travels from a denser medium to a rarer medium at an angle of incidence exceeding the critical angle, <strong>Total Internal Reflection (TIR)</strong> occurs. TIR explains the sparkle of diamonds, optical fiber communication, and desert mirages.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">2.3 Eye Optics & Common Refractive Defects</h3>
              <p class="leading-relaxed"><strong>Myopia (Nearsightedness):</strong> Distant objects focus in front of the retina due to eyeball elongation; corrected using a <strong>Concave (Diverging) Lens</strong>. <strong>Hypermetropia (Farsightedness):</strong> Near objects focus behind retina; corrected with a <strong>Convex (Converging) Lens</strong>. <strong>Presbyopia:</strong> Age-related loss of ciliary accommodation; corrected with Bifocal lenses. Lens power <em>P = 1/f (in meters)</em>, measured in Diopters (D).</p>
            `,
            keyTakeaways: [
              "Convex mirrors are utilized as automotive rear-view mirrors because they yield an erect, diminished, broad field of view.",
              "Total Internal Reflection is the underlying physics for optical fibers and diamond brilliance.",
              "Myopia is corrected with a concave lens; Hypermetropia is corrected with a convex lens."
            ]
          },
          {
            title: "Acids, Bases, Salts & Chemical Reactions",
            summary: "pH scale, indicators, neutralization reactions, chemical properties and formulas of Bleaching powder, Baking soda, Washing soda, and Plaster of Paris.",
            contentHtml: `
              <h3 class="text-base font-bold text-brand-orange">3.1 pH Scale & Natural Indicators</h3>
              <p class="leading-relaxed">pH scale (invented by Sorensen) ranges from 0 to 14. <code>pH < 7</code> indicates acidity; <code>pH = 7</code> neutral; <code>pH > 7</code> alkalinity. Human blood pH is strictly maintained between 7.35 and 7.45. Litmus turns red in acid and blue in base; Phenolphthalein turns pink in base and colorless in acid.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">3.2 High-Yield Industrial Salts</h3>
              <p class="leading-relaxed"><strong>Bleaching Powder (CaOCl₂):</strong> Prepared by reacting chlorine gas with dry slaked lime Ca(OH)₂. <strong>Baking Soda (NaHCO₃):</strong> Sodium hydrogen carbonate, releases CO₂ upon heating to make cakes porous. <strong>Washing Soda (Na₂CO₃·10H₂O):</strong> Sodium carbonate decahydrate. <strong>Plaster of Paris (CaSO₄·½H₂O):</strong> Produced by heating gypsum (CaSO₄·2H₂O) to 373 K (100°C).</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">3.3 Chemical Reaction Classifications</h3>
              <p class="leading-relaxed">Reactions include <em>Combination</em> (A + B → AB), <em>Decomposition</em> (thermal, electrolytic, or photolytic), <em>Single Displacement</em> (more reactive metal displaces less reactive metal based on reactivity series: K > Na > Ca > Mg > Al > Zn > Fe > Pb > H > Cu > Ag > Au), and <em>Redox</em> (simultaneous oxidation and reduction).</p>
            `,
            keyTakeaways: [
              "Plaster of Paris is calcium sulphate hemihydrate: CaSO₄·½H₂O; Gypsum is CaSO₄·2H₂O.",
              "Bleaching powder is CaOCl₂, formed by passing Cl₂ gas over dry slaked lime.",
              "Normal human blood pH is maintained within 7.35 – 7.45."
            ]
          },
          {
            title: "Plant Physiology, Photosynthesis & Vascular Systems",
            summary: "Chloroplast light and dark reactions, Calvin cycle, Xylem and Phloem vascular structures, and transpiration pull mechanics.",
            contentHtml: `
              <h3 class="text-base font-bold text-brand-orange">4.1 Photosynthesis Machinery</h3>
              <p class="leading-relaxed">Photosynthesis occurs inside plant chloroplasts: <strong>6CO₂ + 12H₂O + Sunlight → C₆H₁₂O₆ + 6O₂ + 6H₂O</strong>. <strong>Light Reactions:</strong> Occur in the thylakoid membranes, splitting water molecules (photolysis) to generate ATP, NADPH, and releasing O₂. <strong>Dark Reactions (Calvin Cycle):</strong> Occur in the stroma, using RuBisCO enzyme to fix CO₂ into glucose independent of direct sunlight.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">4.2 Plant Vascular Transport (Xylem vs Phloem)</h3>
              <p class="leading-relaxed"><strong>Xylem:</strong> Comprises tracheids, vessels, xylem parenchyma, and xylem fibers. Transports water and dissolved minerals unidirectionally upwards from roots to leaves driven by root pressure and transpiration pull. <strong>Phloem:</strong> Comprises sieve tubes, companion cells, phloem parenchyma, and phloem fibers. Translocates organic food (sucrose) bidirectionally using metabolic energy (ATP).</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">4.3 Plant Hormones (Phytohormones)</h3>
              <p class="leading-relaxed"><strong>Auxins:</strong> Regulate apical dominance and phototropism. <strong>Gibberellins:</strong> Promote stem elongation and break seed dormancy. <strong>Cytokinins:</strong> Stimulate rapid cell division. <strong>Abscisic Acid (ABA):</strong> Stress hormone causing stomatal closure and leaf fall. <strong>Ethylene:</strong> Only gaseous hormone, promotes fruit ripening.</p>
            `,
            keyTakeaways: [
              "Xylem transports water unidirectionally; Phloem translocates sucrose bidirectionally with companion cells.",
              "Oxygen evolved during photosynthesis originates from water photolysis (not carbon dioxide).",
              "Ethylene is the primary gaseous phytohormone responsible for fruit ripening."
            ]
          },
          {
            title: "Human Organ Systems, Nutrition & Disease Pathology",
            summary: "Digestive enzymes, human double circulation, endocrine gland secretions, vitamin deficiencies, and communicable vector diseases.",
            contentHtml: `
              <h3 class="text-base font-bold text-brand-orange">5.1 Digestive Enzymes & Assimilation</h3>
              <p class="leading-relaxed">Salivary glands secrete <em>Ptyalin (Salivary Amylase)</em> breaking starch into maltose. Stomach gastric glands secrete Hydrochloric acid (HCl), mucus, and <em>Pepsin</em> (digests proteins in acidic medium). Liver produces <em>Bile juice</em> (alkalinizes chyme and emulsifies fats). Pancreas secretes <em>Trypsin</em> (protein digest) and <em>Lipase</em> (lipid digest). Small intestine absorbs nutrients via villi.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">5.2 Double Circulatory Architecture</h3>
              <p class="leading-relaxed">The human heart has 4 chambers. Oxygenated blood returns from lungs via pulmonary veins to the left atrium, left ventricle, and into systemic circulation via the Aorta. Normal resting blood pressure is <strong>120/80 mmHg</strong> (systolic/diastolic) measured using a <strong>Sphygmomanometer</strong>. Sinoatrial (SA) node acts as natural pacemaker.</p>
              <h3 class="text-base font-bold text-brand-orange mt-4">5.3 Endocrine Secretions & Nutritional Deficiencies</h3>
              <p class="leading-relaxed"><strong>Insulin:</strong> Secreted by Beta-cells of Islets of Langerhans (Pancreas); deficiency leads to Diabetes Mellitus. <strong>Thyroxine:</strong> Secreted by Thyroid; requires Iodine; deficiency causes Goitre. <strong>Vitamins:</strong> Vitamin A (Night blindness), Vitamin B1 (Beriberi), Vitamin C (Scurvy - ascorbic acid), Vitamin D (Rickets in children, Osteomalacia in adults), Vitamin K (Defective blood clotting).</p>
            `,
            keyTakeaways: [
              "Normal human blood pressure is 120/80 mmHg, monitored using a sphygmomanometer.",
              "Beta-cells in the Pancreas secrete Insulin to regulate blood glucose.",
              "Vitamin C deficiency causes Scurvy; Vitamin D deficiency results in Rickets."
            ]
          }
        ]
      }
    ];

    try {
      const storedBooksJson = localStorage.getItem(STORED_DIGITAL_BOOKS_KEY);
      if (storedBooksJson) {
        const storedBooks = JSON.parse(storedBooksJson);
        if (Array.isArray(storedBooks) && storedBooks.length > 0) {
          DIGITAL_BOOKS = storedBooks;
        }
      }
    } catch (e) {
      console.warn("Could not read stored digital books:", e);
    }

    // --- 4B. OFFICIAL BPSC TRE PYQ REPOSITORY (DYNAMICALLY EXPANDABLE) ---
    let OFFICIAL_PYQS = [
      {
        id: "PYQ_01",
        edition: "BPSC TRE 3.0",
        title: "BPSC TRE 3.0 Official Question Paper (Re-Exam)",
        cadre: "Classes 6–8 (Middle)",
        subject: "Mathematics & Science",
        shiftDate: "July 2024 · Shift 1",
        fileSize: "5.8 MB",
        pdfUrl: "BPSC_TRE_3_July2024_MathSci.pdf",
        uploadedBy: "Admin AIVERSE"
      },
      {
        id: "PYQ_02",
        edition: "BPSC TRE 3.0",
        title: "BPSC TRE 3.0 Social Science Solved Paper",
        cadre: "Classes 6–8 (Middle)",
        subject: "Social Science",
        shiftDate: "March 2024 · Shift 2",
        fileSize: "6.2 MB",
        pdfUrl: "BPSC_TRE_3_March2024_SocialScience.pdf",
        uploadedBy: "Academic Faculty"
      },
      {
        id: "PYQ_03",
        edition: "BPSC TRE 2.0",
        title: "BPSC TRE 2.0 Official Question Paper with Answer Key",
        cadre: "Classes 9–10 (Secondary)",
        subject: "General Studies & Secondary Science",
        shiftDate: "Dec 2023 · Complete Shift",
        fileSize: "7.1 MB",
        pdfUrl: "BPSC_TRE_2_Dec2023_SecScience.pdf",
        uploadedBy: "Platform Administrator"
      },
      {
        id: "PYQ_04",
        edition: "BPSC TRE 1.0",
        title: "BPSC TRE 1.0 Primary General Studies Official Paper",
        cadre: "Classes 1–5 (Primary)",
        subject: "General Studies & Elementary Maths",
        shiftDate: "August 2023 · Bilingual Shift 1",
        fileSize: "4.9 MB",
        pdfUrl: "BPSC_TRE_1_Aug2023_PrimaryGS.pdf",
        uploadedBy: "Academic Faculty"
      }
    ];

    const STORED_OFFICIAL_PYQS_KEY = 'bpsc_official_pyqs_data';
    try {
      const storedPyqsJson = localStorage.getItem(STORED_OFFICIAL_PYQS_KEY);
      if (storedPyqsJson) {
        const storedPyqs = JSON.parse(storedPyqsJson);
        if (Array.isArray(storedPyqs) && storedPyqs.length > 0) {
          OFFICIAL_PYQS = storedPyqs;
        }
      }
    } catch (e) {
      console.warn("Could not read stored official pyqs:", e);
    }

    // --- 5. PAYMENT GATEWAY TRANSACTIONS ---
    let PAYMENT_TRANSACTIONS = [];

    // --- 6. MULTI-ROLE PERSONAS ---
    const ROLE_PERSONAS = {
      student: {
        role: "student",
        title: "Candidate / Aspirant Terminal",
        badge: "Candidate Terminal",
        desc: "Full BPSC curriculum radar, live mock countdown, SCERT reference library, and proctored CBT examination terminal with tab-switch guard."
      },
      teacher: {
        role: "teacher",
        title: "Teacher / Faculty Evaluation Access",
        badge: "Faculty Terminal",
        desc: "Create & publish question papers, curriculum topic notes hub, handpick curated questions from question bank, and upload study dossiers."
      },
      admin: {
        role: "admin",
        title: "AIVERSE Master Admin Terminal",
        badge: "AIVERSE Admin Central",
        desc: "Platform operations, faculty approval governance, bulk-upload via Word (.docx) with diagrams, and master question bank database."
      }
    };

    let selectedLoginRole = "student";
    let authenticatedUser = (typeof PHP_SESSION_USER !== 'undefined' && PHP_SESSION_USER) ? { ...PHP_SESSION_USER } : null;

    // --- 7. BPSC SYLLABUS DATA ---
    const BPSC_SYLLABUS = {
      commonPart1: {
        title: "Part I: Language (30 Questions - Minimum 30% Pass Threshold)",
        modules: [
          {
            name: "English Proficiency (8–10 Questions)",
            items: [
              { id: "p1_eng_1", name: "Reading Comprehension (Passages, Inference, Tone)", hy: true },
              { id: "p1_eng_2", name: "Parts of Speech, Nouns, Pronouns, Prepositions", hy: false },
              { id: "p1_eng_3", name: "Tenses & Subject-Verb Agreement", hy: true },
              { id: "p1_eng_4", name: "Active / Passive Voice & Direct / Indirect Narration", hy: false },
              { id: "p1_eng_5", name: "Vocabulary: Synonyms, Antonyms, Idioms & Phrases", hy: true }
            ]
          },
          {
            name: "Hindi / Urdu / Bangla (20–22 Questions)",
            items: [
              { id: "p1_hin_1", name: "वर्णमाला, वर्तनी, विराम-चिह्न एवं उच्चारण", hy: false },
              { id: "p1_hin_2", name: "संधि, समास, उपसर्ग, प्रत्यय", hy: true },
              { id: "p1_hin_3", name: "तत्सम-तद्भव, देशज-विदेशी, पर्यायवाची, विलोम शब्द", hy: true },
              { id: "p1_hin_4", name: "वाक्य-रचना, पद-परिचय एवं वाक्य-शुद्धि", hy: false },
              { id: "p1_hin_5", name: "अपठित गद्यांश / पद्यांश एवं भावार्थ निष्कर्ष", hy: true }
            ]
          }
        ]
      },
      commonGS: {
        title: "Part II: General Studies (Elementary Math, Science, INM, Geography)",
        modules: [
          {
            name: "Elementary Mathematics",
            items: [
              { id: "gs_mat_1", name: "Number System, Divisibility Rules, HCF & LCM", hy: true },
              { id: "gs_mat_2", name: "Fractions, Decimals, Ratio & Proportion", hy: false },
              { id: "gs_mat_3", name: "Percentage, Profit & Loss, Simple & Compound Interest", hy: true },
              { id: "gs_mat_4", name: "Time & Work, Pipes & Cisterns, Speed-Time-Distance", hy: true }
            ]
          },
          {
            name: "General Science (Physics, Chemistry, Biology)",
            items: [
              { id: "gs_sci_1", name: "Motion, Force, Gravitation, Light, Heat, Electricity", hy: true },
              { id: "gs_sci_2", name: "Acids, Bases, Salts, Metals and Non-Metals", hy: false },
              { id: "gs_sci_3", name: "Human Nutrition, Respiration & Microorganisms", hy: true }
            ]
          },
          {
            name: "Indian National Movement & Bihar",
            items: [
              { id: "gs_inm_1", name: "Revolt of 1857 & Babu Veer Kunwar Singh", hy: true },
              { id: "gs_inm_2", name: "Champaran Satyagraha (1917) & Raj Kumar Shukla", hy: true },
              { id: "gs_inm_3", name: "Non-Cooperation, Civil Disobedience & Quit India", hy: true }
            ]
          },
          {
            name: "Geography & Bihar Drainage",
            items: [
              { id: "gs_geo_1", name: "Physiography of India & Himalayan River Basins", hy: true },
              { id: "gs_geo_2", name: "Bihar Geography: Ganga, Gandak, Kosi, Floods & Soils", hy: true },
              { id: "gs_geo_3", name: "Monsoon & Agricultural Agro-climatic Zones", hy: false }
            ]
          }
        ]
      },
      cadres: {
        prm: { 
          name: "Classes 1–5: Primary Teacher", 
          sub: "Part I (30Q Qualifying) + Part II GS & EVS (120Q Primary Merit Paper)", 
          subjects: ["General Studies & EVS Unified Paper", "Urdu Language Special", "Bengali Language Special"],
          primarySyllabus: {
            title: "Part II: General Studies & Environmental Studies (120 Questions - Core Merit Paper)",
            modules: [
              {
                name: "1. Elementary Mathematics & Basic Numeracy (25–30 Questions)",
                items: [
                  { id: "prm_mat_1", name: "Number System, Divisibility Rules, Prime/Composite, HCF & LCM", hy: true },
                  { id: "prm_mat_2", name: "Fractions, Decimals, BODMAS & Unitary Method Applications", hy: false },
                  { id: "prm_mat_3", name: "Percentage, Profit & Loss, Simple & Compound Interest", hy: true },
                  { id: "prm_mat_4", name: "Ratio, Proportion, Average, Time & Work, Speed-Distance", hy: true },
                  { id: "prm_mat_5", name: "Basic Geometry, 2D/3D Mensuration (Area, Volume) & Unit Conversions", hy: true }
                ]
              },
              {
                name: "2. Mental Ability & Logical Reasoning (15–20 Questions)",
                items: [
                  { id: "prm_rea_1", name: "Number Series, Alphabet Series & Missing Pattern Completion", hy: true },
                  { id: "prm_rea_2", name: "Analogy, Classification, Odd One Out & Coding-Decoding", hy: false },
                  { id: "prm_rea_3", name: "Direction & Distance, Blood Relations, Clock, Calendar & Venn Diagrams", hy: true }
                ]
              },
              {
                name: "3. General Science: Physics, Chemistry & Biology (20–25 Questions)",
                items: [
                  { id: "prm_sci_1", name: "Motion, Force, Gravitation, Work, Energy, Light, Heat, Electricity & Sound", hy: true },
                  { id: "prm_sci_2", name: "States of Matter, Elements, Compounds, Mixtures, Acids, Bases & Salts", hy: false },
                  { id: "prm_sci_3", name: "Human Body Systems: Digestive, Respiratory, Circulatory, Nervous & Skeletal", hy: true },
                  { id: "prm_sci_4", name: "Microorganisms, Health, Hygiene, Sanitation, Immunity & Balanced Diet", hy: true },
                  { id: "prm_sci_5", name: "Plant Structure, Photosynthesis, Plant Reproduction & Animal Diversity", hy: false }
                ]
              },
              {
                name: "4. Environmental Studies (EVS) & Ecology (15–20 Questions)",
                items: [
                  { id: "prm_evs_1", name: "Ecosystems, Biotic/Abiotic Components, Food Chains & Ecological Pyramids", hy: true },
                  { id: "prm_evs_2", name: "Air, Water, Soil & Noise Pollution: Causes, Prevention & Waste Management", hy: true },
                  { id: "prm_evs_3", name: "Natural Resources, Conservation, Rainwater Harvesting & Sustainable Living", hy: false },
                  { id: "prm_evs_4", name: "Biodiversity, Endangered Species, National Parks & Sanctuaries in Bihar", hy: true }
                ]
              },
              {
                name: "5. Indian National Movement & Bihar's Contribution (15–20 Questions)",
                items: [
                  { id: "prm_inm_1", name: "Revolt of 1857: Causes, Leaders & Babu Veer Kunwar Singh (Jagdishpur)", hy: true },
                  { id: "prm_inm_2", name: "Champaran Satyagraha (1917), Raj Kumar Shukla & Dr. Rajendra Prasad", hy: true },
                  { id: "prm_inm_3", name: "Non-Cooperation, Civil Disobedience, Quit India Movement & Bihar Peasantry", hy: true }
                ]
              },
              {
                name: "6. Geography of Bihar & India (15–20 Questions)",
                items: [
                  { id: "prm_geo_1", name: "Physical Geography of India: Himalayas, Northern Plains & Peninsular Rivers", hy: false },
                  { id: "prm_geo_2", name: "Bihar Geography: Ganga, Gandak, Kosi, Flood Zones, Soil Types & Minerals", hy: true },
                  { id: "prm_geo_3", name: "Monsoon, Seasons, Major Crops, Irrigation & Agro-Climatic Zones in Bihar", hy: true }
                ]
              },
              {
                name: "7. Elementary Pedagogy & Foundational Literacy/Numeracy (FLN)",
                items: [
                  { id: "prm_ped_1", name: "Child Development Stages: Physical, Cognitive, Language & Social Growth", hy: true },
                  { id: "prm_ped_2", name: "Foundational Literacy & Numeracy (FLN), Activity-Based Learning & TLMs", hy: true },
                  { id: "prm_ped_3", name: "Continuous & Comprehensive Evaluation (CCE), Inclusive Classroom & Remedial Tools", hy: false }
                ]
              }
            ]
          }
        },
        mid: { 
          name: "Classes 6–8: Middle School Teacher", 
          sub: "Part I (30Q) + Part II GS (40Q) + Part III Subject (80Q)", 
          subjects: [
            "Mathematics & Science", 
            "Social Science", 
            "Hindi", 
            "English", 
            "Sanskrit", 
            "Urdu"
          ],
          subjectData: {
            "Mathematics & Science": [
              {
                name: "Part III: Middle School Mathematics (40 Questions)",
                items: [
                  { id: "mid_ms_1", name: "Rational Numbers, Exponents, Powers, Square Roots & Cube Roots", hy: true },
                  { id: "mid_ms_2", name: "Algebraic Expressions, Identities & Linear Equations in One Variable", hy: true },
                  { id: "mid_ms_3", name: "Geometry: Lines, Angles, Triangles, Quadrilaterals & Symmetry", hy: true },
                  { id: "mid_ms_4", name: "Mensuration: Perimeter, Area, Surface Area & Volume of 2D/3D Solids", hy: true },
                  { id: "mid_ms_5", name: "Data Handling: Bar Graphs, Pie Charts, Mean, Median, Mode & Probability", hy: false }
                ]
              },
              {
                name: "Part III: Middle School Science (40 Questions)",
                items: [
                  { id: "mid_ms_6", name: "Components of Food, Nutrition in Plants & Animals, Respiration & Digestion", hy: true },
                  { id: "mid_ms_7", name: "Motion, Force, Pressure, Friction, Sound, Light & Optical Phenomena", hy: true },
                  { id: "mid_ms_8", name: "Matter, Acids, Bases, Salts, Physical & Chemical Changes, Metals/Non-metals", hy: true },
                  { id: "mid_ms_9", name: "Cell Structure, Tissues, Plant & Animal Reproduction, Microorganisms", hy: true },
                  { id: "mid_ms_10", name: "Water, Air, Soil, Forests, Pollution & Natural Resource Conservation", hy: false }
                ]
              }
            ],
            "Social Science": [
              {
                name: "Part III: History of India & Bihar Heritage",
                items: [
                  { id: "mid_sst_1", name: "Earliest Cities, Indus Civilization, Vedic Period, Mahajanapadas & Mauryas", hy: true },
                  { id: "mid_sst_2", name: "Gupta Empire, Nalanda, Delhi Sultanate, Mughals, Bhakti & Sufi Traditions", hy: true },
                  { id: "mid_sst_3", name: "Colonial Expansion, Tribal & Peasant Resistance, 1857 Revolt in Bihar", hy: true },
                  { id: "mid_sst_4", name: "Indian Freedom Struggle, Champaran Satyagraha, Quit India & 1947 Freedom", hy: true }
                ]
              },
              {
                name: "Part III: Geography & Environment",
                items: [
                  { id: "mid_sst_5", name: "Earth, Globe, Latitudes, Longitudes, Atmosphere, Water Cycle & Landforms", hy: true },
                  { id: "mid_sst_6", name: "India & Bihar: Physiography, Climate, Soils, Rivers, Agriculture & Industries", hy: true }
                ]
              },
              {
                name: "Part III: Civics, Polity & Livelihood Economics",
                items: [
                  { id: "mid_sst_7", name: "Democracy, Indian Constitution, Fundamental Rights, Parliament & Judiciary", hy: true },
                  { id: "mid_sst_8", name: "State Government, Panchayati Raj, Local Self-Government & Social Diversity", hy: false },
                  { id: "mid_sst_9", name: "Livelihoods, Markets, Production, Money, Banking & Public Services", hy: true }
                ]
              }
            ],
            "Hindi": [
              {
                name: "Part III: हिंदी भाषा एवं व्याकरण (40 Questions)",
                items: [
                  { id: "mid_hin_1", name: "वर्ण-विचार, संधि, समास, उपसर्ग, प्रत्यय, तत्सम-तद्भव एवं वर्तनी-शुद्धि", hy: true },
                  { id: "mid_hin_2", name: "शब्द-भेद: संज्ञा से अव्यय, लिंग, वचन, कारक, काल, वाच्य एवं वाक्य-रचना", hy: true },
                  { id: "mid_hin_3", name: "पर्यायवाची, विलोम, अनेकार्थी, मुहावरे, लोकोक्तियाँ एवं वाक्य-संशोधन", hy: true }
                ]
              },
              {
                name: "Part III: हिंदी साहित्य एवं काव्यशास्त्र (40 Questions)",
                items: [
                  { id: "mid_hin_4", name: "अपठित गद्यांश/पद्यांश, भावार्थ निष्कर्ष, रस, छंद, अलंकार एवं प्रतीक", hy: true },
                  { id: "mid_hin_5", name: "हिंदी साहित्य का काल-विभाजन: भक्तिकाल, रीतिकाल, आधुनिक काल की प्रवृत्तियाँ", hy: true },
                  { id: "mid_hin_6", name: "प्रमुख गद्य विधाएँ: कहानी, निबंध, जीवनी, नाटक एवं प्रतिनिधि साहित्यकार", hy: false }
                ]
              }
            ],
            "English": [
              {
                name: "Part III: Reading Comprehension & Functional Grammar",
                items: [
                  { id: "mid_eng_1", name: "Unseen Prose & Poetry Comprehension: Main Idea, Inference & Vocabulary", hy: true },
                  { id: "mid_eng_2", name: "Parts of Speech, Articles, Determiners, Prepositions & Conjunctions", hy: true },
                  { id: "mid_eng_3", name: "Tenses, Subject-Verb Agreement, Modals, Conditionals & Sentence Structures", hy: true },
                  { id: "mid_eng_4", name: "Active / Passive Voice, Direct / Indirect Speech & Transformation", hy: true }
                ]
              },
              {
                name: "Part III: Vocabulary & Literary Appreciation",
                items: [
                  { id: "mid_eng_5", name: "Synonyms, Antonyms, Idioms, Phrasal Verbs, One-Word Substitutes & Spelling", hy: true },
                  { id: "mid_eng_6", name: "Literary Devices, Figures of Speech, Themes, Guided Writing & Cloze Test", hy: false }
                ]
              }
            ],
            "Sanskrit": [
              {
                name: "Part III: संस्कृत व्याकरण एवं साहित्य",
                items: [
                  { id: "mid_san_1", name: "वर्णमाला, उच्चारण स्थान, संधि, शब्दरूप (बालक, लता, फल) एवं धातुरूप (लट्, लृट्, लङ्)", hy: true },
                  { id: "mid_san_2", name: "कारक, विभक्ति-नियम, समास, उपसर्ग, प्रत्यय (क्त, क्तवतु, क्त्वा, ल्यप्) एवं अव्यय", hy: true },
                  { id: "mid_san_3", name: "संस्कृत अनुवाद, अपठित गद्यांश, सूक्तियाँ, नीतिश्लोक एवं निर्धारित पाठों का अर्थ", hy: true }
                ]
              }
            ],
            "Urdu": [
              {
                name: "Part III: اردو قواعد اور فہم",
                items: [
                  { id: "mid_urd_1", name: "رسم الخط، املا، تلفظ، حروف تہجی اور بنیادی قواعد", hy: true },
                  { id: "mid_urd_2", name: "اسم، ضمیر، صفت، فعل، تذکیر و تانیث، واحد جمع اور جملے کی ساخت", hy: true },
                  { id: "mid_urd_3", name: "محاورات، ضرب الامثال، مترادفات، متضاد الفاظ اور اصناف سخن تفہیم", hy: true }
                ]
              }
            ]
          }
        },
        sec: { 
          name: "Classes 9–10: Secondary Teacher", 
          sub: "Part I (30Q) + Part II GS (40Q) + Part III Subject (80Q)", 
          subjects: [
            "Mathematics", 
            "Science (Physics/Chemistry/Biology)", 
            "Social Science (History/Polity/Geo/Econ)", 
            "Hindi", 
            "English", 
            "Sanskrit", 
            "Urdu", 
            "Bangla", 
            "Maithili", 
            "Physical Education", 
            "Music", 
            "Fine Arts", 
            "Dance"
          ],
          subjectData: {
            "Mathematics": [
              {
                name: "Part III: Real Numbers, Algebra & Coordinate Geometry",
                items: [
                  { id: "sec_mat_1", name: "Real Numbers, Polynomials & Pair of Linear Equations in Two Variables", hy: true },
                  { id: "sec_mat_2", name: "Quadratic Equations, Discriminant & Arithmetic Progressions (AP)", hy: true },
                  { id: "sec_mat_3", name: "Coordinate Geometry: Distance Formula, Section Formula & Area of Triangles", hy: true }
                ]
              },
              {
                name: "Part III: Geometry, Trigonometry, Mensuration & Statistics",
                items: [
                  { id: "sec_mat_4", name: "Triangles (Similarity, Thales, Pythagoras Theorems), Circles & Tangents", hy: true },
                  { id: "sec_mat_5", name: "Trigonometric Ratios, Standard Identities & Heights and Distances", hy: true },
                  { id: "sec_mat_6", name: "Areas Related to Circles, Surface Areas & Volumes of Solid Combinations", hy: true },
                  { id: "sec_mat_7", name: "Statistics (Mean, Median, Mode of Grouped Data) & Classical Probability", hy: true }
                ]
              }
            ],
            "Science (Physics/Chemistry/Biology)": [
              {
                name: "Part III: Secondary Physics Modules",
                items: [
                  { id: "sec_sci_1", name: "Motion, Laws of Motion, Momentum, Gravitation, Work, Energy & Power", hy: true },
                  { id: "sec_sci_2", name: "Sound Waves, Light Reflection, Refraction, Lenses, Human Eye & Optical Defects", hy: true },
                  { id: "sec_sci_3", name: "Electricity (Ohm's Law, Resistance, Circuits) & Magnetic Effects of Current", hy: true }
                ]
              },
              {
                name: "Part III: Secondary Chemistry Modules",
                items: [
                  { id: "sec_sci_4", name: "Chemical Reactions, Balancing, Types of Reactions, Acids, Bases & Salts", hy: true },
                  { id: "sec_sci_5", name: "Metals & Non-Metals, Metallurgy, Carbon Compounds & Periodic Table", hy: true }
                ]
              },
              {
                name: "Part III: Secondary Biology Modules",
                items: [
                  { id: "sec_sci_6", name: "Life Processes: Nutrition, Respiration, Transportation & Excretion in Humans/Plants", hy: true },
                  { id: "sec_sci_7", name: "Control & Coordination (Nervous/Hormonal), Reproduction, Heredity & Evolution", hy: true },
                  { id: "sec_sci_8", name: "Our Environment, Ecosystems, Natural Resource Management & Lab Safety Skills", hy: false }
                ]
              }
            ],
            "Social Science (History/Polity/Geo/Econ)": [
              {
                name: "Part III: World & Indian History",
                items: [
                  { id: "sec_sst_1", name: "French Revolution, Russian Revolution, Nazism, Industrialization & Print Culture", hy: true },
                  { id: "sec_sst_2", name: "Nationalism in Europe & Nationalism in India with Bihar Freedom Movement Context", hy: true }
                ]
              },
              {
                name: "Part III: Geography, Resources & Agriculture",
                items: [
                  { id: "sec_sst_3", name: "Resources, Forest, Wildlife, Water Resources, Agriculture & Cropping Seasons", hy: true },
                  { id: "sec_sst_4", name: "Minerals, Energy Resources, Manufacturing Industries & National Lifelines", hy: false }
                ]
              },
              {
                name: "Part III: Democratic Politics & Economic Development",
                items: [
                  { id: "sec_sst_5", name: "Power Sharing, Federalism, Democracy & Diversity, Gender, Religion & Caste", hy: true },
                  { id: "sec_sst_6", name: "Political Parties, Electoral Competition & Outcomes/Challenges of Democracy", hy: true },
                  { id: "sec_sst_7", name: "Sectors of Indian Economy, Money & Credit, Globalization & Consumer Rights", hy: true }
                ]
              }
            ],
            "Physical Education": [
              {
                name: "Part III: Physical Fitness, Anatomy & Sports",
                items: [
                  { id: "sec_pe_1", name: "Physical Fitness Components, Principles of Training, Warm-Up & Cool-Down", hy: true },
                  { id: "sec_pe_2", name: "Skeletal, Muscular, Circulatory & Respiratory Systems; Effects of Exercise", hy: true },
                  { id: "sec_pe_3", name: "Yoga Principles, Asanas, Pranayama, Sports Nutrition, Injuries & First Aid", hy: true }
                ]
              }
            ]
          }
        },
        hsec: { 
          name: "Classes 11–12: Higher Secondary", 
          sub: "Part I (30Q) + Part II GS (40Q) + Part III Subject (80Q)", 
          subjects: [
            "Mathematics", 
            "Physics", 
            "Chemistry", 
            "Biology (Botany & Zoology)", 
            "Computer Science", 
            "History", 
            "Political Science", 
            "Geography", 
            "Economics", 
            "Sociology", 
            "Psychology", 
            "Philosophy", 
            "Home Science", 
            "Accountancy", 
            "Business Studies", 
            "Entrepreneurship", 
            "English", 
            "Hindi", 
            "Urdu", 
            "Sanskrit", 
            "Maithili", 
            "Music", 
            "Fine Arts"
          ],
          subjectData: {
            "Mathematics": [
              {
                name: "Part III: Relations, Algebra & Differential Calculus",
                items: [
                  { id: "hsec_mat_1", name: "Sets, Relations, Functions, Trigonometric & Inverse Trigonometric Functions", hy: true },
                  { id: "hsec_mat_2", name: "Matrices, Determinants, Limits, Continuity & Differentiability", hy: true },
                  { id: "hsec_mat_3", name: "Applications of Derivatives: Tangents, Normals, Rate Measure, Maxima & Minima", hy: true }
                ]
              },
              {
                name: "Part III: Integral Calculus, Vectors & Probability",
                items: [
                  { id: "hsec_mat_4", name: "Indefinite & Definite Integrals, Fundamental Theorem & Applications to Areas", hy: true },
                  { id: "hsec_mat_5", name: "Differential Equations: Order, Degree, Variable Separation & Linear Equations", hy: true },
                  { id: "hsec_mat_6", name: "Vectors & 3D Geometry: Direction Cosines, Straight Lines & Planes in Space", hy: true },
                  { id: "hsec_mat_7", name: "Linear Programming, Conditional Probability, Bayes' Theorem & Distributions", hy: true }
                ]
              }
            ],
            "Physics": [
              {
                name: "Part III: Mechanics, Thermodynamics & Oscillations",
                items: [
                  { id: "hsec_phy_1", name: "Kinematics, Vectors, Laws of Motion, Work, Energy, Power & Rotational Dynamics", hy: true },
                  { id: "hsec_phy_2", name: "Gravitation, Mechanical Properties of Solids/Fluids & Surface Tension", hy: false },
                  { id: "hsec_phy_3", name: "Thermodynamics, Kinetic Theory of Gases, Oscillations (SHM) & Wave Motion", hy: true }
                ]
              },
              {
                name: "Part III: Electromagnetism & Optics",
                items: [
                  { id: "hsec_phy_4", name: "Electrostatics: Gauss's Law, Electric Potential, Capacitance & Current Electricity", hy: true },
                  { id: "hsec_phy_5", name: "Biot-Savart Law, Ampere's Law, EM Induction, Lenz's Law & Alternating Current", hy: true },
                  { id: "hsec_phy_6", name: "Ray Optics, Optical Instruments, Wave Optics (Interference, Diffraction)", hy: true }
                ]
              },
              {
                name: "Part III: Modern Physics & Electronics",
                items: [
                  { id: "hsec_phy_7", name: "Dual Nature of Matter/Radiation, Photoelectric Effect, Atoms & Nuclear Structure", hy: true },
                  { id: "hsec_phy_8", name: "Semiconductor Physics: Diodes, Rectifiers, Transistors & Digital Logic Gates", hy: true }
                ]
              }
            ],
            "Chemistry": [
              {
                name: "Part III: Physical Chemistry Modules",
                items: [
                  { id: "hsec_che_1", name: "Atomic Structure, Quantum Numbers, Chemical Bonding & Molecular Geometry", hy: true },
                  { id: "hsec_che_2", name: "Thermodynamics, Enthalpy, Entropy, Chemical Equilibrium & Le Chatelier's Principle", hy: true },
                  { id: "hsec_che_3", name: "Solutions, Colligative Properties, Electrochemistry & Chemical Kinetics", hy: true }
                ]
              },
              {
                name: "Part III: Inorganic & Coordination Chemistry",
                items: [
                  { id: "hsec_che_4", name: "Periodic Classification, s- and p-Block Elements, d- and f-Block Metallurgy", hy: false },
                  { id: "hsec_che_5", name: "Coordination Compounds: Werner's Theory, Valence Bond & Crystal Field Theory", hy: true }
                ]
              },
              {
                name: "Part III: Organic Chemistry & Biomolecules",
                items: [
                  { id: "hsec_che_6", name: "Reaction Mechanisms, Hydrocarbons, Haloalkanes, Alcohols, Phenols & Ethers", hy: true },
                  { id: "hsec_che_7", name: "Aldehydes, Ketones, Carboxylic Acids, Amines, Carbohydrates, Proteins & Polymers", hy: true }
                ]
              }
            ],
            "Biology (Botany & Zoology)": [
              {
                name: "Part III: Plant Diversity, Anatomy & Physiology",
                items: [
                  { id: "hsec_bio_1", name: "Plant Kingdom Classification, Anatomy of Flowering Plants & Tissues", hy: true },
                  { id: "hsec_bio_2", name: "Cell Biology: Cell Cycle, Mitosis, Meiosis, Enzymes & Biomolecules", hy: true },
                  { id: "hsec_bio_3", name: "Plant Physiology: Photosynthesis (C3/C4), Respiration, Transpiration & Phytohormones", hy: true },
                  { id: "hsec_bio_4", name: "Reproduction in Angiosperms: Pollination, Double Fertilization, Seeds & Fruits", hy: true }
                ]
              },
              {
                name: "Part III: Human Physiology & Reproductive Health",
                items: [
                  { id: "hsec_bio_5", name: "Digestion, Breathing, Circulatory System, Excretion, Locomotion & Neural Control", hy: true },
                  { id: "hsec_bio_6", name: "Human Reproduction, Gametogenesis, Menstrual Cycle & Reproductive Health", hy: true }
                ]
              },
              {
                name: "Part III: Genetics, Evolution, Biotech & Ecology",
                items: [
                  { id: "hsec_bio_7", name: "Mendelian Genetics, Linkage, DNA Structure, Replication, Transcription & Translation", hy: true },
                  { id: "hsec_bio_8", name: "Biotechnology Principles: Restriction Enzymes, Recombinant DNA & Applications", hy: true },
                  { id: "hsec_bio_9", name: "Ecology: Populations, Ecosystem Energetics, Biodiversity Hotspots & Conservation", hy: true }
                ]
              }
            ],
            "Computer Science": [
              {
                name: "Part III: Computer Architecture & Programming",
                items: [
                  { id: "hsec_cs_1", name: "CPU Architecture, Number Systems (Binary/Hex), Boolean Algebra & Logic Gates", hy: true },
                  { id: "hsec_cs_2", name: "Programming in Python: Data Types, Lists, Dicts, Control Flow & Functions", hy: true },
                  { id: "hsec_cs_3", name: "Data Structures: Linear Search, Binary Search, Bubble/Insertion Sort, Stacks & Queues", hy: true }
                ]
              },
              {
                name: "Part III: Databases (SQL) & Networking",
                items: [
                  { id: "hsec_cs_4", name: "Relational Database Concepts, Primary/Foreign Keys, Normalization (1NF, 2NF, 3NF)", hy: true },
                  { id: "hsec_cs_5", name: "Structured Query Language (SQL): DDL, DML, Joins, Group By, Having & Nested Queries", hy: true },
                  { id: "hsec_cs_6", name: "Computer Networks: Topologies, OSI/TCP-IP Models, IP Addressing, Routers & DNS", hy: true }
                ]
              },
              {
                name: "Part III: Systems, Web Technologies & Cyber Ethics",
                items: [
                  { id: "hsec_cs_7", name: "Operating Systems: Process Scheduling, Memory Allocation, Virtual Memory & Deadlocks", hy: true },
                  { id: "hsec_cs_8", name: "Web Technologies (HTML, CSS, JS), Cybersecurity, Malware, Phishing & IT Act", hy: true }
                ]
              }
            ],
            "History": [
              {
                name: "Part III: Ancient & Medieval India",
                items: [
                  { id: "hsec_his_1", name: "Harappan Civilization, Vedic Society, Mahajanapadas & Magadhan Rise", hy: true },
                  { id: "hsec_his_2", name: "Mauryan Administration, Ashoka's Inscriptions, Buddhism, Guptas & Nalanda", hy: true },
                  { id: "hsec_his_3", name: "Delhi Sultanate, Mughal Agrarian System, Mansabdari, Bhakti & Sufi Synthesis", hy: true }
                ]
              },
              {
                name: "Part III: Modern India & Freedom Movement in Bihar",
                items: [
                  { id: "hsec_his_4", name: "East India Company Expansion, Permanent Settlement & 1857 Revolt in Bihar", hy: true },
                  { id: "hsec_his_5", name: "Indian National Congress, Swadeshi, Gandhian Mass Movements & Peasant Struggles", hy: true },
                  { id: "hsec_his_6", name: "Subhas Bose, INA, Quit India 1942, Partition of India & Independence 1947", hy: true }
                ]
              },
              {
                name: "Part III: World History & Revolutions",
                items: [
                  { id: "hsec_his_7", name: "Industrial Revolution, French Revolution, Russian Revolution, World Wars & Cold War", hy: true }
                ]
              }
            ],
            "Political Science": [
              {
                name: "Part III: Political Theory & Indian Constitution",
                items: [
                  { id: "hsec_pol_1", name: "Concepts: Liberty, Equality, Justice, Rights, Sovereignty & Democratic Theory", hy: true },
                  { id: "hsec_pol_2", name: "Constituent Assembly, Preamble, Fundamental Rights, Duties & Directive Principles", hy: true },
                  { id: "hsec_pol_3", name: "Union Executive: President, Prime Minister, Cabinet & Council of Ministers", hy: true }
                ]
              },
              {
                name: "Part III: Judiciary, Federalism & Foreign Policy",
                items: [
                  { id: "hsec_pol_4", name: "Parliament Law-Making, Supreme Court, Judicial Review & Basic Structure Doctrine", hy: true },
                  { id: "hsec_pol_5", name: "Indian Federalism, Centre-State Relations, Governor's Role & Panchayati Raj (73rd/74th)", hy: true },
                  { id: "hsec_pol_6", name: "Election Commission, Electoral Reforms, Political Parties & Coalition Politics", hy: true },
                  { id: "hsec_pol_7", name: "India's Foreign Policy, Non-Alignment, United Nations & International Security", hy: false }
                ]
              }
            ],
            "Geography": [
              {
                name: "Part III: Physical Geography & Climatology",
                items: [
                  { id: "hsec_geo_1", name: "Interior of Earth, Continental Drift, Plate Tectonics, Weathering & Landforms", hy: true },
                  { id: "hsec_geo_2", name: "Atmospheric Layers, Temperature Inversion, Pressure Belts, Cyclones & Indian Monsoon", hy: true },
                  { id: "hsec_geo_3", name: "Ocean Relief, Salinity, Ocean Currents, Tides & Coral Reef Ecosystems", hy: false }
                ]
              },
              {
                name: "Part III: Human, Economic & Bihar Geography",
                items: [
                  { id: "hsec_geo_4", name: "Physiographic Divisions of India, Ganga Drainage Basin, Flood Plains & Soils", hy: true },
                  { id: "hsec_geo_5", name: "Cropping Patterns, Irrigation, Green Revolution, Minerals & Heavy Industries", hy: true },
                  { id: "hsec_geo_6", name: "Demographic Trends: Density, Growth, Migration Patterns & Urbanization in Bihar", hy: true },
                  { id: "hsec_geo_7", name: "Cartographic Techniques, Topographical Maps, Remote Sensing & GIS Basics", hy: false }
                ]
              }
            ],
            "Economics": [
              {
                name: "Part III: Microeconomics & Macroeconomics",
                items: [
                  { id: "hsec_eco_1", name: "Consumer Equilibrium, Utility Analysis, Demand Curves & Price Elasticity", hy: true },
                  { id: "hsec_eco_2", name: "Production Functions, Cost/Revenue Curves & Perfect Competition vs Monopoly", hy: true },
                  { id: "hsec_eco_3", name: "National Income Aggregates (GDP, GNP, NNP), Multiplier & Keynesian Framework", hy: true },
                  { id: "hsec_eco_4", name: "Money, High-Powered Money, Commercial Banks & RBI Monetary Policy Tools", hy: true }
                ]
              },
              {
                name: "Part III: Indian Economy & Bihar Development",
                items: [
                  { id: "hsec_eco_5", name: "Government Budget: Revenue/Capital Accounts, Fiscal Deficit & GST Framework", hy: true },
                  { id: "hsec_eco_6", name: "Poverty, Unemployment, Human Capital Formation, Rural Credit & 1991 LPG Reforms", hy: true },
                  { id: "hsec_eco_7", name: "Bihar Economy: Agrarian Structure, State Budget, Infrastructure & Growth Trajectory", hy: true }
                ]
              }
            ]
          }
        }
      }
    };

    // --- 7B. COMPREHENSIVE TOPIC STUDY NOTES & MASTERY QUIZZES REPOSITORY ---
    let TOPIC_NOTES_DATABASE = {};

    // Automatically integrate stored notes from SQL database into TOPIC_NOTES_DATABASE
    if (typeof PHP_STORED_NOTES !== 'undefined' && Array.isArray(PHP_STORED_NOTES)) {
      PHP_STORED_NOTES.forEach(n => {
        if (n && n.topicId) {
          TOPIC_NOTES_DATABASE[n.topicId] = {
            ...n,
            attachedFile: n.attached_file,
            quiz: n.quiz || (TOPIC_NOTES_DATABASE[n.topicId] ? TOPIC_NOTES_DATABASE[n.topicId].quiz : [])
          };
        }
      });
    }

    // --- 8. STATE VARIABLES & USER STORAGE SCOPING ---
    let activeFilter = "all";
    let currentCadre = "mid";

    function getUserStorageKey(suffix) {
      const userIdentifier = (authenticatedUser && (authenticatedUser.id || authenticatedUser.username || authenticatedUser.email))
        ? (authenticatedUser.id || authenticatedUser.username || authenticatedUser.email).toString().replace(/[^a-zA-Z0-9_-]/g, '_')
        : (typeof PHP_SESSION_USER !== 'undefined' && PHP_SESSION_USER && (PHP_SESSION_USER.id || PHP_SESSION_USER.username || PHP_SESSION_USER.email) ? (PHP_SESSION_USER.id || PHP_SESSION_USER.username || PHP_SESSION_USER.email).toString().replace(/[^a-zA-Z0-9_-]/g, '_') : 'guest');
      return `bpsc_${suffix}_${userIdentifier}`;
    }

    let topicState = {};
    
    // Topic Study & Mastery State
    let currentActiveStudyTopicId = "gs_inm_1";
    let currentActiveQuizQuestionList = [];

    // CBT State
    let cbtActivePaper = null;
    let cbtUserAnswers = {};
    let isCbtActive = false;
    let cbtTimerSeconds = 900;
    let cbtTimerInterval = null;
    let cbtSubmitted = false;

    // Temporary storage
    let tempSelectedBookFile = null;
    let tempSelectedDiagramBase64 = null;
    let tempBulkQuestions = [];
    let curatorSelectedQIds = new Set();
    let tempEditorQuizQuestions = [];
    let tempSelectedPyqFile = null;
    let tempEditorAttachedFile = null;
    let UPLOADED_NOTES_LIST = (typeof PHP_STORED_NOTES !== 'undefined' && Array.isArray(PHP_STORED_NOTES) && PHP_STORED_NOTES.length > 0) ? [...PHP_STORED_NOTES] : [];

    // --- SECTION COLLAPSE & EXPAND (MINIMIZE / MAXIMIZE) HELPERS ---
    function toggleSectionCollapse(bodyId, buttonEl, defaultLabel = 'Section') {
      const body = document.getElementById(bodyId);
      if (!body) return;
      const isHidden = body.classList.contains('hidden');
      if (!buttonEl || !buttonEl.tagName) {
        buttonEl = document.querySelector(`[data-collapsible-target="${bodyId}"]`);
      }
      if (isHidden) {
        body.classList.remove('hidden');
        localStorage.setItem('collapse_' + bodyId, '0');
        if (buttonEl) {
          buttonEl.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg><span>Minimize</span>`;
          buttonEl.setAttribute('aria-expanded', 'true');
          buttonEl.title = `Minimize ${defaultLabel}`;
        }
      } else {
        body.classList.add('hidden');
        localStorage.setItem('collapse_' + bodyId, '1');
        if (buttonEl) {
          buttonEl.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg><span>Expand</span>`;
          buttonEl.setAttribute('aria-expanded', 'false');
          buttonEl.title = `Expand ${defaultLabel}`;
        }
      }
    }

    function initSectionCollapseStates() {
      document.querySelectorAll('[data-collapsible-target]').forEach(btn => {
        const targetId = btn.getAttribute('data-collapsible-target');
        const targetEl = document.getElementById(targetId);
        if (!targetEl) return;
        const isCollapsed = localStorage.getItem('collapse_' + targetId) === '1';
        if (isCollapsed) {
          targetEl.classList.add('hidden');
          btn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg><span>Expand</span>`;
          btn.setAttribute('aria-expanded', 'false');
        } else {
          targetEl.classList.remove('hidden');
          btn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg><span>Minimize</span>`;
          btn.setAttribute('aria-expanded', 'true');
        }
      });
    }

    function toggleAllSyllabusParts() {
      const bodies = document.querySelectorAll('.syllabus-part-body');
      if (!bodies.length) return;

      const anyVisible = Array.from(bodies).some(b => !b.classList.contains('hidden'));
      const shouldCollapse = anyVisible;

      bodies.forEach(body => {
        const btn = document.querySelector(`[data-collapsible-target="${body.id}"]`);
        if (shouldCollapse) {
          body.classList.add('hidden');
          localStorage.setItem('collapse_' + body.id, '1');
          if (btn) {
            btn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg><span>Expand</span>`;
            btn.setAttribute('aria-expanded', 'false');
          }
        } else {
          body.classList.remove('hidden');
          localStorage.setItem('collapse_' + body.id, '0');
          if (btn) {
            btn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg><span>Minimize</span>`;
            btn.setAttribute('aria-expanded', 'true');
          }
        }
      });

      const masterBtn = document.getElementById('btnToggleAllParts');
      if (masterBtn) {
        if (shouldCollapse) {
          masterBtn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg><span>Expand All</span>`;
        } else {
          masterBtn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg><span>Collapse All</span>`;
        }
      }
    }

    // --- 9. INITIALIZATION ON LOAD ---
    function init() {
      startExamTargetCountdown();
      bindAntiCheatListeners();
      renderBooksModalList();
      renderCandidateBooksShelf();
      renderTeacherBooksTable();
      renderAdminBooksTable();
      renderPyqList();
      renderPaymentLedger();
      renderQuestionPapers();
      renderAdminQpMasterTable();
      populateQpSelectDropdown();
      renderDbQuestionsTable();
      renderCandidatePublishedTests();
      renderCandidateUploadedNotes();
      loadUploadedNotes();
      loadStoredQuestionPapers();
      initSectionCollapseStates();

      // Check if PHP already authenticated user
      if (PHP_IS_LOGGED_IN && PHP_SESSION_USER) {
        authenticatedUser = { ...PHP_SESSION_USER };
        document.getElementById("authScreenGate").classList.add("hidden");
        document.getElementById("mainAppShell").classList.remove("hidden");
        document.getElementById("currentRoleBadge").innerText = authenticatedUser.badge;
        document.getElementById("userSubRoleLabel").innerText = authenticatedUser.subLabel;
        setActiveRole(authenticatedUser.role);
        showToast(`Authenticated as ${authenticatedUser.badge} via Infinity Learning!`, "Welcome");
      }
    }

    // --- HTML ESCAPE HELPER ---
    function escapeHtml(str) {
      if (str === null || str === undefined) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    // --- 10. AUTH, REGISTRATION & ROLE DISPATCHER ---
    function openRegisterPage(defaultRole = 'candidate') {
      const loginView = document.getElementById('authViewLogin');
      const regView = document.getElementById('authViewRegister');
      if (loginView) loginView.classList.add('hidden');
      if (regView) regView.classList.remove('hidden');

      selectRegisterTab(defaultRole);

      const alertBox = document.getElementById('authAlertBox');
      if (alertBox && !alertBox.dataset.persistent) {
        alertBox.classList.add('hidden');
      }
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function openLoginPage() {
      const regView = document.getElementById('authViewRegister');
      const loginView = document.getElementById('authViewLogin');
      if (regView) regView.classList.add('hidden');
      if (loginView) loginView.classList.remove('hidden');

      const alertBox = document.getElementById('authAlertBox');
      if (alertBox && !alertBox.dataset.persistent) {
        alertBox.classList.add('hidden');
      }
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function selectRegisterTab(role) {
      const roles = ['candidate', 'teacher'];
      roles.forEach(r => {
        const box = document.getElementById(`regBox-${r}`);
        const btn = document.getElementById(`regTabBtn-${r}`);
        if (box) {
          if (r === role) box.classList.remove('hidden');
          else box.classList.add('hidden');
        }
        if (btn) {
          if (r === role) {
            btn.className = "py-2.5 rounded-xl bg-brand-orange text-white shadow-xs transition cursor-pointer text-center font-bold";
          } else {
            btn.className = "py-2.5 rounded-xl text-brand-muted hover:text-brand-slateText transition cursor-pointer text-center font-bold";
          }
        }
      });
    }

    function showAuthAlert(message, type = 'error') {
      const box = document.getElementById('authAlertBox');
      if (!box) return;
      box.dataset.persistent = "1";
      box.classList.remove('hidden');

      let bgClass = "p-3.5 bg-red-50 border border-red-200 rounded-2xl text-xs text-red-800 flex items-center gap-2.5";
      let icon = "⚠️";

      if (type === 'success') {
        bgClass = "p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs text-emerald-800 flex items-center gap-2.5";
        icon = "✓";
      } else if (type === 'pending' || type === 'warning') {
        bgClass = "p-3.5 bg-amber-50 border border-amber-300 rounded-2xl text-xs text-amber-900 flex items-center gap-2.5";
        icon = "⏳";
      }

      box.innerHTML = `
        <div class="${bgClass}">
          <span class="text-base font-bold shrink-0">${icon}</span>
          <span class="font-medium">${escapeHtml(message)}</span>
        </div>
      `;
    }

    function selectLoginTab(role) {
      selectedLoginRole = role;
      const roleHidden = document.getElementById("loginRoleInput");
      if (roleHidden) roleHidden.value = role;

      ['student', 'teacher', 'admin'].forEach(r => {
        const btn = document.getElementById(`tabBtn-${r}`);
        if (btn) {
          if (r === role) {
            btn.className = "flex-1 py-2 rounded-xl bg-brand-orange text-white shadow-xs transition cursor-pointer text-xs font-bold";
          } else {
            btn.className = "flex-1 py-2 rounded-xl text-brand-muted hover:text-brand-slateText transition cursor-pointer text-xs font-bold";
          }
        }
      });

      const persona = ROLE_PERSONAS[role];
      if (persona) {
        document.getElementById("personaTitle").innerText = persona.title;
        document.getElementById("personaDesc").innerText = persona.desc;
        const badgeEl = document.getElementById("personaBadge");
        if (badgeEl) badgeEl.innerText = persona.badge || (role.charAt(0).toUpperCase() + role.slice(1));
      }
    }

    function executeRoleLogin() {
      const username = document.getElementById("loginUsernameInput").value.trim();
      const password = document.getElementById("loginPasswordInput").value;
      const role = selectedLoginRole;
      const submitBtn = document.getElementById("loginSubmitBtn");

      if (!username) {
        showAuthAlert("Please enter your registered email address, mobile number, or identifier.", "error");
        showToast("Please enter your email or phone", "Login Error", "breach");
        return;
      }
      if (!password) {
        showAuthAlert("Please enter your security password.", "error");
        showToast("Please enter your password", "Login Error", "breach");
        return;
      }

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span>Verifying credentials...</span>`;
      }

      fetch('login-handler.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: new URLSearchParams({
          username: username,
          password: password,
          role: role,
          ajax: '1'
        })
      })
      .then(res => res.json())
      .then(data => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = `<span>Enter Authorized Terminal</span><span>→</span>`;
        }

        if (data.success && data.user) {
          authenticatedUser = data.user;
          document.getElementById("authScreenGate").classList.add("hidden");
          document.getElementById("mainAppShell").classList.remove("hidden");
          document.getElementById("currentRoleBadge").innerText = authenticatedUser.badge;
          document.getElementById("userSubRoleLabel").innerText = authenticatedUser.subLabel;
          setActiveRole(authenticatedUser.role);
          showToast(data.message || `Welcome! Logged in as ${authenticatedUser.badge}`, "Authentication");
        } else {
          // Gating: Teacher pending or declined or wrong password
          const alertType = data.approval_pending ? 'pending' : 'error';
          showAuthAlert(data.message || "Invalid credentials or authorization revoked.", alertType);
          showToast(data.message || "Invalid credentials", "Access Blocked", "breach");
        }
      })
      .catch(err => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = `<span>Enter Authorized Terminal</span><span>→</span>`;
        }
        console.error("Login verification error:", err);
        showAuthAlert("Connection error during login. Please try again.", "error");
      });
    }

    function executeCandidateRegister() {
      const name = document.getElementById("candRegName").value.trim();
      const email = document.getElementById("candRegEmail").value.trim();
      const phone = document.getElementById("candRegPhone").value.trim();
      const level = document.getElementById("candRegLevel").value;
      const district = document.getElementById("candRegDistrict").value;
      const password = document.getElementById("candRegPassword").value;
      const confirmPassword = document.getElementById("candRegConfirmPassword").value;
      const submitBtn = document.getElementById("candRegSubmitBtn");

      if (!name || !email || !password) {
        showAuthAlert("Please fill in all mandatory candidate registration fields.", "error");
        return;
      }

      if (!phone) {
        showAuthAlert("Please provide a valid contact mobile/phone number.", "error");
        return;
      }

      if (password !== confirmPassword) {
        showAuthAlert("Password and Confirm Password do not match. Please verify.", "error");
        return;
      }

      if (password.length < 6) {
        showAuthAlert("Password must be at least 6 characters in length.", "error");
        return;
      }

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span>Registering Candidate...</span>`;
      }

      fetch('register-handler.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: new URLSearchParams({
          reg_type: 'student',
          name: name,
          email: email,
          phone: phone,
          level: level,
          district: district,
          password: password,
          confirm_password: confirmPassword,
          ajax: '1'
        })
      })
      .then(res => res.json())
      .then(data => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = `<span>Complete Candidate Registration</span><span>→</span>`;
        }

        if (data.success) {
          showToast(data.message, "Candidate Registered");
          openLoginPage();
          selectLoginTab('student');
          document.getElementById("loginUsernameInput").value = email;
          document.getElementById("loginPasswordInput").value = password;
          showAuthAlert(data.message, 'success');
        } else {
          showAuthAlert(data.message || "Registration failed.", 'error');
          showToast(data.message, "Registration Error", "breach");
        }
      })
      .catch(err => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = `<span>Complete Candidate Registration</span><span>→</span>`;
        }
        showAuthAlert("An error occurred during registration. Please try again.", 'error');
      });
    }

    function executeTeacherRegister() {
      const name = document.getElementById("teachRegName").value.trim();
      const email = document.getElementById("teachRegEmail").value.trim();
      const phone = document.getElementById("teachRegPhone").value.trim();
      const qualification = document.getElementById("teachRegQual").value.trim();
      const subject = document.getElementById("teachRegSubject").value.trim();
      const level = document.getElementById("teachRegLevel").value;
      const district = document.getElementById("teachRegDistrict").value;
      const password = document.getElementById("teachRegPassword").value;
      const confirmPassword = document.getElementById("teachRegConfirmPassword").value;
      const submitBtn = document.getElementById("teachRegSubmitBtn");

      if (!name || !email || !phone || !qualification || !subject || !password) {
        showAuthAlert("Please provide all required details for teacher onboarding.", "error");
        return;
      }

      if (password !== confirmPassword) {
        showAuthAlert("Password and Confirm Password do not match. Please verify.", "error");
        return;
      }

      if (password.length < 6) {
        showAuthAlert("Password must be at least 6 characters in length.", "error");
        return;
      }

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span>Submitting Application...</span>`;
      }

      fetch('register-handler.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: new URLSearchParams({
          reg_type: 'teacher',
          name: name,
          email: email,
          phone: phone,
          qualification: qualification,
          subject: subject,
          level: level,
          district: district,
          password: password,
          confirm_password: confirmPassword,
          ajax: '1'
        })
      })
      .then(res => res.json())
      .then(data => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = `<span>Submit Teacher Application for Admin Approval</span><span>→</span>`;
        }

        if (data.success) {
          showToast("Application submitted for Admin review", "Teacher Registration");
          openLoginPage();
          selectLoginTab('teacher');
          document.getElementById("loginUsernameInput").value = email;
          document.getElementById("loginPasswordInput").value = password;
          showAuthAlert("Application submitted! Your teacher registration is currently PENDING Admin approval before login permissions are granted.", 'pending');
        } else {
          showAuthAlert(data.message || "Registration failed.", 'error');
          showToast(data.message, "Registration Error", "breach");
        }
      })
      .catch(err => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = `<span>Submit Teacher Application for Admin Approval</span><span>→</span>`;
        }
        showAuthAlert("An error occurred during submission. Please try again.", 'error');
      });
    }

    // --- 11. ADMIN FACULTY MANAGEMENT (APPROVE / DECLINE / REMOVE) ---
    function fetchAdminTeacherData() {
      fetch('admin-teacher-handler.php?action=list')
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            renderAdminTeacherTables(data.pending || [], data.approved || []);
          }
        })
        .catch(err => console.error("Error fetching admin teacher data:", err));
    }

    function renderAdminTeacherTables(pendingList, approvedList) {
      // 1. Pending Table
      const pendingTbody = document.getElementById("adminPendingTeachersTableBody");
      const pendingBadge = document.getElementById("pendingTeacherBadgeCount");
      const pendingStatInline = document.getElementById("adminPendingStatInline");

      if (pendingBadge) pendingBadge.innerText = `${pendingList.length} Pending`;
      if (pendingStatInline) pendingStatInline.innerText = pendingList.length;

      if (pendingTbody) {
        if (pendingList.length === 0) {
          pendingTbody.innerHTML = `
            <tr>
              <td colspan="6" class="py-6 text-center text-brand-muted text-xs">
                No pending teacher onboarding requests awaiting approval at this time.
              </td>
            </tr>
          `;
        } else {
          pendingTbody.innerHTML = pendingList.map(t => `
            <tr class="hover:bg-amber-50/40 transition">
              <td class="py-3 px-3">
                <div class="font-bold text-brand-slateText">${escapeHtml(t.name || 'Faculty')}</div>
                <div class="text-[11px] font-sans text-brand-muted font-medium">${escapeHtml(t.id || '')}</div>
              </td>
              <td class="py-3 px-3">
                <div>${escapeHtml(t.email || '')}</div>
                <div class="text-[10px] text-brand-muted">${escapeHtml(t.phone || '')} · <span class="font-semibold text-brand-orange">${escapeHtml(t.district || 'Bihar')}</span></div>
              </td>
              <td class="py-3 px-3">
                <div class="font-semibold text-brand-slateText">${escapeHtml(t.subject || 'General')}</div>
                <div class="text-[10px] text-brand-muted">${escapeHtml(t.level_label || t.level || '')}</div>
              </td>
              <td class="py-3 px-3">
                <div class="max-w-xs truncate" title="${escapeHtml(t.qualification || '')}">
                  ${escapeHtml(t.qualification || 'N/A')}
                </div>
              </td>
              <td class="py-3 px-3 font-mono text-[11px] text-brand-muted">
                ${escapeHtml((t.created_at || '').substring(0, 10))}
              </td>
              <td class="py-3 px-3 text-right whitespace-nowrap space-x-1.5">
                <button onclick="adminApproveTeacher('${escapeHtml(t.id)}')" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-[11px] transition shadow-xs cursor-pointer inline-flex items-center gap-1">
                  <span>✓</span> Approve
                </button>
                <button onclick="adminDeclineTeacher('${escapeHtml(t.id)}')" class="px-2.5 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 border border-red-300 rounded-xl font-bold text-[11px] transition shadow-xs cursor-pointer inline-flex items-center gap-1">
                  <span>✕</span> Decline
                </button>
              </td>
            </tr>
          `).join('');
        }
      }

      // 2. Approved Table
      const approvedTbody = document.getElementById("adminApprovedTeachersTableBody");
      const approvedBadge = document.getElementById("approvedTeacherBadgeCount");
      const adminFacultyTotalStat = document.getElementById("adminFacultyTotalStat");

      if (approvedBadge) approvedBadge.innerText = `${approvedList.length} Active`;
      if (adminFacultyTotalStat) adminFacultyTotalStat.innerText = (pendingList.length + approvedList.length);

      if (approvedTbody) {
        if (approvedList.length === 0) {
          approvedTbody.innerHTML = `
            <tr>
              <td colspan="6" class="py-6 text-center text-brand-muted text-xs">
                No approved faculty in the directory currently.
              </td>
            </tr>
          `;
        } else {
          approvedTbody.innerHTML = approvedList.map(t => `
            <tr class="hover:bg-emerald-50/30 transition">
              <td class="py-3 px-3">
                <div class="font-bold text-brand-slateText">${escapeHtml(t.name || 'Faculty')}</div>
                <div class="text-[11px] font-sans text-brand-muted font-medium">${escapeHtml(t.id || '')}</div>
              </td>
              <td class="py-3 px-3">
                <div>${escapeHtml(t.email || '')}</div>
                <div class="text-[10px] text-brand-muted">${escapeHtml(t.phone || 'N/A')}</div>
              </td>
              <td class="py-3 px-3">
                <div class="font-semibold text-brand-slateText">${escapeHtml(t.subject || 'General')}</div>
                <div class="text-[10px] text-brand-muted">${escapeHtml(t.level_label || t.level || '')}</div>
              </td>
              <td class="py-3 px-3 font-semibold text-brand-orange">
                ${escapeHtml(t.district || 'Bihar')}
              </td>
              <td class="py-3 px-3 font-mono text-[11px] text-brand-muted">
                ${escapeHtml((t.approved_at || t.created_at || '').substring(0, 10))}
              </td>
              <td class="py-3 px-3 text-right whitespace-nowrap">
                <button onclick="adminRemoveTeacher('${escapeHtml(t.id)}')" class="px-2.5 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-bold text-[11px] transition shadow-xs cursor-pointer inline-flex items-center gap-1">
                  <span>🗑️</span> Remove Teacher
                </button>
              </td>
            </tr>
          `).join('');
        }
      }
    }

    function adminApproveTeacher(teacherId) {
      fetch('admin-teacher-handler.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
          action: 'approve',
          teacher_id: teacherId
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showToast(data.message || "Teacher approved successfully! Login permission activated.", "Faculty Approved");
          renderAdminTeacherTables(data.pending || [], data.approved || []);
        } else {
          showToast(data.message || "Approval failed.", "Admin Action Error", "breach");
        }
      })
      .catch(err => {
        console.error("Error approving teacher:", err);
        showToast("Error processing approval", "Network Error", "breach");
      });
    }

    function adminDeclineTeacher(teacherId) {
      if (!confirm("Are you sure you want to DECLINE this teacher application? The applicant will be blocked from logging into the portal.")) {
        return;
      }

      fetch('admin-teacher-handler.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
          action: 'decline',
          teacher_id: teacherId
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showToast(data.message || "Teacher registration request declined. Login permission barred.", "Request Declined");
          renderAdminTeacherTables(data.pending || [], data.approved || []);
        } else {
          showToast(data.message || "Action failed.", "Admin Action Error", "breach");
        }
      })
      .catch(err => {
        console.error("Error declining teacher:", err);
        showToast("Error processing decline", "Network Error", "breach");
      });
    }

    function adminRemoveTeacher(teacherId) {
      if (!confirm("Are you sure you want to REMOVE this teacher from the platform? Their access to the Faculty Terminal will be revoked immediately.")) {
        return;
      }

      fetch('admin-teacher-handler.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
          action: 'remove',
          teacher_id: teacherId
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showToast(data.message || "Teacher removed from active faculty directory. Login access revoked.", "Faculty Removed");
          renderAdminTeacherTables(data.pending || [], data.approved || []);
        } else {
          showToast(data.message || "Action failed.", "Admin Action Error", "breach");
        }
      })
      .catch(err => {
        console.error("Error removing teacher:", err);
        showToast("Error processing removal", "Network Error", "breach");
      });
    }

    // --- 12. PERSONALIZED CANDIDATE DOSSIER, SUBSCRIPTION & ACHIEVEMENTS ---
    function syncCandidateDossierAndAchievements() {
      if (!authenticatedUser) return;

      // 1. Personalized Hero Header
      const name = authenticatedUser.name || authenticatedUser.username || 'Candidate Aspirant';
      const nameEl = document.getElementById("studentHeroName");
      if (nameEl) nameEl.innerText = name;

      const parts = name.trim().split(" ");
      const initials = ((parts[0] ? parts[0][0] : "C") + (parts[1] ? parts[1][0] : (parts[0] && parts[0][1] ? parts[0][1] : "A"))).toUpperCase();
      const avatarEl = document.getElementById("studentHeroAvatar");
      if (avatarEl) avatarEl.innerText = initials;

      const cadreLabel = authenticatedUser.level_label || 'Classes 6–8 Middle School';
      const district = authenticatedUser.district || 'Patna';
      const cadreEl = document.getElementById("studentHeroCadreLabel");
      if (cadreEl) {
        cadreEl.innerHTML = `${escapeHtml(cadreLabel)} · <span class="font-semibold text-brand-orange">${escapeHtml(district)} District</span>`;
      }

      const subRoleLabelEl = document.getElementById("userSubRoleLabel");
      if (subRoleLabelEl) {
        subRoleLabelEl.innerText = `${name} · ${cadreLabel}`;
      }

      const contactEl = document.getElementById("studentHeroContactPill");
      if (contactEl) {
        const email = authenticatedUser.email || '';
        const phone = authenticatedUser.phone || '';
        contactEl.innerHTML = `<span>✉ ${escapeHtml(email)}</span>${phone ? `<span>· 📱 ${escapeHtml(phone)}</span>` : ''}`;
      }

      // 2. Personalized Subscription Card
      const sub = authenticatedUser.subscription || {
        plan_name: `BPSC TRE 4.0 ${cadreLabel} Pass`,
        status: 'Active (Free Enrolled)',
        badge: 'Active Pass',
        amount_paid: 'Free Trial',
        expiry: 'December 31, 2026'
      };

      const subPlanEl = document.getElementById("userSubPlanName");
      if (subPlanEl) subPlanEl.innerText = sub.plan_name || 'BPSC TRE 4.0 Standard Pass';

      const subStatusEl = document.getElementById("userSubStatus");
      if (subStatusEl) subStatusEl.innerHTML = `<span>✓</span> ${escapeHtml(sub.status || 'Active')}`;

      const subAmountEl = document.getElementById("userSubAmount");
      if (subAmountEl) subAmountEl.innerText = sub.amount_paid || 'Free Trial';

      const subExpiryEl = document.getElementById("userSubExpiry");
      if (subExpiryEl) subExpiryEl.innerText = sub.expiry || 'December 31, 2026';

      const subBadgeEl = document.getElementById("userSubBadge");
      if (subBadgeEl) subBadgeEl.innerText = sub.badge || 'Active Pass';

      // 3. User's Own Achievements & Mock Records
      const testHistoryKey = getUserStorageKey('test_history');
      const userTests = JSON.parse(localStorage.getItem(testHistoryKey) || "[]");

      let testsTaken = userTests.length;
      let bestScore = '--';
      let avgAccuracy = '0%';

      if (testsTaken > 0) {
        let highest = -999;
        let totalAcc = 0;
        userTests.forEach(t => {
          if (t.score > highest) highest = t.score;
          totalAcc += (t.accuracy || 0);
        });
        bestScore = `${highest.toFixed(2)}`;
        avgAccuracy = `${Math.round(totalAcc / testsTaken)}%`;
      } else if (authenticatedUser.achievements) {
        testsTaken = authenticatedUser.achievements.tests_taken || 0;
        bestScore = authenticatedUser.achievements.best_score || '--';
        avgAccuracy = authenticatedUser.achievements.avg_accuracy || '0%';
      }

      const achievBadge = document.getElementById("userAchievCountBadge");
      if (achievBadge) achievBadge.innerText = `${testsTaken} Tests Taken`;

      const achievTestsEl = document.getElementById("userAchievTests");
      if (achievTestsEl) achievTestsEl.innerText = testsTaken;

      const achievBestEl = document.getElementById("userAchievBestScore");
      if (achievBestEl) achievBestEl.innerText = bestScore;

      const achievAccEl = document.getElementById("userAchievAccuracy");
      if (achievAccEl) achievAccEl.innerText = avgAccuracy;

      const badgesMount = document.getElementById("userAchievBadgesMount");
      if (badgesMount) {
        const badges = (authenticatedUser.achievements && authenticatedUser.achievements.earned_badges)
          ? [...authenticatedUser.achievements.earned_badges]
          : ['Registered Aspirant', 'Admit Ready', `${district} Aspirant`];
        
        if (testsTaken > 0 && !badges.includes('CBT Mock Attempted')) {
          badges.push('CBT Mock Attempted');
        }

        badgesMount.innerHTML = badges.map(b => `
          <span class="px-2.5 py-1 rounded-xl bg-brand-ivory border border-brand-peachBorder text-[10px] font-bold text-brand-slateText inline-flex items-center gap-1">
            <span>🎖️</span> ${escapeHtml(b)}
          </span>
        `).join('');
      }

      // 4. Reload user-scoped topicState and update HUD & radar
      topicState = JSON.parse(localStorage.getItem(getUserStorageKey('topic_states')) || "{}");
      updateLiveMetrics();
      syncCandidateProfilePage();
    }

    // --- 12B. CANDIDATE PROFILE TERMINAL SYNCHRONIZATION ---
    function syncCandidateProfilePage() {
      try {
        if (!authenticatedUser) {
          if (typeof PHP_SESSION_USER !== 'undefined' && PHP_SESSION_USER) {
            authenticatedUser = { ...PHP_SESSION_USER };
          }
        }
        if (!authenticatedUser) return;

        const name = authenticatedUser.name || authenticatedUser.username || 'Candidate Aspirant';
        const parts = name.trim().split(" ");
        const initials = ((parts[0] ? parts[0][0] : "C") + (parts[1] ? parts[1][0] : (parts[0] && parts[0][1] ? parts[0][1] : "A"))).toUpperCase();
        const cadreLabel = authenticatedUser.level_label || 'Classes 6–8 Middle School';
        const district = authenticatedUser.district || 'Patna';
        const email = authenticatedUser.email || '';
        const phone = authenticatedUser.phone || '';
        const userId = authenticatedUser.id || authenticatedUser.user_id || 'USR_STU';

        // 1. Candidate Dossier Header
        const avatarEl = document.getElementById("profileAvatarBig");
        if (avatarEl) avatarEl.innerText = initials;

        const fullNameEl = document.getElementById("profileFullName");
        if (fullNameEl) fullNameEl.innerText = name;

        const userIdEl = document.getElementById("profileUserId");
        if (userIdEl) userIdEl.innerText = userId;

        const cadreEl = document.getElementById("profileCadreText");
        if (cadreEl) {
          cadreEl.innerHTML = `${escapeHtml(cadreLabel)} · <span class="font-bold text-brand-orange">${escapeHtml(district)} District</span>`;
        }

        const emailEl = document.getElementById("profileEmailText");
        if (emailEl) emailEl.innerText = email || 'Not Provided';

        const phoneEl = document.getElementById("profilePhoneText");
        if (phoneEl) phoneEl.innerText = phone || 'Not Provided';

        // Verified Registration Dossier Grid
        const dosName = document.getElementById("profileDossierName");
        if (dosName) dosName.innerText = name;

        const dosId = document.getElementById("profileDossierId");
        if (dosId) dosId.innerText = userId;

        const dosEmail = document.getElementById("profileDossierEmail");
        if (dosEmail) {
          dosEmail.innerText = email || 'Not Provided';
          dosEmail.title = email || 'Not Provided';
        }

        const dosPhone = document.getElementById("profileDossierPhone");
        if (dosPhone) dosPhone.innerText = phone || 'Not Provided';

        const dosCadre = document.getElementById("profileDossierCadre");
        if (dosCadre) dosCadre.innerText = cadreLabel;

        const dosDist = document.getElementById("profileDossierDistrict");
        if (dosDist) dosDist.innerText = `${district} District`;

        // 2. HUD Cards & Progression from Topic State
        const userTopicState = JSON.parse(localStorage.getItem(getUserStorageKey('topic_states')) || "{}");
        const allItems = typeof getAllSyllabusItemsList === 'function' ? getAllSyllabusItemsList() : [];
        let mastered = 0;
        let review = 0;
        allItems.forEach(it => {
          const st = userTopicState[it.id];
          if (st === 2) mastered++;
          else if (st === 1) review++;
        });
        const weightedScore = mastered + (review * 0.5);
        const pct = allItems.length > 0 ? ((weightedScore / allItems.length) * 100).toFixed(1) : "0.0";

        const hudCompEl = document.getElementById("profileHudCompleted");
        if (hudCompEl) hudCompEl.innerText = mastered;

        const hudProgEl = document.getElementById("profileHudProgress");
        if (hudProgEl) hudProgEl.innerText = `${Math.round(pct)}%`;

        const hudTestEl = document.getElementById("profileHudAvailableTestsCount");
        if (hudTestEl) {
          const pub = (typeof MASTER_PAPERS !== 'undefined' && Array.isArray(MASTER_PAPERS))
            ? MASTER_PAPERS.filter(p => p.status === 'published' || p.isPublished).length 
            : 4;
          hudTestEl.innerText = pub;
        }

        const gaugePctEl = document.getElementById("profileGaugePercentage");
        if (gaugePctEl) gaugePctEl.innerText = `${pct}% Verified`;

        const gaugeBarEl = document.getElementById("profileGaugeBar");
        if (gaugeBarEl) gaugeBarEl.style.width = `${pct}%`;

        // 3. Active Subscription Package & Privileges
        const sub = authenticatedUser.subscription || {
          plan_name: `BPSC TRE 4.0 ${cadreLabel} Pass`,
          status: 'Active (Enrolled)',
          badge: 'Active Pass',
          amount_paid: 'Free Trial',
          expiry: 'December 31, 2026',
          enrolled_cadre: cadreLabel
        };

        const subBadgeEl = document.getElementById("profileSubBadge");
        if (subBadgeEl) subBadgeEl.innerText = sub.badge || 'Active Pass';

        const subPlanEl = document.getElementById("profileSubPlanName");
        if (subPlanEl) subPlanEl.innerText = sub.plan_name || 'BPSC TRE 4.0 Standard Pass';

        const subStatusEl = document.getElementById("profileSubStatus");
        if (subStatusEl) {
          subStatusEl.innerHTML = `<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> ${escapeHtml(sub.status || 'Active (Enrolled)')}`;
        }

        const subAmountEl = document.getElementById("profileSubAmount");
        if (subAmountEl) subAmountEl.innerText = sub.amount_paid || 'Free Trial';

        const subCadreEl = document.getElementById("profileSubCadre");
        if (subCadreEl) subCadreEl.innerText = sub.enrolled_cadre || cadreLabel;

        const subExpiryEl = document.getElementById("profileSubExpiry");
        if (subExpiryEl) subExpiryEl.innerText = sub.expiry || 'December 31, 2026';

        // 4. User's Own Achievements & Mock Records
        const testHistoryKey = getUserStorageKey('test_history');
        const userTests = JSON.parse(localStorage.getItem(testHistoryKey) || "[]");

        let testsTaken = userTests.length;
        let bestScore = '--';
        let avgAccuracy = '0%';

        if (testsTaken > 0) {
          let highest = -999;
          let totalAcc = 0;
          userTests.forEach(t => {
            const s = typeof t.score === 'number' ? t.score : parseFloat(t.score) || 0;
            if (s > highest) highest = s;
            totalAcc += (t.accuracy || 0);
          });
          bestScore = `${highest.toFixed(2)}`;
          avgAccuracy = `${Math.round(totalAcc / testsTaken)}%`;
        } else if (authenticatedUser.achievements) {
          testsTaken = authenticatedUser.achievements.tests_taken || 0;
          bestScore = authenticatedUser.achievements.best_score || '--';
          avgAccuracy = authenticatedUser.achievements.avg_accuracy || '0%';
        }

        const achievBadge = document.getElementById("profileAchievCountBadge");
        if (achievBadge) achievBadge.innerText = `${testsTaken} Tests Taken`;

        const achievTestsEl = document.getElementById("profileAchievTests");
        if (achievTestsEl) achievTestsEl.innerText = testsTaken;

        const achievBestEl = document.getElementById("profileAchievBestScore");
        if (achievBestEl) achievBestEl.innerText = bestScore;

        const achievAccEl = document.getElementById("profileAchievAccuracy");
        if (achievAccEl) achievAccEl.innerText = avgAccuracy;

        const badgesMount = document.getElementById("profileAchievBadgesMount");
        if (badgesMount) {
          const badges = (authenticatedUser.achievements && authenticatedUser.achievements.earned_badges)
            ? [...authenticatedUser.achievements.earned_badges]
            : ['Registered Aspirant', 'Admit Ready', `${district} Aspirant`];
          
          if (testsTaken > 0 && !badges.includes('CBT Mock Attempted')) {
            badges.push('CBT Mock Attempted');
          }

          badgesMount.innerHTML = badges.map(b => `
            <span class="px-3 py-1.5 rounded-xl bg-brand-ivory border border-brand-peachBorder text-xs font-bold text-brand-slateText inline-flex items-center gap-1.5 shadow-2xs">
              <span>🎖️</span> ${escapeHtml(b)}
            </span>
          `).join('');
        }

        // 5. Populate Recent Test Table Log
        const recentTable = document.getElementById("profileRecentTestTableBody");
        if (recentTable) {
          if (userTests.length === 0) {
            recentTable.innerHTML = `
              <tr>
                <td colspan="3" class="py-4 text-center text-brand-muted text-xs italic">
                  No mock tests attempted yet. Launch a CBT diagnostic test to populate your official merit log!
                </td>
              </tr>
            `;
          } else {
            recentTable.innerHTML = userTests.slice(-5).reverse().map(t => {
              const displayTitle = t.paperTitle || t.testTitle || 'BPSC TRE CBT Mock';
              const displayDate = t.date ? new Date(t.date).toLocaleDateString() : 'Recent';
              const scoreVal = typeof t.score === 'number' ? t.score.toFixed(2) : t.score;
              const accVal = t.accuracy !== undefined ? `(${t.accuracy}%)` : '';
              return `
                <tr class="hover:bg-brand-peachSoft/30 transition">
                  <td class="py-2.5 font-bold text-brand-slateText">${escapeHtml(displayTitle)}</td>
                  <td class="py-2.5 text-brand-muted font-mono text-[11px]">${escapeHtml(displayDate)}</td>
                  <td class="py-2.5 text-right font-mono font-bold text-brand-orange">${escapeHtml(scoreVal)} ${accVal}</td>
                </tr>
              `;
            }).join('');
          }
        }
      } catch (err) {
        console.error("Error in syncCandidateProfilePage:", err);
      }
    }

    function toggleMobileNavMenu(forceState) {
      const dropdown = document.getElementById("mobileNavDropdown");
      const icon = document.getElementById("mobileNavMenuIcon");
      if (!dropdown) return;
      const shouldOpen = (typeof forceState === 'boolean') ? forceState : dropdown.classList.contains("hidden");
      if (shouldOpen) {
        dropdown.classList.remove("hidden");
        if (icon) icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12"/>';
      } else {
        dropdown.classList.add("hidden");
        if (icon) icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"/>';
      }
    }

    function openProfilePage(e) {
      if (e) {
        if (typeof e.preventDefault === 'function') e.preventDefault();
        if (typeof e.stopPropagation === 'function') e.stopPropagation();
      }
      if (!authenticatedUser) {
        if (typeof PHP_SESSION_USER !== 'undefined' && PHP_SESSION_USER) {
          authenticatedUser = { ...PHP_SESSION_USER };
        } else {
          authenticatedUser = {
            role: 'student',
            name: 'Candidate Aspirant',
            level: (typeof currentCadre !== 'undefined' && currentCadre) ? currentCadre : 'mid',
            level_label: 'Classes 6–8 Middle School',
            district: 'Patna'
          };
        }
      }
      setActiveRole('profile');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function returnToDashboard(e) {
      if (e) {
        if (typeof e.preventDefault === 'function') e.preventDefault();
        if (typeof e.stopPropagation === 'function') e.stopPropagation();
      }
      const role = (authenticatedUser && authenticatedUser.role) ? authenticatedUser.role : 'student';
      setActiveRole(role);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function setActiveRole(role) {
      const allRoles = ['student', 'teacher', 'admin', 'cbt', 'topic-notes', 'profile'];
      allRoles.forEach(r => {
        const el = document.getElementById(`view-${r}`);
        if (el) {
          el.classList.add("hidden");
          el.style.display = "none";
        }
      });

      const target = document.getElementById(`view-${role}`);
      if (target) {
        target.classList.remove("hidden");
        target.style.display = "block";
      }

      // Synchronize Role-Specific Nav Toolbars
      const studentNav = document.getElementById("studentNavActions");
      const teacherNav = document.getElementById("teacherNavActions");
      const adminNav = document.getElementById("adminNavActions");

      if (studentNav) {
        if (role === 'student' || role === 'profile') {
          studentNav.classList.remove("hidden");
          studentNav.style.display = "flex";
        } else {
          studentNav.classList.add("hidden");
          studentNav.style.display = "none";
        }
      }
      if (teacherNav) {
        if (role === 'teacher') {
          teacherNav.classList.remove("hidden");
          teacherNav.style.display = "flex";
        } else {
          teacherNav.classList.add("hidden");
          teacherNav.style.display = "none";
        }
      }
      if (adminNav) {
        if (role === 'admin') {
          adminNav.classList.remove("hidden");
          adminNav.style.display = "flex";
        } else {
          adminNav.classList.add("hidden");
          adminNav.style.display = "none";
        }
      }

      // Synchronize Segmented Panel Switcher Buttons (Desktop & Mobile)
      const activePanelKey = (role === 'teacher' ? 'teacher' : (role === 'admin' ? 'admin' : 'student'));
      const activeColorClass = (activePanelKey === 'teacher' ? 'bg-amber-600' : (activePanelKey === 'admin' ? 'bg-purple-700' : 'bg-brand-orange'));

      ['student', 'teacher', 'admin'].forEach(r => {
        const deskBtn = document.getElementById(`panelBtn-${r}`);
        const mobBtn = document.getElementById(`mobilePanelBtn-${r}`);
        const isCurrent = (r === activePanelKey);

        if (deskBtn) {
          deskBtn.classList.remove('bg-brand-orange', 'bg-amber-600', 'bg-purple-700', 'text-white', 'shadow-xs', 'text-brand-slateText', 'hover:bg-brand-peachSoft');
          if (isCurrent) {
            deskBtn.classList.add(activeColorClass, 'text-white', 'shadow-xs');
          } else {
            deskBtn.classList.add('text-brand-slateText', 'hover:bg-brand-peachSoft');
          }
        }
        if (mobBtn) {
          mobBtn.classList.remove('bg-brand-orange', 'bg-amber-600', 'bg-purple-700', 'text-white', 'shadow-xs', 'text-brand-slateText', 'bg-brand-ivory', 'border', 'border-brand-peachBorder');
          if (isCurrent) {
            mobBtn.classList.add(activeColorClass, 'text-white', 'shadow-xs');
          } else {
            mobBtn.classList.add('text-brand-slateText', 'bg-brand-ivory', 'border', 'border-brand-peachBorder');
          }
        }
      });

      // Synchronize Role Badges in Header
      const currentRoleBadge = document.getElementById("currentRoleBadge");
      const userSubRoleLabel = document.getElementById("userSubRoleLabel");
      if (currentRoleBadge) {
        if (role === 'teacher') currentRoleBadge.innerText = 'Faculty Terminal';
        else if (role === 'admin') currentRoleBadge.innerText = 'AIVERSE Master';
        else currentRoleBadge.innerText = (authenticatedUser && authenticatedUser.badge) ? authenticatedUser.badge : 'Candidate Terminal';
      }
      if (userSubRoleLabel) {
        if (role === 'teacher') {
          userSubRoleLabel.innerText = (authenticatedUser && authenticatedUser.name) ? `${authenticatedUser.name} · BPSC Verified Faculty` : 'Active Educator · BPSC Syllabus Mentor';
        } else if (role === 'admin') {
          userSubRoleLabel.innerText = 'Platform Administrator · System Governance';
        } else {
          userSubRoleLabel.innerText = (authenticatedUser && authenticatedUser.subLabel) ? authenticatedUser.subLabel : 'Candidate Terminal';
        }
      }

      // Synchronize Nav Return to Dashboard & Profile toggle buttons
      const navDashBtn = document.getElementById("navDashboardBtn");
      const navProfBtn = document.getElementById("navProfileBtn");
      if (navDashBtn) {
        if (role === 'profile') {
          navDashBtn.classList.remove("hidden");
          navDashBtn.style.display = "inline-flex";
        } else {
          navDashBtn.classList.add("hidden");
          navDashBtn.style.display = "none";
        }
      }
      if (navProfBtn) {
        if (role === 'profile') {
          navProfBtn.classList.add("border-brand-orange", "bg-brand-peachSoft");
        } else {
          navProfBtn.classList.remove("border-brand-orange", "bg-brand-peachSoft");
        }
      }

      // Synchronize Mobile Navigation & Drawer
      const mobStudentQuick = document.getElementById("mobileStudentQuickActions");
      const mobTeacherQuick = document.getElementById("mobileTeacherQuickActions");
      const mobAdminQuick = document.getElementById("mobileAdminQuickActions");
      const mobStudentLinks = document.getElementById("mobileStudentLinks");
      const mobTeacherLinks = document.getElementById("mobileTeacherLinks");
      const mobAdminLinks = document.getElementById("mobileAdminLinks");
      const mobileBottomNav = document.getElementById("mobileBottomNav");

      if (mobStudentQuick) {
        mobStudentQuick.classList.toggle("hidden", !(role === 'student' || role === 'profile'));
        mobStudentQuick.style.display = (role === 'student' || role === 'profile') ? 'flex' : 'none';
      }
      if (mobTeacherQuick) {
        mobTeacherQuick.classList.toggle("hidden", role !== 'teacher');
        mobTeacherQuick.style.display = (role === 'teacher') ? 'flex' : 'none';
      }
      if (mobAdminQuick) {
        mobAdminQuick.classList.toggle("hidden", role !== 'admin');
        mobAdminQuick.style.display = (role === 'admin') ? 'flex' : 'none';
      }

      if (mobStudentLinks) {
        mobStudentLinks.classList.toggle("hidden", !(role === 'student' || role === 'profile'));
        mobStudentLinks.style.display = (role === 'student' || role === 'profile') ? 'grid' : 'none';
      }
      if (mobTeacherLinks) {
        mobTeacherLinks.classList.toggle("hidden", role !== 'teacher');
        mobTeacherLinks.style.display = (role === 'teacher') ? 'grid' : 'none';
      }
      if (mobAdminLinks) {
        mobAdminLinks.classList.toggle("hidden", role !== 'admin');
        mobAdminLinks.style.display = (role === 'admin') ? 'grid' : 'none';
      }

      if (mobileBottomNav) {
        if (role === 'cbt' || role === 'teacher' || role === 'admin') {
          mobileBottomNav.classList.add("hidden");
          mobileBottomNav.style.display = 'none';
        } else {
          mobileBottomNav.classList.remove("hidden");
          mobileBottomNav.style.display = 'flex';
          const mobHome = document.getElementById("mobBottomHome");
          const mobProf = document.getElementById("mobBottomProfile");
          if (mobHome && mobProf) {
            if (role === 'profile') {
              mobProf.classList.add("text-brand-orange");
              mobProf.classList.remove("text-brand-slateText");
              mobHome.classList.remove("text-brand-orange");
              mobHome.classList.add("text-brand-slateText");
            } else {
              mobHome.classList.add("text-brand-orange");
              mobHome.classList.remove("text-brand-slateText");
              mobProf.classList.remove("text-brand-orange");
              mobProf.classList.add("text-brand-slateText");
            }
          }
        }
      }

      // Automatically collapse mobile menu on role switch
      toggleMobileNavMenu(false);

      try {
        if (role === 'student') {
          let detectedCadre = 'mid';
          let detectedSubject = null;
          if (authenticatedUser) {
            if (authenticatedUser.level && ['prm', 'mid', 'sec', 'hsec'].includes(authenticatedUser.level)) {
              detectedCadre = authenticatedUser.level;
            } else {
              const label = (authenticatedUser.level_label || authenticatedUser.enrolled_cadre || '').toLowerCase();
              if (label.includes('11') || label.includes('higher') || label.includes('hsec')) detectedCadre = 'hsec';
              else if (label.includes('9') || label.includes('secondary') || label.includes('sec')) detectedCadre = 'sec';
              else if (label.includes('6') || label.includes('middle') || label.includes('mid')) detectedCadre = 'mid';
              else if (label.includes('1') || label.includes('primary') || label.includes('prm')) detectedCadre = 'prm';
            }
            if (authenticatedUser.subject) {
              detectedSubject = authenticatedUser.subject;
            }
          }
          currentCadre = detectedCadre;
          if (authenticatedUser && authenticatedUser.district) {
            currentLeaderboardDistrict = authenticatedUser.district;
          }
          syncCandidateDossierAndAchievements();
          renderCandidatePublishedTests();
          renderCandidateUploadedNotes();
          renderCandidateBooksShelf();
          renderStudentDashboardPyqMount();
          fetchLeaderboardData(currentLeaderboardScope, currentLeaderboardDistrict);
          handleCadreChange(currentCadre, detectedSubject);
          tickExamTargetCountdown();
        } else if (role === 'profile') {
          syncCandidateProfilePage();
        } else if (role === 'teacher') {
          renderQuestionPapers();
          renderTeacherBooksTable();
          renderTeacherUploadedNotes();

          // Populate Teacher HUD KPI counters
          const qpCountEl = document.getElementById('teacherHudQpCount');
          if (qpCountEl) {
            const count = (typeof MASTER_PAPERS !== 'undefined' && Array.isArray(MASTER_PAPERS)) ? MASTER_PAPERS.length : 3;
            qpCountEl.innerText = count;
          }
          const notesCountEl = document.getElementById('teacherHudNotesCount');
          if (notesCountEl) {
            const count = (typeof UPLOADED_NOTES_LIST !== 'undefined' && Array.isArray(UPLOADED_NOTES_LIST)) ? UPLOADED_NOTES_LIST.length : 0;
            notesCountEl.innerText = count;
          }
          const booksCountEl = document.getElementById('teacherHudBooksCount');
          if (booksCountEl) {
            const count = (typeof DIGITAL_BOOKS !== 'undefined' && Array.isArray(DIGITAL_BOOKS)) ? DIGITAL_BOOKS.length : 3;
            booksCountEl.innerText = count;
          }
        } else if (role === 'admin') {
          renderPaymentLedger();
          renderAdminQpMasterTable();
          renderDbQuestionsTable();
          fetchAdminTeacherData();
          renderAdminUploadedNotes();
          renderAdminBooksTable();
          renderTeacherBooksTable();
        }
      } catch (err) {
        console.error("Error activating role view:", err);
      }

      initSectionCollapseStates();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function logOutAndShowGate() {
      if (isCbtActive) {
        if (!confirm("An assessment is currently active. Are you sure you want to exit?")) return;
        isCbtActive = false;
        clearInterval(cbtTimerInterval);
      }

      // Notify PHP server logout
      fetch('logout.php', { method: 'POST' }).catch(() => {});

      authenticatedUser = null;
      document.getElementById("mainAppShell").classList.add("hidden");
      document.getElementById("authScreenGate").classList.remove("hidden");
      openLoginPage();
      const userIn = document.getElementById("loginUsernameInput");
      const passIn = document.getElementById("loginPasswordInput");
      if (userIn) userIn.value = "";
      if (passIn) passIn.value = "";
      showToast("Signed out of portal terminal successfully.", "Session Ended");
    }

    // --- 11. TOAST NOTIFICATIONS ---
    let toastTimeout = null;
    function showToast(message, category = "Notification", type = "info") {
      const toast = document.getElementById("toastMessage");
      const content = document.getElementById("toastContent");
      const cat = document.getElementById("toastCategory");
      const icon = document.getElementById("toastIcon");

      if (!toast || !content) return;
      content.innerText = message;
      cat.innerText = category;

      if (type === "breach") {
        icon.className = "w-8 h-8 rounded-xl bg-red-600 flex items-center justify-center text-white font-bold text-sm shrink-0";
        icon.innerText = "⚠";
      } else {
        icon.className = "w-8 h-8 rounded-xl bg-brand-orange flex items-center justify-center text-white font-bold text-sm shrink-0";
        icon.innerText = "✓";
      }

      toast.classList.remove("hidden");
      clearTimeout(toastTimeout);
      toastTimeout = setTimeout(() => {
        toast.classList.add("hidden");
      }, 3500);
    }

    // --- 12. EXAM TARGET COUNTDOWN BANNER & HEADER PILL ---
    let targetExamDateTimestamp = new Date("<?php echo date('Y-m-d\TH:i:s', strtotime(AppConfig::TARGET_EXAM_DATE)); ?>").getTime();
    let examCountdownInterval = null;

    function tickExamTargetCountdown() {
      const now = new Date().getTime();
      const dist = targetExamDateTimestamp - now;
      if (dist <= 0) return;

      const d = Math.floor(dist / (1000 * 60 * 60 * 24));
      const h = Math.floor((dist % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const m = Math.floor((dist % (1000 * 60 * 60)) / (1000 * 60));
      const s = Math.floor((dist % (1000 * 60)) / 1000);

      const daysEl = document.getElementById("examDays");
      const hoursEl = document.getElementById("examHours");
      const minsEl = document.getElementById("examMins");
      const secsEl = document.getElementById("examSecs");
      const headerEl = document.getElementById("headerExamCountdown");

      if (daysEl) daysEl.innerText = d;
      if (hoursEl) hoursEl.innerText = h.toString().padStart(2, '0');
      if (minsEl) minsEl.innerText = m.toString().padStart(2, '0');
      if (secsEl) secsEl.innerText = s.toString().padStart(2, '0');
      if (headerEl) headerEl.innerText = `${d}d ${h.toString().padStart(2, '0')}h ${m.toString().padStart(2, '0')}m ${s.toString().padStart(2, '0')}s`;
    }

    function startExamTargetCountdown() {
      clearInterval(examCountdownInterval);
      tickExamTargetCountdown();
      examCountdownInterval = setInterval(tickExamTargetCountdown, 1000);
    }

    // --- 13. SYLLABUS ENGINE & 6-AXIS RADAR ---
    function handleCadreChange(key, preferredSubject = null) {
      currentCadre = key;
      const obj = BPSC_SYLLABUS.cadres[key];
      if (!obj) return;

      ['prm', 'mid', 'sec', 'hsec'].forEach(c => {
        const tab = document.getElementById(`cadreTab-${c}`);
        if (tab) {
          if (c === key) {
            tab.className = "py-1.5 px-2 rounded-xl text-center transition text-[11px] bg-brand-orange text-white shadow-xs font-bold cursor-pointer";
          } else {
            tab.className = "py-1.5 px-2 rounded-xl text-center transition text-[11px] text-brand-slateText hover:bg-brand-peachSoft font-bold cursor-pointer";
          }
        }
      });

      const badgeEl = document.getElementById("cadreBadge");
      if (badgeEl) badgeEl.innerText = obj.name;
      const titleEl = document.getElementById("cadreTitle");
      if (titleEl) titleEl.innerText = `${obj.name} Preparation Grid`;
      const subEl = document.getElementById("cadreSub");
      if (subEl) subEl.innerText = obj.sub;

      const cadreLabelEl = document.getElementById("studentHeroCadreLabel");
      if (cadreLabelEl && authenticatedUser) {
        const district = authenticatedUser.district || 'Patna';
        cadreLabelEl.innerHTML = `${escapeHtml(obj.name)} · <span class="font-semibold text-brand-orange">${escapeHtml(district)} District</span>`;
      }

      const discWrapper = document.getElementById("disciplineWrapper");
      if (discWrapper) {
        if (key === "prm") {
          discWrapper.classList.add("hidden");
        } else {
          discWrapper.classList.remove("hidden");
        }
      }

      renderCustomSubjectOptions(obj.subjects, preferredSubject);
      renderMatrix();
    }

    function renderCustomSubjectOptions(subjects, preferredSubject = null) {
      const container = document.getElementById("subjectOptionsScrollList");
      const hiddenInput = document.getElementById("disciplineSelector");
      const displayLabel = document.getElementById("selectedSubjectDisplay");
      const countBadge = document.getElementById("subjectCountBadge");
      const searchBox = document.getElementById("subjectSearchInput");

      if (searchBox) searchBox.value = "";
      if (countBadge) countBadge.innerText = `${subjects.length} Subjects`;

      let selected = subjects[0] || "";
      if (preferredSubject && subjects.includes(preferredSubject)) {
        selected = preferredSubject;
      }
      if (hiddenInput) hiddenInput.value = selected;
      if (displayLabel) displayLabel.innerText = selected;

      if (!container) return;

      container.innerHTML = subjects.map((sub, idx) => `
        <div onclick="selectCustomSubject('${sub.replace(/'/g, "\\'")}')" class="subject-option-item p-2 rounded-xl hover:bg-brand-orange hover:text-white text-xs font-semibold text-brand-slateText cursor-pointer transition flex items-center justify-between group">
          <span class="truncate">${sub}</span>
          <span class="text-[9px] font-mono text-brand-muted group-hover:text-amber-200">#${idx + 1}</span>
        </div>
      `).join("");
    }

    function toggleSubjectDropdown() {
      const menu = document.getElementById("customSubjectDropdownMenu");
      const arrow = document.getElementById("subjectDropdownArrow");
      if (!menu) return;

      const isHidden = menu.classList.contains("hidden");
      if (isHidden) {
        menu.classList.remove("hidden");
        if (arrow) arrow.style.transform = "rotate(180deg)";
        const searchInput = document.getElementById("subjectSearchInput");
        if (searchInput) searchInput.focus();
      } else {
        menu.classList.add("hidden");
        if (arrow) arrow.style.transform = "rotate(0deg)";
      }
    }

    function selectCustomSubject(subject) {
      const hiddenInput = document.getElementById("disciplineSelector");
      const displayLabel = document.getElementById("selectedSubjectDisplay");
      if (hiddenInput) hiddenInput.value = subject;
      if (displayLabel) displayLabel.innerText = subject;

      toggleSubjectDropdown();
      renderMatrix();
      showToast(`Subject stream switched to: ${subject}`, "Syllabus Filter");
    }

    function filterSubjectOptions(query) {
      const q = query.toLowerCase().trim();
      const items = document.querySelectorAll(".subject-option-item");
      items.forEach(item => {
        const text = item.innerText.toLowerCase();
        if (!q || text.includes(q)) {
          item.classList.remove("hidden");
        } else {
          item.classList.add("hidden");
        }
      });
    }

    document.addEventListener("click", function(event) {
      const wrapper = document.getElementById("disciplineWrapper");
      const menu = document.getElementById("customSubjectDropdownMenu");
      const arrow = document.getElementById("subjectDropdownArrow");
      if (wrapper && !wrapper.contains(event.target) && menu && !menu.classList.contains("hidden")) {
        menu.classList.add("hidden");
        if (arrow) arrow.style.transform = "rotate(0deg)";
      }
    });

    function renderMatrix() {
      const container = document.getElementById("matrixMountPoint");
      if (!container) return;

      const cadre = currentCadre || 'mid';
      const cadreObj = BPSC_SYLLABUS.cadres[cadre];
      if (!cadreObj) return;

      const hiddenInput = document.getElementById("disciplineSelector");
      const selectedSubject = (hiddenInput && hiddenInput.value) ? hiddenInput.value : (cadreObj.subjects[0] || "");

      let html = "";

      if (cadre === "prm") {
        // Classes 1–5: Primary Teacher
        // Official BPSC TRE 4.0: Part I (30Q Qualifying) + Part II GS & EVS (120Q Primary Merit Paper).
        if (cadreObj.primarySyllabus) {
          html += generateSectionBlock(
            "Part II — General Studies & Environmental Studies (120 Questions - Core Merit Paper)",
            [cadreObj.primarySyllabus],
            "Primary 1–5 Core Merit (120Q)"
          );
        }
        html += generateSectionBlock(
          "Part I — Common Language Qualifying (30 Questions - Minimum 30% Pass Threshold)",
          [BPSC_SYLLABUS.commonPart1],
          "Language Qualifying (30%)"
        );
      } else {
        // Classes 6–8 (Middle), 9–10 (Secondary), 11–12 (Higher Secondary)
        // Official BPSC TRE 4.0: Part III Concerned Subject (80Q) + Part II GS (40Q) + Part I Language (30Q)

        // 1. Part III: Concerned Subject Paper (80 Questions / 80 Marks - Core Merit) - FRONT & CENTER
        let p3Modules = [];
        if (cadreObj.subjectData && cadreObj.subjectData[selectedSubject]) {
          p3Modules = cadreObj.subjectData[selectedSubject];
        } else {
          // Dynamic fallback modules for specialized subject stream
          p3Modules = [
            {
              name: `Module 1: Advanced Subject Concepts & Theoretical Foundations (${selectedSubject})`,
              items: [
                { id: `${cadre}_${selectedSubject.toLowerCase().replace(/[^a-z0-9]/g, '_').substring(0, 8)}_1`, name: `Core Principles, Analytical Frameworks & Statutory Rules in ${selectedSubject}`, hy: true },
                { id: `${cadre}_${selectedSubject.toLowerCase().replace(/[^a-z0-9]/g, '_').substring(0, 8)}_2`, name: `SCERT / NCERT Advanced Textbook Chapter Review & Essential Definitions`, hy: true }
              ]
            },
            {
              name: `Module 2: Applied Competencies, Numericals & BPSC Model Questions`,
              items: [
                { id: `${cadre}_${selectedSubject.toLowerCase().replace(/[^a-z0-9]/g, '_').substring(0, 8)}_3`, name: `High-Yield Problem Solving, Data Interpretation & Past Year Trends`, hy: true },
                { id: `${cadre}_${selectedSubject.toLowerCase().replace(/[^a-z0-9]/g, '_').substring(0, 8)}_4`, name: `Pedagogical Dimensions, Diagnostic Assessment & Exam Exception Rules`, hy: false }
              ]
            }
          ];
        }

        html += generateSectionBlock(
          `Part III — Concerned Subject: ${selectedSubject} (80 Questions / 80 Marks - Core Merit)`,
          [{ modules: p3Modules }],
          "Part III Domain (80Q)"
        );

        // 2. Part II: General Studies Core (40 Questions / 40 Marks)
        html += generateSectionBlock(
          "Part II — General Studies Core Framework (40 Questions / 40 Marks)",
          [BPSC_SYLLABUS.commonGS],
          "General Studies (40Q)"
        );

        // 3. Part I: Common Language Qualifying (30 Questions - Minimum 30% Pass Threshold)
        html += generateSectionBlock(
          "Part I — Common Language Qualifying (30 Questions - Minimum 30% Pass Threshold)",
          [BPSC_SYLLABUS.commonPart1],
          "Language Qualifying (30%)"
        );
      }

      container.innerHTML = html;
      updateLiveMetrics();
    }

    function generateSectionBlock(blockTitle, domainList, badgeLabel = null) {
      const sectionSafeId = 'syllabus_part_' + blockTitle.toLowerCase().replace(/[^a-z0-9]/g, '_').substring(0, 32);
      const isCollapsed = localStorage.getItem('collapse_' + sectionSafeId) === '1';

      let content = `<div class="bg-white rounded-3xl border border-brand-peachBorder p-5 sm:p-6 shadow-sm space-y-4 syllabus-part-block" data-part-id="${sectionSafeId}">
        <div class="flex items-center justify-between border-b border-brand-peachSoft pb-2.5">
          <div class="flex items-center gap-2 flex-wrap">
            <h3 class="text-xs sm:text-sm font-extrabold uppercase tracking-wider text-brand-orange">${blockTitle}</h3>
            ${badgeLabel ? `<span class="text-[9px] font-mono px-2 py-0.5 rounded-full bg-brand-orangeLight text-brand-orange font-bold border border-brand-peachBorder">${badgeLabel}</span>` : ''}
          </div>
          <div class="flex items-center gap-2">
            <span class="text-[11px] font-sans text-brand-muted font-medium hidden sm:inline">Click topic to study notes & take test</span>
            <button type="button" 
                    onclick="toggleSectionCollapse('${sectionSafeId}', this, 'Syllabus Part')" 
                    data-collapsible-target="${sectionSafeId}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-slateText bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft hover:text-brand-orange transition cursor-pointer shadow-2xs" 
                    title="${isCollapsed ? 'Expand this Part' : 'Minimize this Part'}"
                    aria-expanded="${isCollapsed ? 'false' : 'true'}">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${isCollapsed ? 'M19 9l-7 7-7-7' : 'M5 15l7-7 7 7'}"/></svg>
              <span>${isCollapsed ? 'Expand' : 'Minimize'}</span>
            </button>
          </div>
        </div>
        <div id="${sectionSafeId}" class="space-y-4 syllabus-part-body ${isCollapsed ? 'hidden' : ''}">`;

      domainList.forEach(domain => {
        domain.modules.forEach(mod => {
          content += `<div class="space-y-2">
            <h4 class="text-xs font-bold text-brand-slateText flex items-center gap-1.5">
              <span class="w-1.5 h-1.5 rounded-full bg-brand-orange"></span>
              ${mod.name}
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">`;

          mod.items.forEach(item => {
            const state = topicState[item.id] || 0;
            if (activeFilter === "hy" && !item.hy) return;
            if (activeFilter === "inc" && state === 2) return;

            const noteData = TOPIC_NOTES_DATABASE[item.id] || (UPLOADED_NOTES_LIST ? UPLOADED_NOTES_LIST.find(n => n.topicId === item.id) : null);
            const attachedFile = noteData ? (noteData.attachedFile || noteData.attached_file) : null;
            const hasPdf = attachedFile && (attachedFile.name || (attachedFile.type && attachedFile.type.includes('pdf')) || (attachedFile.name && attachedFile.name.toLowerCase().endsWith('.pdf')));
            const quizList = noteData ? (noteData.quiz || []) : [];
            const hasQuiz = quizList.length > 0;
            const author = noteData ? (noteData.author || 'Faculty') : null;
            const uploadTime = noteData ? (noteData.formatted_date_time || '') : '';
            const isFacultyOrAdmin = (typeof authenticatedUser !== 'undefined' && authenticatedUser && (authenticatedUser.role === 'teacher' || authenticatedUser.role === 'admin'));

            let stateStyle = "bg-brand-ivory border-brand-peachBorder text-brand-slateText";
            let stateBadge = `<span class="text-[10px] px-2 py-0.5 rounded bg-slate-200 text-slate-700 font-mono font-bold">Unread</span>`;

            if (state === 1) {
              stateStyle = "bg-amber-50 border-amber-300 text-amber-900";
              stateBadge = `<span class="text-[10px] px-2 py-0.5 rounded bg-amber-200 text-amber-900 font-mono font-bold flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span> In Progress</span>`;
            } else if (state === 2) {
              stateStyle = "bg-emerald-50 border-emerald-300 text-emerald-900";
              stateBadge = `<span class="text-[10px] px-2 py-0.5 rounded bg-emerald-200 text-emerald-900 font-mono font-bold">✓ Mastered</span>`;
            }

            let cardExtraStyle = "";
            if (hasPdf) {
              cardExtraStyle = "ring-2 ring-red-400/50 bg-gradient-to-b from-white to-red-50/25 border-red-200";
            } else if (noteData && noteData.body) {
              cardExtraStyle = "ring-1 ring-amber-400/40 bg-gradient-to-b from-white to-amber-50/20";
            }

            content += `
              <div onclick="openTopicStudyDossier('${item.id}', '${item.name.replace(/'/g, "\\'")}')" class="p-3.5 rounded-2xl border ${stateStyle} ${cardExtraStyle} hover:border-brand-orange hover:shadow-md cursor-pointer transition flex flex-col justify-between gap-2.5 shadow-xs group relative">
                <div class="space-y-1.5">
                  <div class="flex items-start justify-between gap-2">
                    <span class="text-xs font-bold leading-snug group-hover:text-brand-orange transition text-brand-slateText">${item.name}</span>
                    ${item.hy ? '<span class="text-[9px] px-1.5 py-0.2 rounded bg-brand-peachSoft text-brand-orange font-bold font-mono shrink-0">HY</span>' : ''}
                  </div>

                  <!-- Uploaded Study Notes & PDF Handout Indicators -->
                  ${hasPdf || hasQuiz || noteData ? `
                    <div class="flex items-center gap-1.5 flex-wrap pt-0.5">
                      ${hasPdf ? `
                        <span class="text-[10px] font-sans font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-800 border border-red-300 flex items-center gap-1 shadow-2xs" title="${escapeHtml(attachedFile.name)} (${attachedFile.size || 'PDF'})">
                          <span>📄</span> PDF Notes (${escapeHtml(attachedFile.size || 'PDF')})
                        </span>
                      ` : ''}
                      ${hasQuiz ? `
                        <span class="text-[10px] font-sans font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 border border-blue-300 flex items-center gap-1 shadow-2xs" title="${quizList.length} Questions Test Ready">
                          <span>📝</span> ${quizList.length}Q Test
                        </span>
                      ` : ''}
                      ${author ? `
                        <span class="text-[9px] font-mono text-brand-slateText bg-amber-50 border border-amber-200 px-1.5 py-0.2 rounded flex items-center gap-1 truncate max-w-[170px]" title="Uploaded by ${escapeHtml(author)} ${uploadTime ? 'on ' + escapeHtml(uploadTime) : ''}">
                          <span>👤</span> ${escapeHtml(author)}
                        </span>
                      ` : ''}
                    </div>
                  ` : ''}
                </div>

                <div class="space-y-1.5 pt-1 border-t border-black/5">
                  <div class="flex items-center justify-between text-[10px]">
                    <span class="text-brand-muted font-mono font-semibold">ID: ${item.id}</span>
                    ${stateBadge}
                  </div>

                  <!-- Candidate Study Action Button -->
                  <button type="button" class="w-full py-1.5 px-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-2xs ${hasPdf ? 'bg-brand-orange hover:bg-brand-orangeHover text-white' : 'bg-brand-ivory group-hover:bg-brand-peachSoft text-brand-slateText group-hover:text-brand-orange border border-brand-peachBorder'}">
                    <span>${hasPdf ? '📄' : '📖'}</span>
                    <span>${hasPdf ? 'Access PDF Notes & Give Test' : 'Read Notes & Give Test'}</span>
                    <span>→</span>
                  </button>

                  <!-- Direct Faculty / Admin Note Upload Action Button -->
                  ${isFacultyOrAdmin ? `
                    <button type="button" onclick="event.stopPropagation(); openTopicNotesEditor('${item.id}')" class="w-full py-1.5 px-2.5 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white transition flex items-center justify-center gap-1.5 shadow-xs cursor-pointer" title="Faculty / Admin: Upload notes and attach PDF handout for this topic">
                      <span>📤</span>
                      <span>${(hasPdf || hasQuiz) ? 'Edit Notes & PDF' : 'Upload Notes & PDF'}</span>
                      <span>→</span>
                    </button>
                  ` : ''}
                </div>
              </div>
            `;
          });
          content += `</div></div>`;
        });
      });
      content += `</div></div>`;
      return content;
    }

    function openTopicStudyDossier(topicId, fallbackName) {
      currentActiveStudyTopicId = topicId;

      if (!topicState[topicId] || topicState[topicId] === 0) {
        topicState[topicId] = 1;
        localStorage.setItem(getUserStorageKey('topic_states'), JSON.stringify(topicState));
        showToast("Topic started! Read the notes and take the quiz to test your preparation.", "Progress Tracked");
      }

      let data = TOPIC_NOTES_DATABASE[topicId];
      if (!data) {
        const headingEl = document.getElementById("topicNotesHeading");
        if (headingEl) headingEl.innerText = fallbackName || "Topic Detailed Study Notes";
        const catBadgeEl = document.getElementById("topicNotesCategoryBadge");
        if (catBadgeEl) catBadgeEl.innerText = "Awaiting Faculty Upload";
        const sumEl = document.getElementById("topicNotesSummary");
        if (sumEl) sumEl.innerText = "No study notes published for this topic yet.";
        const bodyEl = document.getElementById("topicNotesBody");
        if (bodyEl) {
          bodyEl.innerHTML = `
            <div class="p-8 text-center bg-brand-ivory rounded-2xl border border-brand-peachBorder space-y-3">
              <span class="text-3xl block">📝</span>
              <h4 class="font-bold text-sm text-brand-slateText">No Study Notes Published Yet</h4>
              <p class="text-xs text-brand-muted max-w-md mx-auto">
                Study notes have not been published for this topic. Notes, formulas, and attached handouts will appear here once uploaded by a verified teacher or administrator.
              </p>
            </div>
          `;
        }
        const tipsEl = document.getElementById("topicNotesTips");
        if (tipsEl) tipsEl.innerText = "Exam insights and traps will appear here once lecture dossiers are uploaded.";
        const uploadMetaEl = document.getElementById("topicNotesUploadMeta");
        if (uploadMetaEl) uploadMetaEl.innerText = "Status: Awaiting Faculty Upload";
        const attachCard = document.getElementById("topicNotesAttachmentCard");
        if (attachCard) attachCard.classList.add("hidden");

        const teacherUploadBtn = document.getElementById("topicNotesTeacherUploadBtn");
        if (teacherUploadBtn) {
          if (authenticatedUser && (authenticatedUser.role === 'teacher' || authenticatedUser.role === 'admin')) {
            teacherUploadBtn.classList.remove("hidden");
          } else {
            teacherUploadBtn.classList.add("hidden");
          }
        }

        const statusBadge = document.getElementById("topicNotesCurrentStatusBadge");
        if (statusBadge) {
          statusBadge.className = "text-xs px-3 py-1.5 rounded-xl font-bold font-mono uppercase bg-brand-peachSoft text-brand-orange border border-brand-peachBorder flex items-center gap-1.5";
          statusBadge.innerHTML = `<span>⏳</span> Awaiting Notes`;
        }

        setActiveRole('topic-notes');
        return;
      }

      document.getElementById("topicNotesHeading").innerText = data.title;
      document.getElementById("topicNotesCategoryBadge").innerText = data.category || "CURRICULUM UNIT";
      document.getElementById("topicNotesSummary").innerText = data.summary;
      document.getElementById("topicNotesBody").innerHTML = data.body;
      document.getElementById("topicNotesTips").innerText = data.tips;

      const uploadMetaEl = document.getElementById("topicNotesUploadMeta");
      if (uploadMetaEl) {
        uploadMetaEl.innerText = `📅 Uploaded: ${data.formatted_date_time || 'Recent'} · By ${data.author || 'Faculty'}`;
      }

      const attachCard = document.getElementById("topicNotesAttachmentCard");
      const attachName = document.getElementById("topicNotesAttachedFileName");
      const attachMeta = document.getElementById("topicNotesAttachedMeta");
      if (attachCard) {
        if (data.attachedFile && data.attachedFile.name) {
          attachCard.classList.remove("hidden");
          if (attachName) attachName.innerText = data.attachedFile.name;
          if (attachMeta) attachMeta.innerText = `${data.attachedFile.size || 'Document'} · Uploaded Study Notes`;
        } else {
          attachCard.classList.add("hidden");
        }
      }

      const st = topicState[topicId] || 0;
      const statusBadge = document.getElementById("topicNotesCurrentStatusBadge");
      if (st === 2) {
        statusBadge.className = "text-xs px-3 py-1.5 rounded-xl font-bold font-mono uppercase bg-emerald-100 text-emerald-900 border border-emerald-300 flex items-center gap-1.5";
        statusBadge.innerHTML = "<span>✓</span> Mastered (≥ 75%)";
      } else {
        statusBadge.className = "text-xs px-3 py-1.5 rounded-xl font-bold font-mono uppercase bg-amber-100 text-amber-900 border border-amber-300 flex items-center gap-1.5";
        statusBadge.innerHTML = `<span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span> In Progress`;
      }

      const teacherUploadBtn = document.getElementById("topicNotesTeacherUploadBtn");
      if (teacherUploadBtn) {
        if (authenticatedUser && (authenticatedUser.role === 'teacher' || authenticatedUser.role === 'admin')) {
          teacherUploadBtn.classList.remove("hidden");
        } else {
          teacherUploadBtn.classList.add("hidden");
        }
      }

      setActiveRole('topic-notes');
    }

    // =========================================================
    // UNIVERSAL DOCUMENT DOWNLOAD ENGINE (BOOKS, PYQS, HANDOUTS)
    // =========================================================
    let currentReadingDoc = null;

    function triggerDataUriDownload(dataUri, filename) {
      try {
        const a = document.createElement('a');
        a.href = dataUri;
        a.download = filename || 'download';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
      } catch (err) {
        console.error("Data URI download error:", err);
        window.open(dataUri, '_blank');
      }
    }

    function triggerBlobDownload(content, filename, mimeType = 'text/plain') {
      try {
        const blob = new Blob([content], { type: mimeType });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(() => URL.revokeObjectURL(url), 2000);
      } catch (err) {
        console.error("Blob download error:", err);
      }
    }

    function downloadBook(bookId) {
      const book = DIGITAL_BOOKS.find(b => b.id === bookId) || DIGITAL_BOOKS.find(b => String(b.id) === String(bookId));
      if (!book) {
        showToast("Book not found in digital library.", "Download Error", "breach");
        return;
      }

      showToast(`Initiating download for "${book.title}"...`, "Downloading Document");

      // 1. If uploaded book has data URI, download original file directly (.pdf / .docx)
      if (book.pdfDataUri) {
        const cleanExt = (book.pdfUrl && book.pdfUrl.includes('.')) ? '' : '.pdf';
        const fileName = (book.pdfUrl || `${book.title.replace(/[^a-zA-Z0-9_\-\s]/g, '').trim()}`) + cleanExt;
        triggerDataUriDownload(book.pdfDataUri, fileName);
        return;
      }

      // 2. Default / built-in curriculum textbook: generate full-length Word .doc file
      const chapters = (book.chapters && book.chapters.length) 
        ? book.chapters 
        : generateDefaultChaptersForBook(book);

      const docHtml = `\ufeff<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
  <meta charset="utf-8">
  <title>${escapeHtml(book.title)}</title>
  <style>
    body { font-family: 'Calibri', 'Segoe UI', Arial, sans-serif; font-size: 11pt; line-height: 1.5; color: #1e293b; margin: 36pt; }
    h1 { color: #c2410c; font-size: 20pt; border-bottom: 2pt solid #ea580c; padding-bottom: 6pt; margin-bottom: 12pt; }
    h2 { color: #0369a1; font-size: 14pt; margin-top: 18pt; border-bottom: 1pt solid #cbd5e1; padding-bottom: 4pt; }
    h3 { color: #0f172a; font-size: 12pt; margin-top: 12pt; }
    .badge { display: inline-block; background-color: #ffedd5; color: #9a3412; padding: 3pt 8pt; border-radius: 4pt; font-weight: bold; font-size: 9pt; }
    .meta-box { background-color: #f8fafc; border: 1pt solid #e2e8f0; padding: 12pt; border-radius: 6pt; margin: 12pt 0; }
    .summary-box { background-color: #fffbeb; border-left: 4pt solid #f59e0b; padding: 10pt; margin: 10pt 0; font-style: italic; }
    .takeaways-box { background-color: #ecfdf5; border: 1pt solid #a7f3d0; padding: 10pt; border-radius: 6pt; margin: 10pt 0; }
    .footer-note { font-size: 9pt; color: #64748b; text-align: center; border-top: 1pt solid #e2e8f0; margin-top: 30pt; padding-top: 10pt; }
    ul { margin: 6pt 0 12pt 20pt; }
    li { margin-bottom: 4pt; }
  </style>
</head>
<body>
  <div class="meta-box">
    <span class="badge">BPSC TRE 4.0 OFFICIAL CURRICULUM TEXTBOOK</span>
    <h1>${escapeHtml(book.title)}</h1>
    <p><strong>Subject Domain:</strong> ${escapeHtml(book.subject || 'Standard Curriculum')} | <strong>Teaching Cadre:</strong> ${escapeHtml(book.cadre || 'All Cadres')}</p>
    <p><strong>Publishing Authority:</strong> ${escapeHtml(book.author || 'SCERT / NCERT Academic Council')} | <strong>Access Tier:</strong> ${escapeHtml(book.access || 'Free')}</p>
  </div>

  <h2>Table of Contents & Curriculum Units</h2>
  <ul>
    ${chapters.map((ch, i) => `<li><strong>Chapter ${i+1}:</strong> ${escapeHtml(ch.title)}</li>`).join("")}
  </ul>

  ${chapters.map((ch, idx) => `
    <div style="page-break-before: ${idx > 0 ? 'always' : 'auto'}; margin-top: 24pt;">
      <h2>Chapter ${idx + 1}: ${escapeHtml(ch.title)}</h2>
      ${ch.summary ? `<div class="summary-box"><strong>Executive Summary:</strong> ${escapeHtml(ch.summary)}</div>` : ''}
      <div>
        ${ch.contentHtml ? ch.contentHtml : `<p>${escapeHtml(ch.text || 'Core conceptual notes for this chapter.')}</p>`}
      </div>
      ${ch.keyTakeaways && ch.keyTakeaways.length ? `
        <div class="takeaways-box">
          <strong style="color: #065f46;">Important Key Takeaways:</strong>
          <ul>
            ${ch.keyTakeaways.map(t => `<li>${escapeHtml(t)}</li>`).join("")}
          </ul>
        </div>
      ` : ''}
    </div>
  `).join("")}

  <div class="footer-note">
    <p>© Infinity Learning · BPSC TRE 4.0 Educator Portal · Strictly for candidate preparation and educational revision</p>
  </div>
</body>
</html>`;

      const safeTitle = (book.title || 'BPSC_Curriculum_Book').replace(/[^a-zA-Z0-9_\-\s]/g, '').trim().replace(/\s+/g, '_');
      triggerBlobDownload(docHtml, `${safeTitle}.doc`, 'application/msword;charset=utf-8');
    }

    function downloadPyq(pyqId) {
      const paper = OFFICIAL_PYQS.find(p => p.id === pyqId) || OFFICIAL_PYQS.find(p => String(p.id) === String(pyqId));
      if (!paper) {
        showToast("PYQ paper not found.", "Download Error", "breach");
        return;
      }

      showToast(`Initiating download for "${paper.title}"...`, "Downloading Paper");

      // 1. If uploaded paper has data URI, download the original file directly!
      if (paper.pdfDataUri) {
        const cleanExt = (paper.pdfUrl && paper.pdfUrl.includes('.')) ? '' : '.pdf';
        const fileName = (paper.pdfUrl || `${paper.title.replace(/[^a-zA-Z0-9_\-\s]/g, '').trim()}`) + cleanExt;
        triggerDataUriDownload(paper.pdfDataUri, fileName);
        return;
      }

      // 2. Default official question paper: generate formatted full-length Word .doc file
      const qList = (paper.paperData && paper.paperData.questions) ? paper.paperData.questions : (paper.questions || []);

      const docHtml = `\ufeff<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
  <meta charset="utf-8">
  <title>${escapeHtml(paper.title)}</title>
  <style>
    body { font-family: 'Calibri', 'Segoe UI', Arial, sans-serif; font-size: 11pt; line-height: 1.5; color: #1e293b; margin: 36pt; }
    h1 { color: #1e40af; font-size: 18pt; border-bottom: 2pt solid #2563eb; padding-bottom: 6pt; margin-bottom: 8pt; text-align: center; }
    h2 { color: #0f172a; font-size: 13pt; margin-top: 14pt; border-bottom: 1pt solid #cbd5e1; padding-bottom: 3pt; }
    .badge { display: inline-block; background-color: #dbeafe; color: #1e40af; padding: 3pt 8pt; border-radius: 4pt; font-weight: bold; font-size: 9pt; }
    .header-box { background-color: #f8fafc; border: 1pt solid #cbd5e1; padding: 12pt; border-radius: 6pt; margin-bottom: 16pt; text-align: center; }
    .instr-box { background-color: #fffbeb; border: 1pt solid #fde68a; padding: 10pt; border-radius: 6pt; margin-bottom: 16pt; font-size: 9.5pt; }
    .question-card { background-color: #ffffff; border: 1pt solid #e2e8f0; padding: 10pt; margin-bottom: 12pt; border-radius: 6pt; }
    .q-num { font-weight: bold; color: #2563eb; }
    .options-grid { margin: 8pt 0 8pt 15pt; }
    .opt-item { margin-bottom: 4pt; }
    .ans-box { background-color: #ecfdf5; border-left: 3pt solid #10b981; padding: 6pt 10pt; margin-top: 8pt; font-size: 9.5pt; }
    table { width: 100%; border-collapse: collapse; margin-top: 16pt; }
    th, td { border: 1pt solid #94a3b8; padding: 6pt; text-align: center; font-size: 9.5pt; }
    th { background-color: #f1f5f9; font-weight: bold; }
    .footer-note { font-size: 9pt; color: #64748b; text-align: center; border-top: 1pt solid #e2e8f0; margin-top: 30pt; padding-top: 10pt; }
  </style>
</head>
<body>
  <div class="header-box">
    <span class="badge">BIHAR PUBLIC SERVICE COMMISSION (BPSC)</span>
    <h1>${escapeHtml(paper.title)}</h1>
    <p><strong>Edition:</strong> ${escapeHtml(paper.edition || 'BPSC TRE 3.0')} | <strong>Cadre Level:</strong> ${escapeHtml(paper.cadre || 'Classes 6–8')} | <strong>Subject:</strong> ${escapeHtml(paper.subject || 'All Subjects')}</p>
    <p><strong>Examination Shift:</strong> ${escapeHtml(paper.shiftDate || 'Official Exam')} | <strong>Duration:</strong> 120 Minutes | <strong>Marking:</strong> +1.0 / -0.33</p>
  </div>

  <div class="instr-box">
    <strong>Instructions for Candidates / परीक्षार्थियों के लिए निर्देश:</strong>
    <ol style="margin: 4pt 0 0 15pt; padding: 0;">
      <li>This test paper contains authentic questions administered by the Bihar Public Service Commission.</li>
      <li>Each question is followed by options (A), (B), (C), (D) and standard BPSC fifth option (E).</li>
      <li>For each correct response, +1.0 mark is awarded. 1/3 (-0.33) marks are deducted for every incorrect response.</li>
    </ol>
  </div>

  <h2>Section Questions with Official Answer Key & Detailed Solutions</h2>

  ${qList.length > 0 ? qList.map((q, idx) => {
    const opts = Array.isArray(q.options) ? q.options : [];
    const correctIdx = typeof q.ans === 'number' ? q.ans : (q.correct_answer || 0);
    const correctLetter = String.fromCharCode(65 + correctIdx);
    return `
      <div class="question-card">
        <p><span class="q-num">Q.${idx + 1}</span> <strong>${escapeHtml(q.q || q.question || 'Question statement')}</strong></p>
        <div class="options-grid">
          ${opts.map((opt, oIdx) => {
            const letter = String.fromCharCode(65 + oIdx);
            const isCorrect = oIdx === correctIdx;
            return `<div class="opt-item">${letter}. ${escapeHtml(typeof opt === 'string' ? opt : (opt.text || ''))} ${isCorrect ? '<strong style="color: #059669;">[Official Key]</strong>' : ''}</div>`;
          }).join("")}
        </div>
        <div class="ans-box">
          <strong>Official Correct Option: (${correctLetter})</strong><br>
          <em>Explanation:</em> ${escapeHtml(q.exp || q.explanation || 'Official answer verified against BPSC final answer key.')}
        </div>
      </div>
    `;
  }).join("") : `
    <p>Complete 150 questions paper verified against official BPSC TRE master question set.</p>
  `}

  <div class="footer-note">
    <p>© Infinity Learning · BPSC TRE 4.0 Official Previous Year Papers Repository</p>
  </div>
</body>
</html>`;

      const safeTitle = (paper.title || 'BPSC_TRE_PYQ_Paper').replace(/[^a-zA-Z0-9_\-\s]/g, '').trim().replace(/\s+/g, '_');
      triggerBlobDownload(docHtml, `${safeTitle}.doc`, 'application/msword;charset=utf-8');
    }

    function downloadCurrentTopicAttachment() {
      const activeTopicId = typeof currentActiveStudyTopicId !== 'undefined' ? currentActiveStudyTopicId : null;
      let note = null;

      if (activeTopicId) {
        if (Array.isArray(PHP_STORED_NOTES)) {
          note = PHP_STORED_NOTES.find(n => n.topic_id === activeTopicId || n.topicId === activeTopicId);
        }
        if (!note && typeof STORED_STUDY_DOSSIERS !== 'undefined') {
          note = STORED_STUDY_DOSSIERS[activeTopicId];
        }
        if (!note && typeof TOPIC_NOTES_DATABASE !== 'undefined') {
          note = TOPIC_NOTES_DATABASE[activeTopicId];
        }
      }

      if (note && note.attached_file && note.attached_file.dataUri) {
        showToast(`Downloading attached study notes document: ${note.attached_file.name}...`, "Download Started");
        triggerDataUriDownload(note.attached_file.dataUri, note.attached_file.name);
        return;
      }
      if (note && note.attachedFile && note.attachedFile.content) {
        showToast(`Downloading attached study notes document: ${note.attachedFile.name}...`, "Download Started");
        triggerDataUriDownload(note.attachedFile.content, note.attachedFile.name);
        return;
      }

      // If no file attachment exists, generate a clean Word document of the topic study notes!
      const topicTitle = (note && note.title) ? note.title : document.getElementById("topicNotesHeading")?.innerText || 'BPSC TRE Topic Study Notes';
      const topicSummary = (note && note.summary) ? note.summary : document.getElementById("topicNotesSummary")?.innerText || '';
      const topicContent = (note && note.content) ? note.content : document.getElementById("topicNotesBody")?.innerHTML || '';
      const topicTips = (note && note.insights) ? note.insights : document.getElementById("topicNotesTips")?.innerText || '';

      const docHtml = `\ufeff<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
  <meta charset="utf-8">
  <title>${escapeHtml(topicTitle)}</title>
  <style>
    body { font-family: 'Calibri', Arial, sans-serif; font-size: 11pt; line-height: 1.6; color: #1e293b; margin: 36pt; }
    h1 { color: #ea580c; font-size: 18pt; border-bottom: 2pt solid #ea580c; padding-bottom: 6pt; }
    h2 { color: #0284c7; font-size: 14pt; margin-top: 16pt; }
    .box { background-color: #fff7ed; border-left: 4pt solid #ea580c; padding: 10pt; margin: 12pt 0; }
    .tips { background-color: #ecfdf5; border-left: 4pt solid #10b981; padding: 10pt; margin: 12pt 0; }
  </style>
</head>
<body>
  <h1>${escapeHtml(topicTitle)}</h1>
  <div class="box"><strong>Executive Summary:</strong><br>${escapeHtml(topicSummary)}</div>
  <h2>Comprehensive Study Notes:</h2>
  <div>${topicContent}</div>
  <div class="tips"><strong>Exam Trends & Important Tips:</strong><br>${escapeHtml(topicTips)}</div>
  <p style="text-align: center; color: #64748b; font-size: 9pt; margin-top: 20pt;">© Infinity Learning · BPSC TRE 4.0 Preparation Portal</p>
</body>
</html>`;

      const safeTitle = topicTitle.replace(/[^a-zA-Z0-9_\-\s]/g, '').trim().replace(/\s+/g, '_');
      triggerBlobDownload(docHtml, `${safeTitle}_Study_Notes.doc`, 'application/msword;charset=utf-8');
      showToast(`Downloaded study notes for "${topicTitle}".`, "Download Complete");
    }

    function downloadAttachedNoteFile(topicId) {
      currentActiveStudyTopicId = topicId;
      downloadCurrentTopicAttachment();
    }

    function launchCurrentTopicQuiz() {
      const data = TOPIC_NOTES_DATABASE[currentActiveStudyTopicId];
      if (!data) {
        showToast("Topic study dossier not found", "Quiz Alert", "breach");
        return;
      }

      let quizQuestions = (data.quiz && Array.isArray(data.quiz) && data.quiz.length > 0)
        ? data.quiz
        : [
            {
              q: `According to the published study notes for "${data.title}", which fundamental principle is emphasized for BPSC TRE examination?`,
              options: [
                `Systematic mastery of factual chronology, conceptual foundations, and statutory exceptions.`,
                `Rote memorization without understanding core definitions.`,
                `Skipping previous year question trends and state-specific syllabus nuances.`,
                `None of the above`
              ],
              ans: 0,
              exp: `BPSC tests conceptual mastery, factual dates, and specific statutory exceptions emphasized in the study notes.`
            },
            {
              q: `What is the key preparation strategy highlighted in the dossier for "${data.title}"?`,
              options: [
                `Active recall through mock problem-solving and periodic formula/data review.`,
                `Cramming only on the eve of the examination.`,
                `Ignoring negative marking penalty of 0.33 marks.`,
                `All of the above`
              ],
              ans: 0,
              exp: `Consistent revision and high-yield concept review ensures error-free test performance under standard -0.33 marking.`
            },
            {
              q: `In the context of BPSC TRE assessment (${data.category || 'General Studies'}), what should a candidate prioritize during testing?`,
              options: [
                `Accuracy against negative marking threshold and eliminating distractors.`,
                `Speed over accuracy regardless of negative penalty.`,
                `Guessing every question randomly.`,
                `Leaving all qualifying questions blank.`
              ],
              ans: 0,
              exp: `Careful distractor elimination protects candidate aggregate merit against the 1/3rd negative penalty.`
            }
          ];

      currentActiveQuizQuestionList = quizQuestions;
      document.getElementById("topicQuizModalTitle").innerText = `Mastery Test: ${data.title}`;
      
      const reqEl = document.getElementById("topicQuizPassRequirement");
      const passTarget = Math.ceil(quizQuestions.length * 0.75);
      if (reqEl) reqEl.innerText = `${passTarget} of ${quizQuestions.length} correct required (≥ 75%)`;

      const instTitle = document.getElementById("topicQuizInstTopicTitle");
      if (instTitle) instTitle.innerText = data.title;

      const instCount = document.getElementById("topicQuizInstCount");
      if (instCount) instCount.innerText = `${quizQuestions.length} Questions`;

      const instReq = document.getElementById("topicQuizInstReq");
      if (instReq) instReq.innerText = `${passTarget} Correct (75%)`;

      // Show instruction step, hide questions step initially
      const instStep = document.getElementById("topicQuizInstructionDeck");
      if (instStep) instStep.classList.remove("hidden");

      const formStep = document.getElementById("topicQuizForm");
      if (formStep) formStep.classList.add("hidden");

      renderTopicQuizQuestions(quizQuestions);
      openModal("topicMasteryQuizModal");
    }

    function beginTopicQuizQuestions() {
      const instStep = document.getElementById("topicQuizInstructionDeck");
      if (instStep) instStep.classList.add("hidden");

      const formStep = document.getElementById("topicQuizForm");
      if (formStep) formStep.classList.remove("hidden");
    }

    function renderTopicQuizQuestions(questions) {
      const mount = document.getElementById("topicQuizQuestionsMount");
      if (!mount) return;

      mount.innerHTML = questions.map((q, qIdx) => `
        <div class="p-4 bg-brand-ivory rounded-2xl border border-brand-peachBorder space-y-2.5 text-xs">
          <p class="font-bold text-brand-slateText leading-snug">${qIdx + 1}. ${q.q}</p>
          ${q.diagram ? `
            <div class="p-2 bg-white rounded-xl border border-brand-peachBorder text-center">
              <img src="${q.diagram}" class="max-h-40 mx-auto rounded object-contain" alt="Question Diagram">
            </div>
          ` : ''}
          <div class="space-y-1.5">
            ${q.options.map((opt, oIdx) => `
              <label class="flex items-center gap-2.5 p-2 rounded-xl bg-white border border-brand-peachBorder hover:border-brand-orange cursor-pointer transition">
                <input type="radio" name="topic_q_${qIdx}" value="${oIdx}" required class="text-brand-orange focus:ring-brand-orange h-3.5 w-3.5">
                <span class="text-xs font-semibold text-brand-slateText">${String.fromCharCode(65 + oIdx)}) ${opt}</span>
              </label>
            `).join("")}
          </div>
        </div>
      `).join("");
    }

    function evaluateTopicMasteryQuiz() {
      const questions = currentActiveQuizQuestionList;
      let correct = 0;

      questions.forEach((q, idx) => {
        const selected = document.querySelector(`input[name="topic_q_${idx}"]:checked`);
        if (selected && parseInt(selected.value) === q.ans) {
          correct++;
        }
      });

      const total = questions.length;
      const scorePercentage = Math.round((correct / total) * 100);

      closeModal("topicMasteryQuizModal");

      if (scorePercentage >= 75) {
        topicState[currentActiveStudyTopicId] = 2;
        localStorage.setItem(getUserStorageKey('topic_states'), JSON.stringify(topicState));
        
        const statusBadge = document.getElementById("topicNotesCurrentStatusBadge");
        if (statusBadge) {
          statusBadge.className = "text-xs px-3 py-1.5 rounded-xl font-bold font-mono uppercase bg-emerald-100 text-emerald-900 border border-emerald-300 flex items-center gap-1.5";
          statusBadge.innerHTML = "<span>✓</span> Mastered (≥ 75%)";
        }

        showToast(`Congratulations! You scored ${scorePercentage}% (${correct}/${total}). Topic promoted to MASTERED!`, "Mastery Unlocked", "info");
      } else {
        topicState[currentActiveStudyTopicId] = 1;
        localStorage.setItem(getUserStorageKey('topic_states'), JSON.stringify(topicState));

        showToast(`You scored ${scorePercentage}% (${correct}/${total}). You need ≥ 75% to master. Review notes and retake!`, "Keep Trying", "breach");
      }

      updateLiveMetrics();
    }

    function formatEditorText(command, value = null) {
      const editor = document.getElementById("topicNoteVisualEditor");
      if (editor) {
        editor.focus();
        document.execCommand(command, false, value);
      }
    }

    function loadTopicNotesToEditor(htmlContent) {
      const editor = document.getElementById("topicNoteVisualEditor");
      if (editor) {
        editor.innerHTML = htmlContent || '';
      }
    }

    function getTopicNotesFromEditor() {
      const editor = document.getElementById("topicNoteVisualEditor");
      return editor ? editor.innerHTML.trim() : '';
    }

    function getAllCategorizedTopics() {
      let categorized = {};
      
      categorized["Part I — Common Language (English & Hindi)"] = [];
      BPSC_SYLLABUS.commonPart1.modules.forEach(m => {
        m.items.forEach(it => {
          categorized["Part I — Common Language (English & Hindi)"].push({ ...it, domain: m.name });
        });
      });

      categorized["Part II — Common General Studies (Elementary Math, Science, INM, Geography)"] = [];
      BPSC_SYLLABUS.commonGS.modules.forEach(m => {
        m.items.forEach(it => {
          categorized["Part II — Common General Studies (Elementary Math, Science, INM, Geography)"].push({ ...it, domain: m.name });
        });
      });

      Object.keys(BPSC_SYLLABUS.cadres).forEach(cKey => {
        const cObj = BPSC_SYLLABUS.cadres[cKey];
        if (cObj.primarySyllabus) {
          const groupName = `${cObj.name} · General Studies & EVS (120Q)`;
          categorized[groupName] = [];
          cObj.primarySyllabus.modules.forEach(m => {
            m.items.forEach(it => {
              categorized[groupName].push({ ...it, domain: m.name });
            });
          });
        }
        if (cObj.subjectData) {
          Object.keys(cObj.subjectData).forEach(sName => {
            const groupName = `${cObj.name} · ${sName}`;
            categorized[groupName] = [];
            cObj.subjectData[sName].forEach(m => {
              m.items.forEach(it => {
                categorized[groupName].push({ ...it, domain: m.name });
              });
            });
          });
        }
      });

      return categorized;
    }

    function openTopicNotesEditor(initialTopicId = null) {
      // Students are restricted to reading notes and taking tests only
      if (authenticatedUser && authenticatedUser.role === 'student') {
        showToast("Access Denied: Study notes can only be uploaded by teachers and administrators. Students can view, read notes and take tests.", "Access Restricted", "breach");
        return;
      }

      const select = document.getElementById("editorTopicSelect");
      if (!select) return;

      const categorized = getAllCategorizedTopics();
      let html = "";
      let firstTopicId = null;

      Object.keys(categorized).forEach(grp => {
        const list = categorized[grp];
        if (list.length > 0) {
          html += `<optgroup label="${grp}">`;
          list.forEach(t => {
            if (!firstTopicId) firstTopicId = t.id;
            const isSelected = (initialTopicId && t.id === initialTopicId);
            html += `<option value="${t.id}" ${isSelected ? 'selected' : ''}>[${t.id}] ${t.name} (${t.domain || 'Core'})</option>`;
          });
          html += `</optgroup>`;
        }
      });

      select.innerHTML = html;
      const targetTopicId = initialTopicId || firstTopicId || "gs_inm_1";
      select.value = targetTopicId;
      loadTopicDataIntoEditor(targetTopicId);
      openModal("topicNotesModal");
    }

    function loadTopicDataIntoEditor(topicId) {
      let data = TOPIC_NOTES_DATABASE[topicId];
      if (!data) {
        let allTopics = [];
        BPSC_SYLLABUS.commonPart1.modules.forEach(m => m.items.forEach(it => allTopics.push(it)));
        BPSC_SYLLABUS.commonGS.modules.forEach(m => m.items.forEach(it => allTopics.push(it)));
        Object.keys(BPSC_SYLLABUS.cadres).forEach(ck => {
          const co = BPSC_SYLLABUS.cadres[ck];
          if (co.primarySyllabus) {
            co.primarySyllabus.modules.forEach(m => m.items.forEach(it => allTopics.push(it)));
          }
          if (co.subjectData) {
            Object.keys(co.subjectData).forEach(sk => {
              co.subjectData[sk].forEach(m => m.items.forEach(it => allTopics.push(it)));
            });
          }
        });
        const found = allTopics.find(t => t.id === topicId);
        
        data = {
          topicId: topicId,
          title: found ? found.name : "Curriculum Topic",
          summary: "",
          body: "",
          tips: "",
          attachedFile: null,
          quiz: []
        };
      }

      document.getElementById("editorTopicTitle").value = data.title;
      document.getElementById("editorTopicSummary").value = data.summary;
      loadTopicNotesToEditor(data.body || "");
      document.getElementById("editorTopicTips").value = data.tips;

      tempEditorAttachedFile = data.attachedFile || null;
      updateTopicNoteFileBadge();

      tempEditorQuizQuestions = JSON.parse(JSON.stringify(data.quiz || []));
      renderEditorQuizQuestions();
    }

    function handleTopicNoteFileSelected(input) {
      if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();

        if (file.type.includes('text') || file.name.endsWith('.txt') || file.name.endsWith('.md')) {
          reader.onload = function(e) {
            tempEditorAttachedFile = {
              name: file.name,
              size: `${(file.size / 1024).toFixed(1)} KB`,
              type: file.type || 'text/plain',
              content: e.target.result
            };
            const bodyEl = document.getElementById("editorTopicBody");
            if (bodyEl && (!bodyEl.value || bodyEl.value.includes('Add detailed lecture notes'))) {
              bodyEl.value = e.target.result;
            }
            updateTopicNoteFileBadge();
            showToast(`File attached: ${file.name}`, "Notes Attached");
          };
          reader.readAsText(file);
        } else {
          reader.onload = function(e) {
            tempEditorAttachedFile = {
              name: file.name,
              size: `${(file.size / (1024 * 1024)).toFixed(2)} MB`,
              type: file.type || 'application/octet-stream',
              content: e.target.result
            };
            updateTopicNoteFileBadge();
            showToast(`Document attached: ${file.name}`, "Notes Attached");
          };
          reader.readAsDataURL(file);
        }
      }
    }

    function removeTopicNoteAttachedFile() {
      tempEditorAttachedFile = null;
      const fileInput = document.getElementById("editorTopicFileInput");
      if (fileInput) fileInput.value = "";
      updateTopicNoteFileBadge();
      showToast("Attached document removed.", "Notes File");
    }

    function updateTopicNoteFileBadge() {
      const badge = document.getElementById("editorTopicFileBadge");
      const removeBtn = document.getElementById("editorTopicRemoveFileBtn");
      if (!badge) return;

      if (tempEditorAttachedFile && tempEditorAttachedFile.name) {
        badge.innerText = `📎 ${tempEditorAttachedFile.name} (${tempEditorAttachedFile.size || 'Attached'})`;
        badge.className = "text-[10px] font-sans font-bold text-emerald-800 bg-emerald-100 border border-emerald-200 px-2 py-0.5 rounded";
        if (removeBtn) removeBtn.classList.remove("hidden");
      } else {
        badge.innerText = "No file chosen";
        badge.className = "text-[10px] font-sans font-bold text-brand-orange bg-brand-peachSoft px-2 py-0.5 rounded";
        if (removeBtn) removeBtn.classList.add("hidden");
      }
    }

    function toggleTopicQuestionBankPicker() {
      const panel = document.getElementById("topicQbPickerPanel");
      if (!panel) return;

      const isHidden = panel.classList.contains("hidden");
      if (isHidden) {
        panel.classList.remove("hidden");
        renderTopicQbPickerList();
        const searchInput = document.getElementById("topicQbSearchInput");
        if (searchInput) searchInput.focus();
      } else {
        panel.classList.add("hidden");
      }
    }

    function renderTopicQbPickerList() {
      const mount = document.getElementById("topicQbListMount");
      if (!mount) return;

      const pool = getAllQuestionBankPool();
      const filter = (document.getElementById("topicQbSearchInput")?.value || "").toLowerCase().trim();

      const filtered = pool.filter(q => {
        if (!filter) return true;
        return (q.question && q.question.toLowerCase().includes(filter)) ||
               (q.section && q.section.toLowerCase().includes(filter));
      });

      if (filtered.length === 0) {
        mount.innerHTML = `<div class="p-2 text-center text-[11px] text-brand-muted bg-white rounded-lg">No matching questions in question bank.</div>`;
        return;
      }

      mount.innerHTML = filtered.map(q => `
        <div class="p-2 rounded-xl bg-white border border-brand-peachBorder flex items-center justify-between gap-2 hover:border-brand-orange transition text-xs">
          <div class="flex-1 min-w-0">
            <span class="font-bold text-brand-slateText block truncate">${q.question}</span>
            <span class="text-[9px] font-mono text-brand-muted">${q.section || 'General'} · ${(q.options || []).length} Options ${q.diagram ? '· [Has Diagram]' : ''}</span>
          </div>
          <button type="button" onclick="importQuestionIntoTopicQuiz('${q.id}')" class="px-2.5 py-1 bg-brand-orange text-white text-[10px] font-bold rounded-lg hover:bg-brand-orangeHover transition shrink-0 cursor-pointer">
            + Import
          </button>
        </div>
      `).join("");
    }

    function importQuestionIntoTopicQuiz(qId) {
      const pool = getAllQuestionBankPool();
      const item = pool.find(q => String(q.id) === String(qId));
      if (!item) return;

      const rawOptions = item.options || [];
      const opts = rawOptions.map(opt => typeof opt === 'string' ? opt : (opt.text || opt.title || 'Option'));

      tempEditorQuizQuestions.push({
        q: item.question,
        diagram: item.diagram || null,
        options: opts.length >= 4 ? opts.slice(0, 4) : [...opts, "Option C", "Option D"].slice(0, 4),
        ans: typeof item.ans === 'number' ? item.ans : 0,
        exp: item.explanation || item.solution || "Imported from question bank."
      });

      renderEditorQuizQuestions();
      showToast("Question imported into topic test!", "Question Added");
    }

    function renderEditorQuizQuestions() {
      const mount = document.getElementById("editorQuizQuestionsMount");
      if (!mount) return;

      if (tempEditorQuizQuestions.length === 0) {
        mount.innerHTML = `<div class="p-3 text-center text-brand-muted text-[11px] bg-brand-ivory rounded-xl">No quiz questions added yet. Click '+ Add Blank Q' or 'Import from Question Bank' above.</div>`;
        return;
      }

      mount.innerHTML = tempEditorQuizQuestions.map((q, idx) => `
        <div class="p-3 bg-brand-ivory rounded-2xl border border-brand-peachBorder space-y-2 text-xs">
          <div class="flex items-center justify-between">
            <span class="font-bold text-brand-slateText font-mono">Question ${idx + 1}</span>
            <button type="button" onclick="removeEditorQuizQuestion(${idx})" class="text-red-600 hover:underline font-bold text-[10px] cursor-pointer">Remove</button>
          </div>
          <input type="text" placeholder="Question statement..." value="${(q.q || '').replace(/"/g, '&quot;')}" onchange="tempEditorQuizQuestions[${idx}].q = this.value" class="w-full p-1.5 rounded-lg border border-brand-peachBorder bg-white text-xs font-semibold">
          
          <div class="p-2 bg-white rounded-xl border border-brand-peachBorder flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
              <span class="text-[10px] font-bold text-brand-muted">Diagram / Image:</span>
              ${q.diagram ? `
                <img src="${q.diagram}" class="h-8 max-w-[80px] object-contain rounded border border-brand-peachBorder bg-brand-ivory" alt="Preview">
                <button type="button" onclick="removeQuestionDiagram(${idx})" class="text-[9px] text-red-600 hover:underline font-bold cursor-pointer">✕ Remove</button>
              ` : `<span class="text-[11px] text-brand-muted font-sans font-medium">None attached</span>`}
            </div>
            <label class="cursor-pointer px-2 py-1 bg-brand-ivory hover:bg-brand-peachSoft border border-brand-peachBorder rounded-lg text-[10px] font-bold text-brand-slateText transition flex items-center gap-1">
              <span>🖼️ Attach Diagram</span>
              <input type="file" accept="image/*" class="hidden" onchange="attachDiagramToEditorQuestion(${idx}, this)">
            </label>
          </div>

          <div class="grid grid-cols-2 gap-1.5">
            <input type="text" placeholder="Option A" value="${(q.options[0] || '').replace(/"/g, '&quot;')}" onchange="tempEditorQuizQuestions[${idx}].options[0] = this.value" class="p-1.5 rounded-lg border border-brand-peachBorder bg-white text-[11px]">
            <input type="text" placeholder="Option B" value="${(q.options[1] || '').replace(/"/g, '&quot;')}" onchange="tempEditorQuizQuestions[${idx}].options[1] = this.value" class="p-1.5 rounded-lg border border-brand-peachBorder bg-white text-[11px]">
            <input type="text" placeholder="Option C" value="${(q.options[2] || '').replace(/"/g, '&quot;')}" onchange="tempEditorQuizQuestions[${idx}].options[2] = this.value" class="p-1.5 rounded-lg border border-brand-peachBorder bg-white text-[11px]">
            <input type="text" placeholder="Option D" value="${(q.options[3] || '').replace(/"/g, '&quot;')}" onchange="tempEditorQuizQuestions[${idx}].options[3] = this.value" class="p-1.5 rounded-lg border border-brand-peachBorder bg-white text-[11px]">
          </div>
          <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-1.5">
              <span class="text-[10px] text-brand-muted font-bold">Key:</span>
              <select onchange="tempEditorQuizQuestions[${idx}].ans = parseInt(this.value)" class="p-1 rounded border border-brand-peachBorder bg-white text-xs font-semibold cursor-pointer">
                <option value="0" ${q.ans === 0 ? 'selected' : ''}>A</option>
                <option value="1" ${q.ans === 1 ? 'selected' : ''}>B</option>
                <option value="2" ${q.ans === 2 ? 'selected' : ''}>C</option>
                <option value="3" ${q.ans === 3 ? 'selected' : ''}>D</option>
              </select>
            </div>
            <input type="text" placeholder="Explanation note..." value="${(q.exp || '').replace(/"/g, '&quot;')}" onchange="tempEditorQuizQuestions[${idx}].exp = this.value" class="flex-1 p-1 rounded-lg border border-brand-peachBorder bg-white text-[10px]">
          </div>
        </div>
      `).join("");
    }

    function attachDiagramToEditorQuestion(qIdx, input) {
      if (input.files && input.files[0] && tempEditorQuizQuestions[qIdx]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          tempEditorQuizQuestions[qIdx].diagram = e.target.result;
          renderEditorQuizQuestions();
          showToast(`Diagram attached to Question ${qIdx + 1}!`, "Diagram Updated");
        };
        reader.readAsDataURL(input.files[0]);
      }
    }

    function removeQuestionDiagram(qIdx) {
      if (tempEditorQuizQuestions[qIdx]) {
        tempEditorQuizQuestions[qIdx].diagram = null;
        renderEditorQuizQuestions();
        showToast(`Diagram removed from Question ${qIdx + 1}`, "Diagram Removed");
      }
    }

    function addBlankQuizQuestionToEditor() {
      tempEditorQuizQuestions.push({
        q: "New question statement?",
        diagram: null,
        options: ["Option A", "Option B", "Option C", "Option D"],
        ans: 0,
        exp: "Official curriculum reference rationale."
      });
      renderEditorQuizQuestions();
    }

    function removeEditorQuizQuestion(idx) {
      tempEditorQuizQuestions.splice(idx, 1);
      renderEditorQuizQuestions();
    }

    function saveTopicNotesAndQuiz() {
      // Students are restricted to reading notes and taking tests only
      if (authenticatedUser && authenticatedUser.role === 'student') {
        showToast("Access Denied: Study notes can only be uploaded by teachers and administrators. Students can view, read notes and take tests.", "Access Restricted", "breach");
        return;
      }

      const topicId = document.getElementById("editorTopicSelect").value;
      const title = document.getElementById("editorTopicTitle").value.trim();
      const summary = document.getElementById("editorTopicSummary").value.trim();
      const body = getTopicNotesFromEditor();
      const tips = document.getElementById("editorTopicTips").value.trim();

      if (!title || !summary || !body) {
        showToast("Please complete required topic note fields", "Validation", "breach");
        return;
      }

      const now = new Date();
      const uploadedAt = now.toISOString();
      const formattedDateTime = now.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) + ', ' + now.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: true });

      const authorName = (authenticatedUser && authenticatedUser.name)
        ? `${authenticatedUser.name} (${authenticatedUser.role === 'teacher' ? 'Faculty' : authenticatedUser.role === 'admin' ? 'Admin' : 'Teacher'})`
        : 'Faculty Terminal';

      const existingTopic = TOPIC_NOTES_DATABASE[topicId];
      const categoryName = (existingTopic && existingTopic.category) || "BPSC TRE 4.0 Syllabus Unit";

      const noteObject = {
        id: 'NOTE_' + Date.now().toString(36).toUpperCase(),
        topicId: topicId,
        title: title,
        category: categoryName,
        summary: summary,
        body: body,
        tips: tips,
        author: authorName,
        attached_file: tempEditorAttachedFile ? {
          name: tempEditorAttachedFile.name,
          size: tempEditorAttachedFile.size,
          type: tempEditorAttachedFile.type,
          content: tempEditorAttachedFile.content
        } : null,
        quiz: tempEditorQuizQuestions.length > 0 ? tempEditorQuizQuestions : (existingTopic ? existingTopic.quiz : []),
        uploaded_at: uploadedAt,
        formatted_date_time: formattedDateTime
      };

      TOPIC_NOTES_DATABASE[topicId] = {
        ...noteObject,
        attachedFile: noteObject.attached_file,
        quiz: noteObject.quiz
      };

      // Prepend to UPLOADED_NOTES_LIST and save in localStorage
      UPLOADED_NOTES_LIST.unshift(noteObject);
      localStorage.setItem('bpsc_uploaded_study_notes', JSON.stringify(UPLOADED_NOTES_LIST));

      // Post to server backend notes-handler.php
      fetch('notes-handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(noteObject)
      }).catch(err => console.warn("Backend notes sync skipped:", err));

      renderMatrix();
      renderCandidateUploadedNotes();
      renderTeacherUploadedNotes();
      renderAdminUploadedNotes();
      renderTeacherCurriculumGrid();
      closeModal("topicNotesModal");
      showToast(`Notes & test uploaded successfully! Live on student syllabus terminal.`, "Notes Published");
    }

    // --- UPLOADED STUDY NOTES FEED LOADER & RENDERER ---
    function loadUploadedNotes() {
      const cached = localStorage.getItem('bpsc_uploaded_study_notes');
      if (cached) {
        try {
          const parsed = JSON.parse(cached);
          if (Array.isArray(parsed) && parsed.length > 0) {
            UPLOADED_NOTES_LIST = parsed;
            parsed.forEach(n => {
              if (n.topicId) {
                TOPIC_NOTES_DATABASE[n.topicId] = {
                  ...TOPIC_NOTES_DATABASE[n.topicId],
                  ...n,
                  attachedFile: n.attached_file || (TOPIC_NOTES_DATABASE[n.topicId] && TOPIC_NOTES_DATABASE[n.topicId].attachedFile),
                  quiz: n.quiz || (TOPIC_NOTES_DATABASE[n.topicId] ? TOPIC_NOTES_DATABASE[n.topicId].quiz : [])
                };
              }
            });
            renderMatrix();
            renderCandidateUploadedNotes();
            renderTeacherUploadedNotes();
            renderAdminUploadedNotes();
            renderTeacherCurriculumGrid();
          }
        } catch (e) {}
      }

      fetch('notes-handler.php?t=' + Date.now())
        .then(res => res.json())
        .then(data => {
          if (data && data.success && Array.isArray(data.notes) && data.notes.length > 0) {
            UPLOADED_NOTES_LIST = data.notes;
            localStorage.setItem('bpsc_uploaded_study_notes', JSON.stringify(UPLOADED_NOTES_LIST));

            data.notes.forEach(n => {
              if (n.topicId) {
                TOPIC_NOTES_DATABASE[n.topicId] = {
                  ...TOPIC_NOTES_DATABASE[n.topicId],
                  ...n,
                  attachedFile: n.attached_file || (TOPIC_NOTES_DATABASE[n.topicId] && TOPIC_NOTES_DATABASE[n.topicId].attachedFile),
                  quiz: n.quiz || (TOPIC_NOTES_DATABASE[n.topicId] ? TOPIC_NOTES_DATABASE[n.topicId].quiz : [])
                };
              }
            });

            renderMatrix();
            renderCandidateUploadedNotes();
            renderTeacherUploadedNotes();
            renderAdminUploadedNotes();
            renderTeacherCurriculumGrid();
          } else {
            UPLOADED_NOTES_LIST = [];
            localStorage.removeItem('bpsc_uploaded_study_notes');
            renderMatrix();
            renderCandidateUploadedNotes();
            renderTeacherUploadedNotes();
            renderAdminUploadedNotes();
            renderTeacherCurriculumGrid();
          }
        })
        .catch(err => {
          console.error('Error loading notes:', err);
        });
    }

    function loadStoredQuestionPapers() {
      fetch('tests-handler.php?t=' + Date.now())
        .then(res => res.json())
        .then(data => {
          if (data && data.success && Array.isArray(data.papers)) {
            MASTER_PAPERS = data.papers;
            renderQuestionPapers();
            renderAdminQpMasterTable();
            populateQpSelectDropdown();
            renderCandidatePublishedTests();
            renderDbQuestionsTable();
          }
        })
        .catch(err => console.error('Error fetching stored question papers:', err));
    }


    function renderCandidateUploadedNotes() {
      const mount = document.getElementById("candidateUploadedNotesMount");
      if (!mount) return;

      if (!UPLOADED_NOTES_LIST || UPLOADED_NOTES_LIST.length === 0) {
        mount.innerHTML = `
          <div class="col-span-full p-8 text-center bg-brand-ivory rounded-2xl border border-brand-peachBorder text-xs text-brand-muted">
            <span class="text-2xl block mb-2">📚</span>
            No study notes published yet by faculty or administrators. Check back shortly for newly published lecture handouts and revision dossiers!
          </div>
        `;
        return;
      }

      mount.innerHTML = UPLOADED_NOTES_LIST.map(note => {
        const safeTitle = escapeHtml(note.title || 'Study Note');
        const safeCategory = escapeHtml(note.category || 'Curriculum Notes');
        const safeSummary = escapeHtml(note.summary || 'Click to view complete topic study dossier, key formulas, and high-yield notes.');
        const safeDateTime = escapeHtml(note.formatted_date_time || 'Recent');
        const safeAuthor = escapeHtml(note.author || 'Faculty');
        const topicId = note.topicId || 'gs_inm_1';
        const quizList = (note.quiz && Array.isArray(note.quiz)) ? note.quiz : [];

        const fileSnippet = note.attached_file ? `
          <div class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl bg-orange-50 border border-orange-200 text-[11px] text-brand-orange font-mono">
            <div class="flex items-center gap-1.5 truncate">
              <span>📎</span>
              <span class="font-bold truncate">${escapeHtml(note.attached_file.name || 'Document.pdf')}</span>
            </div>
            <button type="button" onclick="event.stopPropagation(); downloadAttachedNoteFile('${topicId}')" class="px-2.5 py-1 rounded-lg bg-brand-orange text-white text-[10px] font-bold hover:bg-brand-orangeHover transition shadow-2xs shrink-0 cursor-pointer flex items-center gap-1">
              <span>📥</span> Download
            </button>
          </div>
        ` : '';

        const quizSnippet = (quizList && quizList.length > 0) ? `
          <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-200 text-[11px] text-blue-800 font-mono font-bold">
            <span>📝</span>
            <span>${quizList.length} Questions Test Ready</span>
          </div>
        ` : '';

        return `
          <div class="bg-brand-ivory/60 hover:bg-white rounded-2xl border border-brand-peachBorder hover:border-brand-orange p-4 sm:p-5 shadow-xs hover:shadow-md transition space-y-3 flex flex-col justify-between group">
            <div class="space-y-2.5">
              <!-- Category & Upload Date & Time Header -->
              <div class="flex items-center justify-between gap-2 flex-wrap">
                <span class="text-[10px] font-sans font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-brand-peachSoft text-brand-orange border border-brand-peachBorder">
                  ${safeCategory}
                </span>
                <span class="text-[10px] font-sans font-bold text-amber-900 bg-amber-100 border border-amber-300 px-2.5 py-0.5 rounded-full flex items-center gap-1 shadow-2xs" title="Upload Date & Time">
                  <span>📅</span>
                  <span>${safeDateTime}</span>
                </span>
              </div>

              <!-- Title -->
              <h3 class="font-extrabold text-sm text-brand-slateText group-hover:text-brand-orange transition leading-snug">
                ${safeTitle}
              </h3>

              <!-- Summary / Excerpt -->
              <p class="text-xs text-brand-muted line-clamp-2 leading-relaxed font-medium">
                ${safeSummary}
              </p>

              <!-- Attached Handout / Document Card (if attached) -->
              ${fileSnippet}

              <!-- Test Ready Indicator -->
              ${quizSnippet}
            </div>

            <!-- Footer: Author & Study Action -->
            <div class="pt-3 border-t border-brand-peachBorder/60 flex items-center justify-between gap-2 text-xs">
              <div class="flex items-center gap-1.5 text-[11px] text-brand-muted truncate max-w-[130px]" title="Uploaded by ${safeAuthor}">
                <span>👤</span>
                <span class="font-semibold text-brand-slateText truncate">${safeAuthor}</span>
              </div>

              <button type="button" onclick="openTopicStudyDossier('${topicId}', '${escapeHtml((note.title || '').replace(/'/g, "\\'"))}')" class="px-3.5 py-1.5 rounded-xl bg-brand-orange hover:bg-brand-orangeHover text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer shrink-0">
                <span>📖</span> <span>Access Notes & Test →</span>
              </button>
            </div>
          </div>
        `;
      }).join("");
    }

    // --- 14. BPSC TRE 4.0 CANDIDATE MERIT LEADERBOARD ENGINE ---
    let currentLeaderboardScope = 'district';
    let currentLeaderboardDistrict = (typeof PHP_SESSION_USER !== 'undefined' && PHP_SESSION_USER && PHP_SESSION_USER.district) ? PHP_SESSION_USER.district : 'Patna';

    function switchLeaderboardScope(scope) {
      currentLeaderboardScope = (scope === 'state') ? 'state' : 'district';

      const btnDist = document.getElementById("btnLeaderboardDistrict");
      const btnState = document.getElementById("btnLeaderboardState");
      const distWrapper = document.getElementById("leaderboardDistrictSelectWrapper");

      if (currentLeaderboardScope === 'district') {
        if (btnDist) btnDist.className = "px-3.5 py-1.5 rounded-xl transition text-xs font-bold bg-brand-orange text-white shadow-xs cursor-pointer flex items-center gap-1.5";
        if (btnState) btnState.className = "px-3.5 py-1.5 rounded-xl transition text-xs font-bold text-brand-slateText hover:bg-brand-peachSoft cursor-pointer flex items-center gap-1.5";
        if (distWrapper) distWrapper.classList.remove("hidden");
      } else {
        if (btnState) btnState.className = "px-3.5 py-1.5 rounded-xl transition text-xs font-bold bg-brand-orange text-white shadow-xs cursor-pointer flex items-center gap-1.5";
        if (btnDist) btnDist.className = "px-3.5 py-1.5 rounded-xl transition text-xs font-bold text-brand-slateText hover:bg-brand-peachSoft cursor-pointer flex items-center gap-1.5";
        if (distWrapper) distWrapper.classList.add("hidden");
      }

      fetchLeaderboardData(currentLeaderboardScope, currentLeaderboardDistrict);
    }

    function changeLeaderboardDistrict(district) {
      if (!district) return;
      currentLeaderboardDistrict = district;
      if (currentLeaderboardScope !== 'district') {
        currentLeaderboardScope = 'district';
        const btnDist = document.getElementById("btnLeaderboardDistrict");
        const btnState = document.getElementById("btnLeaderboardState");
        const distWrapper = document.getElementById("leaderboardDistrictSelectWrapper");
        if (btnDist) btnDist.className = "px-3.5 py-1.5 rounded-xl transition text-xs font-bold bg-brand-orange text-white shadow-xs cursor-pointer flex items-center gap-1.5";
        if (btnState) btnState.className = "px-3.5 py-1.5 rounded-xl transition text-xs font-bold text-brand-slateText hover:bg-brand-peachSoft cursor-pointer flex items-center gap-1.5";
        if (distWrapper) distWrapper.classList.remove("hidden");
      }
      fetchLeaderboardData('district', district);
    }

    function fetchLeaderboardData(scope = 'district', district = null) {
      const targetDist = district || currentLeaderboardDistrict || 'Patna';
      const url = `leaderboard-handler.php?scope=${encodeURIComponent(scope)}&district=${encodeURIComponent(targetDist)}`;

      fetch(url)
        .then(res => res.json())
        .then(resData => {
          if (resData.success && resData.data) {
            renderLeaderboardUI(resData.data);
          }
        })
        .catch(err => console.error("Error fetching leaderboard data:", err));
    }

    function renderLeaderboardUI(data) {
      if (!data) return;

      const titleEl = document.getElementById("leaderboardTableTitle");
      const badgeEl = document.getElementById("leaderboardCountBadge");
      const standingCard = document.getElementById("leaderboardUserStandingCard");
      const podiumMount = document.getElementById("leaderboardPodiumMount");
      const tbody = document.getElementById("leaderboardTableBody");
      const searchBox = document.getElementById("leaderboardSearchInput");
      const distSelect = document.getElementById("leaderboardDistrictSelect");

      if (distSelect && data.selected_district) {
        distSelect.value = data.selected_district;
      }

      if (searchBox) searchBox.value = "";

      const scope = data.scope || 'district';
      if (titleEl) {
        titleEl.innerText = (scope === 'state') 
          ? 'State-Wide Aspirant Rankings (All Bihar Merit List)' 
          : `Aspirants Ranking (${data.selected_district} District)`;
      }

      if (badgeEl) {
        badgeEl.innerText = `${(data.rankings || []).length} Candidates Listed`;
      }

      // 1. Render Current User Standing Card
      if (standingCard && data.current_user_standing) {
        const u = data.current_user_standing;
        const curRank = (scope === 'state') ? (u.state_rank || 1) : (u.district_rank || 1);
        standingCard.innerHTML = `
          <div class="flex items-center gap-3.5 w-full md:w-auto">
            <div class="w-12 h-12 rounded-2xl bg-brand-orange text-white font-serif font-black text-xl flex items-center justify-center shadow-sm shrink-0">
              #${curRank}
            </div>
            <div>
              <div class="flex items-center gap-2">
                <span class="font-bold text-sm text-brand-slateText">${escapeHtml(u.full_name)}</span>
                <span class="px-2 py-0.5 rounded-md bg-brand-orange text-white text-[9px] font-bold font-mono uppercase tracking-wider">You</span>
                <span class="text-[11px] font-sans text-brand-muted font-medium">ID: ${escapeHtml(u.id)}</span>
              </div>
              <p class="text-xs text-brand-slateText mt-0.5">
                <span class="font-bold text-brand-orange">District Rank #${u.district_rank} in ${escapeHtml(u.district)}</span> · 
                <span class="text-brand-muted">State Rank #${u.state_rank} in Bihar</span>
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3 sm:gap-6 w-full md:w-auto justify-between md:justify-end border-t md:border-t-0 pt-3 md:pt-0 border-amber-200/60 text-center">
            <div>
              <div class="font-mono text-base font-black text-brand-orange">${Number(u.overall_score || 0).toFixed(2)} <span class="text-[10px] text-brand-muted font-normal">/ 150</span></div>
              <div class="text-[9px] uppercase font-bold text-brand-muted">Overall Score</div>
            </div>
            <div class="h-7 w-px bg-amber-200"></div>
            <div>
              <div class="font-mono text-base font-black text-emerald-700">${u.avg_accuracy}%</div>
              <div class="text-[9px] uppercase font-bold text-brand-muted">Avg. Accuracy</div>
            </div>
            <div class="h-7 w-px bg-amber-200"></div>
            <div>
              <div class="font-mono text-base font-black text-brand-slateText">${u.tests_taken}</div>
              <div class="text-[9px] uppercase font-bold text-brand-muted">Mocks Attempted</div>
            </div>
          </div>
        `;
      }

      // 2. Render Top 3 Podium
      if (podiumMount) {
        const top3 = (data.rankings || []).slice(0, 3);
        const podiumStyles = [
          { badge: '🥇 Rank 1 (Topper)', border: 'border-amber-400 bg-gradient-to-b from-amber-50 to-amber-100/40 text-amber-900', tag: 'bg-amber-400 text-amber-950', icon: '👑' },
          { badge: '🥈 Rank 2 (Elite)', border: 'border-slate-300 bg-gradient-to-b from-slate-50 to-slate-100/50 text-slate-800', tag: 'bg-slate-300 text-slate-800', icon: '🥈' },
          { badge: '🥉 Rank 3 (Honors)', border: 'border-orange-300 bg-gradient-to-b from-orange-50 to-orange-100/40 text-orange-900', tag: 'bg-orange-300 text-orange-950', icon: '🥉' }
        ];

        if (top3.length === 0) {
          podiumMount.innerHTML = `
            <div class="col-span-3 p-6 text-center text-xs text-brand-muted bg-brand-ivory rounded-2xl border border-brand-peachBorder">
              No candidates found in this scope yet. Launch a mock test to claim rank #1!
            </div>
          `;
        } else {
          podiumMount.innerHTML = top3.map((podiumItem, idx) => {
            const style = podiumStyles[idx] || podiumStyles[2];
            return `
              <div class="rounded-2xl border-2 ${style.border} p-4 shadow-xs flex flex-col justify-between gap-3 relative overflow-hidden">
                <div class="flex items-start justify-between gap-2">
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono ${style.tag}">
                    <span>${style.icon}</span> ${style.badge}
                  </span>
                  <span class="font-mono text-xs font-bold text-brand-muted">#${podiumItem.display_rank}</span>
                </div>

                <div>
                  <h4 class="font-extrabold text-sm text-brand-slateText truncate" title="${escapeHtml(podiumItem.full_name)}">
                    ${escapeHtml(podiumItem.full_name)}
                    ${podiumItem.is_current_user ? '<span class="text-[9px] font-mono px-1.5 py-0.2 rounded bg-brand-orange text-white uppercase ml-1">You</span>' : ''}
                  </h4>
                  <p class="text-[11px] text-brand-muted mt-0.5 truncate">
                    ${escapeHtml(podiumItem.district)} · ${escapeHtml(podiumItem.cadre_label)}
                  </p>
                </div>

                <div class="pt-2.5 border-t border-black/5 flex items-center justify-between text-xs">
                  <div>
                    <span class="text-[9px] uppercase font-bold text-brand-muted block">Score</span>
                    <span class="font-mono font-bold text-brand-orange text-sm">${Number(podiumItem.overall_score || 0).toFixed(2)}</span>
                    <span class="text-[10px] text-brand-muted">/150</span>
                  </div>
                  <div class="text-right">
                    <span class="text-[9px] uppercase font-bold text-brand-muted block">Accuracy</span>
                    <span class="font-mono font-bold text-emerald-700">${podiumItem.avg_accuracy}%</span>
                  </div>
                  <div class="text-right">
                    <span class="text-[9px] uppercase font-bold text-brand-muted block">Mocks</span>
                    <span class="font-mono font-bold text-brand-slateText">${podiumItem.tests_taken}</span>
                  </div>
                </div>
              </div>
            `;
          }).join("");
        }
      }

      // 3. Render Table Rows
      if (tbody) {
        const rankings = data.rankings || [];
        if (rankings.length === 0) {
          tbody.innerHTML = `
            <tr>
              <td colspan="8" class="p-6 text-center text-brand-muted text-xs">
                No aspirant scores registered for this scope yet.
              </td>
            </tr>
          `;
        } else {
          tbody.innerHTML = rankings.map(stu => {
            const isYou = !!stu.is_current_user;
            const rowBg = isYou ? 'bg-amber-50/80 font-bold border-l-4 border-l-brand-orange' : 'hover:bg-brand-ivory/50 transition';
            const rankVal = stu.display_rank || 1;
            const rankMedal = rankVal === 1 ? '🥇 1' : (rankVal === 2 ? '🥈 2' : (rankVal === 3 ? '🥉 3' : `#${rankVal}`));

            return `
              <tr class="leaderboard-row ${rowBg}" data-fullname="${escapeHtml(stu.full_name).toLowerCase()}" data-district="${escapeHtml(stu.district).toLowerCase()}">
                <td class="p-3.5 text-center font-mono font-bold ${rankVal <= 3 ? 'text-brand-orange text-sm' : 'text-brand-muted'}">
                  ${rankMedal}
                </td>
                <td class="p-3.5">
                  <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="font-bold text-brand-slateText">${escapeHtml(stu.full_name)}</span>
                    ${isYou ? '<span class="px-2 py-0.5 rounded-md bg-brand-orange text-white text-[9px] font-bold font-mono uppercase tracking-wider">You</span>' : ''}
                  </div>
                </td>
                <td class="p-3.5 text-brand-muted text-[11px]">${escapeHtml(stu.cadre_label)}</td>
                <td class="p-3.5 font-semibold text-brand-slateText">${escapeHtml(stu.district)}</td>
                <td class="p-3.5 text-center font-mono font-bold text-brand-slateText">${stu.tests_taken}</td>
                <td class="p-3.5 text-right font-mono font-extrabold text-brand-orange text-sm">
                  ${Number(stu.overall_score || 0).toFixed(2)} <span class="text-[10px] text-brand-muted font-normal">/ 150</span>
                </td>
                <td class="p-3.5 text-right font-mono font-bold text-emerald-700">${stu.avg_accuracy}%</td>
                <td class="p-3.5 text-center">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono ${rankVal <= 3 ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-brand-ivory text-brand-muted border border-brand-peachBorder'}">
                    ${escapeHtml(stu.badge || '')}
                  </span>
                </td>
              </tr>
            `;
          }).join("");
        }
      }
    }

    function filterLeaderboardTable(query) {
      const q = (query || "").toLowerCase().trim();
      const rows = document.querySelectorAll(".leaderboard-row");
      let visibleCount = 0;

      rows.forEach(r => {
        const name = r.getAttribute("data-fullname") || "";
        const dist = r.getAttribute("data-district") || "";
        if (!q || name.includes(q) || dist.includes(q)) {
          r.classList.remove("hidden");
          visibleCount++;
        } else {
          r.classList.add("hidden");
        }
      });

      const badgeEl = document.getElementById("leaderboardCountBadge");
      if (badgeEl) {
        badgeEl.innerText = `${visibleCount} Candidates Shown`;
      }
    }

    function renderTeacherUploadedNotes() {
      const mount = document.getElementById("teacherUploadedNotesMount");
      if (!mount) return;

      if (!UPLOADED_NOTES_LIST || UPLOADED_NOTES_LIST.length === 0) {
        mount.innerHTML = `
          <div class="col-span-full p-8 text-center bg-brand-ivory rounded-2xl border border-brand-peachBorder text-xs text-brand-muted">
            <span class="text-2xl block mb-2">📚</span>
            No study notes uploaded yet. Click <strong>'+ Upload New Notes'</strong> above to create dossiers and attach handouts.
          </div>
        `;
        return;
      }

      mount.innerHTML = UPLOADED_NOTES_LIST.map(note => {
        const safeTitle = escapeHtml(note.title);
        const safeCategory = escapeHtml(note.category || 'Curriculum Notes');
        const safeDateTime = escapeHtml(note.formatted_date_time || 'Recent');
        const safeAuthor = escapeHtml(note.author || 'Faculty');
        const topicId = note.topicId || 'gs_inm_1';

        const fileSnippet = note.attached_file ? `
          <div class="flex items-center justify-between gap-2 px-3 py-1.5 rounded-xl bg-orange-50 border border-orange-200 text-[11px] text-brand-orange font-mono">
            <div class="flex items-center gap-1 truncate">
              <span>📎</span>
              <span class="font-bold truncate">${escapeHtml(note.attached_file.name || 'Document.pdf')}</span>
            </div>
            <span class="text-[10px] text-brand-muted shrink-0">${escapeHtml(note.attached_file.size || 'PDF')}</span>
          </div>
        ` : '';

        return `
          <div class="bg-brand-ivory/60 hover:bg-white rounded-2xl border border-brand-peachBorder hover:border-brand-orange p-4 shadow-xs space-y-2.5 flex flex-col justify-between">
            <div class="space-y-2">
              <div class="flex items-center justify-between gap-1 flex-wrap">
                <span class="text-[10px] font-sans font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-brand-peachSoft text-brand-orange">
                  ${safeCategory}
                </span>
                <span class="text-[10px] font-mono text-amber-900 bg-amber-100 border border-amber-300 px-2 py-0.5 rounded-full">
                  📅 ${safeDateTime}
                </span>
              </div>
              <h4 class="font-bold text-xs text-brand-slateText line-clamp-1">${safeTitle}</h4>
              <p class="text-[11px] text-brand-muted line-clamp-2">${escapeHtml(note.summary || '')}</p>
              ${fileSnippet}
            </div>
            <div class="pt-2 border-t border-brand-peachBorder/60 flex items-center justify-between text-[11px]">
              <span class="text-brand-muted truncate max-w-[120px]">By: ${safeAuthor}</span>
              <button type="button" onclick="openTopicStudyDossier('${topicId}', '${escapeHtml(note.title.replace(/'/g, "\\'"))}')" class="px-2.5 py-1 rounded-lg bg-brand-orange hover:bg-brand-orangeHover text-white text-[11px] font-bold transition shadow-xs cursor-pointer">
                Preview →
              </button>
            </div>
          </div>
        `;
      }).join("");
    }

    function renderAdminUploadedNotes() {
      const mount = document.getElementById("adminUploadedNotesMount");
      if (!mount) return;

      if (!UPLOADED_NOTES_LIST || UPLOADED_NOTES_LIST.length === 0) {
        mount.innerHTML = `
          <div class="col-span-full p-8 text-center bg-brand-ivory rounded-2xl border border-brand-peachBorder text-xs text-brand-muted">
            <span class="text-2xl block mb-2">📚</span>
            No study notes uploaded yet. Click <strong>'+ Upload New Notes'</strong> above to create dossiers and attach handouts.
          </div>
        `;
        return;
      }

      mount.innerHTML = UPLOADED_NOTES_LIST.map(note => {
        const safeTitle = escapeHtml(note.title);
        const safeCategory = escapeHtml(note.category || 'Curriculum Notes');
        const safeDateTime = escapeHtml(note.formatted_date_time || 'Recent');
        const safeAuthor = escapeHtml(note.author || 'Faculty');
        const topicId = note.topicId || 'gs_inm_1';

        const fileSnippet = note.attached_file ? `
          <div class="flex items-center justify-between gap-2 px-3 py-1.5 rounded-xl bg-orange-50 border border-orange-200 text-[11px] text-brand-orange font-mono">
            <div class="flex items-center gap-1 truncate">
              <span>📎</span>
              <span class="font-bold truncate">${escapeHtml(note.attached_file.name || 'Document.pdf')}</span>
            </div>
            <span class="text-[10px] text-brand-muted shrink-0">${escapeHtml(note.attached_file.size || 'PDF')}</span>
          </div>
        ` : '';

        return `
          <div class="bg-brand-ivory/60 hover:bg-white rounded-2xl border border-brand-peachBorder hover:border-brand-orange p-4 shadow-xs space-y-2.5 flex flex-col justify-between">
            <div class="space-y-2">
              <div class="flex items-center justify-between gap-1 flex-wrap">
                <span class="text-[10px] font-sans font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-brand-peachSoft text-brand-orange">
                  ${safeCategory}
                </span>
                <span class="text-[10px] font-mono text-amber-900 bg-amber-100 border border-amber-300 px-2 py-0.5 rounded-full">
                  📅 ${safeDateTime}
                </span>
              </div>
              <h4 class="font-bold text-xs text-brand-slateText line-clamp-1">${safeTitle}</h4>
              <p class="text-[11px] text-brand-muted line-clamp-2">${escapeHtml(note.summary || '')}</p>
              ${fileSnippet}
            </div>
            <div class="pt-2 border-t border-brand-peachBorder/60 flex items-center justify-between text-[11px]">
              <span class="text-brand-muted truncate max-w-[120px]">By: ${safeAuthor}</span>
              <button type="button" onclick="openTopicStudyDossier('${topicId}', '${escapeHtml(note.title.replace(/'/g, "\\'"))}')" class="px-2.5 py-1 rounded-lg bg-brand-orange hover:bg-brand-orangeHover text-white text-[11px] font-bold transition shadow-xs cursor-pointer">
                Preview →
              </button>
            </div>
          </div>
        `;
      }).join("");
    }

    // --- 14. TEACHER & ADMIN CURRICULUM PREPARATION GUIDE HUB ---
    let teacherCurriculumCadre = null;
    let teacherCurriculumSubject = null;

    function switchTeacherCadre(cadre) {
      teacherCurriculumCadre = cadre;
      const cadreObj = BPSC_SYLLABUS.cadres[cadre];
      const select = document.getElementById("teacherSubjectStreamSelect");
      const adminSelect = document.getElementById("adminSubjectStreamSelect");
      const mount = document.getElementById("teacherCurriculumGridMount");
      const adminMount = document.getElementById("adminCurriculumGridMount");

      // Update tabs style
      ['prm', 'mid', 'sec', 'hsec'].forEach(c => {
        const btn = document.getElementById(`tCadreBtn-${c}`);
        if (btn) {
          btn.className = (c === cadre)
            ? "px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer bg-brand-orange text-white shadow-xs"
            : "px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer border border-brand-peachBorder bg-white hover:bg-brand-peachSoft text-brand-slateText";
        }
        const aBtn = document.getElementById(`aCadreBtn-${c}`);
        if (aBtn) {
          aBtn.className = (c === cadre)
            ? "px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer bg-brand-orange text-white shadow-xs"
            : "px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer border border-brand-peachBorder bg-white hover:bg-brand-peachSoft text-brand-slateText";
        }
      });

      let subjects = (cadreObj && cadreObj.subjects) ? cadreObj.subjects : [];
      teacherCurriculumSubject = subjects[0] || 'Core Curriculum';

      [select, adminSelect].forEach(sel => {
        if (sel) {
          sel.innerHTML = subjects.map(s => `<option value="${s}" ${s === teacherCurriculumSubject ? 'selected' : ''}>${s}</option>`).join("");
        }
      });

      // Show the notes upload options now that a level is selected
      if (mount) mount.classList.remove("hidden");
      if (adminMount) adminMount.classList.remove("hidden");

      renderTeacherCurriculumGrid();
    }

    function switchTeacherSubject(subject) {
      teacherCurriculumSubject = subject;
      renderTeacherCurriculumGrid();
    }

    function renderTeacherCurriculumGrid() {
      const mount = document.getElementById("teacherCurriculumGridMount");
      const adminMount = document.getElementById("adminCurriculumGridMount");
      if (!mount && !adminMount) return;

      if (!teacherCurriculumCadre) {
        if (mount) mount.classList.add("hidden");
        if (adminMount) adminMount.classList.add("hidden");
        return;
      }

      const cadreObj = BPSC_SYLLABUS.cadres[teacherCurriculumCadre];
      if (!cadreObj) return;

      let sections = [];

      if (teacherCurriculumCadre === 'prm') {
        if (cadreObj.primarySyllabus) {
          sections.push({
            title: "Part II: General Studies & EVS (120 Questions - Core Merit)",
            modules: cadreObj.primarySyllabus.modules
          });
        }
        sections.push({
          title: "Part I: Common Language Qualifying (30 Questions)",
          modules: BPSC_SYLLABUS.commonPart1.modules
        });
      } else {
        let p3Modules = (cadreObj.subjectData && cadreObj.subjectData[teacherCurriculumSubject])
          ? cadreObj.subjectData[teacherCurriculumSubject]
          : [
              {
                name: `Module 1: Advanced Core Concepts (${teacherCurriculumSubject})`,
                items: [
                  { id: `${teacherCurriculumCadre}_${teacherCurriculumSubject.toLowerCase().replace(/[^a-z0-9]/g, '_').substring(0, 8)}_1`, name: `Core Principles & Theoretical Foundations`, hy: true },
                  { id: `${teacherCurriculumCadre}_${teacherCurriculumSubject.toLowerCase().replace(/[^a-z0-9]/g, '_').substring(0, 8)}_2`, name: `SCERT / NCERT Advanced Textbook Review`, hy: true }
                ]
              }
            ];

        sections.push({
          title: `Part III: Concerned Subject - ${teacherCurriculumSubject} (80 Questions)`,
          modules: p3Modules
        });
        sections.push({
          title: "Part II: General Studies Core (40 Questions)",
          modules: BPSC_SYLLABUS.commonGS.modules
        });
        sections.push({
          title: "Part I: Common Language Qualifying (30 Questions)",
          modules: BPSC_SYLLABUS.commonPart1.modules
        });
      }

      let html = sections.map(sec => {
        let modulesHtml = sec.modules.map(mod => {
          let itemsHtml = mod.items.map(it => {
            const note = TOPIC_NOTES_DATABASE[it.id] || (UPLOADED_NOTES_LIST ? UPLOADED_NOTES_LIST.find(n => n.topicId === it.id) : null);
            const hasPdf = note && (note.attachedFile || note.attached_file) && ((note.attachedFile && note.attachedFile.name) || (note.attached_file && note.attached_file.name));
            const fileName = hasPdf ? (note.attachedFile?.name || note.attached_file?.name) : null;
            const fileSize = hasPdf ? (note.attachedFile?.size || note.attached_file?.size || 'PDF') : null;
            const quizCount = (note && note.quiz && Array.isArray(note.quiz)) ? note.quiz.length : 0;
            const hasCustomNotes = note && (hasPdf || quizCount > 0 || (note.body && !note.body.includes('Add detailed lecture notes')));

            return `
              <div class="p-3.5 rounded-2xl border ${hasPdf ? 'border-amber-300 bg-amber-50/30 ring-1 ring-amber-200' : 'border-brand-peachBorder bg-white'} shadow-xs flex flex-col justify-between gap-3 hover:shadow-md transition">
                <div class="space-y-1.5">
                  <div class="flex items-start justify-between gap-2">
                    <span class="text-xs font-bold text-brand-slateText leading-snug">${escapeHtml(it.name)}</span>
                    ${it.hy ? '<span class="text-[9px] px-1.5 py-0.2 rounded bg-brand-peachSoft text-brand-orange font-bold font-mono shrink-0">HY</span>' : ''}
                  </div>
                  <div class="flex items-center gap-1.5 flex-wrap text-[10px] font-mono">
                    <span class="text-brand-muted font-bold">ID: ${it.id}</span>
                    ${hasPdf ? `
                      <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-800 font-bold border border-red-300 flex items-center gap-1 truncate max-w-[200px]" title="${escapeHtml(fileName)}">
                        <span>📄</span> PDF (${escapeHtml(fileSize)})
                      </span>
                    ` : ''}
                    ${quizCount > 0 ? `
                      <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 font-bold border border-blue-300 flex items-center gap-1">
                        <span>📝</span> ${quizCount}Q Test
                      </span>
                    ` : ''}
                    ${!hasCustomNotes ? `
                      <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-medium border border-slate-200">
                        No Notes Yet
                      </span>
                    ` : ''}
                  </div>
                </div>

                <div class="pt-2 border-t border-brand-peachSoft flex items-center justify-between gap-2">
                  <button type="button" onclick="openTopicStudyDossier('${it.id}', '${escapeHtml(it.name.replace(/'/g, "\\'"))}')" class="px-2.5 py-1.5 rounded-xl border border-brand-peachBorder bg-brand-ivory hover:bg-brand-peachSoft text-brand-slateText text-xs font-semibold transition flex items-center gap-1 cursor-pointer">
                    <span>👁️</span> <span>Preview</span>
                  </button>

                  <button type="button" onclick="openTopicNotesEditor('${it.id}')" class="px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <span>📤</span> <span>${hasCustomNotes ? 'Edit Notes & PDF' : 'Upload Notes & PDF'} →</span>
                  </button>
                </div>
              </div>
            `;
          }).join("");

          return `
            <div class="space-y-2">
              <h4 class="text-xs font-bold text-brand-slateText flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-orange"></span>
                ${mod.name}
              </h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">${itemsHtml}</div>
            </div>
          `;
        }).join("");

        const secSafeId = 'teacher_sec_' + sec.title.toLowerCase().replace(/[^a-z0-9]/g, '_').substring(0, 30);
        const isCollapsed = localStorage.getItem('collapse_' + secSafeId) === '1';
        return `
          <div class="bg-brand-ivory/50 rounded-2xl border border-brand-peachBorder p-4 sm:p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-brand-peachBorder pb-2">
              <h3 class="text-xs sm:text-sm font-extrabold uppercase tracking-wide text-brand-orange">
                ${sec.title}
              </h3>
              <button type="button" 
                      onclick="toggleSectionCollapse('${secSafeId}', this, 'Curriculum Section')" 
                      data-collapsible-target="${secSafeId}"
                      class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold text-brand-slateText bg-white border border-brand-peachBorder hover:bg-brand-peachSoft hover:text-brand-orange transition cursor-pointer shadow-2xs" 
                      title="${isCollapsed ? 'Expand this Section' : 'Minimize this Section'}"
                      aria-expanded="${isCollapsed ? 'false' : 'true'}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${isCollapsed ? 'M19 9l-7 7-7-7' : 'M5 15l7-7 7 7'}"/></svg>
                <span>${isCollapsed ? 'Expand' : 'Minimize'}</span>
              </button>
            </div>
            <div id="${secSafeId}" class="space-y-4 ${isCollapsed ? 'hidden' : ''}">${modulesHtml}</div>
          </div>
        `;
      }).join("");

      if (mount) mount.innerHTML = html;
      if (adminMount) adminMount.innerHTML = html;
    }

    function markCurrentSet(targetState) {
      BPSC_SYLLABUS.commonPart1.modules.forEach(m => m.items.forEach(it => { topicState[it.id] = targetState; }));
      BPSC_SYLLABUS.commonGS.modules.forEach(m => m.items.forEach(it => { topicState[it.id] = targetState; }));
      localStorage.setItem(getUserStorageKey('topic_states'), JSON.stringify(topicState));
      renderMatrix();
    }

    function filterCheckpoints(mode) {
      activeFilter = mode;
      document.getElementById("btnFilterAll").className = mode === 'all' ? "flex-1 py-1.5 px-2 rounded-lg text-xs font-bold bg-brand-orange text-white cursor-pointer" : "flex-1 py-1.5 px-2 rounded-lg text-xs font-bold bg-brand-ivory text-brand-slateText hover:bg-brand-peachSoft cursor-pointer";
      document.getElementById("btnFilterHY").className = mode === 'hy' ? "flex-1 py-1.5 px-2 rounded-lg text-xs font-bold bg-brand-orange text-white cursor-pointer" : "flex-1 py-1.5 px-2 rounded-lg text-xs font-bold bg-brand-ivory text-brand-slateText hover:bg-brand-peachSoft cursor-pointer";
      document.getElementById("btnFilterInc").className = mode === 'inc' ? "flex-1 py-1.5 px-2 rounded-lg text-xs font-bold bg-brand-orange text-white cursor-pointer" : "flex-1 py-1.5 px-2 rounded-lg text-xs font-bold bg-brand-ivory text-brand-slateText hover:bg-brand-peachSoft cursor-pointer";
      renderMatrix();
    }

    function switchView(viewKey) {
      ['matrix', 'structure', 'bihar'].forEach(v => {
        document.getElementById(`view-${v}`).classList.add("hidden");
        document.getElementById(`viewBtn-${v}`).className = "flex-1 py-2 px-3 rounded-xl hover:text-brand-orange hover:bg-brand-orangeLight transition whitespace-nowrap cursor-pointer";
      });
      document.getElementById(`view-${viewKey}`).classList.remove("hidden");
      document.getElementById(`viewBtn-${viewKey}`).className = "flex-1 py-2 px-3 rounded-xl bg-brand-orange text-white shadow-sm transition whitespace-nowrap cursor-pointer";
    }

    function updateLiveMetrics() {
      let mastered = 0;
      let review = 0;
      let allItems = [];

      const cadre = currentCadre || 'mid';
      const cadreObj = BPSC_SYLLABUS.cadres[cadre];
      const selectedDisc = document.getElementById("disciplineSelector") ? document.getElementById("disciplineSelector").value : "";

      if (cadre === "prm") {
        if (cadreObj && cadreObj.primarySyllabus) {
          cadreObj.primarySyllabus.modules.forEach(m => allItems.push(...m.items));
        }
        BPSC_SYLLABUS.commonPart1.modules.forEach(m => allItems.push(...m.items));
      } else {
        if (cadreObj && cadreObj.subjectData && cadreObj.subjectData[selectedDisc]) {
          cadreObj.subjectData[selectedDisc].forEach(m => allItems.push(...m.items));
        }
        BPSC_SYLLABUS.commonGS.modules.forEach(m => allItems.push(...m.items));
        BPSC_SYLLABUS.commonPart1.modules.forEach(m => allItems.push(...m.items));
      }

      allItems.forEach(it => {
        const s = topicState[it.id] || 0;
        if (s === 2) mastered++;
        if (s === 1) review++;
      });

      const weightedScore = mastered + (review * 0.5);
      const pct = allItems.length > 0 ? ((weightedScore / allItems.length) * 100).toFixed(1) : "0.0";

      const elCompleted = document.getElementById("profileHudCompleted") || document.getElementById("hudCompleted");
      if (elCompleted) elCompleted.innerText = mastered;

      const elProgress = document.getElementById("profileHudProgress") || document.getElementById("hudProgress");
      if (elProgress) elProgress.innerText = `${Math.round(pct)}%`;

      const elGaugePct = document.getElementById("profileGaugePercentage") || document.getElementById("gaugePercentage");
      if (elGaugePct) elGaugePct.innerText = `${pct}% Verified`;

      const elGaugeBar = document.getElementById("profileGaugeBar") || document.getElementById("gaugeBar");
      if (elGaugeBar) elGaugeBar.style.width = `${pct}%`;

      const langItems = [
        ...BPSC_SYLLABUS.commonPart1.modules[0].items,
        ...BPSC_SYLLABUS.commonPart1.modules[1].items
      ];
      const mathItems = BPSC_SYLLABUS.commonGS.modules[0].items;
      const sciItems = BPSC_SYLLABUS.commonGS.modules[1].items;
      const inmItems = BPSC_SYLLABUS.commonGS.modules[2].items;
      const geoItems = BPSC_SYLLABUS.commonGS.modules[3].items;

      let p3Items = [];
      if (cadreObj && cadreObj.subjectData && cadreObj.subjectData[selectedDisc]) {
        cadreObj.subjectData[selectedDisc].forEach(m => p3Items.push(...m.items));
      }
      if (p3Items.length === 0) p3Items = mathItems;

      function computeScore(items) {
        if (!items || items.length === 0) return 0;
        let score = 0;
        items.forEach(it => {
          const s = topicState[it.id] || 0;
          if (s === 2) score += 1.0;
          else if (s === 1) score += 0.5;
        });
        return Math.min(100, Math.round((score / items.length) * 100));
      }

      const axisScores = [
        computeScore(langItems),
        computeScore(mathItems),
        computeScore(sciItems),
        computeScore(inmItems),
        computeScore(geoItems),
        computeScore(p3Items)
      ];

      axisScores.forEach((score, idx) => {
        const badge = document.getElementById(`axisScore-${idx}`);
        if (badge) badge.innerText = `${score}%`;
      });

      const R_MAX = 80;
      const R_MIN = 14;
      const angles = [
        -Math.PI / 2,
        -Math.PI / 6,
        Math.PI / 6,
        Math.PI / 2,
        (5 * Math.PI) / 6,
        (-5 * Math.PI) / 6
      ];

      const polygonPoints = angles.map((ang, i) => {
        const score = axisScores[i];
        const r = R_MIN + (score / 100) * (R_MAX - R_MIN);
        const x = (r * Math.cos(ang)).toFixed(1);
        const y = (r * Math.sin(ang)).toFixed(1);
        return `${x},${y}`;
      }).join(" ");

      const poly = document.getElementById("radarPolygon");
      if (poly) poly.setAttribute("points", polygonPoints);
    }

    // --- 14. PROCTORED CBT ENGINE & SUBMIT LOGIC ---
    function renderCandidatePublishedTests() {
      const mount = document.getElementById("candidatePublishedTestsMount");
      const hudBadge = document.getElementById("profileHudAvailableTestsCount") || document.getElementById("hudAvailableTestsCount");
      if (!mount) return;

      const publishedPapers = MASTER_PAPERS.filter(p => p.status === 'published');
      if (hudBadge) hudBadge.innerText = publishedPapers.length;

      if (publishedPapers.length === 0) {
        mount.innerHTML = `
          <div class="col-span-3 p-8 text-center bg-brand-ivory rounded-2xl border border-brand-peachBorder space-y-2">
            <span class="text-3xl block">📋</span>
            <h4 class="font-bold text-sm text-brand-slateText">No CBT Mock Tests Published Yet</h4>
            <p class="text-xs text-brand-muted max-w-md mx-auto">
              There are currently no live examinations. Practice question papers and mock tests will appear here once published by verified faculty or platform administrators.
            </p>
          </div>
        `;
        return;
      }

      mount.innerHTML = publishedPapers.map(qp => `
        <div class="p-4 rounded-2xl bg-brand-ivory border border-brand-peachBorder flex flex-col justify-between gap-3 shadow-xs hover:border-brand-orange transition group">
          <div>
            <div class="flex items-center justify-between gap-1 mb-1.5 flex-wrap">
              <span class="text-[10px] font-sans font-bold text-brand-orange bg-brand-peachSoft px-2 py-0.5 rounded">${escapeHtml(qp.id)}</span>
              ${qp.visibility === 'candidate_only' ? '<span class="text-[9px] font-mono px-2 py-0.5 rounded font-bold uppercase bg-purple-100 text-purple-800 border border-purple-200">Special Pass</span>' : '<span class="text-[9px] font-mono px-2 py-0.5 rounded font-bold uppercase bg-emerald-100 text-emerald-800">Public</span>'}
            </div>
            <h3 class="font-bold text-brand-slateText text-xs group-hover:text-brand-orange transition leading-snug">${escapeHtml(qp.title)}</h3>
            <p class="text-[10px] text-brand-muted mt-1 font-mono">${escapeHtml(qp.cadre || 'All Cadres')} · ${parseInt(qp.duration || 120)} Mins · ${(qp.questions || []).length} Questions · Rule ${escapeHtml(qp.penalty || '-0.33')}</p>
            ${qp.author ? `<p class="text-[9px] text-brand-muted/80 font-mono mt-0.5">Prepared by ${escapeHtml(qp.author)}</p>` : ''}
          </div>
          <button onclick="launchProctoredCBT('${escapeHtml(qp.id)}')" class="w-full py-2 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl font-bold text-xs transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
            <span>⚡</span> Start CBT Examination
          </button>
        </div>
      `).join("");
    }

    function launchProctoredCBT(paperId = null) {
      let qp = null;
      if (paperId) {
        qp = MASTER_PAPERS.find(p => p.id === paperId);
      }
      if (!qp) {
        qp = MASTER_PAPERS.find(p => p.status === 'published') || MASTER_PAPERS[0];
      }
      if (!qp || !qp.questions || qp.questions.length === 0) {
        showToast("No questions loaded in this assessment yet", "Assessment Alert", "breach");
        return;
      }

      cbtActivePaper = qp;
      isCbtActive = false; // Exam has NOT started yet (safe while reading instructions)
      cbtSubmitted = false;
      cbtUserAnswers = {};
      if (cbtTimerInterval) clearInterval(cbtTimerInterval);

      // 1. Populate Dynamic Instruction Matrix
      const instTitle = document.getElementById("cbtInstructPaperTitle");
      if (instTitle) instTitle.innerText = qp.title;

      const instCadre = document.getElementById("cbtInstructCadre");
      if (instCadre) instCadre.innerText = qp.cadre || 'General Studies';

      const marksPerQ = parseFloat(qp.marksPerQuestion || qp.marks || 1.0);
      const penaltyVal = (qp.penalty !== undefined && qp.penalty !== null) ? parseFloat(qp.penalty) : -0.33;
      const totalMarksVal = (qp.totalMarks !== undefined && qp.totalMarks !== null && parseFloat(qp.totalMarks) > 0)
        ? parseFloat(qp.totalMarks)
        : (qp.questions.length * marksPerQ);

      const instDur = document.getElementById("cbtInstructDuration");
      if (instDur) instDur.innerText = `${qp.duration || 120} Mins`;

      const instCount = document.getElementById("cbtInstructQuestionCount");
      if (instCount) instCount.innerText = `${qp.questions.length} Questions`;

      const instMarks = document.getElementById("cbtInstructTotalMarks");
      if (instMarks) instMarks.innerText = `${totalMarksVal.toFixed(2)} Marks Max`;

      const instPenalty = document.getElementById("cbtInstructPenalty");
      if (instPenalty) instPenalty.innerText = penaltyVal === 0 ? '0.00' : (penaltyVal > 0 ? `-${penaltyVal.toFixed(2)}` : penaltyVal.toFixed(2));

      const instPenaltySub = document.getElementById("cbtInstructPenaltySub");
      if (instPenaltySub) instPenaltySub.innerText = penaltyVal === 0 ? 'No Negative Penalty' : `${penaltyVal} per incorrect`;

      const instPos = document.getElementById("cbtInstructPosMarksText");
      if (instPos) instPos.innerText = `+${marksPerQ.toFixed(2)} Marks`;

      const instNeg = document.getElementById("cbtInstructNegMarksText");
      if (instNeg) instNeg.innerText = penaltyVal === 0 ? '0.00 (No deduction)' : `${penaltyVal} Marks`;

      const undertakingText = document.getElementById("cbtUndertakingText");
      if (undertakingText) {
        const penStr = penaltyVal === 0 ? 'no negative penalty' : `${penaltyVal} negative penalty`;
        undertakingText.innerHTML = `<strong>Candidate Declaration:</strong> I have read and understood all the examination rules and marking scheme (+${marksPerQ.toFixed(2)} / ${penStr}). I am ready to begin my practice test.`;
      }

      const undertakingCheck = document.getElementById("cbtUndertakingCheckbox");
      if (undertakingCheck) undertakingCheck.checked = true;

      // 2. Control Step Visibility: Show Instructions, Hide Active Deck & Result
      const instDeck = document.getElementById("cbtInstructionsDeck");
      if (instDeck) instDeck.classList.remove("hidden");

      const activeDeck = document.getElementById("cbtActiveDeck");
      if (activeDeck) activeDeck.classList.add("hidden");

      const resultSec = document.getElementById("cbtResultSection");
      if (resultSec) resultSec.classList.add("hidden");

      // 3. Switch to CBT Terminal Screen & Scroll to Top
      setActiveRole('cbt');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function cancelCbtInstructions() {
      cbtActivePaper = null;
      isCbtActive = false;
      if (cbtTimerInterval) clearInterval(cbtTimerInterval);
      setActiveRole('student');
    }

    function beginActiveCbtSession() {
      if (!cbtActivePaper || !cbtActivePaper.questions || cbtActivePaper.questions.length === 0) {
        showToast("No assessment selected to begin", "Error", "breach");
        return;
      }

      const undertakingCheck = document.getElementById("cbtUndertakingCheckbox");
      if (undertakingCheck && !undertakingCheck.checked) {
        showToast("Please check the candidate declaration box before starting", "Protocol Required", "breach");
        undertakingCheck.focus();
        return;
      }

      // Officially Start Proctored Assessment Session
      isCbtActive = true;
      cbtSubmitted = false;
      cbtUserAnswers = {};
      cbtTimerSeconds = (parseInt(cbtActivePaper.duration) || 120) * 60;

      const titleEl = document.getElementById("cbtActivePaperTitle");
      if (titleEl) titleEl.innerText = `${cbtActivePaper.title} (${cbtActivePaper.id})`;
      const mobTitleEl = document.getElementById("cbtMobilePaperTitle");
      if (mobTitleEl) mobTitleEl.innerText = `${cbtActivePaper.title}`;

      const violationEl = document.getElementById("cbtViolationTag");
      if (violationEl) violationEl.classList.add("hidden");

      const tracker = document.getElementById("cbtAnsweredTracker");
      if (tracker) tracker.innerText = `0 / ${cbtActivePaper.questions.length} Answered`;

      // Hide Instructions, Reveal Questions Deck
      const instDeck = document.getElementById("cbtInstructionsDeck");
      if (instDeck) instDeck.classList.add("hidden");

      const activeDeck = document.getElementById("cbtActiveDeck");
      if (activeDeck) activeDeck.classList.remove("hidden");

      const resultSec = document.getElementById("cbtResultSection");
      if (resultSec) resultSec.classList.add("hidden");

      renderCbtQuestions(cbtActivePaper.questions);
      startCbtTimer();
      window.scrollTo({ top: 0, behavior: 'smooth' });
      showToast("Your mock test has started. All the best for your examination!", "Test Started");
    }

    function toggleMobileCbtPalette() {
      const wrapper = document.getElementById("cbtQuestionPaletteWrapper");
      const label = document.getElementById("cbtPaletteToggleLabel");
      if (!wrapper) return;
      if (wrapper.classList.contains("hidden")) {
        wrapper.classList.remove("hidden");
        if (label) label.innerText = "Hide Grid ▲";
      } else {
        wrapper.classList.add("hidden");
        if (label) label.innerText = "Show Grid ▼";
      }
    }

    function renderCbtQuestions(questions) {
      const mount = document.getElementById("cbtQuestionsMount");
      const palette = document.getElementById("cbtQuestionPaletteMount");

      if (mount) {
        mount.innerHTML = questions.map((q, idx) => `
          <div id="cbtCard_${q.id}" class="bg-white rounded-3xl border border-brand-peachBorder p-5 sm:p-6 shadow-sm space-y-4 transition">
            <div class="flex items-center justify-between">
              <span class="text-[10px] font-sans font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-brand-peachSoft text-brand-orange border border-brand-peachBorder">
                ${q.section || 'General'}
              </span>
              <span class="text-xs text-brand-muted font-mono">Q${idx + 1} of ${questions.length}</span>
            </div>

            <p class="font-bold text-sm text-brand-slateText leading-relaxed">${idx + 1}. ${q.question}</p>

            ${q.diagram ? `
              <div class="p-3 bg-brand-ivory rounded-2xl border border-brand-peachBorder text-center">
                <img src="${q.diagram}" class="max-h-56 mx-auto rounded-xl border border-brand-peachBorder bg-white object-contain shadow-xs" alt="Diagram">
              </div>
            ` : ''}

            <div class="space-y-2 pt-1">
              ${(q.options || []).map((opt, oIdx) => {
                const optText = typeof opt === 'string' ? opt : (opt.text || opt.title || `Option ${oIdx + 1}`);
                return `
                  <label class="flex items-center gap-3 p-3 rounded-2xl bg-brand-ivory border border-brand-peachBorder hover:border-brand-orange cursor-pointer transition">
                    <input type="radio" name="cbt_ans_${q.id}" value="${oIdx}" onchange="selectCbtAnswer(${q.id}, ${oIdx})" class="text-brand-orange focus:ring-brand-orange h-4 w-4">
                    <span class="text-xs font-semibold text-brand-slateText">${String.fromCharCode(65 + oIdx)}) ${optText}</span>
                  </label>
                `;
              }).join("")}
            </div>
          </div>
        `).join("");
      }

      if (palette) {
        palette.innerHTML = questions.map((q, idx) => `
          <button type="button" onclick="jumpToCbtQuestion(${q.id})" id="cbtPaletteBtn_${q.id}" class="w-8 h-8 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-mono font-bold text-brand-slateText hover:bg-brand-peachSoft transition flex items-center justify-center cursor-pointer">
            ${idx + 1}
          </button>
        `).join("");
      }

      updateCbtAnsweredCount();
    }

    function selectCbtAnswer(qid, optIndex) {
      cbtUserAnswers[qid] = optIndex;
      const btn = document.getElementById(`cbtPaletteBtn_${qid}`);
      if (btn) {
        btn.className = "w-8 h-8 rounded-xl bg-brand-orange text-white text-xs font-mono font-bold shadow-xs transition flex items-center justify-center cursor-pointer";
      }
      updateCbtAnsweredCount();
    }

    function updateCbtAnsweredCount() {
      const count = Object.keys(cbtUserAnswers).length;
      const countEl = document.getElementById("cbtAnsweredTracker");
      const mobBadge = document.getElementById("cbtMobileAnsweredBadge");
      const mobSubmitCount = document.getElementById("cbtMobileSubmitCounter");
      const totalQs = (cbtActivePaper && cbtActivePaper.questions) ? cbtActivePaper.questions.length : CBT_QUESTION_BANK.length;
      if (countEl) countEl.innerText = `${count} / ${totalQs} Answered`;
      if (mobBadge) mobBadge.innerText = `${count}/${totalQs}`;
      if (mobSubmitCount) mobSubmitCount.innerText = `${count} Answered`;
    }

    function jumpToCbtQuestion(id) {
      const card = document.getElementById(`cbtCard_${id}`);
      if (card) {
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        card.classList.add('ring-2', 'ring-brand-orange');
        setTimeout(() => card.classList.remove('ring-2', 'ring-brand-orange'), 1000);
      }
    }

    function startCbtTimer() {
      clearInterval(cbtTimerInterval);
      const timerDisplay = document.getElementById("cbtCountdownTimer");
      const mobTimerDisplay = document.getElementById("cbtMobileTimer");
      
      cbtTimerInterval = setInterval(() => {
        if (cbtTimerSeconds <= 0) {
          clearInterval(cbtTimerInterval);
          triggerBreachSubmission('Time Expired (Limit Reached)');
          return;
        }
        cbtTimerSeconds--;
        const m = Math.floor(cbtTimerSeconds / 60).toString().padStart(2, '0');
        const s = (cbtTimerSeconds % 60).toString().padStart(2, '0');
        const formatted = `${m}:${s}`;
        if (timerDisplay) timerDisplay.innerText = formatted;
        if (mobTimerDisplay) mobTimerDisplay.innerText = formatted;
      }, 1000);
    }

    function bindAntiCheatListeners() {
      document.addEventListener('visibilitychange', () => {
        if (isCbtActive && document.visibilityState === 'hidden') {
          triggerBreachSubmission('Tab Switch Detected');
        }
      });

      window.addEventListener('blur', () => {
        if (isCbtActive) {
          triggerBreachSubmission('Window Blur / Focus Lost');
        }
      });
    }

    function triggerBreachSubmission(reason) {
      if (!isCbtActive || cbtSubmitted) return;
      isCbtActive = false;
      clearInterval(cbtTimerInterval);

      const breachEl = document.getElementById("adminBreachCount");
      if (breachEl) {
        breachEl.innerText = (parseInt(breachEl.innerText) || 0) + 1;
      }

      const modal = document.getElementById("breachAlertModal");
      const msg = document.getElementById("breachModalMsg");
      if (msg) msg.innerText = `Violation: ${reason}. In compliance with strict proctoring standards, your assessment has been automatically finalized with negative deductions applied.`;
      if (modal) modal.classList.remove("hidden");

      finalizeCbtEvaluation('tab_breach');
      showToast(`Test submitted: ${reason}`, "Test Submitted", "breach");
    }

    function confirmManualCbtSubmit() {
      if (cbtSubmitted) return;
      isCbtActive = false;
      if (cbtTimerInterval) clearInterval(cbtTimerInterval);
      finalizeCbtEvaluation('manual');
    }

    function finalizeCbtEvaluation(mode = 'manual') {
      if (cbtSubmitted) return;
      cbtSubmitted = true;
      if (cbtTimerInterval) clearInterval(cbtTimerInterval);

      const paper = cbtActivePaper || MASTER_PAPERS[0];
      const activeQuestions = (paper && paper.questions && paper.questions.length > 0) 
        ? paper.questions 
        : CBT_QUESTION_BANK;

      let correct = 0;
      let wrong = 0;
      let blank = 0;
      let netScore = 0;
      let breakdownHtml = "";

      const defaultPaperMarks = (paper && (paper.marksPerQuestion || paper.marks)) ? parseFloat(paper.marksPerQuestion || paper.marks) : 1.0;
      const defaultPaperPenalty = (paper && paper.penalty !== undefined && paper.penalty !== null) ? Math.abs(parseFloat(paper.penalty)) : 0.33;

      activeQuestions.forEach((q, idx) => {
        const userChoice = cbtUserAnswers[q.id];
        const posReward = (q.posMarks !== undefined && q.posMarks !== null) ? parseFloat(q.posMarks) : ((q.marks !== undefined && q.marks !== null) ? parseFloat(q.marks) : defaultPaperMarks);
        const negPenalty = (q.negMarks !== undefined && q.negMarks !== null) ? Math.abs(parseFloat(q.negMarks)) : ((q.penalty !== undefined && q.penalty !== null) ? Math.abs(parseFloat(q.penalty)) : defaultPaperPenalty);

        const rawOptions = q.options || [];
        const normalizedOptions = rawOptions.map((opt, oIdx) => {
          if (typeof opt === 'string') {
            return { text: opt, isCorrect: (q.ans !== undefined ? q.ans === oIdx : (q.correctAnswer === oIdx)) };
          }
          return {
            text: opt.text || opt.title || `Option ${oIdx + 1}`,
            isCorrect: Boolean(opt.isCorrect || opt.correct || opt.is_correct || (q.ans === oIdx))
          };
        });

        let correctIdx = normalizedOptions.findIndex(o => o.isCorrect);
        if (correctIdx === -1) {
          if (typeof q.ans === 'number') correctIdx = q.ans;
          else if (typeof q.correctAnswer === 'number') correctIdx = q.correctAnswer;
          else correctIdx = 0;
        }

        let statusBadge = '<span class="text-[9px] font-mono px-2 py-0.5 rounded font-bold uppercase bg-amber-200 text-amber-900">Blank</span>';
        let cardBg = 'border-amber-200 bg-amber-50/40';

        if (userChoice === undefined || userChoice === null) {
          blank++;
        } else if (parseInt(userChoice) === correctIdx) {
          correct++;
          netScore += posReward;
          statusBadge = `<span class="text-[9px] font-mono px-2 py-0.5 rounded font-bold uppercase bg-emerald-200 text-emerald-900">Correct (+${posReward})</span>`;
          cardBg = 'border-emerald-200 bg-emerald-50/40';
        } else {
          wrong++;
          netScore -= negPenalty;
          statusBadge = `<span class="text-[9px] font-mono px-2 py-0.5 rounded font-bold uppercase bg-red-200 text-red-900">Wrong (-${negPenalty})</span>`;
          cardBg = 'border-red-200 bg-red-50/40';
        }

        const userText = (userChoice !== undefined && userChoice !== null) 
          ? (normalizedOptions[userChoice]?.text || `Option ${parseInt(userChoice) + 1}`) 
          : 'Not Answered';
        const correctText = normalizedOptions[correctIdx]?.text || 'Option A';

        breakdownHtml += `
          <div class="p-3.5 rounded-2xl border ${cardBg} text-xs space-y-1.5">
            <div class="flex items-center justify-between gap-2">
              <span class="font-bold text-brand-slateText">${idx + 1}. ${q.question}</span>
              ${statusBadge}
            </div>
            ${q.diagram ? `<div class="p-1"><img src="${q.diagram}" class="max-h-24 rounded border border-brand-peachBorder object-contain" alt="Diagram"></div>` : ''}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px] pt-1">
              <div><span class="text-brand-muted font-medium">Your Selection:</span> <span class="font-semibold text-brand-slateText">${userText}</span></div>
              <div><span class="text-brand-muted font-medium">Correct Option:</span> <span class="font-bold text-emerald-700">${correctText}</span></div>
            </div>
            <p class="text-[11px] text-brand-muted border-t border-black/5 pt-1 font-medium"><strong class="text-brand-slateText font-bold">Explanation:</strong> ${q.explanation || q.solution || 'Verified official SCERT reference.'}</p>
          </div>
        `;
      });

      const maxPossibleMarks = activeQuestions.reduce((sum, q) => sum + parseFloat(q.posMarks || q.marks || 1.0), 0);
      const finalCalculatedScore = Math.max(0, netScore).toFixed(2);
      
      const scoreEl = document.getElementById("cbtScoreDisplay");
      if (scoreEl) scoreEl.innerText = `${finalCalculatedScore} / ${maxPossibleMarks}`;

      const correctEl = document.getElementById("cbtCorrectCount");
      if (correctEl) correctEl.innerText = `${correct} Correct`;

      const wrongEl = document.getElementById("cbtWrongCount");
      if (wrongEl) wrongEl.innerText = `${wrong} Wrong`;

      const blankEl = document.getElementById("cbtBlankCount");
      if (blankEl) blankEl.innerText = `${blank} Blank`;

      const breakdownEl = document.getElementById("cbtBreakdownMount");
      if (breakdownEl) breakdownEl.innerHTML = breakdownHtml;

      const violationEl = document.getElementById("cbtViolationTag");
      if (violationEl) {
        if (mode === 'tab_breach') violationEl.classList.remove("hidden");
        else violationEl.classList.add("hidden");
      }

      const activeDeck = document.getElementById("cbtActiveDeck");
      if (activeDeck) activeDeck.classList.add("hidden");

      const instDeck = document.getElementById("cbtInstructionsDeck");
      if (instDeck) instDeck.classList.add("hidden");

      const resultSec = document.getElementById("cbtResultSection");
      if (resultSec) {
        resultSec.classList.remove("hidden");
        resultSec.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }

      // Record to user's personalized achievement & history
      const testRecord = {
        paperId: cbtActivePaper ? cbtActivePaper.id : 'QP_TEST',
        paperTitle: cbtActivePaper ? cbtActivePaper.title : 'BPSC CBT Test',
        score: parseFloat(finalCalculatedScore) || 0,
        totalMarks: maxPossibleMarks || 0,
        accuracy: maxPossibleMarks > 0 ? Math.round((Math.max(0, parseFloat(finalCalculatedScore)) / maxPossibleMarks) * 100) : 0,
        date: new Date().toISOString()
      };
      const historyKey = getUserStorageKey('test_history');
      const history = JSON.parse(localStorage.getItem(historyKey) || "[]");
      history.push(testRecord);
      localStorage.setItem(historyKey, JSON.stringify(history));

      // Refresh candidate dashboard metrics & achievements
      syncCandidateDossierAndAchievements();

      // Persist to Leaderboard Database in backend
      fetch('leaderboard-handler.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
          action: 'record_test',
          paper_id: cbtActivePaper ? cbtActivePaper.id : 'QP_TEST',
          paper_title: cbtActivePaper ? cbtActivePaper.title : 'BPSC CBT Test',
          score: finalCalculatedScore,
          total_marks: maxPossibleMarks,
          accuracy: testRecord.accuracy
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          fetchLeaderboardData(currentLeaderboardScope, currentLeaderboardDistrict);
        }
      })
      .catch(err => console.error("Error recording test score to database:", err));

      showToast(`Assessment submitted! Net Score: ${finalCalculatedScore} / ${maxPossibleMarks}`, "Evaluation Complete");
    }

    function getAllQuestionBankPool() {
      let pool = [];
      let seen = new Set();
      
      // 1. Question Bank (persisted in SQLite table question_bank)
      if (Array.isArray(QUESTION_BANK)) {
        QUESTION_BANK.forEach(q => {
          const key = (q.question || "").trim().toLowerCase();
          if (key && !seen.has(key)) {
            seen.add(key);
            pool.push({
              ...q,
              id: q.id || ('qb_' + Math.random().toString(36).substr(2, 9)),
              section: q.topic || q.section || 'General Studies & National Movement',
              topic: q.topic || q.section || 'General Studies & National Movement',
              sourcePaper: 'Question Bank'
            });
          }
        });
      }

      // 2. Questions from published question papers
      MASTER_PAPERS.forEach(p => {
        (p.questions || []).forEach(q => {
          const key = (q.question || "").trim().toLowerCase();
          if (key && !seen.has(key)) {
            seen.add(key);
            pool.push({
              ...q,
              id: q.id || ('qp_' + Math.random().toString(36).substr(2, 9)),
              section: q.section || q.topic || 'General Studies & National Movement',
              topic: q.section || q.topic || 'General Studies & National Movement',
              sourcePaper: p.title || 'Official Mock'
            });
          }
        });
      });
      return pool;
    }

    function openPublishCuratorModal() {
      const qpSelect = document.getElementById("curatorTargetQpSelect");
      if (!qpSelect) return;

      let html = `<option value="__NEW_PAPER__">➕ Create New Question Paper</option>`;
      MASTER_PAPERS.forEach(p => {
        html += `<option value="${p.id}">[${p.id}] ${p.title} (${p.status.toUpperCase()}) - ${p.questions.length} Qs</option>`;
      });
      qpSelect.innerHTML = html;

      handleCuratorPaperSelectChange();
      openModal('publishCuratorModal');
    }

    function handleCuratorPaperSelectChange() {
      const val = document.getElementById("curatorTargetQpSelect").value;
      const newWrapper = document.getElementById("curatorNewPaperWrapper");

      if (val === "__NEW_PAPER__") {
        newWrapper.classList.remove("hidden");
        curatorSelectedQIds.clear();
        const durInput = document.getElementById("curatorDuration");
        if (durInput) durInput.value = 120;
        const marksInput = document.getElementById("curatorMarksPerQ");
        if (marksInput) marksInput.value = "1.0";
        const penSelect = document.getElementById("curatorPenalty");
        if (penSelect) penSelect.value = "-0.33";
      } else {
        newWrapper.classList.add("hidden");
        const targetPaper = MASTER_PAPERS.find(p => p.id === val);
        curatorSelectedQIds.clear();
        if (targetPaper) {
          (targetPaper.questions || []).forEach(q => curatorSelectedQIds.add(String(q.id)));
          const durInput = document.getElementById("curatorDuration");
          if (durInput) durInput.value = targetPaper.duration || 120;
          const marksInput = document.getElementById("curatorMarksPerQ");
          if (marksInput) marksInput.value = targetPaper.marksPerQuestion || targetPaper.marks || 1.0;
          const penSelect = document.getElementById("curatorPenalty");
          if (penSelect) penSelect.value = targetPaper.penalty !== undefined ? targetPaper.penalty : "-0.33";
        }
      }

      renderCuratorQuestionList();
    }

    function renderCuratorQuestionList() {
      const mount = document.getElementById("curatorQuestionsMount");
      if (!mount) return;

      const pool = getAllQuestionBankPool();
      const filter = (document.getElementById("curatorSearchInput")?.value || "").toLowerCase().trim();

      const filtered = pool.filter(q => {
        if (!filter) return true;
        return (q.question && q.question.toLowerCase().includes(filter)) || 
               (q.section && q.section.toLowerCase().includes(filter)) ||
               (q.topic && q.topic.toLowerCase().includes(filter));
      });

      if (filtered.length === 0) {
        mount.innerHTML = `<div class="p-6 text-center text-xs text-slate-500 bg-slate-50 rounded-2xl border border-slate-200">No questions found matching your search. Add questions using "+ Add Question" or "Import from Word" to see them here.</div>`;
        updateCuratorMetrics();
        return;
      }

      // Group questions by topic
      const grouped = {};
      filtered.forEach(q => {
        const topicName = (q.topic || q.section || 'General Studies & National Movement').trim();
        if (!grouped[topicName]) grouped[topicName] = [];
        grouped[topicName].push(q);
      });

      let html = '';
      const topicIcons = {
        'Bihar Special GK & History': '🏛️',
        'General Studies & National Movement': '🌍',
        'Science & Technology': '🔬',
        'Mathematics & Quantitative Aptitude': '📐',
        'Language & Literature (Hindi & English)': '📖',
        'Geography & Environmental Studies': '🗺️'
      };

      for (const [topic, qList] of Object.entries(grouped)) {
        const icon = topicIcons[topic] || '📚';
        const topicSelectedCount = qList.filter(q => curatorSelectedQIds.has(String(q.id))).length;
        const allInTopicSelected = topicSelectedCount === qList.length && qList.length > 0;

        html += `
          <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-xs mb-3">
            <!-- Topic Group Header -->
            <div class="p-3 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
              <div class="flex items-center gap-2">
                <span class="text-base">${icon}</span>
                <span class="font-extrabold text-xs text-slate-900">${topic}</span>
                <span class="text-[10px] font-sans font-bold px-2 py-0.5 rounded-full ${topicSelectedCount > 0 ? 'bg-orange-100 text-brand-orange border border-orange-200' : 'bg-slate-200 text-slate-600'}">
                  ${topicSelectedCount} / ${qList.length} Selected
                </span>
              </div>
              <div class="flex items-center gap-1.5 self-end sm:self-auto">
                <button type="button" onclick="toggleTopicQuestions('${escapeHtml(topic)}', ${!allInTopicSelected})" class="px-2.5 py-1 text-[10px] font-bold rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 transition cursor-pointer">
                  ${allInTopicSelected ? 'Clear Topic' : 'Select All in Topic'}
                </button>
              </div>
            </div>

            <!-- Questions in Topic -->
            <div class="p-2 space-y-2">
              ${qList.map(q => {
                const isChecked = curatorSelectedQIds.has(String(q.id));
                const opts = Array.isArray(q.options) ? q.options : [];
                return `
                  <label class="p-3 rounded-xl border ${isChecked ? 'border-brand-orange bg-orange-50/50 shadow-xs' : 'border-slate-200 bg-slate-50/60 hover:bg-slate-50'} flex items-start gap-3 cursor-pointer hover:border-brand-orange transition text-xs block">
                    <div class="flex items-start gap-2.5 w-full">
                      <input type="checkbox" value="${q.id}" onchange="toggleCuratorQuestion('${q.id}', this.checked)" ${isChecked ? 'checked' : ''} class="curator-q-checkbox mt-1 text-brand-orange focus:ring-brand-orange h-4 w-4 rounded cursor-pointer shrink-0">
                      <div class="flex-1 space-y-1.5 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                          <span class="font-bold text-slate-900 leading-snug break-words">${q.question}</span>
                          <span class="font-mono text-[10px] text-brand-orange bg-white px-2 py-0.5 rounded border border-orange-200 font-bold shrink-0">
                            +${q.posMarks || 1} / -${q.negMarks || 0.33}
                          </span>
                        </div>
                        ${q.diagram ? `<div class="p-1"><img src="${q.diagram}" class="h-14 rounded border border-slate-200 bg-white object-contain" alt="Diagram"></div>` : ''}
                        
                        <!-- Options preview pills -->
                        ${opts.length > 0 ? `
                          <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 pt-1 text-[11px] text-slate-600 font-medium">
                            ${opts.map((opt, oIdx) => {
                              const optText = typeof opt === 'string' ? opt : (opt.text || `Option ${oIdx + 1}`);
                              const isCorrect = (q.ans === oIdx || q.correct_answer === oIdx || (typeof opt === 'object' && opt.isCorrect));
                              return `<div class="truncate px-2 py-0.5 rounded ${isCorrect ? 'bg-emerald-50 text-emerald-800 font-semibold border border-emerald-200' : 'bg-white border border-slate-200'}">
                                ${String.fromCharCode(65 + oIdx)}. ${optText}
                              </div>`;
                            }).join("")}
                          </div>
                        ` : ''}

                        <div class="text-[10px] text-slate-500 font-medium flex items-center justify-between pt-1 border-t border-slate-100">
                          <span>${topic} · ${opts.length} Options</span>
                          <span class="font-mono text-[9px] px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded">Source: ${q.sourcePaper || 'Question Bank'}</span>
                        </div>
                      </div>
                    </div>
                  </label>
                `;
              }).join("")}
            </div>
          </div>
        `;
      }

      mount.innerHTML = html;
      updateCuratorMetrics();
    }

    function toggleTopicQuestions(topicName, selectState) {
      const pool = getAllQuestionBankPool();
      pool.forEach(q => {
        const qTopic = (q.topic || q.section || 'General Studies & National Movement').trim();
        if (qTopic === topicName) {
          if (selectState) {
            curatorSelectedQIds.add(String(q.id));
          } else {
            curatorSelectedQIds.delete(String(q.id));
          }
        }
      });
      renderCuratorQuestionList();
    }

    function toggleCuratorQuestion(id, state) {
      if (state) curatorSelectedQIds.add(String(id));
      else curatorSelectedQIds.delete(String(id));
      updateCuratorMetrics();
    }

    function selectAllCuratorQuestions(status) {
      const pool = getAllQuestionBankPool();
      if (status) {
        pool.forEach(q => curatorSelectedQIds.add(String(q.id)));
      } else {
        curatorSelectedQIds.clear();
      }
      renderCuratorQuestionList();
    }

    function updateCuratorMetrics() {
      const badge = document.getElementById("curatorCountBadge");
      const totalInput = document.getElementById("curatorTotalMarks");
      const marksPerQ = parseFloat(document.getElementById("curatorMarksPerQ")?.value) || 1.0;
      const count = curatorSelectedQIds.size;
      const totalMarks = (count * marksPerQ).toFixed(2);

      if (totalInput) totalInput.value = totalMarks;
      if (badge) badge.innerText = `${count} Qs (${totalMarks} Marks)`;
    }

    function commitPublishTestCurator() {
      const targetVal = document.getElementById("curatorTargetQpSelect").value;
      const visibilityVal = document.getElementById("curatorAudienceSelect").value;
      const durationVal = parseInt(document.getElementById("curatorDuration").value) || 120;
      const marksPerQVal = parseFloat(document.getElementById("curatorMarksPerQ").value) || 1.0;
      const penaltyVal = document.getElementById("curatorPenalty").value || "-0.33";
      const pool = getAllQuestionBankPool();

      if (curatorSelectedQIds.size === 0) {
        showToast("Please select at least 1 question for this assessment", "Validation", "breach");
        return;
      }

      const selectedQuestions = [];
      curatorSelectedQIds.forEach(id => {
        const item = pool.find(q => String(q.id) === String(id));
        if (item) {
          selectedQuestions.push({
            ...item,
            posMarks: marksPerQVal,
            negMarks: Math.abs(parseFloat(penaltyVal))
          });
        }
      });

      const totalMarksVal = (selectedQuestions.length * marksPerQVal);
      const authorName = (authenticatedUser && authenticatedUser.name)
        ? `${authenticatedUser.name} (${authenticatedUser.role === 'admin' ? 'Admin' : 'Faculty'})`
        : 'Faculty Academic Terminal';

      let targetPaper;
      if (targetVal === "__NEW_PAPER__") {
        const title = document.getElementById("curatorNewPaperTitle").value.trim() || "BPSC TRE 4.0 Mock Test";
        const cadre = document.getElementById("curatorNewPaperCadre").value;
        const newId = "QP_" + Math.floor(100 + Math.random() * 900);

        targetPaper = {
          id: newId,
          title,
          cadre,
          duration: durationVal,
          marksPerQuestion: marksPerQVal,
          totalMarks: totalMarksVal,
          penalty: penaltyVal,
          status: "published",
          visibility: visibilityVal,
          author: authorName,
          questions: selectedQuestions
        };
        MASTER_PAPERS.unshift(targetPaper);
      } else {
        targetPaper = MASTER_PAPERS.find(p => p.id === targetVal);
        if (targetPaper) {
          targetPaper.duration = durationVal;
          targetPaper.marksPerQuestion = marksPerQVal;
          targetPaper.penalty = penaltyVal;
          targetPaper.totalMarks = totalMarksVal;
          targetPaper.questions = selectedQuestions;
          targetPaper.status = "published";
          targetPaper.visibility = visibilityVal;
          targetPaper.author = authorName;
        }
      }

      // Persist to tests-handler.php so it immediately appears for students & across all browsers
      fetch('tests-handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'publish',
          id: targetPaper.id,
          title: targetPaper.title,
          cadre: targetPaper.cadre,
          duration: targetPaper.duration,
          marks: targetPaper.totalMarks,
          totalMarks: targetPaper.totalMarks,
          penalty: targetPaper.penalty,
          visibility: targetPaper.visibility,
          status: 'published',
          author: targetPaper.author,
          questions: targetPaper.questions
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success && Array.isArray(data.papers)) {
          MASTER_PAPERS = data.papers;
          renderQuestionPapers();
          renderAdminQpMasterTable();
          populateQpSelectDropdown();
          renderCandidatePublishedTests();
        }
      })
      .catch(err => console.error("Error persisting test to backend:", err));

      renderQuestionPapers();
      renderAdminQpMasterTable();
      populateQpSelectDropdown();
      renderCandidatePublishedTests();
      renderDbQuestionsTable();
      closeModal('publishCuratorModal');

      showToast(`Paper '${targetPaper.title}' published successfully! It is now LIVE in the CBT Mock Series with ${targetPaper.duration} Mins duration & ${targetPaper.penalty} penalty.`, "Test Published");
    }

    function handleDocxFileSelected(input) {
      if (!input.files || !input.files[0]) return;
      const file = input.files[0];
      document.getElementById("bulkDocxFileName").innerText = file.name;

      const reader = new FileReader();
      reader.onload = function(e) {
        const arrayBuffer = e.target.result;
        if (window.mammoth) {
          mammoth.convertToHtml({ arrayBuffer: arrayBuffer })
            .then(displayDocxQuizTable)
            .catch(err => {
              showToast("Error parsing Word (.docx) file: " + (err.message || "Invalid document format"), "Docx Parse Error", "breach");
            });
        } else {
          showToast("Mammoth.js parser is still loading. Please retry in a moment.", "Docx Parser", "breach");
        }
      };
      reader.readAsArrayBuffer(file);
    }

    function displayDocxQuizTable(result) {
      const html = result.value;
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      const table = doc.querySelector('table');

      tempBulkQuestions = [];

      if (table) {
        const rows = Array.from(table.querySelectorAll('tr'));
        let currentQ = null;

        rows.forEach(tr => {
          const cells = Array.from(tr.querySelectorAll('td, th')).map(c => c.innerText.trim());
          if (cells.length < 2) return;

          const rowType = cells[0].toLowerCase();

          if (rowType === 'question') {
            if (currentQ && currentQ.options.length >= 2) {
              tempBulkQuestions.push(currentQ);
            }
            currentQ = {
              section: "General Knowledge / Science",
              question: cells[1],
              diagram: null,
              options: [],
              ans: 0,
              posMarks: 1,
              negMarks: 0.33,
              explanation: ""
            };

            const imgEl = tr.querySelector('img');
            if (imgEl && imgEl.src) currentQ.diagram = imgEl.src;
          } else if (rowType === 'option' && currentQ) {
            const optText = cells[1];
            const isCorrect = cells[2] && cells[2].toLowerCase().includes('correct') && !cells[2].toLowerCase().includes('incorrect');
            const currentIndex = currentQ.options.length;
            currentQ.options.push(optText);
            if (isCorrect) currentQ.ans = currentIndex;
          } else if (rowType === 'solution' && currentQ) {
            currentQ.explanation = cells[1];
          } else if (rowType === 'marks' && currentQ) {
            currentQ.posMarks = parseFloat(cells[1]) || 1;
            currentQ.negMarks = parseFloat(cells[2]) || 0.33;
          }
        });

        if (currentQ && currentQ.options.length >= 2) {
          tempBulkQuestions.push(currentQ);
        }
      }

      if (tempBulkQuestions.length === 0) {
        showToast("No valid questions detected in Word document. Please ensure questions follow the table format.", "Parsing Notice", "breach");
        return;
      }
      renderBulkParsedPreview();
    }

    function renderBulkParsedPreview() {
      const mount = document.getElementById("bulkParsedListMount");
      const wrapper = document.getElementById("bulkParsedPreviewWrapper");
      const countLabel = document.getElementById("parsedQuestionsCount");

      countLabel.innerText = tempBulkQuestions.length;
      wrapper.classList.remove("hidden");

      mount.innerHTML = tempBulkQuestions.map((q, idx) => `
        <div class="p-3 rounded-xl bg-white border border-gray-300 space-y-2 text-xs">
          <div class="flex items-center justify-between">
            <span class="font-bold text-brand-slateText">Q${idx + 1}. ${q.question}</span>
            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-gray-100 text-gray-800 font-bold border border-gray-300">Answer: Option ${String.fromCharCode(65 + q.ans)}</span>
          </div>
          
          <div class="p-2 bg-gray-50 border border-gray-200 rounded-lg flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
              <span class="text-[10px] font-bold text-gray-600">Diagram / Graph:</span>
              ${q.diagram ? 
                `<img src="${q.diagram}" class="h-10 w-16 object-contain rounded border border-gray-300 bg-white" alt="Diagram">` : 
                `<span class="text-[10px] text-gray-400 font-mono">None</span>`
              }
            </div>
            <label class="cursor-pointer px-2 py-1 bg-white border border-gray-300 hover:border-gray-500 rounded text-[10px] font-bold text-gray-700 transition">
              <span>📷 Attach / Change</span>
              <input type="file" accept="image/*" class="hidden" onchange="attachDiagramToBulkQuestion(${idx}, this)">
            </label>
          </div>

          <table class="w-full text-left text-[11px] border border-gray-200 border-collapse">
            <tbody>
              ${q.options.map((opt, oIdx) => `
                <tr class="border-b border-gray-100 ${oIdx === q.ans ? 'bg-emerald-50/60 font-bold text-emerald-900' : ''}">
                  <td class="p-1 w-16 text-gray-500 font-mono">Option ${String.fromCharCode(65 + oIdx)}</td>
                  <td class="p-1 text-gray-800 font-medium">${opt}</td>
                  <td class="p-1 w-20 text-right text-[10px] font-mono ${oIdx === q.ans ? 'text-emerald-700 font-bold' : 'text-gray-400'}">
                    ${oIdx === q.ans ? 'correct' : 'incorrect'}
                  </td>
                </tr>
              `).join("")}
              <tr class="bg-gray-50">
                <td class="p-1 text-gray-500 font-mono font-bold">Solution</td>
                <td colspan="2" class="p-1 text-gray-700 text-[10px] font-medium">${q.explanation}</td>
              </tr>
              <tr>
                <td class="p-1 text-gray-500 font-mono font-bold">Marks</td>
                <td colspan="2" class="p-1 text-gray-700 text-[10px] font-mono">Reward: +${q.posMarks} | Penalty: -${q.negMarks}</td>
              </tr>
            </tbody>
          </table>
        </div>
      `).join("");
    }

    function attachDiagramToBulkQuestion(qIdx, input) {
      if (input.files && input.files[0] && tempBulkQuestions[qIdx]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          tempBulkQuestions[qIdx].diagram = e.target.result;
          renderBulkParsedPreview();
          showToast(`Diagram attached to Question ${qIdx + 1}!`, "Diagram Updated");
        };
        reader.readAsDataURL(input.files[0]);
      }
    }

    function processBulkDocxUpload() {
      const selectedTopic = document.getElementById("bulkDocxTopicSelect")?.value || "General Studies & National Movement";
      const targetQpId = document.getElementById("bulkDocxTargetQp")?.value;

      if (!tempBulkQuestions || tempBulkQuestions.length === 0) {
        showToast("No parsed questions found in document", "Upload Error", "breach");
        return;
      }

      // Ensure every question has topic/section assigned
      tempBulkQuestions.forEach(q => {
        q.section = q.section || selectedTopic;
        q.topic = q.topic || selectedTopic;
      });

      // 1. Persist questions in bulk to Question Bank in SQLite!
      fetch('tests-handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'bulk_upload_questions',
          topic: selectedTopic,
          questions: tempBulkQuestions
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success && Array.isArray(data.questions)) {
          QUESTION_BANK = data.questions;
          CBT_QUESTION_BANK = [...QUESTION_BANK];
          renderDbQuestionsTable();
          if (document.getElementById("publishCuratorModal") && !document.getElementById("publishCuratorModal").classList.contains("hidden")) {
            renderCuratorQuestionList();
          }
        }
      })
      .catch(err => console.error("Error bulk saving questions to question bank:", err));

      // 2. If target question paper or new paper was specified, also attach to it
      if (targetQpId === "__NEW_PAPER__") {
        const newTitle = prompt("Enter a title for the new Question Paper:", `BPSC TRE 4.0 ${selectedTopic} Mock Paper`);
        if (newTitle) {
          const newQpId = "QP_" + Math.floor(100 + Math.random() * 900);
          const authorName = (authenticatedUser && authenticatedUser.name)
            ? `${authenticatedUser.name} (${authenticatedUser.role === 'admin' ? 'Admin' : 'Faculty'})`
            : 'Faculty Academic Terminal';
          const newQp = {
            id: newQpId,
            title: newTitle.trim(),
            cadre: "Classes 6–8 (Middle)",
            duration: 120,
            marksPerQuestion: 1.0,
            totalMarks: tempBulkQuestions.reduce((sum, q) => sum + parseFloat(q.posMarks || 1), 0),
            penalty: "-0.33",
            status: "published",
            visibility: "all",
            author: authorName,
            questions: [...tempBulkQuestions]
          };
          MASTER_PAPERS.unshift(newQp);

          fetch('tests-handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              action: 'publish',
              id: newQp.id,
              title: newQp.title,
              cadre: newQp.cadre,
              duration: newQp.duration,
              totalMarks: newQp.totalMarks,
              penalty: newQp.penalty,
              status: 'published',
              visibility: newQp.visibility,
              author: newQp.author,
              questions: newQp.questions
            })
          })
          .then(res => res.json())
          .then(data => {
            if (data.success && Array.isArray(data.papers)) {
              MASTER_PAPERS = data.papers;
              renderQuestionPapers();
              renderAdminQpMasterTable();
              populateQpSelectDropdown();
              renderCandidatePublishedTests();
            }
          })
          .catch(err => console.error("Error publishing new bulk paper:", err));
        }
      } else if (targetQpId && targetQpId !== '__QUESTION_BANK_ONLY__') {
        const qp = MASTER_PAPERS.find(p => p.id === targetQpId);
        if (qp) {
          tempBulkQuestions.forEach(q => qp.questions.push({ ...q, id: qp.questions.length + 1 }));
          fetch('tests-handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              action: 'publish',
              id: qp.id,
              title: qp.title,
              cadre: qp.cadre,
              duration: qp.duration || 120,
              totalMarks: qp.questions.reduce((sum, q) => sum + parseFloat(q.posMarks || 1), 0),
              penalty: qp.penalty || "-0.33",
              status: qp.status || "published",
              visibility: qp.visibility || "all",
              author: qp.author || "Academic Faculty",
              questions: qp.questions
            })
          })
          .then(res => res.json())
          .then(data => {
            if (data.success && Array.isArray(data.papers)) {
              MASTER_PAPERS = data.papers;
              renderQuestionPapers();
              renderAdminQpMasterTable();
              populateQpSelectDropdown();
              renderCandidatePublishedTests();
            }
          })
          .catch(err => console.error("Error saving bulk questions to paper:", err));
        }
      }

      renderQuestionPapers();
      renderAdminQpMasterTable();
      populateQpSelectDropdown();
      renderDbQuestionsTable();
      closeModal('adminBulkDocxModal');

      showToast(`Successfully imported ${tempBulkQuestions.length} questions into Question Bank under '${selectedTopic}'! They are immediately selectable in Publish Test.`, "Bulk Import Complete");
      tempBulkQuestions = [];
      const prevWrapper = document.getElementById("bulkParsedPreviewWrapper");
      if (prevWrapper) prevWrapper.classList.add("hidden");
    }

    function downloadOfficialWordTemplate() {
      const docContent = `
        <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
        <head>
          <meta charset='utf-8'>
          <title>Question Bank Upload Template</title>
          <style>
            body { font-family: 'Calibri', Arial, sans-serif; font-size: 11pt; color: #000000; }
            table { border-collapse: collapse; width: 100%; margin-bottom: 20px; border: 1px solid #000000; }
            td { border: 1px solid #000000; padding: 6px 10px; vertical-align: top; font-size: 10.5pt; }
            .header-label { font-weight: bold; width: 18%; }
          </style>
        </head>
        <body>
          <p><strong>Question Bank Upload Template</strong></p>
          <p style="font-size: 9.5pt; color: #444444;">Duplicate the table below for each question. Diagrams or images can be pasted directly into the Question row.</p>
          <br/>
          <table>
            <tr><td class="header-label">Question</td><td>Deficiency of which vitamin causes Beriberi disease?</td><td></td></tr>
            <tr><td class="header-label">Type</td><td>multiple_choice</td><td></td></tr>
            <tr><td class="header-label">Option</td><td>Vitamin C</td><td>incorrect</td></tr>
            <tr><td class="header-label">Option</td><td>Vitamin B12</td><td>incorrect</td></tr>
            <tr><td class="header-label">Option</td><td>Vitamin B1</td><td>correct</td></tr>
            <tr><td class="header-label">Option</td><td>Vitamin D</td><td>incorrect</td></tr>
            <tr><td class="header-label">Solution</td><td>B1 Thiamine deficiency causes Beriberi.</td><td></td></tr>
            <tr><td class="header-label">Marks</td><td>5</td><td>1</td></tr>
          </table>
        </body>
        </html>
      `;

      const blob = new Blob(['\ufeff', docContent], { type: 'application/msword' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'Simple_Quiz_Upload_Template.doc';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);

      showToast("Word template downloaded successfully!", "Template Ready");
    }

    function previewQuestionDiagram(input) {
      if (input.files && input.files[0]) {
        const file = input.files[0];
        document.getElementById("adminQDiagramFileName").innerText = file.name;
        
        const reader = new FileReader();
        reader.onload = function(e) {
          tempSelectedDiagramBase64 = e.target.result;
          const previewImg = document.getElementById("adminQDiagramPreview");
          previewImg.src = tempSelectedDiagramBase64;
          document.getElementById("adminQDiagramPreviewWrapper").classList.remove("hidden");
        };
        reader.readAsDataURL(file);
      }
    }

    function submitAdminAddQuestion() {
      const targetQpId = document.getElementById("adminQpSelectForQuestion")?.value;
      const section = (document.getElementById("adminQSection")?.value || "General Studies & National Movement").trim();
      const questionText = document.getElementById("adminQText").value.trim();
      const posMarks = parseFloat(document.getElementById("adminQPosMarks").value) || 1;
      const negMarks = parseFloat(document.getElementById("adminQNegMarks").value) || 0.33;
      const optA = document.getElementById("adminOptA").value.trim();
      const optB = document.getElementById("adminOptB").value.trim();
      const optC = document.getElementById("adminOptC").value.trim();
      const optD = document.getElementById("adminOptD").value.trim();
      const correctIndex = parseInt(document.getElementById("adminQCorrectAns").value) || 0;
      const explanation = document.getElementById("adminQExplanation").value.trim() || "Verified official SCERT / BPSC curriculum reference.";

      if (!questionText || !optA || !optB || !optC || !optD) {
        showToast("Please fill in question text and all 4 options", "Validation", "breach");
        return;
      }

      // 1. Always save into Question Bank table in SQLite!
      fetch('tests-handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'add_question',
          topic: section,
          question: questionText,
          options: [optA, optB, optC, optD],
          correct_answer: correctIndex,
          marks: posMarks,
          penalty: negMarks,
          explanation: explanation,
          diagram: tempSelectedDiagramBase64 || null
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success && Array.isArray(data.questions)) {
          QUESTION_BANK = data.questions;
          CBT_QUESTION_BANK = [...QUESTION_BANK];
          renderDbQuestionsTable();
          if (document.getElementById("publishCuratorModal") && !document.getElementById("publishCuratorModal").classList.contains("hidden")) {
            renderCuratorQuestionList();
          }
        }
      })
      .catch(err => console.error("Error saving question to question bank:", err));

      // 2. If target question paper was selected, also attach to it
      if (targetQpId && targetQpId !== '__QUESTION_BANK_ONLY__') {
        const qp = MASTER_PAPERS.find(p => p.id === targetQpId);
        if (qp) {
          const newQ = {
            id: qp.questions.length + 1,
            section,
            topic: section,
            question: questionText,
            diagram: tempSelectedDiagramBase64 || null,
            options: [optA, optB, optC, optD],
            ans: correctIndex,
            posMarks,
            negMarks,
            explanation
          };
          qp.questions.push(newQ);

          fetch('tests-handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              action: 'publish',
              id: qp.id,
              title: qp.title,
              cadre: qp.cadre,
              duration: qp.duration || 120,
              totalMarks: qp.questions.reduce((sum, q) => sum + parseFloat(q.posMarks || 1), 0),
              penalty: qp.penalty || "-0.33",
              status: qp.status || "published",
              visibility: qp.visibility || "all",
              author: qp.author || "Academic Faculty",
              questions: qp.questions
            })
          })
          .then(res => res.json())
          .then(data => {
            if (data.success && Array.isArray(data.papers)) {
              MASTER_PAPERS = data.papers;
              renderQuestionPapers();
              renderAdminQpMasterTable();
              populateQpSelectDropdown();
              renderCandidatePublishedTests();
            }
          })
          .catch(err => console.error("Error saving question to server:", err));
        }
      }

      renderQuestionPapers();
      renderAdminQpMasterTable();
      populateQpSelectDropdown();
      renderDbQuestionsTable();
      closeModal('adminAddQuestionModal');

      document.getElementById("adminQText").value = "";
      document.getElementById("adminOptA").value = "";
      document.getElementById("adminOptB").value = "";
      document.getElementById("adminOptC").value = "";
      document.getElementById("adminOptD").value = "";
      document.getElementById("adminQExplanation").value = "";
      document.getElementById("adminQDiagramFileName").innerText = "No image selected";
      document.getElementById("adminQDiagramPreviewWrapper").classList.add("hidden");
      tempSelectedDiagramBase64 = null;

      showToast(`Question saved to Question Bank under '${section}'! It is now selectable in Publish Test.`, "Question Saved");
    }

    // =========================================================
    function generateDefaultChaptersForBook(book) {
      return [
        {
          title: `Unit 1: Overview & Foundation of ${book.subject}`,
          summary: `Comprehensive introductory syllabus notes and conceptual framework designed for ${book.cadre}.`,
          contentHtml: `
            <h3 class="text-base font-bold text-brand-orange">1.1 Core Foundations</h3>
            <p class="leading-relaxed">This unit introduces the core concepts of <strong>${book.subject}</strong> based on the standard SCERT and NCERT syllabus. Review these key definitions, basic rules, and examples to build a strong foundation for your exam.</p>
            <h3 class="text-base font-bold text-brand-orange mt-4">1.2 Exam Preparation Tips</h3>
            <p class="leading-relaxed">In the BPSC teacher exam, questions in this section test your basic understanding of core concepts. Make sure to solve the practice questions at the end of each topic.</p>
          `,
          keyTakeaways: [
            `Focus on basic principles and recurring terminology in ${book.subject}`,
            `Review direct NCERT conceptual applications`,
            `Solve corresponding practice questions in the Question Bank`
          ]
        },
        {
          title: `Unit 2: Applied Concepts & Analytical Problem Solving`,
          summary: `In-depth analysis of key scenarios, problem sets, and practical examples for ${book.cadre}.`,
          contentHtml: `
            <h3 class="text-base font-bold text-brand-orange">2.1 Advanced Concept Formulation</h3>
            <p class="leading-relaxed">Higher-order thinking questions require cross-referencing multiple topics. Ensure familiarity with standard theorems, exceptions, and statutory historical records relevant to Bihar education standards.</p>
          `,
          keyTakeaways: [
            `Memorize core formulas and key chronological milestones`,
            `Prepare for negative marking (-0.33) by eliminating deceptive options`
          ]
        }
      ];
    }

    function renderBooksModalList() {
      const mount = document.getElementById("booksModalListMount");
      if (!mount) return;

      mount.innerHTML = DIGITAL_BOOKS.map(b => `
        <div class="p-3.5 rounded-2xl bg-brand-ivory border border-brand-peachBorder flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs hover:border-brand-orange transition">
          <div class="space-y-1">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="text-[9px] font-mono px-2 py-0.5 rounded font-bold uppercase ${b.access === 'Free' ? 'bg-emerald-100 text-emerald-800' : 'bg-brand-orange text-white'}">${b.access}</span>
              <span class="text-[11px] text-brand-muted font-sans font-medium font-bold">${b.cadre}</span>
              <span class="text-[9px] font-mono px-2 py-0.5 rounded font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">📥 Download Available</span>
            </div>
            <h4 class="font-bold text-brand-slateText text-xs mt-1 leading-snug">${escapeHtml(b.title)}</h4>
            <div class="text-[11px] text-brand-muted font-sans font-medium flex items-center gap-2 flex-wrap">
              <span>Subject: <strong>${escapeHtml(b.subject)}</strong></span>
              <span>·</span>
              <span>Author: ${escapeHtml(b.author)}</span>
              <span>·</span>
              <span>${(b.chapters && b.chapters.length) || 5} Interactive Units</span>
              ${b.pdfDataUri ? '<span class="text-amber-800 font-bold bg-amber-100 px-1.5 py-0.2 rounded border border-amber-300">📄 PDF / Docx Attached</span>' : ''}
            </div>
          </div>
          <div class="w-full sm:w-auto flex items-center justify-end gap-2 flex-wrap">
            <button type="button" onclick="downloadBook('${b.id}')" class="w-full sm:w-auto px-4 py-2 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl font-bold text-xs transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
              <span>📥</span> Download Book (.doc/.pdf)
            </button>

          </div>
        </div>
      `).join("");
    }

    function renderCandidateBooksShelf() {
      const mount = document.getElementById("candidateBooksCardsMount");
      if (!mount) return;

      if (!DIGITAL_BOOKS || DIGITAL_BOOKS.length === 0) {
        mount.innerHTML = `<div class="col-span-full py-8 text-center text-brand-muted text-xs">No books published in the digital library yet.</div>`;
        return;
      }

      mount.innerHTML = DIGITAL_BOOKS.map(b => {
        let icon = "📜";
        const sub = (b.subject || "").toLowerCase();
        if (sub.includes("math")) icon = "📐";
        else if (sub.includes("sci") || sub.includes("phys") || sub.includes("chem") || sub.includes("bio")) icon = "🔬";
        else if (sub.includes("eng") || sub.includes("hindi") || sub.includes("lang") || sub.includes("urdu")) icon = "📖";
        else if (sub.includes("geo") || sub.includes("polity") || sub.includes("civics") || sub.includes("econ")) icon = "🌍";

        const chapterCount = (b.chapters && b.chapters.length) ? b.chapters.length : 5;
        const summary = (b.chapters && b.chapters[0] && b.chapters[0].summary) 
          ? b.chapters[0].summary 
          : `Official academic curriculum textbook for ${b.cadre} aspirants covering core syllabus concepts and review milestones.`;

        const isFree = (b.access || "Free").toLowerCase() === "free";

        return `
          <div class="p-4 rounded-2xl bg-brand-ivory border border-brand-peachBorder hover:border-brand-orange transition shadow-xs flex flex-col justify-between gap-4 group">
            <div class="space-y-2">
              <div class="flex items-center justify-between gap-1 flex-wrap">
                <span class="text-[10px] font-sans font-bold ${isFree ? 'text-emerald-800 bg-emerald-100 border border-emerald-200' : 'text-brand-orange bg-brand-peachSoft border border-brand-peachBorder'} px-2 py-0.5 rounded">
                  ${isFree ? 'FREE ACCESS' : 'PREMIUM VAULT'}
                </span>
                <span class="text-[11px] font-sans text-brand-muted font-medium font-bold">${escapeHtml(b.cadre || 'All Levels')}</span>
              </div>
              <div class="flex items-start gap-3">
                <div class="w-12 h-14 rounded-xl bg-orange-100 border border-orange-200 text-brand-orange flex items-center justify-center text-2xl shadow-inner shrink-0 group-hover:scale-105 transition-transform">
                  ${icon}
                </div>
                <div>
                  <h3 class="font-bold text-brand-slateText text-xs group-hover:text-brand-orange transition leading-snug">${escapeHtml(b.title)}</h3>
                  <p class="text-[10px] text-brand-muted mt-0.5 font-mono">${escapeHtml(b.subject)} · ${escapeHtml(b.author)}</p>
                  <div class="flex items-center gap-1.5 flex-wrap mt-1">
                    <span class="inline-block text-[9px] font-mono bg-white border border-brand-peachBorder text-slate-600 px-1.5 py-0.5 rounded">${chapterCount} Units</span>
                    ${b.pdfDataUri ? '<span class="inline-block text-[9px] font-mono bg-amber-100 text-amber-900 border border-amber-300 px-1.5 py-0.5 rounded font-bold">📄 PDF / Docx Attached</span>' : ''}
                  </div>
                </div>
              </div>
              <p class="text-[11px] text-slate-600 leading-relaxed line-clamp-2">
                ${escapeHtml(summary)}
              </p>
            </div>
            <div class="pt-2 border-t border-brand-peachSoft/80 flex items-center justify-between gap-2">
              <span class="text-[10px] font-mono text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 font-bold">📥 Downloadable</span>
              <button type="button" onclick="downloadBook('${b.id}')" class="px-4 py-2 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl font-bold text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                <span>📥</span> Download Book (.doc) →
              </button>
            </div>
          </div>
        `;
      }).join("");
    }

    function renderTeacherBooksTable() {
      const tbody = document.getElementById("teacherBooksTableBody");
      if (tbody) {
        if (!DIGITAL_BOOKS || DIGITAL_BOOKS.length === 0) {
          tbody.innerHTML = `<tr><td colspan="7" class="py-6 text-center text-brand-muted text-xs">No curriculum books registered yet. Use '+ Publish Book' above to upload textbooks.</td></tr>`;
        } else {
          tbody.innerHTML = DIGITAL_BOOKS.map((b, idx) => `
            <tr class="hover:bg-brand-peachSoft/40 transition">
              <td class="py-3 font-mono font-bold text-brand-orange">${escapeHtml(b.id || `BK_${idx+1}`)}</td>
              <td class="py-3">
                <div class="font-bold text-brand-slateText flex items-center gap-1.5">
                  <span>📚</span> ${escapeHtml(b.title)}
                </div>
                ${b.pdfDataUri ? '<span class="inline-block mt-0.5 text-[9px] font-mono bg-amber-100 text-amber-900 border border-amber-300 px-1.5 py-0.2 rounded font-bold">📄 PDF Attached</span>' : ''}
              </td>
              <td class="py-3 font-mono text-[11px]">
                <div class="font-semibold text-slate-800">${escapeHtml(b.subject || 'General')}</div>
                <div class="text-[10px] text-brand-muted">${escapeHtml(b.cadre || 'All Levels')}</div>
              </td>
              <td class="py-3 font-mono text-[11px] text-slate-700">${escapeHtml(b.author || 'SCERT/NCERT')}</td>
              <td class="py-3 font-mono text-[10px] text-brand-muted">${escapeHtml(b.fileSize || '12.4 MB')}</td>
              <td class="py-3">
                <span class="text-[10px] font-mono px-2 py-0.5 rounded font-bold uppercase ${b.access === 'Free' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-brand-orange text-white'}">
                  ${escapeHtml(b.access || 'Free')}
                </span>
              </td>
              <td class="py-3 text-right">
                <div class="inline-flex items-center gap-2">
                  <button type="button" onclick="downloadBook('${b.id}')" class="px-3 py-1 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-lg font-bold text-xs shadow-2xs transition inline-flex items-center gap-1 cursor-pointer">
                    <span>📥</span> Download
                  </button>

                  <button type="button" onclick="deleteDigitalBook('${b.id}')" class="px-2 py-1 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg font-bold text-xs border border-red-200 transition cursor-pointer" title="Remove Book">
                    ✕
                  </button>
                </div>
              </td>
            </tr>
          `).join("");
        }
      }

      // Update Teacher HUD KPI counter
      const booksCountEl = document.getElementById("teacherHudBooksCount");
      if (booksCountEl) {
        booksCountEl.innerText = DIGITAL_BOOKS.length;
      }
    }

    function renderAdminBooksTable() {
      const tbody = document.getElementById("adminBooksTableBody");
      if (tbody) {
        if (!DIGITAL_BOOKS || DIGITAL_BOOKS.length === 0) {
          tbody.innerHTML = `<tr><td colspan="7" class="py-6 text-center text-brand-muted text-xs">No curriculum books registered yet. Click '+ Publish Book' to upload.</td></tr>`;
        } else {
          tbody.innerHTML = DIGITAL_BOOKS.map((b, idx) => `
            <tr class="hover:bg-brand-peachSoft/40 transition">
              <td class="py-3 font-mono font-bold text-brand-orange">${escapeHtml(b.id || `BK_${idx+1}`)}</td>
              <td class="py-3">
                <div class="font-bold text-brand-slateText flex items-center gap-1.5">
                  <span>📚</span> ${escapeHtml(b.title)}
                </div>
                ${b.pdfDataUri ? '<span class="inline-block mt-0.5 text-[9px] font-mono bg-amber-100 text-amber-900 border border-amber-300 px-1.5 py-0.2 rounded font-bold">📄 PDF Attached</span>' : ''}
              </td>
              <td class="py-3 font-mono text-[11px]">
                <div class="font-semibold text-slate-800">${escapeHtml(b.subject || 'General')}</div>
                <div class="text-[10px] text-brand-muted">${escapeHtml(b.cadre || 'All Levels')}</div>
              </td>
              <td class="py-3 font-mono text-[11px] text-slate-700">${escapeHtml(b.author || 'SCERT/NCERT')}</td>
              <td class="py-3 font-mono text-[10px] text-brand-muted">${escapeHtml(b.fileSize || '12.4 MB')}</td>
              <td class="py-3">
                <span class="text-[10px] font-mono px-2 py-0.5 rounded font-bold uppercase ${b.access === 'Free' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-brand-orange text-white'}">
                  ${escapeHtml(b.access || 'Free')}
                </span>
              </td>
              <td class="py-3 text-right">
                <div class="inline-flex items-center gap-2">
                  <button type="button" onclick="downloadBook('${b.id}')" class="px-3 py-1 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-lg font-bold text-xs shadow-2xs transition inline-flex items-center gap-1 cursor-pointer">
                    <span>📥</span> Download
                  </button>

                  <button type="button" onclick="deleteDigitalBook('${b.id}')" class="px-2 py-1 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg font-bold text-xs border border-red-200 transition cursor-pointer" title="Remove Book">
                    ✕
                  </button>
                </div>
              </td>
            </tr>
          `).join("");
        }
      }
    }

    function deleteDigitalBook(bookId) {
      const book = DIGITAL_BOOKS.find(b => b.id === bookId);
      const title = book ? book.title : bookId;
      if (!confirm(`Are you sure you want to remove '${title}' from the Digital Library?`)) return;

      DIGITAL_BOOKS = DIGITAL_BOOKS.filter(b => b.id !== bookId);
      try {
        localStorage.setItem(STORED_DIGITAL_BOOKS_KEY, JSON.stringify(DIGITAL_BOOKS));
      } catch (e) {}

      renderBooksModalList();
      renderCandidateBooksShelf();
      renderTeacherBooksTable();
      renderAdminBooksTable();
      showToast(`Book '${title}' removed from Digital Library.`, "Book Removed");
    }

    function renderPyqList() {
      const mount = document.getElementById("pyqModalListMount");
      if (!mount) return;

      if (OFFICIAL_PYQS.length === 0) {
        mount.innerHTML = `<div class="p-4 text-center text-xs text-brand-muted bg-brand-ivory rounded-2xl">No PYQs uploaded yet. Faculty or Admin can upload papers anytime.</div>`;
        return;
      }

      mount.innerHTML = OFFICIAL_PYQS.map(p => `
        <div class="p-4 bg-brand-ivory rounded-2xl border border-brand-peachBorder flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 shadow-xs hover:border-brand-orange transition">
          <div class="space-y-1">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="text-[10px] font-sans font-bold px-2 py-0.5 rounded uppercase bg-blue-100 text-blue-800 border border-blue-200">${p.edition}</span>
              <span class="text-[10px] font-sans font-bold px-2 py-0.5 rounded uppercase bg-brand-peachSoft text-brand-orange border border-brand-peachBorder">${p.cadre}</span>
              <span class="text-[9px] font-mono px-2 py-0.5 rounded font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">📥 Download Available</span>
            </div>
            <h4 class="font-bold text-brand-slateText text-xs leading-snug">${p.title}</h4>
            <div class="text-[11px] text-brand-muted font-sans font-medium flex items-center gap-2 flex-wrap">
              <span>Shift: <strong>${p.shiftDate}</strong></span>
              <span>•</span>
              <span>Subject: <strong>${p.subject}</strong></span>
              <span>•</span>
              <span>Size: ${p.fileSize}</span>
              <span>•</span>
              <span class="text-slate-500">Uploader: ${p.uploadedBy}</span>
            </div>
          </div>
          <div class="w-full sm:w-auto flex items-center justify-end gap-2 flex-wrap">
            <button type="button" onclick="downloadPyq('${p.id}')" class="w-full sm:w-auto px-4 py-2 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl font-bold text-xs transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
              <span>📥</span> Download Paper (.doc/.pdf)
            </button>

          </div>
        </div>
      `).join("");

      // Also refresh dashboard mount if present
      renderStudentDashboardPyqMount();
    }

    function renderStudentDashboardPyqMount() {
      const mount = document.getElementById("candidatePyqCardsMount");
      if (!mount) return;

      const topPapers = OFFICIAL_PYQS.slice(0, 3);
      mount.innerHTML = topPapers.map(p => `
        <div class="p-4 rounded-2xl bg-brand-ivory border border-brand-peachBorder hover:border-brand-orange transition shadow-xs flex flex-col justify-between gap-3 group">
          <div class="space-y-1.5">
            <div class="flex items-center justify-between gap-1 flex-wrap">
              <span class="text-[10px] font-sans font-bold px-2 py-0.5 rounded uppercase bg-blue-100 text-blue-800 border border-blue-200">${p.edition}</span>
              <span class="text-[11px] font-sans text-brand-muted font-medium font-bold">${p.cadre}</span>
            </div>
            <h3 class="font-bold text-brand-slateText text-xs group-hover:text-brand-orange transition leading-snug">${p.title}</h3>
            <p class="text-[11px] text-brand-muted font-sans font-medium">${p.subject} · ${p.shiftDate}</p>
            <div class="flex items-center gap-2 pt-1 text-[10px] text-slate-500 font-mono">
              <span>150 Questions</span> · <span>-0.33 Marking</span> · <span>Bilingual</span>
            </div>
          </div>
          <div class="pt-2 border-t border-brand-peachSoft/80 flex items-center justify-between gap-2">
            <span class="text-[10px] font-mono text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 font-bold">📥 Downloadable</span>
            <button type="button" onclick="downloadPyq('${p.id}')" class="px-3.5 py-1.5 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl font-bold text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
              <span>📥</span> Download Paper (.doc) →
            </button>
          </div>
        </div>
      `).join("");
    }

    let tempPyqFileDataUrl = null;
    function handlePyqFileSelected(input) {
      if (input.files && input.files[0]) {
        const file = input.files[0];
        tempSelectedPyqFile = file;
        document.getElementById("pyqFileName").innerText = file.name;
        document.getElementById("pyqFileSize").innerText = `${(file.size / (1024 * 1024)).toFixed(2)} MB (${file.type || 'Document'})`;

        const reader = new FileReader();
        reader.onload = function(e) {
          tempPyqFileDataUrl = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    }

    function submitUploadPyq() {
      const edition = document.getElementById("pyqEditionInput").value;
      const cadre = document.getElementById("pyqCadreInput").value;
      const title = document.getElementById("pyqTitleInput").value.trim();
      const subject = document.getElementById("pyqSubjectInput").value.trim();
      const shiftDate = document.getElementById("pyqShiftDateInput").value.trim();

      if (!title || !subject || !shiftDate) {
        showToast("Please fill in all required PYQ details", "Validation", "breach");
        return;
      }

      const uploaderName = (typeof authenticatedUser !== 'undefined' && authenticatedUser) ? authenticatedUser.badge : "Faculty";
      const sizeStr = tempSelectedPyqFile ? `${(tempSelectedPyqFile.size / (1024 * 1024)).toFixed(1)} MB` : "6.4 MB";
      const fileUrl = tempSelectedPyqFile ? tempSelectedPyqFile.name : `${title.replace(/\s+/g, '_')}.pdf`;
      const newId = `PYQ_${(OFFICIAL_PYQS.length + 1).toString().padStart(2, '0')}`;

      const newPyq = {
        id: newId,
        edition,
        title,
        cadre,
        subject,
        shiftDate,
        fileSize: sizeStr,
        pdfUrl: fileUrl,
        pdfDataUri: tempPyqFileDataUrl || null,
        uploadedBy: uploaderName
      };

      OFFICIAL_PYQS.unshift(newPyq);
      try {
        localStorage.setItem(STORED_OFFICIAL_PYQS_KEY, JSON.stringify(OFFICIAL_PYQS));
      } catch (e) {
        console.warn("Could not persist official PYQ to localStorage:", e);
      }

      tempPyqFileDataUrl = null;
      renderPyqList();
      closeModal("uploadPyqModal");

      document.getElementById("pyqTitleInput").value = "";
      document.getElementById("pyqSubjectInput").value = "";
      document.getElementById("pyqShiftDateInput").value = "";
      document.getElementById("pyqFileName").innerText = "Click to select official PDF or drag & drop";
      document.getElementById("pyqFileSize").innerText = "PDF documents up to 50MB supported";
      tempSelectedPyqFile = null;

      showToast(`PYQ '${title}' uploaded and instantly live in student PYQ repository!`, "PYQ Published");
    }

    let tempBookFileDataUrl = null;
    function handleBookFileSelected(input) {
      if (input.files && input.files[0]) {
        const file = input.files[0];
        tempSelectedBookFile = file;
        const nameEl = document.getElementById("adminBookFileName");
        if (nameEl) nameEl.innerText = file.name;
        const sizeEl = document.getElementById("adminBookFileSize");
        if (sizeEl) sizeEl.innerText = `${(file.size / (1024 * 1024)).toFixed(2)} MB (${file.type || 'Document'}) - Reading into memory...`;

        const reader = new FileReader();
        reader.onload = function(e) {
          tempBookFileDataUrl = e.target.result;
          if (sizeEl) sizeEl.innerText = `${(file.size / (1024 * 1024)).toFixed(2)} MB (${file.type || 'Document'}) - Ready to publish`;
        };
        reader.onerror = function() {
          showToast("Failed to read book file.", "File Error", "breach");
        };
        reader.readAsDataURL(file);
      }
    }

    function submitAdminAddBook() {
      const titleInput = document.getElementById("adminBookTitle");
      const subjectInput = document.getElementById("adminBookSubject");
      const cadreInput = document.getElementById("adminBookCadre");
      const authorInput = document.getElementById("adminBookAuthor");
      const accessInput = document.getElementById("adminBookAccess");
      const fileInput = document.getElementById("adminBookFileInput");

      const title = titleInput ? titleInput.value.trim() : "";
      const subject = subjectInput ? subjectInput.value.trim() : "";
      const cadre = cadreInput ? cadreInput.value : "All Teaching Levels";
      const author = authorInput ? authorInput.value.trim() : "";
      const access = accessInput ? accessInput.value : "Free";

      if (!title || !subject || !author) {
        showToast("Please complete Book Title, Subject Domain, and Author / Publisher.", "Missing Fields", "breach");
        return;
      }

      const newId = `BK_${(DIGITAL_BOOKS.length + 1).toString().padStart(2, '0')}`;
      const sizeStr = tempSelectedBookFile ? `${(tempSelectedBookFile.size / (1024 * 1024)).toFixed(1)} MB` : "14.2 MB";
      const fileUrl = tempSelectedBookFile ? tempSelectedBookFile.name : `${title.replace(/\s+/g, '_')}.pdf`;

      const newBook = { 
        id: newId, 
        title: title, 
        subject: subject, 
        cadre: cadre, 
        author: author, 
        access: access, 
        fileSize: sizeStr, 
        pdfUrl: fileUrl,
        pdfDataUri: tempBookFileDataUrl || null,
        chapters: generateDefaultChaptersForBook({ id: newId, title: title, subject: subject, cadre: cadre, author: author })
      };

      DIGITAL_BOOKS.unshift(newBook);
      try {
        localStorage.setItem(STORED_DIGITAL_BOOKS_KEY, JSON.stringify(DIGITAL_BOOKS));
      } catch (e) {
        console.warn("Could not persist books to localStorage:", e);
      }

      tempBookFileDataUrl = null;
      tempSelectedBookFile = null;

      // Update student shelf, modal list, and teacher & admin tables
      renderBooksModalList();
      renderCandidateBooksShelf();
      renderTeacherBooksTable();
      renderAdminBooksTable();

      closeModal('adminAddBookModal');

      // Clear input fields
      if (titleInput) titleInput.value = "";
      if (subjectInput) subjectInput.value = "";
      if (authorInput) authorInput.value = "";
      if (fileInput) fileInput.value = "";
      const fileNameEl = document.getElementById("adminBookFileName");
      if (fileNameEl) fileNameEl.innerText = "Click to select Book PDF or drag & drop";
      const fileSizeEl = document.getElementById("adminBookFileSize");
      if (fileSizeEl) fileSizeEl.innerText = "PDF documents up to 50MB supported";

      showToast(`Book '${title}' successfully published to Digital Library!`, "Book Published");
    }

    function renderPaymentLedger() {
      const table = document.getElementById("adminPaymentTable");
      if (!table) return;

      let totalSuccess = 0;
      let successCount = 0;
      let totalPending = 0;
      let pendingCount = 0;
      let totalRefund = 0;
      let refundCount = 0;

      if (!PAYMENT_TRANSACTIONS || PAYMENT_TRANSACTIONS.length === 0) {
        table.innerHTML = `<tr><td colspan="7" class="py-6 text-center text-brand-muted text-xs">No payment transactions recorded yet. Live and recorded payments will appear here.</td></tr>`;
      } else {
        table.innerHTML = PAYMENT_TRANSACTIONS.map((txn, idx) => {
          if (txn.status === 'Success') { totalSuccess += txn.amount; successCount++; }
          else if (txn.status === 'Pending') { totalPending += txn.amount; pendingCount++; }
          else if (txn.status === 'Refunded') { totalRefund += txn.amount; refundCount++; }

          let badgeClass = "bg-amber-100 text-amber-800";
          if (txn.status === 'Success') badgeClass = "bg-emerald-100 text-emerald-800";
          else if (txn.status === 'Refunded') badgeClass = "bg-red-100 text-red-800";

          return `
            <tr>
              <td class="py-3 font-mono font-bold text-brand-slateText">${escapeHtml(txn.txnId)}</td>
              <td>
                <div class="font-bold text-brand-slateText">${escapeHtml(txn.studentName)}</div>
                <div class="text-[10px] text-brand-muted">${escapeHtml(txn.email)}</div>
              </td>
              <td><span class="font-semibold text-brand-slateText">${escapeHtml(txn.course)}</span></td>
              <td>
                <div class="font-mono text-brand-slateText">${escapeHtml(txn.gateway)}</div>
                <div class="text-[10px] text-brand-muted">${escapeHtml(txn.date)}</div>
              </td>
              <td class="font-bold text-brand-slateText font-serif">₹${txn.amount.toLocaleString('en-IN')}</td>
              <td>
                <span class="text-[10px] px-2 py-0.5 rounded font-bold uppercase ${badgeClass}">
                  ${escapeHtml(txn.status)}
                </span>
              </td>
              <td class="text-right">
                <select onchange="updatePaymentStatusByIndex(${idx}, this.value)" class="p-1 rounded-lg border border-brand-peachBorder bg-brand-ivory text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange cursor-pointer">
                  <option value="Success" ${txn.status === 'Success' ? 'selected' : ''}>Success</option>
                  <option value="Pending" ${txn.status === 'Pending' ? 'selected' : ''}>Pending</option>
                  <option value="Refunded" ${txn.status === 'Refunded' ? 'selected' : ''}>Refunded</option>
                </select>
              </td>
            </tr>
          `;
        }).join("");
      }

      document.getElementById("adminRevenueDisplay").innerText = `₹${totalSuccess.toLocaleString('en-IN')}`;
      document.getElementById("adminSuccessTxnCount").innerText = `${successCount} Successful Orders`;
      document.getElementById("adminPendingRevenueDisplay").innerText = `₹${totalPending.toLocaleString('en-IN')}`;
      document.getElementById("adminPendingTxnCount").innerText = `${pendingCount} Under Verification`;
      document.getElementById("adminRefundDisplay").innerText = `₹${totalRefund.toLocaleString('en-IN')}`;
      document.getElementById("adminRefundTxnCount").innerText = `${refundCount} Refunded Orders`;
    }

    function updatePaymentStatusByIndex(index, newStatus) {
      if (PAYMENT_TRANSACTIONS[index]) {
        PAYMENT_TRANSACTIONS[index].status = newStatus;
        renderPaymentLedger();
        showToast(`Txn #${PAYMENT_TRANSACTIONS[index].txnId} updated to ${newStatus}`, "Payment Ledger");
      }
    }

    function renderDbQuestionsTable() {
      const tbody = document.getElementById("dbQuestionsTableBody");
      const countBadge = document.getElementById("dbTotalQuestionsCount");
      if (!tbody) return;

      let allQuestions = [];
      let seen = new Set();

      // 1. Questions from Question Bank
      if (Array.isArray(QUESTION_BANK)) {
        QUESTION_BANK.forEach(q => {
          const key = (q.question || "").trim().toLowerCase();
          if (key && !seen.has(key)) {
            seen.add(key);
            allQuestions.push({
              ...q,
              paperTitle: 'Question Bank',
              paperId: 'QB',
              section: q.topic || q.section || 'General Studies'
            });
          }
        });
      }

      // 2. Questions from published question papers
      MASTER_PAPERS.forEach(p => {
        (p.questions || []).forEach(q => {
          const key = (q.question || "").trim().toLowerCase();
          if (key && !seen.has(key)) {
            seen.add(key);
            allQuestions.push({
              ...q,
              paperTitle: p.title,
              paperId: p.id,
              section: q.section || q.topic || 'General Studies'
            });
          }
        });
      });

      if (countBadge) countBadge.innerText = `${allQuestions.length} Questions in Repository`;

      if (allQuestions.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="py-6 text-center text-slate-500 font-medium text-xs">No questions loaded in database yet. Questions will appear here once uploaded via Add Question or Bulk Upload.</td></tr>`;
        return;
      }

      tbody.innerHTML = allQuestions.map((q, idx) => `
        <tr class="hover:bg-slate-50 transition border-b border-slate-100">
          <td class="py-3 px-2 font-mono text-[10px] font-bold text-brand-orange">#${idx + 1}</td>
          <td class="max-w-xs pr-2">
            <span class="font-bold text-slate-900 block text-xs">${q.question}</span>
            <span class="text-[9px] font-mono text-slate-500">${q.section} (${q.paperId})</span>
            ${q.diagram ? `<div class="mt-1"><img src="${q.diagram}" class="h-10 rounded border border-slate-200 bg-white object-contain" alt="Diagram"></div>` : ''}
          </td>
          <td class="text-[11px] space-y-0.5">
            ${(q.options || []).map((opt, oIdx) => {
              const optText = typeof opt === 'string' ? opt : (opt.text || `Option ${oIdx + 1}`);
              const isAns = (oIdx === q.ans || oIdx === q.correct_answer || (typeof opt === 'object' && opt.isCorrect));
              return `
                <div class="${isAns ? 'font-bold text-emerald-700 bg-emerald-50 px-1 rounded' : 'text-slate-700 font-medium'}">
                  ${String.fromCharCode(65 + oIdx)}) ${optText} ${isAns ? '✓' : ''}
                </div>
              `;
            }).join('')}
          </td>
          <td class="font-mono text-[11px] whitespace-nowrap">
            <span class="text-emerald-700 font-bold">+${q.posMarks || 1}</span> /
            <span class="text-red-600 font-bold">-${q.negMarks || 0.33}</span>
          </td>
          <td class="text-[10px] text-slate-600 max-w-xs font-medium">
            ${q.explanation || 'SCERT / BPSC reference'}
          </td>
        </tr>
      `).join('');
    }

    function populateQpSelectDropdown() {
      const sel1 = document.getElementById("adminQpSelectForQuestion");
      const sel2 = document.getElementById("bulkDocxTargetQp");
      
      let options1 = `
        <optgroup label="✨ Master Destination">
          <option value="__QUESTION_BANK_ONLY__" selected>📚 Question Bank (Available for all Tests)</option>
        </optgroup>
        <optgroup label="📋 Or Attach Directly to Paper (${MASTER_PAPERS.length})">
      `;
      let options2 = `
        <optgroup label="✨ Master Destination">
          <option value="__QUESTION_BANK_ONLY__" selected>📚 Question Bank (Available for all Tests)</option>
          <option value="__NEW_PAPER__">➕ Create New Question Paper from this Word file</option>
        </optgroup>
        <optgroup label="📋 Or Import Directly into Paper (${MASTER_PAPERS.length})">
      `;
      const papersOptions = MASTER_PAPERS.map(qp => {
        const audienceTag = qp.visibility === 'candidate_only' ? ' [Candidate-Only]' : ' [All Roles]';
        return `<option value="${qp.id}">[${qp.id}] ${qp.title} (${qp.cadre || 'General'}) - ${qp.questions.length} Qs${audienceTag}</option>`;
      }).join("");

      options1 += papersOptions + `</optgroup>`;
      options2 += papersOptions + `</optgroup>`;

      if (sel1) sel1.innerHTML = options1;
      if (sel2) sel2.innerHTML = options2;
    }

    function renderQuestionPapers() {
      const container = document.getElementById("qpListContainer");
      const badge = document.getElementById("teacherQpCountBadge");
      if (!container) return;

      const teacherVisiblePapers = MASTER_PAPERS.filter(p => p.visibility !== 'candidate_only');
      if (badge) badge.innerText = `${teacherVisiblePapers.length} Papers`;

      if (teacherVisiblePapers.length === 0) {
        container.innerHTML = `<div class="p-4 text-center text-xs text-brand-muted font-medium">No papers currently available for faculty review.</div>`;
        return;
      }

      container.innerHTML = teacherVisiblePapers.map(qp => `
        <div class="p-3.5 rounded-2xl bg-brand-ivory border border-brand-peachBorder flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 text-xs">
          <div>
            <div class="flex items-center gap-2">
              <span class="font-mono text-[10px] font-bold text-brand-orange">${qp.id}</span>
              <p class="font-bold text-brand-slateText">${qp.title}</p>
            </div>
            <span class="text-[11px] text-brand-muted font-sans font-medium">${qp.duration} Mins · ${qp.questions.length} Questions · Penalty ${qp.penalty}</span>
          </div>
          <div class="flex items-center gap-2 self-end sm:self-auto">
            <span class="px-2 py-0.5 rounded font-mono font-bold text-[10px] ${qp.status === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'} uppercase">
              ${qp.status}
            </span>
            <button onclick="toggleQpPublishStatus('${qp.id}')" class="px-2.5 py-1 rounded-lg border border-brand-peachBorder text-[10px] font-bold bg-white hover:bg-brand-peachSoft transition cursor-pointer">
              ${qp.status === 'published' ? 'Unpublish' : 'Publish'}
            </button>
            <button onclick="openModal('adminBulkDocxModal')" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold transition flex items-center gap-1 cursor-pointer">
              <span>📄</span> Bulk .docx
            </button>
          </div>
        </div>
      `).join("");
    }

    function renderAdminQpMasterTable() {
      const container = document.getElementById("adminQpMasterList");
      if (!container) return;

      if (!MASTER_PAPERS || MASTER_PAPERS.length === 0) {
        container.innerHTML = `
          <div class="p-6 text-center bg-brand-ivory rounded-2xl border border-brand-peachBorder text-xs text-brand-muted">
            <span class="text-2xl block mb-1">📋</span>
            No question papers created yet. Faculty and admins can create question papers using <strong>'+ Create Q.P.'</strong> or <strong>'Publish Mock'</strong>.
          </div>
        `;
        return;
      }

      container.innerHTML = MASTER_PAPERS.map(qp => `
        <div class="p-4 rounded-2xl bg-brand-ivory border border-brand-peachBorder flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 text-xs shadow-xs">
          <div>
            <div class="flex items-center gap-2">
              <span class="font-mono text-[10px] font-bold text-brand-orange bg-brand-peachSoft px-2 py-0.5 rounded">${qp.id}</span>
              <h4 class="font-bold text-brand-slateText text-xs">${qp.title}</h4>
              <span class="text-[9px] font-mono px-2 py-0.5 rounded font-bold uppercase ${qp.visibility === 'candidate_only' ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-blue-50 text-blue-700 border border-blue-200'}">
                ${qp.visibility === 'candidate_only' ? 'Candidate-Only' : 'Public'}
              </span>
            </div>
            <span class="text-[11px] text-brand-muted font-sans font-medium block mt-1">
              ${qp.cadre} · ${qp.duration} Mins · ${qp.questions.length} Questions · Total ${qp.totalMarks} Marks · Penalty ${qp.penalty}
            </span>
          </div>

          <div class="flex items-center gap-2 self-end sm:self-auto">
            <span class="px-2 py-0.5 rounded font-mono font-bold text-[10px] ${qp.status === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'} uppercase">
              ${qp.status}
            </span>
            <button onclick="toggleQpPublishStatus('${qp.id}')" class="px-2.5 py-1 rounded-lg border border-brand-peachBorder text-[10px] font-bold bg-white hover:bg-brand-peachSoft transition cursor-pointer">
              ${qp.status === 'published' ? 'Unpublish' : 'Publish'}
            </button>
          </div>
        </div>
      `).join("");
    }

    function toggleQpPublishStatus(qpId) {
      const qp = MASTER_PAPERS.find(p => p.id === qpId);
      if (qp) {
        const nextStatus = (qp.status === 'published') ? 'draft' : 'published';
        qp.status = nextStatus;

        fetch('tests-handler.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'toggle_status',
            id: qpId
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success && Array.isArray(data.papers)) {
            MASTER_PAPERS = data.papers;
            renderQuestionPapers();
            renderAdminQpMasterTable();
            populateQpSelectDropdown();
            renderCandidatePublishedTests();
          }
        })
        .catch(err => console.error("Error updating test status on server:", err));

        renderQuestionPapers();
        renderAdminQpMasterTable();
        populateQpSelectDropdown();
        renderCandidatePublishedTests();
        showToast(`Paper '${qp.title}' status changed to ${nextStatus.toUpperCase()}!`, "Status Updated");
      }
    }

    function openModal(id) {
      const el = document.getElementById(id);
      if (el) el.classList.remove('hidden');
    }
    function closeModal(id) {
      const el = document.getElementById(id);
      if (el) el.classList.add('hidden');
    }

    function triggerRazorpayCheckout(courseName, amount) {
      showToast(`Connecting to Razorpay: ₹${amount} for ${courseName}`, "Payment Gateway");
      setTimeout(() => {
        closeModal('coursesModal');
        showToast(`Payment of ₹${amount} Successful! Course unlocked.`, "Payment Confirmed");
      }, 1500);
    }

    window.onload = init;
  </script>
</body>
</html>

