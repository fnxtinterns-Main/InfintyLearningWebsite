<?php
use App\Lesson;
use App\Helper;
use App\Auth;
use App\LessonQuiz;
use App\DoubtFeed;

$id = (int)($_GET['id'] ?? 0);
$lesson = Lesson::find($id);
if (!$lesson) { http_response_code(404); require VIEW_PATH . '/404.php'; return; }

$user = Auth::user();
if (!Lesson::canView($lesson, $user)) {
    http_response_code(403);
    exit('This lesson is private to a school or batch you are not a member of.');
}

if ($user) {
    Lesson::markProgress($id, (int)$user['id'], 10, 'in_progress');
}
$userProgress = $user ? Lesson::getProgress($id, (int)$user['id']) : null;

$attachments = $lesson['attachments_array'] ?? [];
$quizQuestions = LessonQuiz::getOrCreateQuiz($id);
$latestAttempt = $user ? LessonQuiz::getLatestAttempt($id, (int)$user['id']) : null;
$doubts = DoubtFeed::listForLesson($id);

$pageTitle = $lesson['title'] . ' — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section-sm">
    <div class="container">

        <!-- INTERACTIVE COURSE & SUBJECT NAVIGATION -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
            <div style="display:flex; align-items:center; gap:8px; font-size:13px; flex-wrap:wrap;">
                <a href="/lessons" class="muted" style="text-decoration:none; font-weight:600;">📚 Lessons</a>
                <span class="muted">›</span>
                <a href="/lessons?subject=<?= (int)$lesson['subject_id'] ?>" style="color:var(--il-primary); font-weight:700; text-decoration:none; background:rgba(255,122,24,0.1); padding:3px 10px; border-radius:6px; display:inline-flex; align-items:center; gap:4px;">
                    <span><?= Helper::e($lesson['subject_name']) ?></span>
                </a>
                <?php if (!empty($lesson['batch_id'])): ?>
                    <span class="muted">›</span>
                    <a href="/teacher/batch?id=<?= (int)$lesson['batch_id'] ?>" class="badge badge-secondary" style="font-size:12px;">
                        🏫 <?= Helper::e($lesson['batch_name'] ?: 'Course Batch') ?>
                    </a>
                <?php endif; ?>
            </div>
            <div style="display:flex; gap:8px;">
                <a href="/lessons?subject=<?= (int)$lesson['subject_id'] ?>" class="btn btn-secondary btn-xs" style="font-weight:600;">
                    Explore <?= Helper::e($lesson['subject_name']) ?> Course →
                </a>
            </div>
        </div>

        <!-- ============ HERO ============ -->
        <div class="lesson-hero">
            <div class="lesson-hero-inner">
                <div class="lesson-hero-icon"
                     style="background:linear-gradient(135deg,<?= Helper::e($lesson['subject_color'] ?: '#FF7A18') ?>22,var(--surface-2));color:<?= Helper::e($lesson['subject_color'] ?: '#FF7A18') ?>">
                    📖
                </div>
                <div class="lesson-hero-text">
                    <div class="flex" style="gap:.4rem;flex-wrap:wrap;margin-bottom:.5rem">
                        <span class="badge badge-primary"><?= Helper::e($lesson['subject_name']) ?></span>
                        <?php if (!empty($lesson['school_name'])): ?>
                            <span class="badge badge-brand">🏫 <?= Helper::e($lesson['school_name']) ?></span>
                        <?php else: ?>
                            <span class="badge badge-info">🌐 Global</span>
                        <?php endif; ?>
                        <?php if (!empty($lesson['batch_name'])): ?>
                            <span class="badge badge-primary">🎓 <?= Helper::e($lesson['batch_name']) ?></span>
                        <?php endif; ?>
                    </div>
                    <h1><?= Helper::e($lesson['title']) ?></h1>
                    <p class="lesson-hero-sub"><?= Helper::e($lesson['summary'] ?? '') ?></p>
                    <div class="lesson-hero-meta">
                        <span>⏱ <?= (int)$lesson['estimated_minutes'] ?> min</span>
                        <span class="pill"><?= Helper::e(ucfirst($lesson['difficulty'])) ?></span>
                        <?php if (!empty($lesson['creator_name'])): ?>
                            <span>✍️ <?= Helper::e($lesson['creator_name']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============ BODY + SIDEBAR ============ -->
        <div class="detail-grid">

            <div class="detail-main">
                <?php if ($lesson['video_url']): ?>
                    <div class="card">
                        <h2 class="detail-h2">🎬 Video lesson</h2>
                        <div class="lesson-video">
                            <iframe src="<?= Helper::e($lesson['video_url']) ?>"
                                    allowfullscreen></iframe>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($lesson['content'])): ?>
                    <div class="card <?= $lesson['video_url'] ? 'mt-3' : '' ?>">
                        <h2 class="detail-h2">📝 Lesson content</h2>
                        <div class="detail-content"><?= nl2br(Helper::e($lesson['content'])) ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($attachments): ?>
                    <div class="card mt-3">
                        <h2 class="detail-h2">📎 Attachments</h2>
                        <p class="muted" style="font-size:.85rem"><?= count($attachments) ?> file<?= count($attachments) > 1 ? 's' : '' ?></p>
                        <div class="divider"></div>

                        <?php foreach ($attachments as $i => $a):
                            $ext      = strtolower($a['ext'] ?? pathinfo($a['path'], PATHINFO_EXTENSION));
                            $isImage  = in_array($ext, ['jpg','jpeg','png','webp','gif'], true);
                            $isPdf    = $ext === 'pdf';
                            $isOffice = in_array($ext, ['doc','docx','xls','xlsx','ppt','pptx'], true);
                            $icon     = Lesson::fileIcon($ext);
                            $sizeKb   = !empty($a['size']) ? round($a['size'] / 1024) : null;
                        ?>
                            <div class="attachment-block">
                                <div class="attachment-head">
                                    <div class="attachment-head-left">
                                        <span class="attachment-icon"><?= $icon ?></span>
                                        <div>
                                            <strong><?= Helper::e($a['name'] ?? basename($a['path'])) ?></strong>
                                            <div class="muted" style="font-size:.78rem">
                                                <?= strtoupper($ext) ?>
                                                <?php if ($sizeKb): ?> · <?= $sizeKb ?> KB<?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex" style="gap:.35rem;flex-wrap:wrap">
                                        <?php if ($isPdf): ?>
                                            <a class="btn btn-secondary btn-sm" href="<?= Helper::e($a['path']) ?>" target="_blank" rel="noopener">
                                                🔍 Open
                                            </a>
                                        <?php endif; ?>
                                        <a class="btn btn-ghost btn-sm" href="<?= Helper::e($a['path']) ?>" download>
                                            ⬇ Download
                                        </a>
                                    </div>
                                </div>

                                <?php if ($isImage): ?>
                                    <div class="attachment-image-preview">
                                        <img src="<?= Helper::e($a['path']) ?>" alt="">
                                    </div>
                                <?php elseif ($isPdf): ?>
                                    <div class="attachment-pdf-preview">
                                        <iframe src="<?= Helper::e($a['path']) ?>#view=FitH"></iframe>
                                    </div>
                                <?php elseif ($isOffice): ?>
                                    <div class="attachment-office-note">
                                        <span style="font-size:1.6rem"><?= $icon ?></span>
                                        <div>
                                            <strong><?= strtoupper($ext) ?> document</strong>
                                            <p class="muted" style="font-size:.82rem;margin-top:.15rem">
                                                Office documents must be downloaded to view.
                                                <a href="<?= Helper::e($a['path']) ?>" download>Download now</a>.
                                            </p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($i < count($attachments) - 1): ?>
                                <div class="divider"></div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- ============ 10 MCQ LESSON QUIZ ============ -->
                <div class="card mt-4" id="lesson-quiz-section" style="border:1px solid rgba(255,122,24,0.25);box-shadow:0 10px 25px -5px rgba(255,122,24,0.08)">
                    <div class="flex items-center justify-between" style="flex-wrap:wrap;gap:.6rem">
                        <div>
                            <span class="badge badge-warning" style="font-weight:700">🎯 Check for Understanding</span>
                            <h2 class="detail-h2" style="margin-top:.4rem;margin-bottom:.2rem">10-Question Lesson Quiz</h2>
                            <p class="muted" style="font-size:.88rem">Test your comprehension immediately. Score 60%+ to earn <strong>+25 XP</strong> and mark this lesson fully complete!</p>
                        </div>
                        <?php if ($latestAttempt): ?>
                            <div style="text-align:right">
                                <span class="badge <?= $latestAttempt['passed'] ? 'badge-success' : 'badge-danger' ?>" style="font-size:.88rem;padding:.4rem .8rem">
                                    <?= $latestAttempt['passed'] ? '✅ Passed' : '⚠️ Need Practice' ?> · <?= (int)$latestAttempt['score'] ?>/<?= (int)$latestAttempt['total_questions'] ?>
                                </span>
                                <div class="muted" style="font-size:.76rem;margin-top:.2rem">Last attempt: <?= date('d M Y, H:i', strtotime($latestAttempt['attempted_at'])) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="divider"></div>

                    <form id="quiz-form">
                        <div class="quiz-questions-list" style="display:flex;flex-direction:column;gap:1.4rem">
                            <?php foreach ($quizQuestions as $qIdx => $q): ?>
                                <div class="card p-3 quiz-q-card" id="quiz-q-<?= $qIdx ?>" style="background:var(--surface-2);border-radius:10px;border:1px solid var(--border)">
                                    <div class="flex items-start justify-between" style="gap:.5rem">
                                        <div style="font-weight:600;font-size:.95rem;color:var(--text)">
                                            <span style="display:inline-block;width:24px;height:24px;border-radius:50%;background:var(--primary);color:#fff;text-align:center;line-height:24px;font-size:.75rem;margin-right:.4rem">
                                                <?= $qIdx + 1 ?>
                                            </span>
                                            <?= Helper::e($q['question']) ?>
                                        </div>
                                    </div>

                                    <div class="quiz-options-group" style="margin-top:.8rem;display:flex;flex-direction:column;gap:.45rem">
                                        <?php foreach ($q['options'] as $optIdx => $optText): ?>
                                            <label class="quiz-option-label" style="display:flex;align-items:center;gap:.6rem;padding:.5rem .75rem;border-radius:8px;background:var(--surface);border:1px solid var(--border);cursor:pointer;transition:all .15s">
                                                <input type="radio" name="answer_<?= $qIdx ?>" value="<?= $optIdx ?>" required style="accent-color:var(--primary)">
                                                <span style="font-size:.9rem"><?= Helper::e($optText) ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="quiz-explanation-box" id="quiz-exp-<?= $qIdx ?>" style="display:none;margin-top:.7rem;padding:.6rem .8rem;border-radius:8px;font-size:.85rem"></div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div id="quiz-results-banner" style="display:none;margin-top:1.2rem;padding:1rem;border-radius:10px;text-align:center"></div>

                        <div class="flex items-center justify-between mt-4" style="flex-wrap:wrap;gap:.8rem">
                            <span class="muted" style="font-size:.85rem">Answer all 10 questions to submit.</span>
                            <button type="submit" id="btn-submit-quiz" class="btn btn-primary" style="padding:.6rem 1.8rem;font-weight:600">
                                🚀 Submit Quiz & Check Score
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ============ ASK A DOUBT FEED ============ -->
                <div class="card mt-4" id="lesson-doubts-section">
                    <div class="flex items-center justify-between" style="flex-wrap:wrap;gap:.6rem">
                        <div>
                            <span class="badge badge-info" style="font-weight:700">💬 Q&A & Discussion</span>
                            <h2 class="detail-h2" style="margin-top:.4rem;margin-bottom:.2rem">Ask a Doubt</h2>
                            <p class="muted" style="font-size:.88rem">Stuck on a concept? Post your question here for teachers and peers to answer.</p>
                        </div>
                        <span class="pill" style="font-size:.82rem"><?= count($doubts) ?> question<?= count($doubts) === 1 ? '' : 's' ?></span>
                    </div>
                    <div class="divider"></div>

                    <!-- New Doubt Form -->
                    <form id="new-doubt-form" style="background:var(--surface-2);padding:1rem;border-radius:10px;border:1px solid var(--border)">
                        <input type="hidden" name="action" value="ask">
                        <input type="hidden" name="lesson_id" value="<?= $id ?>">
                        <div class="form-group" style="margin-bottom:.8rem">
                            <label style="font-weight:600;font-size:.85rem;margin-bottom:.3rem;display:block">Question Title</label>
                            <input type="text" name="title" class="input" placeholder="e.g. Why does step 3 invert the fraction?" required style="width:100%">
                        </div>
                        <div class="form-group" style="margin-bottom:.8rem">
                            <label style="font-weight:600;font-size:.85rem;margin-bottom:.3rem;display:block">Detailed Explanation</label>
                            <textarea name="body" class="input" rows="3" placeholder="Describe what you tried or where you got confused..." required style="width:100%"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" id="btn-post-doubt" class="btn btn-secondary btn-sm" style="font-weight:600">
                                ❓ Post Question
                            </button>
                        </div>
                    </form>

                    <!-- Doubts Stream -->
                    <div class="doubts-feed-list mt-4" style="display:flex;flex-direction:column;gap:1.2rem">
                        <?php if (empty($doubts)): ?>
                            <div class="text-center py-4 muted" style="font-size:.9rem" id="empty-doubts-msg">
                                No questions posted yet for this lesson. Be the first to ask!
                            </div>
                        <?php else: ?>
                            <?php foreach ($doubts as $d): ?>
                                <div class="card p-3 doubt-card" style="background:var(--surface);border:1px solid var(--border);border-radius:10px">
                                    <div class="flex items-start justify-between" style="gap:.5rem">
                                        <div>
                                            <div class="flex items-center" style="gap:.5rem">
                                                <strong><?= Helper::e($d['student_name'] ?: 'Student') ?></strong>
                                                <span class="muted" style="font-size:.78rem">· <?= date('d M, H:i', strtotime($d['created_at'])) ?></span>
                                            </div>
                                            <h4 style="margin:.3rem 0 .2rem;font-size:1.02rem"><?= Helper::e($d['title']) ?></h4>
                                            <p style="font-size:.9rem;color:var(--text);margin-bottom:.5rem"><?= nl2br(Helper::e($d['body'])) ?></p>
                                        </div>
                                        <span class="badge <?= $d['status'] === 'answered' ? 'badge-success' : 'badge-warning' ?>" style="font-size:.75rem">
                                            <?= $d['status'] === 'answered' ? '✅ Answered' : '⏳ Open' ?>
                                        </span>
                                    </div>

                                    <!-- Replies -->
                                    <?php if (!empty($d['replies'])): ?>
                                        <div class="doubt-replies" style="margin-top:.8rem;padding-left:1rem;border-left:3px solid var(--border);display:flex;flex-direction:column;gap:.6rem">
                                            <?php foreach ($d['replies'] as $rep): ?>
                                                <?php 
                                                $isAiReply = (!empty($rep['is_ai']) || ($rep['user_role'] ?? '') === 'ai' || strpos($rep['reply_text'] ?? '', '<!-- AI_ANSWER -->') !== false);
                                                $isTeacher = (!$isAiReply && (!empty($rep['is_teacher_endorsed']) || ($rep['user_role'] ?? '') === 'teacher' || ($rep['user_role'] ?? '') === 'admin'));
                                                $cleanText = str_replace(['<!-- AI_ANSWER -->', '[AI_GENERATED]'], '', $rep['reply_text'] ?? '');
                                                ?>
                                                <div style="background:<?= $isAiReply ? 'rgba(99,102,241,0.04)' : ($rep['is_teacher_endorsed'] ? 'rgba(34,197,94,0.08)' : 'var(--surface-2)') ?>;border:1px solid <?= $isAiReply ? 'rgba(99,102,241,0.3)' : ($rep['is_teacher_endorsed'] ? 'rgba(34,197,94,0.3)' : 'var(--border)') ?>;border-radius:8px;padding:.6rem .8rem;position:relative">
                                                    <div class="flex items-center justify-between" style="gap:.4rem">
                                                        <div class="flex items-center" style="gap:.4rem">
                                                            <?php if ($isAiReply): ?>
                                                                <span style="font-size:14px;">🤖</span>
                                                                <strong style="font-size:.85rem">Infinity AI Tutor</strong>
                                                            <?php else: ?>
                                                                <strong style="font-size:.85rem"><?= Helper::e($rep['author_name']) ?></strong>
                                                                <?php if ($isTeacher): ?>
                                                                    <span class="badge badge-primary" style="font-size:.7rem;padding:.15rem .45rem">Teacher / Staff</span>
                                                                <?php endif; ?>
                                                                <?php if ($rep['is_teacher_endorsed']): ?>
                                                                    <span class="badge badge-success" style="font-size:.7rem;padding:.15rem .45rem">⭐ Verified Answer</span>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="flex items-center" style="gap:.5rem">
                                                            <?php if ($isAiReply): ?>
                                                                <span class="badge" style="background:linear-gradient(135deg, #6366f1, #8b5cf6); color:#fff; font-size:9.5px; font-weight:700; padding:2px 7px; border-radius:999px; text-transform:uppercase; letter-spacing:0.04em;">
                                                                    ✨ Generated by AI
                                                                </span>
                                                            <?php endif; ?>
                                                            <span class="muted" style="font-size:.74rem"><?= date('d M, H:i', strtotime($rep['created_at'])) ?></span>
                                                        </div>
                                                    </div>
                                                    <div style="font-size:.88rem;margin-top:.3rem;color:var(--text);line-height:1.5">
                                                        <?= nl2br(Helper::e(trim($cleanText))) ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Quick Reply Form -->
                                    <form class="doubt-reply-form mt-3" style="display:flex;gap:.5rem">
                                        <input type="hidden" name="action" value="reply">
                                        <input type="hidden" name="doubt_id" value="<?= (int)$d['id'] ?>">
                                        <input type="text" name="reply_text" class="input" placeholder="Write an answer or hint..." required style="flex:1;font-size:.85rem">
                                        <button type="submit" class="btn btn-secondary btn-sm" style="white-space:nowrap">Reply</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Quiz Submission Handler
                const quizForm = document.getElementById('quiz-form');
                if (quizForm) {
                    quizForm.addEventListener('submit', async function(e) {
                        e.preventDefault();
                        const btn = document.getElementById('btn-submit-quiz');
                        btn.disabled = true;
                        btn.textContent = 'Evaluating answers...';

                        const answers = [];
                        for (let i = 0; i < <?= count($quizQuestions) ?>; i++) {
                            const selected = document.querySelector(`input[name="answer_${i}"]:checked`);
                            answers.push(selected ? parseInt(selected.value) : -1);
                        }

                        try {
                            const res = await fetch('/lesson-quiz-handler.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ lesson_id: <?= $id ?>, answers: answers })
                            });
                            const data = await res.json();
                            if (!data.success) {
                                alert(data.error || 'Submission failed');
                                btn.disabled = false;
                                btn.textContent = '🚀 Submit Quiz & Check Score';
                                return;
                            }

                            // Show evaluations
                            const evals = data.data.evaluations || [];
                            evals.forEach((ev, idx) => {
                                const card = document.getElementById(`quiz-q-${idx}`);
                                const expBox = document.getElementById(`quiz-exp-${idx}`);
                                if (ev.is_correct) {
                                    card.style.borderColor = 'rgba(34, 197, 94, 0.6)';
                                    card.style.background = 'rgba(34, 197, 94, 0.05)';
                                } else {
                                    card.style.borderColor = 'rgba(239, 68, 68, 0.6)';
                                    card.style.background = 'rgba(239, 68, 68, 0.05)';
                                }

                                if (expBox) {
                                    expBox.style.display = 'block';
                                    expBox.style.background = ev.is_correct ? 'rgba(34, 197, 94, 0.12)' : 'rgba(239, 68, 68, 0.12)';
                                    expBox.style.color = ev.is_correct ? '#166534' : '#991b1b';
                                    expBox.innerHTML = `<strong>${ev.is_correct ? '✅ Correct!' : '❌ Incorrect.'}</strong> ` + (ev.explanation ? `<div>💡 <em>${ev.explanation}</em></div>` : '');
                                }
                            });

                            const banner = document.getElementById('quiz-results-banner');
                            banner.style.display = 'block';
                            if (data.data.passed) {
                                banner.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                                banner.style.color = '#fff';
                                banner.innerHTML = `
                                    <h3 style="font-size:1.3rem;margin:0 0 .3rem">🎉 Outstanding Work! Score: ${data.data.score}/${data.data.total} (${data.data.percentage}%)</h3>
                                    <p style="margin:0;font-size:.95rem">You passed the comprehension check and earned <strong>+${data.data.xp_awarded} XP</strong>! Lesson marked as completed.</p>
                                `;
                            } else {
                                banner.style.background = 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)';
                                banner.style.color = '#fff';
                                banner.innerHTML = `
                                    <h3 style="font-size:1.3rem;margin:0 0 .3rem">Score: ${data.data.score}/${data.data.total} (${data.data.percentage}%)</h3>
                                    <p style="margin:0;font-size:.95rem">You scored below 60%. Review the explanations above and try again to master this topic!</p>
                                `;
                            }

                            btn.disabled = false;
                            btn.textContent = '🔄 Retake Quiz';
                            banner.scrollIntoView({ behavior: 'smooth' });
                        } catch (err) {
                            alert('Network error while evaluating quiz.');
                            btn.disabled = false;
                            btn.textContent = '🚀 Submit Quiz & Check Score';
                        }
                    });
                }

                // New Doubt Form Handler
                const doubtForm = document.getElementById('new-doubt-form');
                if (doubtForm) {
                    doubtForm.addEventListener('submit', async function(e) {
                        e.preventDefault();
                        const btn = document.getElementById('btn-post-doubt');
                        btn.disabled = true;
                        const fd = new FormData(doubtForm);

                        try {
                            const res = await fetch('/lesson-doubt-handler.php', { method: 'POST', body: fd });
                            const data = await res.json();
                            if (data.success) {
                                location.reload();
                            } else {
                                alert(data.error || 'Could not post question');
                                btn.disabled = false;
                            }
                        } catch (err) {
                            alert('Network error');
                            btn.disabled = false;
                        }
                    });
                }

                // Reply Forms Handlers
                document.querySelectorAll('.doubt-reply-form').forEach(rf => {
                    rf.addEventListener('submit', async function(e) {
                        e.preventDefault();
                        const btn = rf.querySelector('button');
                        btn.disabled = true;
                        const fd = new FormData(rf);
                        try {
                            const res = await fetch('/lesson-doubt-handler.php', { method: 'POST', body: fd });
                            const data = await res.json();
                            if (data.success) {
                                location.reload();
                            } else {
                                alert(data.error || 'Could not post reply');
                                btn.disabled = false;
                            }
                        } catch (err) {
                            alert('Network error');
                            btn.disabled = false;
                        }
                    });
                });
            });
            </script>

            <aside class="detail-side">
                <!-- ============ INTERACTIVE LESSON PROGRESS TRACKING ============ -->
                <?php if ($user): 
                    $curPct = (int)($userProgress['progress_percent'] ?? 10);
                    $curStatus = $userProgress['status'] ?? 'in_progress';
                    $curScore = (int)($userProgress['comprehension_score'] ?? 0);
                    $startedAt = !empty($userProgress['last_viewed_at']) ? date('d M Y, H:i', strtotime($userProgress['last_viewed_at'])) : date('d M Y, H:i');
                ?>
                    <div class="card" style="border-radius:14px; border:1.5px solid var(--il-primary); padding:18px 20px; margin-bottom:18px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                            <h3 style="margin:0; font-size:16px; display:flex; align-items:center; gap:6px;">
                                <span>🎯</span> Lesson Progress
                            </h3>
                            <span id="badge-lesson-status" class="badge badge-<?= $curStatus === 'completed' ? 'success' : 'primary' ?>" style="font-size:11px; text-transform:capitalize;">
                                <?= $curStatus === 'completed' ? '✅ Completed' : '🔄 In Progress' ?>
                            </span>
                        </div>

                        <!-- 1. Lesson Start -->
                        <div style="font-size:12px; color:var(--il-muted); margin-bottom:12px;">
                            <strong>⏱️ Lesson Started:</strong> <span id="label-started-at"><?= $startedAt ?></span>
                        </div>

                        <!-- 2. Progress Rate -->
                        <div style="margin-bottom:14px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; font-size:12.5px; font-weight:700; margin-bottom:6px;">
                                <span>Progress Rate</span>
                                <span id="label-progress-pct" style="color:var(--il-primary);"><?= $curPct ?>%</span>
                            </div>
                            <div style="background:var(--surface-2); border-radius:999px; height:8px; overflow:hidden; border:1px solid var(--il-border); margin-bottom:8px;">
                                <div id="bar-progress-fill" style="background:linear-gradient(90deg, #FF7A18, #10B981); height:100%; width:<?= $curPct ?>%; border-radius:999px; transition:width 0.4s ease;"></div>
                            </div>
                            <!-- Quick Rate Selector -->
                            <div style="display:flex; gap:6px; justify-content:space-between;">
                                <button type="button" onclick="updateLessonRate(25)" class="btn btn-ghost btn-xs" style="font-size:11px; padding:3px 6px;">25%</button>
                                <button type="button" onclick="updateLessonRate(50)" class="btn btn-ghost btn-xs" style="font-size:11px; padding:3px 6px;">50%</button>
                                <button type="button" onclick="updateLessonRate(75)" class="btn btn-ghost btn-xs" style="font-size:11px; padding:3px 6px;">75%</button>
                                <button type="button" onclick="updateLessonRate(100)" class="btn btn-ghost btn-xs" style="font-size:11px; padding:3px 6px;">100%</button>
                            </div>
                        </div>

                        <!-- 3. Lesson Completion Toggle -->
                        <div style="margin-bottom:16px;">
                            <button type="button" id="btn-toggle-completion" onclick="toggleCompletion()" class="btn btn-block <?= $curStatus === 'completed' ? 'btn-outline-secondary' : 'btn-primary' ?>" style="font-size:13px; font-weight:700; padding:8px 12px;">
                                <?= $curStatus === 'completed' ? '✓ Mark as In Progress' : '✅ Mark Lesson Completed' ?>
                            </button>
                        </div>

                        <!-- 4. Score Selection (Comprehension Self-Rating) -->
                        <div style="border-top:1px dashed var(--il-border); padding-top:12px;">
                            <div style="font-size:12px; font-weight:700; margin-bottom:6px; display:flex; justify-content:space-between;">
                                <span>Comprehension Score:</span>
                                <span id="label-score-val" style="color:#d97706; font-weight:800;"><?= $curScore ? ($curScore . '/5 ★') : 'Not rated' ?></span>
                            </div>
                            <div style="display:flex; gap:8px; justify-content:center;">
                                <?php for ($s = 1; $s <= 5; $s++): ?>
                                    <button type="button" onclick="setLessonScore(<?= $s ?>)" id="star-btn-<?= $s ?>" style="background:none; border:none; font-size:22px; cursor:pointer; color:<?= $s <= $curScore ? '#f59e0b' : '#cbd5e1' ?>; transition:transform 0.15s ease;" title="<?= $s ?> Star">
                                        ★
                                    </button>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <script>
                    let currentProgressPct = <?= $curPct ?>;
                    let currentStatus = '<?= $curStatus ?>';
                    let currentScore = <?= $curScore ?>;

                    async function updateLessonRate(pct) {
                        currentProgressPct = pct;
                        if (pct >= 100) currentStatus = 'completed';
                        await syncLessonProgress();
                    }

                    async function toggleCompletion() {
                        if (currentStatus === 'completed') {
                            currentStatus = 'in_progress';
                            currentProgressPct = 50;
                        } else {
                            currentStatus = 'completed';
                            currentProgressPct = 100;
                        }
                        await syncLessonProgress();
                    }

                    async function setLessonScore(score) {
                        currentScore = score;
                        await syncLessonProgress();
                    }

                    async function syncLessonProgress() {
                        try {
                            const res = await fetch('/lesson-progress-handler.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    lesson_id: <?= $id ?>,
                                    progress_percent: currentProgressPct,
                                    status: currentStatus,
                                    score: currentScore
                                })
                            });
                            const data = await res.json();
                            if (data.success) {
                                document.getElementById('label-progress-pct').textContent = currentProgressPct + '%';
                                document.getElementById('bar-progress-fill').style.width = currentProgressPct + '%';
                                
                                const statusBadge = document.getElementById('badge-lesson-status');
                                const toggleBtn = document.getElementById('btn-toggle-completion');
                                if (currentStatus === 'completed') {
                                    statusBadge.className = 'badge badge-success';
                                    statusBadge.textContent = '✅ Completed';
                                    toggleBtn.className = 'btn btn-block btn-outline-secondary';
                                    toggleBtn.textContent = '✓ Mark as In Progress';
                                } else {
                                    statusBadge.className = 'badge badge-primary';
                                    statusBadge.textContent = '🔄 In Progress';
                                    toggleBtn.className = 'btn btn-block btn-primary';
                                    toggleBtn.textContent = '✅ Mark Lesson Completed';
                                }

                                if (currentScore > 0) {
                                    document.getElementById('label-score-val').textContent = currentScore + '/5 ★';
                                    for (let s = 1; s <= 5; s++) {
                                        const btn = document.getElementById('star-btn-' + s);
                                        if (btn) btn.style.color = s <= currentScore ? '#f59e0b' : '#cbd5e1';
                                    }
                                }
                            }
                        } catch (e) {
                            console.error('Progress sync error', e);
                        }
                    }
                    </script>
                <?php endif; ?>

                <div class="card">
                    <h3>📚 Lesson info</h3>
                    <div class="divider"></div>
                    <div class="kv"><span>Subject</span><span><?= Helper::e($lesson['subject_name']) ?></span></div>
                    <div class="kv"><span>Difficulty</span><span><?= Helper::e(ucfirst($lesson['difficulty'])) ?></span></div>
                    <div class="kv"><span>Duration</span><span><?= (int)$lesson['estimated_minutes'] ?> min</span></div>
                    <?php if (!empty($lesson['batch_name'])): ?>
                        <div class="kv"><span>Batch</span><span><?= Helper::e($lesson['batch_name']) ?></span></div>
                    <?php endif; ?>
                    <?php if (!empty($lesson['school_name'])): ?>
                        <div class="kv"><span>School</span><span><?= Helper::e($lesson['school_name']) ?></span></div>
                    <?php endif; ?>
                    <div class="kv"><span>Published</span><span><?= date('d M Y', strtotime($lesson['created_at'])) ?></span></div>
                </div>

                <div class="card mt-3">
                    <a href="/lessons" class="btn btn-secondary btn-block">← Browse more lessons</a>
                </div>
            </aside>
        </div>
    </div>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>