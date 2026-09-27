<style>
    /* Assistant Chat Styling */
.assistant-page {
  max-width: 800px;
  margin: 0 auto;
}

.eyebrow-cyan {
  color: #22d3ee;
  font-weight: 700;
  letter-spacing: 1.5px;
  font-size: 0.85rem;
}

.chips-wrapper {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-top: 16px;
}

.btn-chip-neon {
  background: #0a0a14;
  border: 1px solid #232842;
  color: #c8cbe0;
  padding: 8px 16px;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-chip-neon:hover {
  border-color: #22d3ee;
  color: #f0f2ff;
  box-shadow: 0 0 10px rgba(34, 211, 238, 0.3);
}

.neon-panel {
  background: #0a0a14;
  border: 1px solid #232842;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 20px rgba(5, 5, 8, 0.8);
}

.chat-entry {
  border-bottom: 1px solid #232842;
  padding-bottom: 16px;
  margin-bottom: 16px;
}

.chat-entry:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.user-msg {
  color: #4f9dff;
  margin-bottom: 8px;
}

.bot-msg {
  background: rgba(35, 40, 66, 0.4);
  padding: 12px 16px;
  border-radius: 8px;
  border-left: 3px solid #a855f7;
}

.msg-time {
  color: #8b90b0;
  font-size: 0.75rem;
  display: block;
  margin-top: 6px;
}

.input-neon {
  width: 100%;
  background: #050508;
  border: 1px solid #232842;
  color: #f0f2ff;
  padding: 12px;
  border-radius: 8px;
  outline: none;
  transition: border-color 0.3s ease;
}

.input-neon:focus {
  border-color: #4f9dff;
  box-shadow: 0 0 8px rgba(79, 157, 255, 0.4);
}

.text-button-danger {
  background: none;
  border: none;
  color: #ec4899;
  cursor: pointer;
  margin-top: 12px;
  text-decoration: underline;
}

.text-button-danger:hover {
  color: #a855f7;
}
</style>
<?php
require_once __DIR__ . '/includes/functions.php';

// User and session initialization
$user = current_user();
$userId = $user ? (int) $user['user_id'] : null;
$sessionKey = hash('sha256', session_id());
$errorMessage = '';

// Handle POST actions (Clear history & Send message)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = input('action');

    // Action: Clear Chat History
    if ($action === 'clear') {
        if ($userId) {
            db_query('DELETE FROM chatbot_messages WHERE user_id = ?', [$userId]);
        } else {
            db_query('DELETE FROM chatbot_messages WHERE session_key = ? AND user_id IS NULL', [$sessionKey]);
        }
        redirect(base_url('chatbot.php'));
    }

    // Action: Submit Question
    $userMessage = trim((string) input('message'));
    $lastChatTime = $_SESSION['last_chat'] ?? 0;

    if ($userMessage === '' || mb_strlen($userMessage) > 1000) {
        $errorMessage = 'Enter a question up to 1,000 characters.';
    } elseif (time() - $lastChatTime < 2) {
        $errorMessage = 'Please wait a moment before sending another question.';
    } else {
        // FAQ Matching Logic
        $faqs = db_all('SELECT * FROM chatbot_faqs WHERE is_active = 1');
        $highestScore = 0;
        $botResponse = 'I could not find an answer in the knowledge base. Try asking about bookmarks, categories, events, submissions or password recovery. You can also send a query through Feedback.';

        // Extract meaningful search terms
        $stopWords = ['how', 'the', 'can', 'you', 'what', 'with', 'have', 'and', 'does', 'are', 'for'];
        $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($userMessage));
        $terms = array_filter($words, fn($word) => mb_strlen($word) > 2 && !in_array($word, $stopWords, true));

        foreach ($faqs as $faq) {
            $searchableText = mb_strtolower($faq['question'] . ' ' . $faq['keywords']);
            $score = 0;

            foreach ($terms as $term) {
                if (str_contains($searchableText, $term)) {
                    $score++;
                }
            }

            // Exact match bonus
            if (mb_strtolower($userMessage) === mb_strtolower($faq['question'])) {
                $score += 10;
            }

            if ($score > $highestScore) {
                $highestScore = $score;
                $botResponse = $faq['answer'];
            }
        }

        // Save conversation entry
        db_query(
            'INSERT INTO chatbot_messages (user_id, session_key, message, response) VALUES (?, ?, ?, ?)',
            [$userId, $sessionKey, $userMessage, $botResponse]
        );

        $_SESSION['last_chat'] = time();
        redirect(base_url('chatbot.php#conversation'));
    }
}

