<?php
require_once __DIR__ . '/includes/db.php';
require_login();

$reportId = (int) ($_GET['report_id'] ?? 0);
$withUser = (int) ($_GET['with'] ?? 0);

if ($reportId <= 0 || $withUser <= 0 || $withUser === (int) $_SESSION['user_id']) {
    redirect('dashboard.php');
}

$reportStmt = $pdo->prepare('SELECT * FROM reports WHERE id = ? LIMIT 1');
$reportStmt->execute([$reportId]);
$report = $reportStmt->fetch();
if (!$report) {
    redirect('dashboard.php');
}

$userStmt = $pdo->prepare('SELECT id, name, role FROM users WHERE id = ? LIMIT 1');
$userStmt->execute([$withUser]);
$receiver = $userStmt->fetch();
if (!$receiver) {
    redirect('dashboard.php');
}

$messagesStmt = $pdo->prepare('SELECT * FROM chat_messages WHERE report_id = ? AND ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) ORDER BY id ASC');
$messagesStmt->execute([$reportId, $_SESSION['user_id'], $withUser, $withUser, $_SESSION['user_id']]);
$messages = $messagesStmt->fetchAll();

$markRead = $pdo->prepare('UPDATE chat_messages SET is_read = 1 WHERE report_id = ? AND sender_id = ? AND receiver_id = ?');
$markRead->execute([$reportId, $withUser, $_SESSION['user_id']]);

$pageTitle = 'Chat - ' . SITE_NAME;
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<div class="container py-5" style="max-width:860px;">
  <div class="report-card p-3 mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
      <a href="<?= BASE_URL ?>report-detail.php?id=<?= (int) $reportId ?>" class="btn btn-outline-custom btn-sm"><i class="bi bi-arrow-left"></i></a>
      <div class="avatar-sm"><?= h(strtoupper(substr($receiver['name'], 0, 2))) ?></div>
      <div>
        <div><strong><?= h($receiver['name']) ?></strong> <span class="text-muted small">(<?= h(ucfirst($receiver['role'])) ?>)</span></div>
        <div class="small text-muted"><?= h($report['title']) ?></div>
      </div>
    </div>
    <span class="<?= report_type_badge($report['type']) ?>"><?= strtoupper(h($report['type'])) ?></span>
  </div>

  <div id="chat-box" class="chat-box" data-report-id="<?= (int) $reportId ?>" data-with-id="<?= (int) $withUser ?>" data-my-id="<?= (int) $_SESSION['user_id'] ?>">
    <?php foreach ($messages as $m): ?>
      <?php $mine = (int) $m['sender_id'] === (int) $_SESSION['user_id']; ?>
      <div class="<?= $mine ? 'chat-bubble-mine' : 'chat-bubble-other' ?>" data-message-id="<?= (int) $m['id'] ?>">
        <?= nl2br(h($m['message'])) ?>
        <div class="chat-time"><?= h(human_datetime($m['created_at'])) ?><?= $mine ? ' ' . ($m['is_read'] ? '✓✓' : '✓') : '' ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <form id="chat-form" class="mt-3 d-flex gap-2" data-report-id="<?= (int) $reportId ?>" data-receiver-id="<?= (int) $withUser ?>">
    <textarea id="chat-message" class="form-custom" rows="2" placeholder="Type message. Enter to send, Shift+Enter new line"></textarea>
    <button type="submit" class="btn btn-accent px-4">Send</button>
  </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
