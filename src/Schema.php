<?php

namespace Leaf;

class Schema
{
    /**
     * @param \Illuminate\Database\Capsule\Manager $capsule
     * @param string $table The name of table to manipulate
     * @param string|null $schema The JSON schema for database
     */
    public static function build(\Illuminate\Database\Capsule\Manager $capsule, string $table, string $schema = null)
    {
        if (file_exists($table)) {
            $schema = json_decode(file_get_contents($table));
            $table = basename($table);
            $table = str_replace(".json", "", $table);
        } else {
            $schema = json_decode($schema);
        }

        if (is_array($schema)) {
            $schema = $schema[0];
        }

        if (!$capsule::schema()->hasTable($table)) {
            $capsule::schema()->create($table, function ($table) use ($schema) {
                foreach ($schema as $key => $value) {
                    if ($key == "id" || $key == "_id") {
                        $table->increments($key);
                        continue;
                    }

                    if (strpos($key, "*") === 0) {
                        $table->unsignedBigInteger(substr($key, 1));
                        continue;
                    }

                    if ($key == "timestamps") {
                        $table->timestamps();
                        continue;
                    }

                    if (
                        $key == "created_at" ||
                        $key == "updated_at" ||
                        $key == "timestamp" ||
                        strpos($key, "date") != false ||
                        strpos($key, "time") != false ||
                        strpos($key, "_at") != false
                    ) {
                        if (substr($key, -1) === "?") {
                            $table->timestamp(substr($key, 0, -1))->nullable();
                            continue;
                        }

                        $table->timestamp($key);
                        continue;
                    }

                    $type = gettype($value);

                    if ($type == "integer") {
                        if (substr($key, -1) === "?") {
                            $table->integer(substr($key, 0, -1))->nullable();
                            continue;
                        }

                        $table->integer($key);
                        continue;
                    }

                    if ($type == "string") {
                        if (strpos($value, "{") === 0 || strpos($value, "[") === 0) {
                            if (substr($key, -1) === "?") {
                                $table->json(substr($key, 0, -1))->nullable();
                                continue;
                            }

                            $table->json($key);
                            continue;
                        }

                        if ($key == "description" || $key == "text" || strlen($value) > 150) {
                            if (substr($key, -1) === "?") {
                                $table->text(substr($key, 0, -1))->nullable();
                                continue;
                            }

                            $table->text($key);
                            continue;
                        }

                        if (substr($key, -1) === "?") {
                            $table->string(substr($key, 0, -1))->nullable();
                            continue;
                        }

                        $table->string($key);
                        continue;
                    }

                    if ($type == "array") {
                        if (substr($key, -1) === "?") {
                            $table->enum(substr($key, 0, -1), $value)->nullable();
                            continue;
                        }

                        $table->enum($key, $value);
                        continue;
                    }

                    if ($type == "boolean") {
                        if (substr($key, -1) === "?") {
                            $table->boolean(substr($key, 0, -1))->nullable();
                            continue;
                        }

                        $table->boolean($key);
                    }
                }
            });
        }
    }
}
