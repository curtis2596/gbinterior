<?php

namespace App\Http\Controllers;

use App\Helpers\FileUtility;
use App\Models\SqlLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

abstract class Controller
{
    protected function deleteCascade(Model $model)
    {
        $class_name = get_class($model);

        $class_display_name = "";
        if (method_exists($model, 'getClassDisplayName'))
        {
            $class_display_name = $model->getClassDisplayName();
        }
        else
        {
            throw_exception("Please Declare getClassDisplayName() method in $class_name");
        }

        if ( !isset($model->child_model_class))
        {
            throw_exception("child_model_class Array is not set in $class_name");
        }

        foreach($model->child_model_class as $childClass => $arr)
        {
            $child_class_display_name = null;

            if (get_parent_class($childClass) == "BaseModel")
            {
                $child_class_display_name = $childClass::classDisplayName();
            }
 
            if (isset($arr['label']) && !empty($arr['label']) && is_string($arr['label']))
            {
                $child_class_display_name = $arr['label'];
            }

            if (!$child_class_display_name)
            {
                throw_exception("Please Add label in child_model_class of $class_name");
            }
            
            array_check_key_and_throw_error($arr, ["preventDelete", "foreignKey"], "$class_name - child_model_class : {key} not found");
            array_check_value_and_throw_error($arr, ["foreignKey"], "$class_name - child_model_class : {key} is empty");

            if (is_array($arr['foreignKey']))
            {
                $foreign_fields = $arr['foreignKey'];
            }
            else
            {
                $foreign_fields = [$arr['foreignKey']];
            }

            if ($arr['preventDelete'])
            {
                foreach($foreign_fields as $foreign_field)
                {
                    $count = $childClass::where($foreign_field, $model->id)->count();

                    if ($count > 0)
                    {
                        throw new \Exception("Record has associated data in $child_class_display_name. can't delete");
                    }
                }
            }
            else
            {
                foreach($foreign_fields as $foreign_field)
                {
                    $child_records = $childClass::where($foreign_field, $model->id)->get();

                    if ($child_records->count() > 0)
                    {
                        foreach($child_records as $child_record)
                        {
                            $this->deleteCascade($child_record);
                        }
                    }
                }
            }
        }

        if ( !$model->delete() )
        {
            throw_exception("Fail to delete record of $class_display_name");
        }
    }
    

    protected function saveSqlLog()
    {
        if (App::environment('production'))
        {
            return false;
        }

        $db_logs = DB::getQueryLog();

        if (count($db_logs) == 0)
        {
            return false;
        }

        $sqlLogModel = new SqlLog();

        $sqlLogModel->route_name_or_url = Route::getCurrentRoute()->getName();

        if (!$sqlLogModel->route_name_or_url)
        {
            $sqlLogModel->route_name_or_url = request()->getRequestUri();
        }

        $sqlLogModel->have_dml_query = false;
        $sqlLogModel->have_heavy_query = false;

        if ( !$sqlLogModel->saveQuietly() )
        {
            throw_exception("Fail to Save Sql Log");
        }

        $sql_list = $dml_sql_list = [
            implode(",", ["Query", "Time-In-MilliSeconds"])
        ];

        foreach ($db_logs as $row)
        {
            $is_dml = false;

            $query = $row['query'];

            $query = vsprintf(str_replace('?', "'%s'", $query), $row['bindings']);

            $query = trim(preg_replace('/\s+/', ' ', $query));

            $sql_row = implode(", ", [$query, $row["time"]]);

            $sql_list[] = $sql_row;

            $query_first_10_chars = substr($query, 0, 10);
            if (!$is_dml)
            {
                $is_dml = strpos($query_first_10_chars, "INSERT") !== false;
            }

            if (!$is_dml)
            {
                $is_dml = strpos($query_first_10_chars, "insert") !== false;
            }

            if (!$is_dml)
            {
                $is_dml = strpos($query_first_10_chars, "UPDATE") !== false;
            }

            if (!$is_dml)
            {
                $is_dml = strpos($query_first_10_chars, "update") !== false;
            }

            if (!$is_dml)
            {
                $is_dml = strpos($query_first_10_chars, "DELETE") !== false;
            }

            if (!$is_dml)
            {
                $is_dml = strpos($query_first_10_chars, "delete") !== false;
            }

            if ($is_dml)
            {
                $sqlLogModel->have_dml_query = true;

                $dml_sql_list[] = $sql_row;
            }

            if ($row["time"] > 1000)
            {
                $sqlLogModel->have_heavy_query = true;
            }
        }

        $path = SqlLog::getFileSavePath() . $sqlLogModel->id . "/";
        FileUtility::createFolder($path);

        $sqlLogModel->sql_log_file = $path . "sql.txt";

        $content = implode(PHP_EOL, $sql_list);
        file_put_contents($sqlLogModel->sql_log_file, $content);

        if ($sqlLogModel->have_dml_query)
        {
            $sqlLogModel->sql_dml_log_file = $path . "sql_dml.txt";
            $content = implode(PHP_EOL, $dml_sql_list);
            file_put_contents($sqlLogModel->sql_dml_log_file, $content);
        }

        if ( !$sqlLogModel->saveQuietly() )
        {
            throw_exception("Fail to Save Sql Log");
        }

        return true;
    }

    protected function getQueryLog()
    {
        $db_logs = DB::getQueryLog();

        foreach ($db_logs as $k => $row)
        {
            $query = $row['query'];

            $query = vsprintf(str_replace('?', "'%s'", $query), $row['bindings']);

            $row['query'] = trim(preg_replace('/\s+/', ' ', $query));

            unset($row['bindings']);

            $db_logs[$k] = $row;
        }

        return $db_logs;
    }

    protected function responseJson(Array $response)
    {
        $this->saveSqlLog();

        return response()->json($response);
    }
}
