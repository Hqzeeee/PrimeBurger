<?php
class UserController
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    public function store(array $post): array
    {
        Csrf::verifyRequest();
        $data = Validator::clean($post);

        $v = new Validator($data);
        $v->required('username', 'Username')->maxLength('username', 50, 'Username')
          ->required('email', 'Email')->email('email', 'Email')
          ->required('full_name', 'Full name')
          ->required('role', 'Role')
          ->required('password', 'Password');

        if (!empty($data['password']) && strlen($data['password']) < 8) {
            return [false, 'Password must be at least 8 characters long.'];
        }
        if (!in_array($data['role'] ?? '', ['owner', 'staff'], true)) {
            return [false, 'Invalid role selected.'];
        }
        if ($v->fails()) {
            return [false, 'Please correct the errors below.'];
        }
        if ($this->users->usernameOrEmailExists($data['username'], $data['email'])) {
            return [false, 'That username or email is already in use.'];
        }

        $this->users->create($data);
        ActivityLogger::log('user_created', "Created user {$data['username']} ({$data['role']})");

        return [true, "User \"{$data['username']}\" was created."];
    }

    public function update(int $id, array $post): array
    {
        Csrf::verifyRequest();
        $data = Validator::clean($post);

        $v = new Validator($data);
        $v->required('username', 'Username')->required('email', 'Email')->email('email', 'Email')
          ->required('full_name', 'Full name')->required('role', 'Role')->required('status', 'Status');

        if ($v->fails()) {
            return [false, 'Please correct the errors below.'];
        }
        if ($this->users->usernameOrEmailExists($data['username'], $data['email'], $id)) {
            return [false, 'That username or email is already in use by another account.'];
        }
        if ($id === Auth::id() && $data['status'] === 'disabled') {
            return [false, 'You cannot disable your own account while logged in.'];
        }

        $this->users->update($id, $data);

        if (!empty($data['password'])) {
            if (strlen($data['password']) < 8) {
                return [false, 'Account details saved, but the password was not changed: it must be at least 8 characters.'];
            }
            $this->users->changePassword($id, $data['password']);
        }

        ActivityLogger::log('user_updated', "Updated user #{$id}");
        return [true, 'User updated successfully.'];
    }

    public function changeOwnPassword(int $id, array $post): array
    {
        Csrf::verifyRequest();
        $current = $post['current_password'] ?? '';
        $new = $post['new_password'] ?? '';
        $confirm = $post['confirm_password'] ?? '';

        if (!$this->users->verifyCurrentPassword($id, $current)) {
            return [false, 'Current password is incorrect.'];
        }
        if (strlen($new) < 8) {
            return [false, 'New password must be at least 8 characters long.'];
        }
        if ($new !== $confirm) {
            return [false, 'New password and confirmation do not match.'];
        }

        $this->users->changePassword($id, $new);
        ActivityLogger::log('password_changed', 'User changed their own password');
        return [true, 'Password updated successfully.'];
    }
}
