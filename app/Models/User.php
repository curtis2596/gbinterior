<?php

namespace App\Models;

use App\Acl\AccessControl;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected static $model_cache_key = "User";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
        'dont_send_email',
        'is_active',
    ];

    protected static $tableInfo = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'display_name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'used_in_other_table_created_by' => 'array',
        'used_in_other_table_updated_by' => 'array',
    ];

    public array $child_model_class = [
        UserRole::class => [
            "foreignKey" => "user_id",
            "preventDelete" => false,
            "label" => "User's Role"
        ],
    ];

    protected function getDisplayNameAttribute()
    {
        return $this->name;
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function userRole()
    {
        return $this->hasMany(UserRole::class, 'user_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, UserRole::class);
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {

            if (Schema::hasColumn($model->getTable(), "created_by")) {
                $auth_user = Auth::user();
                if ($auth_user) {
                    $model->created_by = $auth_user->id;
                }
            }
        });

        self::created(function ($model) {
            static::forgotCache();
        });

        self::updating(function ($model) {

            if (Schema::hasColumn($model->getTable(), "updated_by")) {
                $auth_user = Auth::user();
                if ($auth_user) {
                    $model->updated_by = $auth_user->id;
                }
            }
        });

        self::updated(function ($model) {
            static::forgotCache();
        });

        self::deleting(function ($model) {});

        self::deleted(function ($model) {
            static::forgotCache();
        });
    }
    public static function tableHaveField($table_name, $field)
    {
        if (!isset(static::$tableInfo[$table_name])) {
            static::setTableInfo($table_name);
        }

        $info = static::$tableInfo[$table_name];

        if (isset($info["columns"][$field])) {
            return true;
        }

        return false;
    }
    public static function getTableInfo($table_name)
    {
        if (!isset(static::$tableInfo[$table_name])) {
            static::setTableInfo($table_name);
        }

        return static::$tableInfo[$table_name];
    }

    public static function setTableInfo($table_name)
    {
        $cache_key = "table-info-" . $table_name;

        $table_info = Cache::get($cache_key);

        if (!$table_info) {
            $table_info = [
                "columns" => []
            ];

            $columns = DB::select('SHOW COLUMNS FROM ' . $table_name);

            foreach ($columns as $column) {
                $table_info['columns'][$column->Field] = (array) $column;
            }

            Cache::put($cache_key, $table_info);
        }

        static::$tableInfo[$table_name] = $table_info;
    }
    public static function addCache($cache_key, $data)
    {
        Cache::put($cache_key, $data, laravel_constant("cache_time.model"));

        $list_of_cache_keys = [];
        if (Cache::has(static::$model_cache_key)) {
            $list_of_cache_keys = Cache::get(static::$model_cache_key);
        }

        if (!in_array($cache_key, $list_of_cache_keys)) {
            $list_of_cache_keys[] = $cache_key;
        }

        Cache::put(static::$model_cache_key, $list_of_cache_keys, laravel_constant("cache_time.model"));
    }

    public static function forgotCache()
    {
        if (static::$model_cache_key && Cache::has(static::$model_cache_key)) {
            $list_of_cache_keys = Cache::get(static::$model_cache_key);

            foreach ($list_of_cache_keys as $cache_key) {
                Cache::forget($cache_key);
            }

            Cache::forget(static::$model_cache_key);
        }
    }

    public function assignRoles(array $new_role_id_list)
    {
        $user_id = $this->id;

        $roles = Role::find($new_role_id_list);

        $this->roles()->sync($roles);

        $accessControl = AccessControl::init();
        $accessControl->clearMenuCache([$user_id]);
    }

    public function getRolesList()
    {
        $list = [];

        foreach ($this->roles as $role) {
            $arr = $role->toArray();
            $list[$arr['id']] = $arr['display_name'];
        }

        return $list;
    }


    public function activate()
    {
        $this->is_active = 1;
        $this->save();
    }

    public function deActivate()
    {
        $auth_user = Auth::user();

        if ($auth_user && $auth_user->id == $this->id) {
            throw new \Exception("You can not de-activate your self");
        }

        $this->is_active = 0;
        $this->save();
    }

    public static function fetchList(Builder $builder, String $key_field, String $value_field, String $table_name)
    {
        if (Schema::hasColumn($table_name, $key_field) && Schema::hasColumn($table_name, $value_field)) {
            return $builder->pluck($value_field, $key_field)->toArray();
        }

        $records = $builder->get();

        $list = [];

        foreach ($records as $record) {
            if ($value_field == "display_name_with_id") {
                $list[$record->{$key_field}] = $record->display_name . "-" . $record->id;
            } else {
                $list[$record->{$key_field}] = $record->{$value_field};
            }
        }

        return $list;
    }

    public static function getList(String $key_field = "id", String $value_field = "display_name", $conditions = [], $order_by = "name", $order_dir = "asc")
    {
        $builder = static::query();

        $model = new static();
        $table_name = $model->getTable();

        $builder->orWhere(function ($query) use ($conditions) {
            if (isset($conditions['or_id'])) {
                $v = $conditions['or_id'];

                if (is_array($v)) {
                    if (!empty($v)) {
                        $query->whereIn("id", $v);
                    }
                } else {
                    $query->where("id", $v);
                }
            }
        });

        unset($conditions['or_id']);

        if (static::tableHaveField($table_name, 'is_active')) {
            if (!isset($conditions['is_active'])) {
                $conditions['is_active'] = 1;
            }
        }

        $builder->orWhere(function ($query) use ($conditions) {
            foreach ($conditions as $k => $v) {
                if (is_array($v)) {
                    if (!empty($v)) {
                        $query->whereIn($k, $v);
                    }
                } else {
                    $query->where($k, $v);
                }
            }
        });

        if ($order_by && $order_dir) {
            $builder->orderBy($order_by, $order_dir);
        }

        $list = static::fetchList($builder, $key_field, $value_field, $table_name);

        return $list;
    }

    public static function getListCache(String $key_field = "id", String $value_field = "display_name_with_id", $order_by = "name", $order_dir = "ASC")
    {
        if (!static::$model_cache_key) {
            throw_exception("model_cache_key is not set in Model");
        }

        $cache_key = static::$model_cache_key . "-" . $key_field . "-" . $value_field;
        //d($cache_key);

        if (Cache::has($cache_key)) {
            return Cache::get($cache_key);
        }

        $builder = static::query();

        $model = new static();
        $table_name = $model->getTable();

        if ($order_by && $order_dir) {
            $builder->orderBy($order_by, $order_dir);
        }

        $list = static::fetchList($builder, $key_field, $value_field, $table_name);

        self::addCache($cache_key, $list);

        return $list;
    }

    private function _updateUsageJson($json, $table, $label, $counter, $extra = [])
    {
        if (!is_array($json)) {
            $json = [];
        }

        $is_found = false;
        foreach ($json as $k => $arr) {
            if (isset($arr['table']) && $arr['table'] == $table) {
                if (!isset($arr['counter'])) {
                    $arr['counter'] = 0;
                }

                $arr['counter'] += $counter;

                if ($arr['counter'] < 0) {
                    $arr['counter'] = 0;
                }

                $arr = array_merge($arr, $extra);

                $json[$k] = $arr;

                $is_found = true;
            }
        }

        if (!$is_found && $counter >= 0) {
            $arr = array_merge([
                "table" => $table,
                "label" => $label,
                "counter" => $counter
            ], $extra);

            $json[] = $arr;
        }

        return $json;
    }

    public function changeCreatedByUsage(BaseModel $model, $counter)
    {
        $json = $this->used_in_other_table_created_by;

        $this->used_in_other_table_created_by = $this->_updateUsageJson($json, $model->getTable(), $model->getClassDisplayName(), $counter, [
            "last_id" => $model->id
        ]);

        $this->save();
    }

    public function changeCreatedByUsageBulk(array $models)
    {
        $json = $this->used_in_other_table_created_by;

        foreach ($models as $model) {
            if ($model instanceof Model) {
                $this->used_in_other_table_created_by = $this->_updateUsageJson($json, $model->getTable(), $model->getClassDisplayName(), 1, [
                    "last_id" => $model->id
                ]);
            }
        }

        $this->save();
    }

    public function changeUpdatedByUsage(BaseModel $model, $counter)
    {
        $json = $this->_parseJsonBeforeUpdateByUsage($model, $this->used_in_other_table_updated_by);

        $this->used_in_other_table_updated_by = $this->_updateUsageJson($json, $model->getTable(), $model->getClassDisplayName(), $counter, [
            "last_id" => $model->id
        ]);

        $this->save();
    }

    private function _parseJsonBeforeUpdateByUsage(BaseModel $model, $json)
    {
        if ($json && is_array($json)) {
            $model_table = $model->getTable();

            foreach ($json as $k => $arr) {
                if (isset($arr['table']) && $arr['table'] == $model_table) {
                    if (isset($arr['last_id']) && $arr['last_id']) {
                        if ($arr['last_id'] == $model->id) {
                            $arr['counter'] -= 1;
                        }
                    }
                }

                $json[$k] = $arr;
            }
        }

        return $json;
    }

    public function changeUpdatedByUsageBulk(array $models)
    {
        $json = $this->used_in_other_table_updated_by;

        foreach ($models as $model) {
            if ($model instanceof Model) {
                $json = $this->_parseJsonBeforeUpdateByUsage($model, $json);
                $this->used_in_other_table_updated_by = $this->_updateUsageJson($json, $model->getTable(), $model->getClassDisplayName(), 1, [
                    "last_id" => $model->id
                ]);
            }
        }

        $this->save();
    }

    public function isAdmin()
    {
        $roles = $this->userRole()->pluck("role_id")->toArray();

        if ($roles) {
            $c = Role::whereIn("id", $roles)->where("is_admin", 1)->count();

            if ($c > 0) {
                return true;
            }
        }

        return false;
    }
}
