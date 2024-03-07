<?php

namespace App\Eloquent;

use App\Models\User;
use Illuminate\Support\Arr;
use App\Core\Facades\Application;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Core\Repository\AppRepository;
use Illuminate\Support\Facades\Storage;
use App\Contracts\Repositories\UserRepository;

use function Psy\debug;

class UserEloquent extends AppRepository implements UserRepository
{
    /**
     * Searchable fields
     *
     * @var array
     */
    protected static $fieldSearchable = [
        'name' => 'like',
        'email' => 'like',
    ];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return User::class;
    }

    /**
     * Boot the repository
     *
     * @return void
     */
    public static function boot()
    {
        static::deleting(function ($model, $repository) {
            if ($model->id === Auth::id()) {
                /**
                 * User cannot delete own account
                 */
                abort(409, __('user.delete_own_account_warning'));
            }

            /**
             * Delete notifications
             */
            // $model->notifications()->delete();

            if ($model->avatar) {
                // $this->removeAvatarImage($model);
            }
        });
    }

    /**
     * Save a new entity in repository
     *
     *
     * @return mixed
     */
    public function create(array $data)
    {
        $data['password'] = Hash::make(config('core.default_password'));

        $user = parent::create($data);

        if (isset($data['notifications'])) {
            Application::updateNotificationSettings($user, $data['notifications']);
        }

        $user->assignRole($data['roles'] ?? []);

        if (isset($data['settings'])) {
            $user->settings()->create($data['settings']);
        }

        return $user;
    }

    /**
     * Create user via the installer
     */
    public function createViaInstall(array $data): User
    {
        return $this->unguarded(function ($repository) use ($data) {
            $data['super_admin'] = true;
            $data['access_api'] = true;
            $data['first_day_of_week'] = config('core.first_day_of_week');
            $data['time_format'] = config('core.time_format');
            $data['date_format'] = config('core.date_format');
            $data['timezone'] = $data['timezone'];

            return parent::create($data);
        });
    }

    /**
     * Update a entity in repository by id
     *
     * @param  int  $id
     * @return mixed
     */
    public function update(array $data, $id)
    {
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        } elseif (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user = parent::update($data, $id);

        if (isset($data['notifications'])) {
            Application::updateNotificationSettings($user, $data['notifications']);
        }

        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return $user;
    }

    /**
     * Find user by email address
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findByField('email', $email)->first();
    }

    /**
     * Store the given user avatar
     *
     *
     * @return void
     */
    public function storeAvatar(User $user, UploadedFile $file): User
    {
        $this->removeAvatarImage($user);

        return $this->update([
            'avatar' => $file->store('avatars', 'public'),
        ], $user->id);
    }

    /**
     * Delete user avatar
     */
    public function removeAvatarImage(User $user): static
    {
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        return $this;
    }

    /**
     * The relations that are required for the response
     *
     * @return array
     */
    protected function eagerLoad()
    {
        // $this->withCount('unreadNotifications');

        return [
            // 'latestFifteenNotifications',
            'settings',
            'roles.permissions',
        ];
    }
}
