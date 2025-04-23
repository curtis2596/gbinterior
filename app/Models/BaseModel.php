<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BaseModel extends Model
{
    /**
     * name of table fields which uniquly identify the record
     */
    protected static array $unique_fields = [];

    /**
     * set extra relationship array to overcome problem of accidential delete
     * this variable used in Controller.php -> delete()
     */
    public array $child_model_class = [];

    protected $guarded = ["id"];

    private static $tableInfo = [];

    public static function classDisplayName() : string
    {
        $class_name = str_class_name_to_human_text(static::class);
        
        return $class_name;
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {

            if (static::tableHaveField($model->getTable(), 'created_by')) {
                $auth_user = Auth::user();
                if ($auth_user) {
                    $model->created_by = $auth_user->id;
                }
            }
        });

        self::created(function ($model) {
            $model->forgotCache();
        });

        self::updating(function ($model) {

            if (static::tableHaveField($model->getTable(), 'updated_by')) {
                $auth_user = Auth::user();
                if ($auth_user) {
                    $model->updated_by = $auth_user->id;                    
                }
            }
        });

        self::updated(function ($model) {
            $model->forgotCache();
            
        });

        self::deleting(function ($model) {

        });

        self::deleted(function ($model) {
            $model->forgotCache();           
        });
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

    public static function tableHaveField($table_name, $field)
    {
        if (!isset(static::$tableInfo[$table_name])) {
            static::setTableInfo($table_name);
        }

        $info = static::$tableInfo[$table_name];

        if (isset($info["columns"][$field]))
        {
            return true;
        }

        return false;
    }
    
    public static function getModelCacheKey() : string
    {
        $str = get_called_class();
        $arr = explode("\\", $str);
        $str = end($arr);
        $str = trim(preg_replace('/(?<!\ )[A-Z]/', ' $0', $str));

        $key = get_cache_prefix() . "-" . $str;

        return  static::optimizeCacheKey($key);
    }

    public static function optimizeCacheKey($key) : string
    {
        $key = str_replace(".", "-", $key);

        $key = preg_replace('!\s+!', '-', $key);

        $key = trim($key, "-");
        
        $key = trim($key);

        return $key;
    }

    public static function getCache($key)
    {
        $model_cache_key = static::getModelCacheKey();

        $cache_key = $model_cache_key . "-" . $key;

        if (Cache::has($cache_key))
        {
            return Cache::get($cache_key);
        }

        return null;
    }

    public static function addCache($key, $data)
    {
        $model_cache_key = static::getModelCacheKey();

        $cache_key = $model_cache_key . "-" . static::optimizeCacheKey($key);

        //put cache
        Cache::put($cache_key, $data, laravel_constant("cache_time.model"));

        //make refrence to cache keys 
        $list_of_cache_keys = [];
        if (Cache::has($model_cache_key))
        {
            $list_of_cache_keys = Cache::get($list_of_cache_keys);
        }

        if (!in_array($cache_key, $list_of_cache_keys))
        {
            $list_of_cache_keys[] = $cache_key;
        }

        if (!Cache::put($model_cache_key, $list_of_cache_keys, laravel_constant("cache_time.model")))
        {
            throw_exception("Fail To Put Cache");
        }
    }

    protected function forgotCache()
    {
        $model_cache_key = static::getModelCacheKey();
        
        if (Cache::has($model_cache_key))
        {
            $list_of_cache_keys = Cache::get($model_cache_key);

            foreach($list_of_cache_keys as $cache_key)
            {
                Cache::forget($cache_key);
            }

            Cache::forget($model_cache_key);
        }
    }

    public static function parseValueBeforeUseInDBQuery($field, $value)
    {
        return $value;
    }

    public static function fetchList(Builder $builder, String $key_field, String $value_field, String $table_name)
    {    
        if (self::tableHaveField($table_name, $key_field) && self::tableHaveField($table_name, $value_field))   
        {
            return $builder->pluck($value_field, $key_field)->toArray();
        }
        
        $records = $builder->get();

        // d($value_field); dd($records->toArray());

        $list = [];
        
        foreach($records as $record)
        {
            $arr = $record->toArray();     
            // dd($arr);       
            $list[$arr[$key_field]] = $arr[$value_field];
        }
        return $list;
    }
    // protected static function fetchList(Builder $builder, String $id, String $value)
    // {
    //     if (!$id)
    //     {
    //         $id = "id";
    //     }

    //     if (!$value)
    //     {
    //         $value = "display_name";
    //     }

    //     $model = new static();
    //     if (Schema::hasColumn($model->getTable(), $id) && Schema::hasColumn($model->getTable(), $value))
    //     {
    //         return $builder->pluck($value, $id)->toArray();
    //     }

    //     $records = $builder->get();

    //     $list = [];

    //     foreach($records as $record)
    //     {
    //         if ($value == "display_name" && method_exists($record, 'getDisplayName'))
    //         {
    //             $list[$record->{$id}] = $record->getDisplayName();
    //         }
    //         else
    //         {
    //             $list[$record->{$id}] = $record->{$value};
    //         }
    //     }

    //     return $list;
    // }

    public static function getList(String $key_field = "id", String $value_field = "display_name", $conditions = [], $order_by = "name", $order_dir = "asc")
    {
        $builder = static::query();

        $model = new static();
        $table_name = $model->getTable();

        $builder->orWhere(function ($query) use ($conditions) {
            if (isset($conditions['or_id']))
            {
                $v = $conditions['or_id'];

                if (is_array($v))
                {
                    if (!empty($v))
                    {
                        $query->whereIn("id", $v);
                    }
                }
                else
                {
                    $query->where("id", $v);
                }
            }
        });

        unset($conditions['or_id']);

        if (static::tableHaveField($table_name, 'is_active'))
        {
            if (!isset($conditions['is_active']))
            {
                $conditions['is_active'] = 1;
            }
        }

        $builder->orWhere(function ($query) use ($conditions) {
            foreach($conditions as $k => $v)
            {
                if (is_array($v))
                {
                    if (!empty($v))
                    {
                        $query->whereIn($k, $v);
                    }
                }
                else
                {
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

    public static function getListCache(String $id = "id", String $value = "display_name", $order_by = "name", $order_dir = "ASC")
    {
        $key = "list-" . $id . "-" . $value;

        $list = static::getCache($key);

        if ($list)
        {
            return $list;
        }

        $builder = static::query();

        if ($order_by && $order_dir)
        {
            $builder->orderBy($order_by, $order_dir);
        }

        $model = new static();
        $table_name = $model->getTable();

        $list = static::fetchList($builder, $id, $value, $table_name);

        self::addCache($key, $list);

        return $list;
    }

    /*------------------------------------------------------------------*/
    /**------------------------Member Functions ----------------------- */
    /*------------------------------------------------------------------*/

    public function hasAttribute($key)
    {
        return array_key_exists($key, $this->attributes);
    }

    public function getClassDisplayName()
    {
        $class_name = static::classDisplayName();
        
        return $class_name;
    }

    public function getFilePath()
    {
        return "files/" . str_class_name_without_namespace(get_class($this)) . "/";
    }

    public function getFilePathForRecord()
    {
        return $this->getFilePath() . $this->id . "/";
    }

    public function getNextId()
    {
        $table = $this->getTable();
        $statement = DB::select("SHOW TABLE STATUS LIKE '" . $table . "'");
        $nextId = $statement[0]->Auto_increment;

        return $nextId;
    }

    public function insertOrUpdate(array $data, &$is_insert = null, &$is_update = null)
    {
        $model = $this->getUniqueId($data);

        if ($model) {
            $model->fill($data);
            if ($model->isDirty())
            {
                if ( $model->save() )
                {
                    $is_update = true;
                }
                else
                {
                    $is_update = false;
                    throw new \Exception("Fail To Save");
                }
            }
        } else {
            $model = static::create($data);

            if ($model)
            {
                $is_insert = true;
            }
            else
            {
                $is_insert = false;
                return null;
            }
        }

        return $model->id;
    }

    public function insertIgnoreIfExist(array $data)
    {
        $record = $this->getUniqueId($data);

        if (!$record) {
            $record = static::create($data);
        }

        return $record->id;
    }

    public function getUniqueId(array $data)
    {
        if (!static::$unique_fields) {
            throw_exception("unique_fields is not set yet");
        }

        $conditions = [];
        foreach (static::$unique_fields as $unique_field) {
            if (!isset($data[$unique_field])) {
                throw_exception("field $unique_field missing in argument array");
            }

            $conditions[] = [$unique_field, '=', self::parseValueBeforeUseInDBQuery($unique_field, $data[$unique_field])];
        }

        $count = static::where($conditions)->count();

        if ($count == 0) {
            return false;
        }

        if ($count > 1) {
            throw_exception("more than 1 records found");
        }

        $record = static::where($conditions)->first(["id"]);

        return $record;
    }

    public function activate()
    {
        $this->is_active = 1;
        $this->save();
    }

    public function deActivate()
    {
        $this->is_active = 0;
        $this->save();
    }
}
