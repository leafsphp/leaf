<?php

namespace Leaf\Auth;

use Leaf\Date;
use Leaf\Helpers\Authentication;
use Leaf\Helpers\Password;

/**
 * Leaf Simple Register
 * -------------------------
 * Registration made easy.
 *
 * @author Michael Darko
 * @since 3.0
 * @version 2.0.0
 */
class Register extends Session
{
    /**
     * Simple user registration
     *
     * @param string $table Table to store user in
     * @param array $credentials Information for new user
     * @param array $uniques Parameters which should be unique
     * @param array $validate Validation for parameters
     *
     * @return array user: all user info + tokens + session data
     */
    public static function user(string $table, array $credentials, array $uniques = [], array $validate = [])
    {
        $passKey = static::$settings["PASSWORD_KEY"];

        if (!isset($credentials[$passKey])) {
            static::$settings["AUTH_NO_PASS"] = true;
        }

        if (static::$settings["AUTH_NO_PASS"] === false) {
            if (static::$settings["PASSWORD_ENCODE"] !== false) {
                if (is_callable(static::$settings["PASSWORD_ENCODE"])) {
                    $credentials[$passKey] = call_user_func(static::$settings["PASSWORD_ENCODE"], $credentials[$passKey]);
                } else if (static::$settings["PASSWORD_ENCODE"] === "md5") {
                    $credentials[$passKey] = md5($credentials[$passKey]);
                } else {
                    $credentials[$passKey] = Password::hash($credentials[$passKey]);
                }
            }
        }

        if (static::$settings["USE_TIMESTAMPS"]) {
            $now = Date::now();
            $credentials["created_at"] = $now;
            $credentials["updated_at"] = $now;
        }

        if (static::$settings["USE_UUID"] !== false) {
            $credentials[static::$settings["ID_KEY"]] = static::$settings["USE_UUID"];
        }

        try {
            $query = static::$db->insert($table)->params($credentials)->unique($uniques)->validate($validate)->execute();
        } catch (\Throwable $th) {
            trigger_error($th->getMessage());
        }

        if (!$query) {
            static::$errorsArray = array_merge(static::$errorsArray, static::$db->errors());
            return null;
        }

        $user = static::$db->select($table)->where($credentials)->validate($validate)->fetchAssoc();

        if (!$user) {
            static::$errorsArray = array_merge(static::$errorsArray, static::$db->errors());
            return null;
        }

        $token = Authentication::generateSimpleToken(
            $user[static::$settings["ID_KEY"]],
            static::config("TOKEN_SECRET"),
            static::config("TOKEN_LIFETIME")
        );

        if (isset($user[static::$settings["ID_KEY"]])) {
            $userId = $user[static::$settings["ID_KEY"]];
        }

        if (static::$settings["HIDE_ID"]) {
            unset($user[static::$settings["ID_KEY"]]);
        }

        if (static::$settings["HIDE_PASSWORD"] && (isset($user[$passKey]) || !$user[$passKey])) {
            unset($user[$passKey]);
        }

        if (!$token) {
            static::$errorsArray = array_merge(static::$errorsArray, Authentication::errors());
            return null;
        }

        if (static::config("USE_SESSION")) {
            if (static::config("SESSION_ON_REGISTER")) {
                if (isset($userId)) {
                    $user[static::$settings["ID_KEY"]] = $userId;
                }

                static::save("AUTH_USER", $user);
                static::save("HAS_SESSION", true);

                if (static::config("SAVE_SESSION_JWT")) {
                    static::save("AUTH_TOKEN", $token);
                }

                exit(header("location: " . static::config("GUARD_HOME")));
            } else {
                exit(header("location: " . static::config("GUARD_LOGIN")));
            }
        }

        $response["user"] = $user;
        $response["token"] = $token;

        return $response;
    }
}
