<?php

namespace Leaf\Auth;

use Leaf\Helpers\Authentication;
use Leaf\Helpers\Password;

/**
 * Leaf Simple Login
 * -------------------------
 * Logins made easy.
 *
 * @author Michael Darko
 * @since 3.0
 * @version 2.0.0
 */
class Login extends Session
{
    /**
     * Simple user login
     *
     * @param string table: Table to look for users
     * @param array $credentials User credentials
     * @param array $validate Validation for parameters
     *
     * @return array user: all user info + tokens + session data
     */
    public static function user(string $table, array $credentials, array $validate = [])
    {
        $passKey = static::$settings["PASSWORD_KEY"];
        $password = $credentials[$passKey] ?? null;

        if (isset($credentials[$passKey])) {
            unset($credentials[$passKey]);
        } else {
            static::$settings["AUTH_NO_PASS"] = true;
        }

        $user = static::$db->select($table)->where($credentials)->validate($validate)->fetchAssoc();
        if (!$user) {
            static::$errorsArray["auth"] = static::$settings["LOGIN_PARAMS_ERROR"];
            return null;
        }

        if (static::$settings["AUTH_NO_PASS"] === false) {
            $passwordIsValid = true;

            if (static::$settings["PASSWORD_VERIFY"] !== false && isset($user[$passKey])) {
                if (is_callable(static::$settings["PASSWORD_VERIFY"])) {
                    $passwordIsValid = call_user_func(static::$settings["PASSWORD_VERIFY"], $password, $user[$passKey]);
                } else if (static::$settings["PASSWORD_VERIFY"] === Password::MD5) {
                    $passwordIsValid = (md5($password) === $user[$passKey]);
                } else {
                    $passwordIsValid = Password::verify($password, $user[$passKey]);
                }
            }

            if (!$passwordIsValid) {
                static::$errorsArray["password"] = static::$settings["LOGIN_PASSWORD_ERROR"];
                return null;
            }
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
            if (isset($userId)) {
                $user[static::$settings["ID_KEY"]] = $userId;
            }

            static::save("AUTH_USER", $user);
            static::save("HAS_SESSION", true);

            if (static::config("SAVE_SESSION_JWT")) {
                static::save("AUTH_TOKEN", $token);
            }

            exit(header("location: " . static::config("GUARD_HOME")));
        }

        $response["user"] = $user;
        $response["token"] = $token;

        return $response;
    }
}
