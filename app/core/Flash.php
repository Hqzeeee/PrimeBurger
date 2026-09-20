<?php
/**
 * Flash
 * One-time session flash messages shown after a redirect (success/error/info).
 */
class Flash
{
    public static function set(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    public static function render(): void
    {
        if (empty($_SESSION['flash'])) {
            return;
        }
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $type = $flash['type'] === 'error' ? 'error' : ($flash['type'] === 'info' ? 'info' : 'success');
        echo '<div class="alert alert-' . $type . '" data-autohide>' . Validator::e($flash['message']) . '</div>';
    }
}