// Fetch chat history
if ($userId) {
    $chatHistory = db_all('SELECT * FROM chatbot_messages WHERE user_id = ? ORDER BY message_id DESC LIMIT 30', [$userId]);
} else {
    $chatHistory = db_all('SELECT * FROM chatbot_messages WHERE session_key = ? AND user_id IS NULL ORDER BY message_id DESC LIMIT 30', [$sessionKey]);
}

$page_title = 'Fan Hub Assistant';
require __DIR__ . '/includes/header.php';
?>

<main id="main" class="page narrow assistant-page">
    <header class="assistant-header" data-aos="fade-down">
        <span class="eyebrow-cyan">PLATFORM HELP</span>
        <h1 class="title-gradient">Fan Hub Assistant</h1>
        <p class="text-soft">
            Answers from our administrator-managed FAQ. This is a knowledge-base assistant; it does not use a paid AI service.
        </p>
    </header>

    <!-- Quick Prompt Chips -->
    <div class="chips-wrapper" data-aos="fade-up" data-aos-delay="100">
        <?php 
        $suggestedQuestions = [
            'How do I bookmark an item?',
            'How do I find events near me?',
            'How do I share my fan work?'
        ];
        foreach ($suggestedQuestions as $prompt): 
        ?>
            <form method="post" class="chip-form">
                <?= csrf_field() ?>
                <input type="hidden" name="message" value="<?= e($prompt) ?>">
                <button type="submit" class="btn-chip-neon">
                    <?= e($prompt) ?>
                </button>
            </form>
        <?php endforeach; ?>
    </div>

    <!-- Conversation Window -->
    <section id="conversation" class="panel neon-panel conversation-box" data-aos="fade-up" data-aos-delay="200">
        <?php foreach (array_reverse($chatHistory) as $entry): ?>
            <article class="chat-entry">
                <div class="user-msg">
                    <span class="msg-author">You:</span>
                    <p><?= e($entry['message']) ?></p>
                </div>
                <div class="bot-msg">
                    <span class="msg-author-bot">Assistant:</span>
                    <p><?= nl2br(e($entry['response'])) ?></p>
                    <small class="msg-time"><?= e($entry['created_at']) ?> UTC</small>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if (empty($chatHistory)): ?>
            <div class="empty-state">
                <p>Ask a question to get started. Your conversation will appear here.</p>
            </div>
        <?php endif; ?>
    </section>

    <!-- Error Alert -->
    <?php if ($errorMessage): ?>
        <p class="errors-neon" role="alert" data-aos="shake"><?= e($errorMessage) ?></p>
    <?php endif; ?>

    <!-- Question Input Form -->
    <form method="post" class="panel neon-panel input-form" data-aos="fade-up" data-aos-delay="300">
        <?= csrf_field() ?>
        <div class="field">
            <label for="message" class="text-soft">Your question</label>
            <input 
                id="message" 
                name="message" 
                type="text" 
                class="input-neon" 
                required 
                maxlength="1000" 
                placeholder="How do I save a bookmark?"
            >
        </div>
        <button type="submit" class="btn-neon-primary">Ask Assistant</button>
    </form>

    <!-- Clear History Button -->
    <?php if (!empty($chatHistory)): ?>
        <div class="clear-history-container" data-aos="fade-up">
            <form method="post" data-confirm="Clear your assistant history?">
                <?= csrf_field() ?>
                <button type="submit" class="text-button-danger" name="action" value="clear">
                    Clear my history
                </button>
            </form>
        </div>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>